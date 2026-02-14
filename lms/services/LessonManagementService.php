<?php

class LessonManagementService {
    private $pdo;
    private $videoUploadService;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        require_once 'VideoUploadService.php';
        $this->videoUploadService = new VideoUploadService();
    }
    
    public function createLesson($data, $files = []) {
        try {
            $this->pdo->beginTransaction();
            
            // Generate UUID for lesson
            $lessonId = $this->generateUUID();
            
            // Validate required fields
            $this->validateLessonData($data);
            
            // Validate course access
            $this->validateCourseAccess($data['course_id']);
            
            // Handle Vimeo ID input
            if (empty($data['vimeo_id'])) {
                throw new Exception('Vimeo ID is required');
            }
            
            $videoData = $this->videoUploadService->getVimeoEmbedData($data['vimeo_id']);
            
            // Get next lesson order
            $lessonOrder = $this->getNextLessonOrder($data['course_id']);
            if (isset($data['lesson_order']) && $data['lesson_order'] > 0) {
                $lessonOrder = $data['lesson_order'];
                $this->adjustLessonOrders($data['course_id'], $lessonOrder);
            }
            
            // Insert lesson
            $stmt = $this->pdo->prepare("
                INSERT INTO lessons (
                    id, course_id, title, lesson_order, vimeo_id,
                    vimeo_url, embed_url, video_duration, video_width, video_height,
                    visibility_status, access_permissions
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $lessonId,
                $data['course_id'],
                $data['title'],
                $lessonOrder,
                $videoData['vimeo_id'],
                $videoData['vimeo_url'],
                $videoData['embed_url'],
                $data['video_duration'] ?? $videoData['duration'],
                $videoData['width'],
                $videoData['height'],
                $data['visibility_status'] ?? 'Visible',
                $data['access_permissions'] ?? 'Enrolled Only'
            ]);
            
            // Handle supplementary materials
            if (!empty($files['materials'])) {
                $this->uploadLessonMaterials($lessonId, $files['materials']);
            }
            
            // Update course statistics
            $this->updateCourseStatistics($data['course_id']);
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'lesson_id' => $lessonId,
                'message' => 'Lesson created successfully'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception('Lesson creation failed: ' . $e->getMessage());
        }
    }
    
    public function updateLesson($lessonId, $data, $files = []) {
        try {
            $this->pdo->beginTransaction();
            
            // Validate lesson exists and user has permission
            $lesson = $this->validateLessonAccess($lessonId);
            
            // Build update query
            $updateFields = [];
            $params = [];
            
            if (isset($data['title'])) {
                $updateFields[] = 'title = ?';
                $params[] = $data['title'];
            }
            
            if (isset($data['lesson_order']) && $data['lesson_order'] != $lesson['lesson_order']) {
                $this->adjustLessonOrders($lesson['course_id'], $data['lesson_order'], $lessonId);
                $updateFields[] = 'lesson_order = ?';
                $params[] = $data['lesson_order'];
            }
            
            if (isset($data['visibility_status'])) {
                $updateFields[] = 'visibility_status = ?';
                $params[] = $data['visibility_status'];
            }
            
            if (isset($data['access_permissions'])) {
                $updateFields[] = 'access_permissions = ?';
                $params[] = $data['access_permissions'];
            }
            
            // Handle Vimeo ID update
            if (isset($data['vimeo_id']) && !empty($data['vimeo_id'])) {
                $videoData = $this->videoUploadService->getVimeoEmbedData($data['vimeo_id']);
                $updateFields[] = 'vimeo_id = ?';
                $params[] = $videoData['vimeo_id'];
                $updateFields[] = 'vimeo_url = ?';
                $params[] = $videoData['vimeo_url'];
                $updateFields[] = 'embed_url = ?';
                $params[] = $videoData['embed_url'];
                if (isset($data['video_duration'])) {
                    $updateFields[] = 'video_duration = ?';
                    $params[] = $data['video_duration'];
                }
            }
            
            if (!empty($updateFields)) {
                $params[] = $lessonId;
                $stmt = $this->pdo->prepare("
                    UPDATE lessons SET " . implode(', ', $updateFields) . " 
                    WHERE id = ?
                ");
                $stmt->execute($params);
            }
            
            // Handle new materials
            if (!empty($files['materials'])) {
                $this->uploadLessonMaterials($lessonId, $files['materials']);
            }
            
            // Update course statistics
            $this->updateCourseStatistics($lesson['course_id']);
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Lesson updated successfully'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception('Lesson update failed: ' . $e->getMessage());
        }
    }
    
    public function getLessons($courseId, $includeHidden = false) {
        $where = ['l.course_id = ?'];
        $params = [$courseId];
        
        $isStudent = isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'User';
        
        // Role-based filtering
        if ($isStudent) {
            // Students only see lessons from enrolled courses
            $where[] = "EXISTS (
                SELECT 1 FROM course_enrollments ce 
                WHERE ce.course_id = l.course_id 
                AND ce.student_id = ? 
                AND ce.status = 'Active'
            )";
            $params[] = $_SESSION['user_id'];
            
            // Students only see visible lessons
            $where[] = "l.visibility_status = 'Visible'";
        } else {
            // Admin/Instructor can see all or only visible based on parameter
            if (!$includeHidden) {
                $where[] = "l.visibility_status = 'Visible'";
            }
        }
        
        // Build analytics join condition
        $analyticsJoin = $isStudent 
            ? "LEFT JOIN lesson_analytics la ON l.id = la.lesson_id AND la.student_id = ?"
            : "LEFT JOIN lesson_analytics la ON l.id = la.lesson_id";
        
        $stmt = $this->pdo->prepare("
            SELECT l.*, 
                   COUNT(DISTINCT lm.id) as material_count,
                   COALESCE(SUM(la.view_count), 0) as student_views,
                   COALESCE(MAX(la.completion_status), 0) as is_completed
            FROM lessons l
            LEFT JOIN lesson_materials lm ON l.id = lm.lesson_id
            $analyticsJoin
            WHERE " . implode(' AND ', $where) . "
            GROUP BY l.id, l.course_id, l.title, l.lesson_order, l.vimeo_id, l.vimeo_url, 
                     l.embed_url, l.video_duration, l.video_width, l.video_height, 
                     l.visibility_status, l.access_permissions, l.created_at, l.updated_at
            ORDER BY l.lesson_order ASC
        ");
        
        // Add student_id parameter only for students
        if ($isStudent) {
            $params[] = $_SESSION['user_id'];
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getLesson($lessonId) {
        $stmt = $this->pdo->prepare("
            SELECT l.*, c.title as course_title
            FROM lessons l
            JOIN courses c ON l.course_id = c.id
            WHERE l.id = ?
        ");
        $stmt->execute([$lessonId]);
        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($lesson) {
            // Get lesson materials
            $stmt = $this->pdo->prepare("
                SELECT * FROM lesson_materials 
                WHERE lesson_id = ? 
                ORDER BY uploaded_at DESC
            ");
            $stmt->execute([$lessonId]);
            $lesson['materials'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get student analytics if student is logged in
            if ($_SESSION['role_name'] === 'Student') {
                $stmt = $this->pdo->prepare("
                    SELECT * FROM lesson_analytics 
                    WHERE lesson_id = ? AND student_id = ?
                ");
                $stmt->execute([$lessonId, $_SESSION['user_id']]);
                $lesson['analytics'] = $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        
        return $lesson;
    }
    
    public function deleteLesson($lessonId) {
        try {
            $this->pdo->beginTransaction();
            
            $lesson = $this->validateLessonAccess($lessonId);
            
            // Delete video from Vimeo
            $this->videoUploadService->deleteVideo($lesson['vimeo_id']);
            
            // Delete material files
            $stmt = $this->pdo->prepare("SELECT file_path FROM lesson_materials WHERE lesson_id = ?");
            $stmt->execute([$lessonId]);
            $materials = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($materials as $filePath) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Delete lesson (cascade will handle related records)
            $stmt = $this->pdo->prepare("DELETE FROM lessons WHERE id = ?");
            $stmt->execute([$lessonId]);
            
            // Reorder remaining lessons
            $this->reorderLessons($lesson['course_id']);
            
            // Update course statistics
            $this->updateCourseStatistics($lesson['course_id']);
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Lesson deleted successfully'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception('Lesson deletion failed: ' . $e->getMessage());
        }
    }
    
    public function trackLessonView($lessonId, $studentId, $watchData = []) {
        try {
            // Update or create lesson analytics
            $stmt = $this->pdo->prepare("
                INSERT INTO lesson_analytics (lesson_id, student_id, view_count, first_viewed_at, last_viewed_at)
                VALUES (?, ?, 1, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                view_count = view_count + 1,
                last_viewed_at = NOW()
            ");
            $stmt->execute([$lessonId, $studentId]);
            
            // Create view session
            $stmt = $this->pdo->prepare("
                INSERT INTO lesson_view_sessions (
                    lesson_id, student_id, start_position, ip_address, user_agent
                ) VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $lessonId,
                $studentId,
                $watchData['start_position'] ?? 0,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
            
            return $this->pdo->lastInsertId();
            
        } catch (Exception $e) {
            throw new Exception('Failed to track lesson view: ' . $e->getMessage());
        }
    }
    
    public function updateWatchProgress($sessionId, $watchData) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE lesson_view_sessions 
                SET session_end = NOW(),
                    watch_duration = ?,
                    end_position = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $watchData['watch_duration'] ?? 0,
                $watchData['end_position'] ?? 0,
                $sessionId
            ]);
            
            // Update analytics
            $stmt = $this->pdo->prepare("
                UPDATE lesson_analytics la
                JOIN lesson_view_sessions lvs ON la.lesson_id = lvs.lesson_id AND la.student_id = lvs.student_id
                SET la.total_time_spent = la.total_time_spent + ?,
                    la.last_watched_position = ?,
                    la.completion_status = CASE 
                        WHEN ? >= (SELECT video_duration * 0.9 FROM lessons WHERE id = la.lesson_id) 
                        THEN TRUE ELSE la.completion_status END,
                    la.completed_at = CASE 
                        WHEN ? >= (SELECT video_duration * 0.9 FROM lessons WHERE id = la.lesson_id) AND la.completed_at IS NULL
                        THEN NOW() ELSE la.completed_at END
                WHERE lvs.id = ?
            ");
            $stmt->execute([
                $watchData['watch_duration'] ?? 0,
                $watchData['end_position'] ?? 0,
                $watchData['end_position'] ?? 0,
                $watchData['end_position'] ?? 0,
                $sessionId
            ]);
            
        } catch (Exception $e) {
            throw new Exception('Failed to update watch progress: ' . $e->getMessage());
        }
    }
    
    private function validateLessonData($data) {
        if (empty($data['title'])) {
            throw new Exception('Lesson title is required');
        }
        if (empty($data['course_id'])) {
            throw new Exception('Course ID is required');
        }
    }
    
    private function validateCourseAccess($courseId) {
        if ($_SESSION['role_name'] === 'Instructor') {
            $stmt = $this->pdo->prepare("
                SELECT 1 FROM course_instructors 
                WHERE course_id = ? AND instructor_id = ?
            ");
            $stmt->execute([$courseId, $_SESSION['user_id']]);
            if (!$stmt->fetch()) {
                throw new Exception('Access denied to this course');
            }
        }
    }
    
    private function validateLessonAccess($lessonId) {
        $stmt = $this->pdo->prepare("
            SELECT l.*, c.created_by as course_creator
            FROM lessons l
            JOIN courses c ON l.course_id = c.id
            WHERE l.id = ?
        ");
        $stmt->execute([$lessonId]);
        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$lesson) {
            throw new Exception('Lesson not found');
        }
        
        if ($_SESSION['role_name'] === 'Instructor') {
            $stmt = $this->pdo->prepare("
                SELECT 1 FROM course_instructors 
                WHERE course_id = ? AND instructor_id = ?
            ");
            $stmt->execute([$lesson['course_id'], $_SESSION['user_id']]);
            if (!$stmt->fetch()) {
                throw new Exception('Access denied to this lesson');
            }
        }
        
        return $lesson;
    }
    
    private function getNextLessonOrder($courseId) {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(MAX(lesson_order), 0) + 1 
            FROM lessons WHERE course_id = ?
        ");
        $stmt->execute([$courseId]);
        return $stmt->fetchColumn();
    }
    
    private function adjustLessonOrders($courseId, $newOrder, $excludeLessonId = null) {
        $excludeClause = $excludeLessonId ? "AND id != ?" : "";
        $params = [$courseId, $newOrder];
        if ($excludeLessonId) $params[] = $excludeLessonId;
        
        $stmt = $this->pdo->prepare("
            UPDATE lessons 
            SET lesson_order = lesson_order + 1 
            WHERE course_id = ? AND lesson_order >= ? $excludeClause
        ");
        $stmt->execute($params);
    }
    
    private function reorderLessons($courseId) {
        $stmt = $this->pdo->prepare("
            SELECT id FROM lessons 
            WHERE course_id = ? 
            ORDER BY lesson_order ASC
        ");
        $stmt->execute([$courseId]);
        $lessons = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($lessons as $index => $lessonId) {
            $stmt = $this->pdo->prepare("
                UPDATE lessons SET lesson_order = ? WHERE id = ?
            ");
            $stmt->execute([$index + 1, $lessonId]);
        }
    }
    
    private function uploadLessonMaterials($lessonId, $files) {
        $uploadDir = 'uploads/materials/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        foreach ($files as $file) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'audio/mpeg', 'audio/wav'];
                if (!in_array($file['type'], $allowedTypes)) {
                    continue; // Skip invalid files
                }
                
                if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
                    continue; // Skip large files
                }
                
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO lesson_materials (lesson_id, file_name, file_path, file_type, file_size)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $lessonId,
                        $file['name'],
                        $filepath,
                        $file['type'],
                        $file['size']
                    ]);
                }
            }
        }
    }
    
    private function updateCourseStatistics($courseId) {
        $stmt = $this->pdo->prepare("
            UPDATE courses 
            SET total_lessons = (
                SELECT COUNT(*) FROM lessons WHERE course_id = ?
            ),
            total_duration_seconds = (
                SELECT COALESCE(SUM(video_duration), 0) FROM lessons WHERE course_id = ?
            )
            WHERE id = ?
        ");
        $stmt->execute([$courseId, $courseId, $courseId]);
    }
    
    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
<?php

class CourseManagementService {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function createCourse($data, $files = []) {
        try {
            $this->pdo->beginTransaction();
            
            // Generate UUID for course
            $courseId = $this->generateUUID();
            
            // Validate required fields
            $this->validateCourseData($data);
            
            // Handle thumbnail upload
            $thumbnailPath = null;
            if (isset($files['thumbnail']) && $files['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $thumbnailPath = $this->uploadFile($files['thumbnail']);
            }
            
            // Insert course
            $stmt = $this->pdo->prepare("
                INSERT INTO courses (
                    id, title, description, thumbnail_path, category_id, 
                    target_audience, skill_level, price, discount_amount,
                    validity_type, validity_months, status, created_by
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $courseId,
                $data['title'],
                $data['description'],
                $thumbnailPath,
                $data['category_id'] ?: null,
                $data['target_audience'] ?: null,
                $data['skill_level'],
                $data['price'],
                $data['discount_amount'] ?: 0.00,
                $data['validity_type'],
                $data['validity_type'] === 'Fixed Duration' ? $data['validity_months'] : null,
                $data['status'] ?: 'Draft',
                $_SESSION['user_id']
            ]);
            
            // Assign instructors
            if (!empty($data['instructors'])) {
                $this->assignInstructors($courseId, $data['instructors']);
            }
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'course_id' => $courseId,
                'message' => 'Course created successfully'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception('Course creation failed: ' . $e->getMessage());
        }
    }
    
    public function updateCourse($courseId, $data, $files = []) {
        try {
            $this->pdo->beginTransaction();
            
            // Validate course exists and user has permission
            $this->validateCourseAccess($courseId);
            
            // Handle thumbnail upload if provided
            $thumbnailPath = null;
            if (isset($files['thumbnail']) && $files['thumbnail']['error'] === UPLOAD_ERR_OK) {
                $thumbnailPath = $this->uploadFile($files['thumbnail']);
            }
            
            // Build update query
            $updateFields = [];
            $params = [];
            
            if (isset($data['title'])) {
                $updateFields[] = 'title = ?';
                $params[] = $data['title'];
            }
            if (isset($data['description'])) {
                $updateFields[] = 'description = ?';
                $params[] = $data['description'];
            }
            if ($thumbnailPath) {
                $updateFields[] = 'thumbnail_path = ?';
                $params[] = $thumbnailPath;
            }
            if (isset($data['category_id'])) {
                $updateFields[] = 'category_id = ?';
                $params[] = $data['category_id'] ?: null;
            }
            if (isset($data['skill_level'])) {
                $updateFields[] = 'skill_level = ?';
                $params[] = $data['skill_level'];
            }
            if (isset($data['price'])) {
                $updateFields[] = 'price = ?';
                $params[] = $data['price'];
            }
            if (isset($data['status'])) {
                $updateFields[] = 'status = ?';
                $params[] = $data['status'];
            }
            
            if (!empty($updateFields)) {
                $params[] = $courseId;
                $stmt = $this->pdo->prepare("
                    UPDATE courses SET " . implode(', ', $updateFields) . " 
                    WHERE id = ?
                ");
                $stmt->execute($params);
            }
            
            // Update instructors if provided
            if (isset($data['instructors'])) {
                $this->updateInstructors($courseId, $data['instructors']);
            }
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Course updated successfully'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception('Course update failed: ' . $e->getMessage());
        }
    }
    
    public function getCourses($filters = []) {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = 'c.status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['category_id'])) {
            $where[] = 'c.category_id = ?';
            $params[] = $filters['category_id'];
        }
        
        // Role-based filtering
        if ($_SESSION['role_name'] === 'Instructor') {
            $where[] = 'ci.instructor_id = ?';
            $params[] = $_SESSION['user_id'];
        }
        
        $stmt = $this->pdo->prepare("
            SELECT c.*, cc.name as category_name,
                   GROUP_CONCAT(u.email) as instructors
            FROM courses c
            LEFT JOIN course_categories cc ON c.category_id = cc.id
            LEFT JOIN course_instructors ci ON c.id = ci.course_id
            LEFT JOIN users u ON ci.instructor_id = u.id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY c.id
            ORDER BY c.created_at DESC
        ");
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getCourse($courseId) {
        $stmt = $this->pdo->prepare("
            SELECT c.*, cc.name as category_name
            FROM courses c
            LEFT JOIN course_categories cc ON c.category_id = cc.id
            WHERE c.id = ?
        ");
        $stmt->execute([$courseId]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($course) {
            // Get assigned instructors
            $stmt = $this->pdo->prepare("
                SELECT u.id, u.email
                FROM course_instructors ci
                JOIN users u ON ci.instructor_id = u.id
                WHERE ci.course_id = ?
            ");
            $stmt->execute([$courseId]);
            $course['instructors'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $course;
    }
    
    public function getCategories() {
        $stmt = $this->pdo->query("SELECT * FROM course_categories ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getInstructors() {
        $stmt = $this->pdo->prepare("
            SELECT u.id, u.email
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE r.name = 'Instructor'
            ORDER BY u.email
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function validateCourseData($data) {
        if (empty($data['title'])) {
            throw new Exception('Course title is required');
        }
        if (empty($data['description']) || strlen($data['description']) < 50) {
            throw new Exception('Course description must be at least 50 characters');
        }
        if (empty($data['skill_level'])) {
            throw new Exception('Skill level is required');
        }
        if (!isset($data['price']) || $data['price'] < 0) {
            throw new Exception('Valid price is required');
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
    
    private function assignInstructors($courseId, $instructorIds) {
        foreach ($instructorIds as $instructorId) {
            $stmt = $this->pdo->prepare("
                INSERT IGNORE INTO course_instructors (course_id, instructor_id)
                VALUES (?, ?)
            ");
            $stmt->execute([$courseId, $instructorId]);
        }
    }
    
    private function updateInstructors($courseId, $instructorIds) {
        // Remove existing assignments
        $stmt = $this->pdo->prepare("DELETE FROM course_instructors WHERE course_id = ?");
        $stmt->execute([$courseId]);
        
        // Add new assignments
        $this->assignInstructors($courseId, $instructorIds);
    }
    
    private function uploadFile($file) {
        $uploadDir = 'uploads/thumbnails/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Invalid file type. Only JPEG and PNG allowed.');
        }
        
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new Exception('File too large. Maximum 2MB allowed.');
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Failed to upload file.');
        }
        
        return $filepath;
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
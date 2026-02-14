<?php
session_start();
require_once '../config.php';
require_once '../services/VideoUploadService.php';
require_once '../services/LessonManagementService.php';

// Disable output buffering for streaming
if (ob_get_level()) {
    ob_end_clean();
}

// Get parameters
$lessonId = $_GET['lesson'] ?? '';
$token = $_GET['token'] ?? '';

if (!$lessonId || !$token) {
    http_response_code(400);
    die('Missing parameters');
}

try {
    $videoService = new VideoUploadService();
    $lessonService = new LessonManagementService($pdo);
    
    // Validate token
    $tokenData = $videoService->validateStreamToken($token);
    if (!$tokenData || $tokenData['lesson_id'] !== $lessonId) {
        http_response_code(403);
        die('Invalid or expired token');
    }
    
    // Get lesson details
    $lesson = $lessonService->getLesson($lessonId);
    if (!$lesson) {
        http_response_code(404);
        die('Lesson not found');
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        die('Authentication required');
    }
    
    // Verify access permissions
    $hasAccess = false;
    
    if ($_SESSION['role_name'] === 'Admin') {
        $hasAccess = true;
    } elseif ($_SESSION['role_name'] === 'Instructor') {
        // Check if instructor is assigned to the course
        $stmt = $pdo->prepare("
            SELECT 1 FROM course_instructors ci
            JOIN lessons l ON ci.course_id = l.course_id
            WHERE l.id = ? AND ci.instructor_id = ?
        ");
        $stmt->execute([$lessonId, $_SESSION['user_id']]);
        $hasAccess = $stmt->fetch() !== false;
    } elseif ($_SESSION['role_name'] === 'Student') {
        if ($lesson['access_permissions'] === 'Free Preview') {
            $hasAccess = true;
        } else {
            // Check if student is enrolled in the course
            $stmt = $pdo->prepare("
                SELECT 1 FROM course_enrollments ce
                JOIN lessons l ON ce.course_id = l.course_id
                WHERE l.id = ? AND ce.student_id = ? AND ce.status = 'Active'
            ");
            $stmt->execute([$lessonId, $_SESSION['user_id']]);
            $hasAccess = $stmt->fetch() !== false;
        }
    }
    
    if (!$hasAccess) {
        http_response_code(403);
        die('Access denied');
    }
    
    // Check if lesson is visible (unless admin/instructor)
    if (!in_array($_SESSION['role_name'], ['Admin', 'Instructor']) && $lesson['visibility_status'] !== 'Visible') {
        http_response_code(403);
        die('Lesson not available');
    }
    
    // Track lesson view for students
    if ($_SESSION['role_name'] === 'Student') {
        $sessionId = $lessonService->trackLessonView($lessonId, $_SESSION['user_id']);
        
        // Store session ID in session for progress tracking
        $_SESSION['current_lesson_session'] = $sessionId;
    }
    
    // Get video file path
    $videoPath = $lesson['video_file_path'];
    if (!file_exists($videoPath)) {
        http_response_code(404);
        die('Video file not found');
    }
    
    // Get file info
    $fileSize = filesize($videoPath);
    $mimeType = mime_content_type($videoPath);
    
    // Handle range requests for video streaming
    $start = 0;
    $end = $fileSize - 1;
    
    if (isset($_SERVER['HTTP_RANGE'])) {
        $range = $_SERVER['HTTP_RANGE'];
        if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
            $start = intval($matches[1]);
            if (!empty($matches[2])) {
                $end = intval($matches[2]);
            }
        }
    }
    
    // Validate range
    if ($start > $end || $start >= $fileSize || $end >= $fileSize) {
        http_response_code(416);
        header('Content-Range: bytes */' . $fileSize);
        die('Requested range not satisfiable');
    }
    
    $length = $end - $start + 1;
    
    // Set headers for video streaming
    header('Content-Type: ' . $mimeType);
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . $length);
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Set range headers if partial content
    if (isset($_SERVER['HTTP_RANGE'])) {
        http_response_code(206);
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize);
    }
    
    // Prevent direct linking
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    
    // Stream the video file
    $file = fopen($videoPath, 'rb');
    if ($file === false) {
        http_response_code(500);
        die('Cannot open video file');
    }
    
    // Seek to start position
    fseek($file, $start);
    
    // Stream in chunks
    $chunkSize = 8192; // 8KB chunks
    $bytesRemaining = $length;
    
    while ($bytesRemaining > 0 && !feof($file)) {
        $chunkSize = min($chunkSize, $bytesRemaining);
        $chunk = fread($file, $chunkSize);
        
        if ($chunk === false) {
            break;
        }
        
        echo $chunk;
        flush();
        
        $bytesRemaining -= strlen($chunk);
        
        // Check if client disconnected
        if (connection_aborted()) {
            break;
        }
    }
    
    fclose($file);
    
} catch (Exception $e) {
    error_log('Video streaming error: ' . $e->getMessage());
    http_response_code(500);
    die('Internal server error');
}
?>
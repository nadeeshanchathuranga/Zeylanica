<?php
session_start();
require_once '../config.php';
require_once '../services/LessonManagementService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Authentication required']);
    exit();
}

$lessonId = $_GET['id'] ?? '';
if (!$lessonId) {
    http_response_code(400);
    echo json_encode(['error' => 'Lesson ID required']);
    exit();
}

try {
    $lessonService = new LessonManagementService($pdo);
    $lesson = $lessonService->getLesson($lessonId);
    
    if (!$lesson) {
        http_response_code(404);
        echo json_encode(['error' => 'Lesson not found']);
        exit();
    }
    
    echo json_encode($lesson);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load lesson']);
}
?>
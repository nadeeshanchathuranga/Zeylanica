<?php
require_once '../config.php';
require_once '../services/LessonManagementService.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_name'], ['Admin', 'Instructor'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

$lessonService = new LessonManagementService($pdo);

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    switch ($action) {
        case 'create':
            $lessonData = [
                'course_id' => $input['course_id'],
                'title' => $input['title'],
                'description' => $input['description'] ?? '',
                'lesson_order' => $input['lesson_order'],
                'vimeo_id' => $input['vimeo_id'] ?? null,
                'duration' => $input['duration'] ?? null,
                'is_visible' => $input['is_visible'] ?? 1,
                'is_free' => $input['is_free'] ?? 0,
                'allow_download' => $input['allow_download'] ?? 0
            ];
            
            $lessonId = $lessonService->createLesson($lessonData);
            echo json_encode(['success' => true, 'lesson_id' => $lessonId]);
            break;

        case 'update':
            $lessonData = [
                'title' => $input['title'],
                'description' => $input['description'] ?? '',
                'lesson_order' => $input['lesson_order'],
                'vimeo_id' => $input['vimeo_id'] ?? null,
                'duration' => $input['duration'] ?? null,
                'is_visible' => $input['is_visible'] ?? 1,
                'is_free' => $input['is_free'] ?? 0,
                'allow_download' => $input['allow_download'] ?? 0
            ];
            
            $success = $lessonService->updateLesson($input['lesson_id'], $lessonData);
            echo json_encode(['success' => $success]);
            break;

        case 'delete':
            $success = $lessonService->deleteLesson($input['lesson_id']);
            echo json_encode(['success' => $success]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
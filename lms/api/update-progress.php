<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role_name'] !== 'User') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$lessonId = $input['lesson_id'] ?? '';
$action = $input['action'] ?? '';

if (!$lessonId || !$action) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

try {
    if ($action === 'complete') {
        $stmt = $pdo->prepare("
            UPDATE lesson_analytics 
            SET completion_status = 1, completed_at = NOW()
            WHERE lesson_id = ? AND student_id = ?
        ");
        $stmt->execute([$lessonId, $_SESSION['user_id']]);
        
    } elseif ($action === 'time') {
        $duration = $input['duration'] ?? 0;
        $stmt = $pdo->prepare("
            UPDATE lesson_analytics 
            SET total_time_spent = total_time_spent + ?
            WHERE lesson_id = ? AND student_id = ?
        ");
        $stmt->execute([$duration, $lessonId, $_SESSION['user_id']]);
    }
    
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
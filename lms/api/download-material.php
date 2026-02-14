<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('Authentication required');
}

$materialId = $_GET['id'] ?? '';
if (!$materialId) {
    http_response_code(400);
    die('Material ID required');
}

try {
    // Get material details
    $stmt = $pdo->prepare("
        SELECT lm.*, l.course_id 
        FROM lesson_materials lm
        JOIN lessons l ON lm.lesson_id = l.id
        WHERE lm.id = ?
    ");
    $stmt->execute([$materialId]);
    $material = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$material) {
        http_response_code(404);
        die('Material not found');
    }
    
    // Check access permissions (basic check)
    if ($_SESSION['role_name'] === 'Student') {
        // Add enrollment check here if needed
    }
    
    // Serve file
    if (file_exists($material['file_path'])) {
        header('Content-Type: ' . $material['file_type']);
        header('Content-Disposition: attachment; filename="' . $material['file_name'] . '"');
        header('Content-Length: ' . $material['file_size']);
        readfile($material['file_path']);
    } else {
        http_response_code(404);
        die('File not found');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    die('Download failed');
}
?>
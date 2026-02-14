<?php
function checkMenuPermission($pdo, $urlPattern) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
        header('Location: index.php');
        exit();
    }
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM role_permissions rp 
        JOIN menu_items mi ON rp.menu_item_id = mi.id 
        WHERE rp.role_id = ? AND mi.url LIKE ?
    ");
    $stmt->execute([$_SESSION['role_id'], "%$urlPattern%"]);
    
    if ($stmt->fetchColumn() == 0) {
        // Redirect to role-specific dashboard instead of generic dashboard
        switch ($_SESSION['role_name']) {
            case 'Admin':
                header('Location: dashboard-admin.php');
                break;
            case 'Instructor':
                header('Location: dashboard-instructor.php');
                break;
            default:
                header('Location: dashboard-student.php');
                break;
        }
        exit();
    }
}
?>
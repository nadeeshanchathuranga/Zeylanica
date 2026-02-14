<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Redirect to role-specific dashboard
switch ($_SESSION['role_name']) {
    case 'Admin':
        header('Location: dashboard-admin.php');
        break;
    case 'Instructor':
        header('Location: dashboard-instructor.php');
        break;
    case 'User': // Student role
        header('Location: dashboard-student.php');
        break;
    default:
        header('Location: dashboard-student.php');
        break;
}
exit;
?>
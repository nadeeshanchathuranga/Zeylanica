<?php
require_once 'config.php';
require_once 'auth.php';

checkMenuPermission($pdo, 'dashboard');

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
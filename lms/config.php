<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'my_testlms');
define('BASE_URL', '/lms/');
define('APP_BASE_URL', '/lms/');

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'educationzeylanica@gmail.com'); // Update with your email
define('SMTP_PASSWORD', 'baryvrehvtwsewav'); // Update with your password
define('FROM_EMAIL', 'educationzeylanica@gmail.com');
define('FROM_NAME', 'LMS System');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<?php
require_once 'config.php';
require_once 'services/StudentRegistrationService.php';
require_once 'services/UserIntegrationService.php';

if (!isset($_SESSION['registration_verified']) || !isset($_SESSION['registration_id'])) {
    header('Location: register.php');
    exit;
}

$registrationId = $_SESSION['registration_id'];
$registrationService = new StudentRegistrationService($pdo);
$registration = $registrationService->getRegistration($registrationId);

if (!$registration || $registration['status'] !== 'VERIFIED') {
    header('Location: register.php');
    exit;
}

$userCreated = false;
$tempPassword = null;
$error = null;

try {
    $userIntegrationService = new UserIntegrationService($pdo);
    $result = $userIntegrationService->createUserFromRegistration($registrationId);
    
    if ($result) {
        $userCreated = true;
        $tempPassword = $result['temp_password'];
        
        unset($_SESSION['registration_id']);
        unset($_SESSION['student_id']);
        unset($_SESSION['registration_verified']);
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful - Zeylanica Education</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/auth-styles.php'; ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; height: 100vh; overflow: hidden; }
        .success-container { display: flex; height: 100vh; }
        
        .right-panel {
            flex: 1;
        }
        
        .content-card {
            max-width: 500px;
        }
        
        .content-header p { color: #6B7280; margin-bottom: 2rem; }
        
        .info-section {
            border-left-color: #059669;
        }
        
        .info-row:last-child { border-bottom: none; }
        
        .credentials-box {
            background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #3B82F6;
        }
        
        .credentials-box h3 { color: #1E40AF; margin-bottom: 1rem; font-size: 1.1rem; }
        .credential-item { margin-bottom: 1rem; }
        .credential-item label { display: block; color: #374151; font-weight: 500; margin-bottom: 0.5rem; }
        .credential-value {
            background: white;
            padding: 0.75rem;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #DC2626;
            border: 2px solid #3B82F6;
        }
        
        .alert-box {
            background: #FEF3C7;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #F59E0B;
            color: #92400E;
        }
        
        .btn {
            width: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
        }
        
        .error { background: #DC2626; color: white; }
        
        @media (max-width: 768px) {
            .success-container { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <?php 
        $customTitle = 'Welcome Aboard!';
        $customSubtitle = 'Registration Complete';
        $customMessage = "Your registration has been completed successfully. You're now part of Zeylanica Education family.";
        include 'includes/auth-left-panel.php'; 
        ?>
        
        <div class="right-panel scrollable">
            <div class="auth-card content-card">
                <?php if ($error): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <div class="auth-header content-header">
                    <h2>Registration Complete</h2>
                    <p>Your student account has been created successfully</p>
                </div>
                
                <div class="info-section">
                    <div class="info-row">
                        <span class="info-label">Student ID:</span>
                        <span class="info-value"><?= htmlspecialchars($registration['student_id']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value"><?= htmlspecialchars($registration['full_name']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?= htmlspecialchars($registration['email']) ?></span>
                    </div>
                </div>
                
                <?php if ($userCreated && $tempPassword): ?>
                <div class="credentials-box">
                    <h3>🔐 Your Login Credentials</h3>
                    <div class="credential-item">
                        <label>Email:</label>
                        <div class="credential-value"><?= htmlspecialchars($registration['email']) ?></div>
                    </div>
                    <div class="credential-item">
                        <label>Temporary Password:</label>
                        <div class="credential-value"><?= htmlspecialchars($tempPassword) ?></div>
                    </div>
                </div>
                
                <div class="alert-box">
                    ⚠️ Please save your credentials and change your password after first login.
                </div>
                <?php endif; ?>
                
                <a href="index.php" class="auth-btn primary btn-primary">Go to Login</a>
            </div>
        </div>
    </div>
</body>
</html>

<?php
require_once 'config.php';
require_once 'services/StudentRegistrationService.php';

if (!isset($_SESSION['registration_id'])) {
    header('Location: register.php');
    exit;
}

$registrationId = $_SESSION['registration_id'];
$studentId = $_SESSION['student_id'] ?? '';
$success = null;
$error = null;

$registrationService = new StudentRegistrationService($pdo);
$registration = $registrationService->getRegistration($registrationId);

if (!$registration) {
    header('Location: register.php');
    exit;
}

if ($registration['status'] === 'VERIFIED') {
    header('Location: registration-success.php');
    exit;
}

if ($registration['status'] === 'EXPIRED' || strtotime($registration['expires_at']) < time()) {
    $error = 'Registration has expired. Please register again.';
}

$attempts = $registration['verification_attempts'];
$maxAttempts = 3;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['verify_otp'])) {
        $otp = $_POST['otp'];
        
        try {
            if ($registrationService->verifyRegistration($registrationId, $otp)) {
                $_SESSION['registration_verified'] = true;
                header('Location: registration-success.php');
                exit;
            } else {
                $error = 'Invalid or expired OTP code. Please try again.';
                $attempts++;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
    
    if (isset($_POST['resend_otp'])) {
        try {
            $registrationService->resendOTP($registrationId);
            $success = 'New OTP has been sent to your email and mobile number.';
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$expiresAt = strtotime($registration['expires_at']);
$remainingTime = max(0, $expiresAt - time());
$remainingMinutes = floor($remainingTime / 60);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Registration - Zeylanica Education</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/auth-styles.php'; ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; height: 100vh; overflow: hidden; }
        .verify-container { display: flex; height: 100vh; }
        
        .right-panel {
            flex: 1;
        }
        
        .verify-card {
            max-width: 450px;
        }
        
        .verify-header p { color: #6B7280; margin-bottom: 2rem; }
        
        .student-info {
            background: #F9FAFB;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #4F46E5;
        }
        .student-info p { color: #374151; font-size: 0.9rem; margin-bottom: 0.25rem; }
        
        .otp-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #D1D5DB;
            border-radius: 8px;
            font-size: 2rem;
            text-align: center;
            letter-spacing: 0.5rem;
            margin-bottom: 1rem;
            font-family: 'Courier New', monospace;
        }
        .otp-input:focus {
            outline: none;
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .btn:disabled { background: #E5E7EB; color: #9CA3AF; cursor: not-allowed; }
        
        .error { background: #FEE2E2; color: #991B1B; }
        .success { background: #D1FAE5; color: #065F46; }
        .timer { background: #FEF3C7; color: #92400E; padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; text-align: center; font-weight: 600; }
        .attempts { color: #DC2626; font-size: 0.9rem; margin-bottom: 1rem; text-align: center; }
        
        .help-box {
            background: #F9FAFB;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1.5rem;
        }
        .help-box p { color: #6B7280; font-size: 0.85rem; margin-bottom: 0.5rem; }
        
        .back-link { display: block; text-align: center; margin-top: 1rem; color: #4F46E5; text-decoration: none; font-size: 0.9rem; }
        .back-link:hover { text-decoration: underline; }
        
        @media (max-width: 768px) {
            .verify-container { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <?php 
        $customIcon = '🔐';
        $customTitle = 'Verify Your Account';
        $customSubtitle = 'Almost There!';
        $customMessage = "We've sent a 6-digit verification code to your email and mobile number to confirm your identity.";
        include 'includes/auth-left-panel.php'; 
        ?>
        
        <div class="right-panel scrollable">
            <div class="auth-card verify-card">
                <div class="auth-header verify-header">
                    <h2>Enter Verification Code</h2>
                    <p>Check your email and SMS for the code</p>
                </div>
                
                <div class="student-info">
                    <p><strong>Student ID:</strong> <?= htmlspecialchars($studentId) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($registration['email']) ?></p>
                    <p><strong>Mobile:</strong> <?= htmlspecialchars($registration['mobile_number']) ?></p>
                </div>
                
                <?php if ($error): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                
                <?php if ($remainingTime > 0): ?>
                    <div class="timer">⏰ Expires in: <span id="timer"><?= $remainingMinutes ?> minutes</span></div>
                <?php endif; ?>
                
                <?php if ($attempts > 0): ?>
                    <div class="attempts">⚠️ Attempts: <?= $attempts ?>/<?= $maxAttempts ?></div>
                <?php endif; ?>
                
                <?php if ($registration['status'] !== 'EXPIRED' && $attempts < $maxAttempts): ?>
                    <form method="POST">
                        <input type="text" name="otp" class="otp-input" placeholder="000000" 
                               maxlength="6" pattern="[0-9]{6}" required autofocus>
                        <button type="submit" name="verify_otp" class="auth-btn primary">Verify Code</button>
                    </form>
                    
                    <form method="POST">
                        <button type="submit" name="resend_otp" class="auth-btn secondary" style="margin-bottom: 0.75rem;">Resend Code</button>
                    </form>
                <?php else: ?>
                    <div class="error">
                        <?= $attempts >= $maxAttempts ? 'Maximum attempts exceeded.' : 'Registration expired.' ?>
                        Please register again.
                    </div>
                    <a href="register.php" class="auth-btn primary">Register Again</a>
                <?php endif; ?>
                
                <div class="help-box">
                    <p>📧 Check your spam/junk folder</p>
                    <p>📱 Ensure your mobile number is correct</p>
                    <p>🔄 Wait 2 minutes before resending</p>
                </div>
                
                <a href="register.php" class="back-link">← Back to Registration</a>
            </div>
        </div>
    </div>
    
    <script>
        const otpInput = document.querySelector('.otp-input');
        if (otpInput) {
            otpInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
        
        <?php if ($remainingTime > 0): ?>
        let seconds = <?= $remainingTime ?>;
        setInterval(() => {
            if (seconds <= 0) location.reload();
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            document.getElementById('timer').textContent = `${mins}m ${secs}s`;
            seconds--;
        }, 1000);
        <?php endif; ?>
    </script>
</body>
</html>

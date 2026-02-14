<?php
/**
 * Email Service
 * Handles email sending functionality using PHPMailer
 * Based on Unit 2 service design
 */

// Uncomment when PHPMailer is installed
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
// use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;
    
    public function __construct() {
        $this->initializeMailer();
    }
    
    /**
     * Initialize PHPMailer
     */
    private function initializeMailer() {
        // For now, we'll simulate email sending
        // Uncomment when PHPMailer is installed:
        /*
        $this->mailer = new PHPMailer(true);
        $this->mailer->isSMTP();
        $this->mailer->Host = SMTP_HOST;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = SMTP_USERNAME;
        $this->mailer->Password = SMTP_PASSWORD;
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = SMTP_PORT;
        $this->mailer->setFrom(FROM_EMAIL, FROM_NAME);
        */
    }
    
    /**
     * Send OTP verification email
     */
    public function sendOTP($email, $otp) {
        $subject = 'LMS Registration - Verification Code';
        $body = $this->getOTPEmailTemplate($otp);
        
        return $this->sendEmail($email, $subject, $body);
    }
    
    /**
     * Send welcome email with temporary password
     */
    public function sendWelcomeEmail($email, $name, $tempPassword) {
        $subject = 'Welcome to LMS - Account Created';
        $body = $this->getWelcomeEmailTemplate($name, $tempPassword);
        
        return $this->sendEmail($email, $subject, $body);
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordReset($email, $resetToken) {
        $subject = 'LMS - Password Reset Request';
        $resetLink = $this->getBaseUrl() . '/reset-password.php?token=' . $resetToken;
        $body = $this->getPasswordResetTemplate($resetLink);
        
        return $this->sendEmail($email, $subject, $body);
    }
    
    /**
     * Send profile verification email
     */
    public function sendProfileVerification($email, $verificationToken) {
        $subject = 'LMS - Verify Email Address';
        $verificationLink = $this->getBaseUrl() . '/verify-email.php?token=' . $verificationToken;
        $body = $this->getProfileVerificationTemplate($verificationLink);
        
        return $this->sendEmail($email, $subject, $body);
    }
    
    /**
     * Send generic email
     */
    private function sendEmail($to, $subject, $body) {
        try {
            // For development/testing - log email instead of sending
            if ($this->isTestMode()) {
                return $this->logEmail($to, $subject, $body);
            }
            
            // Uncomment when PHPMailer is configured:
            /*
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->isHTML(true);
            
            return $this->mailer->send();
            */
            
            // Temporary: simulate successful sending
            return true;
            
        } catch (Exception $e) {
            error_log("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if in test mode
     */
    private function isTestMode() {
        return empty(SMTP_USERNAME) || 
               empty(SMTP_PASSWORD);
    }
    
    /**
     * Log email for testing
     */
    private function logEmail($to, $subject, $body) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $to,
            'subject' => $subject,
            'body' => $body
        ];
        
        $logFile = __DIR__ . '/../logs/email.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND);
        
        return true;
    }
    
    /**
     * Get OTP email template
     */
    private function getOTPEmailTemplate($otp) {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #007bff;'>LMS Registration Verification</h2>
                <p>Thank you for registering with our Learning Management System.</p>
                <p>Your verification code is:</p>
                <div style='background: #f8f9fa; padding: 20px; text-align: center; margin: 20px 0; border-radius: 5px;'>
                    <h1 style='color: #007bff; font-size: 36px; margin: 0; letter-spacing: 5px;'>{$otp}</h1>
                </div>
                <p>This code will expire in 10 minutes.</p>
                <p>If you didn't request this verification, please ignore this email.</p>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666;'>This is an automated message. Please do not reply.</p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Get welcome email template
     */
    private function getWelcomeEmailTemplate($name, $tempPassword) {
        $loginUrl = $this->getBaseUrl() . '/index.php';
        
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #28a745;'>Welcome to LMS, {$name}!</h2>
                <p>Your student account has been successfully created.</p>
                <p>Your login credentials:</p>
                <div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <p><strong>Email:</strong> Your registered email address</p>
                    <p><strong>Temporary Password:</strong> <code style='background: #e9ecef; padding: 2px 5px; border-radius: 3px;'>{$tempPassword}</code></p>
                </div>
                <p><strong>Important:</strong> Please change your password after your first login.</p>
                <p><a href='{$loginUrl}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Login Now</a></p>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666;'>This is an automated message. Please do not reply.</p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Get password reset template
     */
    private function getPasswordResetTemplate($resetLink) {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #dc3545;'>Password Reset Request</h2>
                <p>You have requested to reset your password for your LMS account.</p>
                <p>Click the button below to reset your password:</p>
                <p><a href='{$resetLink}' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a></p>
                <p>This link will expire in 15 minutes.</p>
                <p>If you didn't request this reset, please ignore this email.</p>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666;'>This is an automated message. Please do not reply.</p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Get profile verification template
     */
    private function getProfileVerificationTemplate($verificationLink) {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #007bff;'>Verify Your Email Address</h2>
                <p>Please verify your email address to complete your profile update.</p>
                <p><a href='{$verificationLink}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Verify Email</a></p>
                <p>This link will expire in 24 hours.</p>
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p style='font-size: 12px; color: #666;'>This is an automated message. Please do not reply.</p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Get base URL for links
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        
        return $protocol . '://' . $host . $path;
    }
}
?>
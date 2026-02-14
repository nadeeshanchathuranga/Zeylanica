<?php
/**
 * OTP Service
 * Handles OTP generation, sending, and verification
 * Based on Unit 2 service design
 */

require_once __DIR__ . '/EmailService.php';
require_once __DIR__ . '/SMSService.php';

class OTPService {
    private $pdo;
    private $emailService;
    private $smsService;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->emailService = new EmailService();
        $this->smsService = new SMSService();
    }
    
    /**
     * Send verification OTP to email and mobile
     */
    public function sendVerificationOTP($registrationId, $email, $mobile) {
        $otp = $this->generateOTP();
        
        // Store OTP for email
        $this->storeOTP($registrationId, $otp, 'EMAIL', $email);
        
        // Store OTP for SMS
        $this->storeOTP($registrationId, $otp, 'SMS', $mobile);
        
        // Send OTP via email
        //$this->emailService->sendOTP($email, $otp);
        
        // Send OTP via SMS
        //$this->smsService->sendOTP($mobile, $otp);
        
        return $otp;
    }
    
    /**
     * Verify OTP code
     */
    public function verifyOTP($registrationId, $code) {
        // Get valid OTP record
        $stmt = $this->pdo->prepare("
            SELECT * FROM otp_codes 
            WHERE registration_id = ? AND code = ? AND used = FALSE AND expires_at > NOW()
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$registrationId, $code]);
        $otpRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$otpRecord) {
            // Increment attempts for all OTP records of this registration
            $this->incrementAttempts($registrationId);
            return false;
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Mark OTP as used
            $stmt = $this->pdo->prepare("
                UPDATE otp_codes 
                SET used = TRUE 
                WHERE registration_id = ? AND code = ?
            ");
            $stmt->execute([$registrationId, $code]);
            
            // Update registration status to verified
            $stmt = $this->pdo->prepare("
                UPDATE student_registrations 
                SET status = 'VERIFIED', verified_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$registrationId]);
            
            $this->pdo->commit();
            
            return true;
            
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new Exception('Failed to verify OTP: ' . $e->getMessage());
        }
    }
    
    /**
     * Resend OTP
     */
    public function resendOTP($registrationId, $email, $mobile) {
        // Check if can resend (2-minute cooldown)
        if (!$this->canResendOTP($registrationId)) {
            throw new Exception('Please wait before requesting another OTP');
        }
        
        // Check daily limit (max 5 OTPs per day)
        if ($this->hasExceededDailyLimit($registrationId)) {
            throw new Exception('Daily OTP limit exceeded');
        }
        
        return $this->sendVerificationOTP($registrationId, $email, $mobile);
    }
    
    /**
     * Generate 6-digit OTP
     */
    private function generateOTP() {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Store OTP in database
     */
    private function storeOTP($registrationId, $code, $contactType, $contactValue) {
        $stmt = $this->pdo->prepare("
            INSERT INTO otp_codes (registration_id, code, contact_type, contact_value)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$registrationId, $code, $contactType, $contactValue]);
    }
    
    /**
     * Increment verification attempts
     */
    private function incrementAttempts($registrationId) {
        $stmt = $this->pdo->prepare("
            UPDATE otp_codes 
            SET attempts = attempts + 1 
            WHERE registration_id = ? AND used = FALSE
        ");
        $stmt->execute([$registrationId]);
        
        // Also increment attempts in registration table
        $stmt = $this->pdo->prepare("
            UPDATE student_registrations 
            SET verification_attempts = verification_attempts + 1 
            WHERE id = ?
        ");
        $stmt->execute([$registrationId]);
    }
    
    /**
     * Check if OTP can be resent (2-minute cooldown)
     */
    private function canResendOTP($registrationId) {
        $stmt = $this->pdo->prepare("
            SELECT created_at FROM otp_codes 
            WHERE registration_id = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$registrationId]);
        $lastOTP = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$lastOTP) {
            return true;
        }
        
        $lastSent = strtotime($lastOTP['created_at']);
        $now = time();
        
        // 2-minute cooldown
        return ($now - $lastSent) >= 120;
    }
    
    /**
     * Check daily OTP limit
     */
    private function hasExceededDailyLimit($registrationId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM otp_codes 
            WHERE registration_id = ? 
            AND created_at >= CURDATE()
        ");
        $stmt->execute([$registrationId]);
        $dailyCount = $stmt->fetchColumn();
        
        return $dailyCount >= 5;
    }
    
    /**
     * Get OTP attempts for registration
     */
    public function getOTPAttempts($registrationId) {
        $stmt = $this->pdo->prepare("
            SELECT verification_attempts FROM student_registrations 
            WHERE id = ?
        ");
        $stmt->execute([$registrationId]);
        return $stmt->fetchColumn() ?: 0;
    }
    
    /**
     * Check if registration has exceeded max attempts
     */
    public function hasExceededMaxAttempts($registrationId) {
        return $this->getOTPAttempts($registrationId) >= 3;
    }
    
    /**
     * Clean up expired OTP codes
     */
    public function cleanupExpiredOTPs() {
        $stmt = $this->pdo->prepare("
            DELETE FROM otp_codes 
            WHERE expires_at < NOW()
        ");
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}
?>
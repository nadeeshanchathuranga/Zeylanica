<?php
/**
 * SMS Service
 * Handles SMS sending functionality
 * Based on Unit 2 service design
 */

class SMSService {
    private $config;
    
    public function __construct() {
        $this->config = [
            'provider' => 'twilio', // or 'local_gateway'
            'twilio_sid' => '', // Update with your Twilio SID
            'twilio_token' => '', // Update with your Twilio Token
            'twilio_from' => '', // Update with your Twilio phone number
            'local_gateway_url' => '', // Update with local SMS gateway URL
            'local_gateway_key' => '' // Update with local SMS gateway API key
        ];
    }
    
    /**
     * Send OTP via SMS
     */
    public function sendOTP($mobile, $otp) {
        $message = "Your LMS verification code is: {$otp}. This code will expire in 10 minutes.";
        
        return $this->sendSMS($mobile, $message);
    }
    
    /**
     * Send welcome SMS
     */
    public function sendWelcomeSMS($mobile, $name, $tempPassword) {
        $message = "Welcome to LMS, {$name}! Your account is ready. Temporary password: {$tempPassword}. Please change it after login.";
        
        return $this->sendSMS($mobile, $message);
    }
    
    /**
     * Send password reset SMS
     */
    public function sendPasswordResetSMS($mobile, $resetCode) {
        $message = "Your LMS password reset code is: {$resetCode}. This code will expire in 15 minutes.";
        
        return $this->sendSMS($mobile, $message);
    }
    
    /**
     * Send profile verification SMS
     */
    public function sendProfileVerificationSMS($mobile, $otp) {
        $message = "Your LMS profile verification code is: {$otp}. This code will expire in 10 minutes.";
        
        return $this->sendSMS($mobile, $message);
    }
    
    /**
     * Send SMS using configured provider
     */
    private function sendSMS($mobile, $message) {
        try {
            // Format mobile number (add country code if needed)
            $formattedMobile = $this->formatMobileNumber($mobile);
            
            // For development/testing - log SMS instead of sending
            if ($this->isTestMode()) {
                return $this->logSMS($formattedMobile, $message);
            }
            
            switch ($this->config['provider']) {
                case 'twilio':
                    return $this->sendViaTwilio($formattedMobile, $message);
                    
                case 'local_gateway':
                    return $this->sendViaLocalGateway($formattedMobile, $message);
                    
                default:
                    throw new Exception('SMS provider not configured');
            }
            
        } catch (Exception $e) {
            error_log("SMS sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send SMS via Twilio
     */
    private function sendViaTwilio($mobile, $message) {
        // Uncomment when Twilio SDK is installed:
        /*
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $twilio = new \Twilio\Rest\Client(
            $this->config['twilio_sid'],
            $this->config['twilio_token']
        );
        
        $result = $twilio->messages->create(
            $mobile,
            [
                'from' => $this->config['twilio_from'],
                'body' => $message
            ]
        );
        
        return $result->sid !== null;
        */
        
        // Temporary: simulate successful sending
        return true;
    }
    
    /**
     * Send SMS via local gateway
     */
    private function sendViaLocalGateway($mobile, $message) {
        $url = $this->config['local_gateway_url'];
        $data = [
            'api_key' => $this->config['local_gateway_key'],
            'to' => $mobile,
            'message' => $message
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200 && $response !== false;
    }
    
    /**
     * Format mobile number with country code
     */
    private function formatMobileNumber($mobile) {
        // Remove any non-digit characters
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        
        // Add Sri Lankan country code if not present
        if (strlen($mobile) === 10 && substr($mobile, 0, 1) === '0') {
            $mobile = '94' . substr($mobile, 1);
        } elseif (strlen($mobile) === 9) {
            $mobile = '94' . $mobile;
        }
        
        // Add + prefix for international format
        if (substr($mobile, 0, 1) !== '+') {
            $mobile = '+' . $mobile;
        }
        
        return $mobile;
    }
    
    /**
     * Check if in test mode
     */
    private function isTestMode() {
        return empty($this->config['twilio_sid']) && 
               empty($this->config['local_gateway_url']);
    }
    
    /**
     * Log SMS for testing
     */
    private function logSMS($mobile, $message) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $mobile,
            'message' => $message,
            'provider' => $this->config['provider']
        ];
        
        $logFile = __DIR__ . '/../logs/sms.log';
        $logDir = dirname($logFile);
        
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND);
        
        return true;
    }
    
    /**
     * Validate mobile number format
     */
    public function isValidMobileNumber($mobile) {
        // Remove any non-digit characters
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        
        // Check Sri Lankan mobile number formats
        if (strlen($mobile) === 10 && substr($mobile, 0, 1) === '0') {
            return true;
        }
        
        if (strlen($mobile) === 9) {
            return true;
        }
        
        if (strlen($mobile) === 11 && substr($mobile, 0, 2) === '94') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Get SMS delivery status (if supported by provider)
     */
    public function getDeliveryStatus($messageId) {
        // Implementation depends on SMS provider
        // For now, return unknown status
        return 'unknown';
    }
}
?>
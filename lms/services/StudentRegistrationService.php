<?php
/**
 * Student Registration Service
 * Handles student registration workflow and validation
 * Based on Unit 2 service design
 */

require_once __DIR__ . '/OTPService.php';
require_once __DIR__ . '/FileUploadService.php';

class StudentRegistrationService {
    private $pdo;
    private $otpService;
    private $fileUploadService;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->otpService = new OTPService($pdo);
        $this->fileUploadService = new FileUploadService();
    }
    
    /**
     * Register a new student
     */
    public function registerStudent($data, $files = []) {
        // Validate input data
        $this->validateRegistrationData($data);
        
        // Generate unique student ID
        $studentId = $this->generateStudentId();
        
        // Handle profile photo upload if provided
        $profilePhoto = null;
        if (isset($files['profile_photo']) && $files['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $profilePhoto = $this->fileUploadService->processProfilePhoto($files['profile_photo']);
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Insert registration record
            $stmt = $this->pdo->prepare("
                INSERT INTO student_registrations 
                (student_id, full_name, name_with_initials, gender, date_of_birth, 
                 nic, profile_photo, mobile_number, whatsapp_number, email, 
                 address_line1, address_line2, city, district, province, postal_code, 
                 preferred_communication, current_school, grade_year, examination_year, 
                 guardian_name, guardian_relationship, guardian_address, 
                 guardian_mobile, guardian_mobile_alt)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $studentId,
                $data['full_name'],
                $data['name_with_initials'],
                $data['gender'] ?? null,
                $data['date_of_birth'],
                $data['nic'] ?? null,
                $profilePhoto,
                $data['mobile_number'],
                $data['whatsapp_number'] ?? null,
                $data['email'],
                $data['address_line1'],
                $data['address_line2'] ?? null,
                $data['city'],
                $data['district'] ?? null,
                $data['province'] ?? null,
                $data['postal_code'] ?? null,
                $data['preferred_communication'],
                $data['current_school'],
                $data['grade_year'],
                $data['examination_year'],
                $data['guardian_name'],
                $data['guardian_relationship'],
                $data['guardian_address'] ?? null,
                $data['guardian_mobile'],
                $data['guardian_mobile_alt'] ?? null
            ]);
            
            $registrationId = $this->pdo->lastInsertId();
            
            // Send OTP for verification
            $this->otpService->sendVerificationOTP($registrationId, $data['email'], $data['mobile_number']);
            
            $this->pdo->commit();
            
            return [
                'registration_id' => $registrationId,
                'student_id' => $studentId,
                'status' => 'PENDING_VERIFICATION'
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception('Registration failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get registration details
     */
    public function getRegistration($registrationId) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM student_registrations 
            WHERE id = ?
        ");
        $stmt->execute([$registrationId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verify registration with OTP
     */
    public function verifyRegistration($registrationId, $otp) {
        return $this->otpService->verifyOTP($registrationId, $otp);
    }
    
    /**
     * Resend OTP for registration
     */
    public function resendOTP($registrationId) {
        $registration = $this->getRegistration($registrationId);
        if (!$registration) {
            throw new Exception('Registration not found');
        }
        
        if ($registration['status'] !== 'PENDING_VERIFICATION') {
            throw new Exception('Registration is not pending verification');
        }
        
        return $this->otpService->resendOTP($registrationId, $registration['email'], $registration['mobile_number']);
    }
    
    /**
     * Generate unique student ID
     */
    private function generateStudentId() {
        $year = date('Y');
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM student_registrations 
            WHERE student_id LIKE ?
        ");
        $stmt->execute(["STU{$year}%"]);
        $count = $stmt->fetchColumn() + 1;
        
        return "STU{$year}" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Validate registration data
     */
    private function validateRegistrationData($data) {
        $errors = [];
        
        // Required fields
        $required = [
            'full_name', 'name_with_initials', 'date_of_birth', 'mobile_number',
            'email', 'address_line1', 'city', 'preferred_communication',
            'current_school', 'grade_year', 'examination_year',
            'guardian_name', 'guardian_relationship', 'guardian_mobile'
        ];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        
        // Email validation
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        // Full name validation (only letters and spaces)
        if (!empty($data['full_name']) && !preg_match('/^[a-zA-Z\s]+$/', $data['full_name'])) {
            $errors[] = 'Full name must contain only letters and spaces';
        }
        
        // Date of birth validation (not future date)
        if (!empty($data['date_of_birth']) && strtotime($data['date_of_birth']) > time()) {
            $errors[] = 'Date of birth cannot be in the future';
        }
        
        // Mobile number validation
        if (!empty($data['mobile_number']) && !preg_match('/^[0-9]{10}$/', $data['mobile_number'])) {
            $errors[] = 'Mobile number must be 10 digits';
        }
        
        // Guardian mobile validation
        if (!empty($data['guardian_mobile']) && !preg_match('/^[0-9]{10}$/', $data['guardian_mobile'])) {
            $errors[] = 'Guardian mobile number must be 10 digits';
        }
        
        // NIC validation (if provided)
        if (!empty($data['nic']) && !$this->isValidNIC($data['nic'])) {
            $errors[] = 'Invalid NIC format';
        }
        
        // Examination year validation
        $currentYear = date('Y');
        if (!empty($data['examination_year']) && 
            ($data['examination_year'] < $currentYear || $data['examination_year'] > $currentYear + 10)) {
            $errors[] = 'Examination year must be between ' . $currentYear . ' and ' . ($currentYear + 10);
        }
        
        // Check for duplicate email
        if (!empty($data['email'])) {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM student_registrations 
                WHERE email = ? AND status != 'EXPIRED'
            ");
            $stmt->execute([$data['email']]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Email address is already registered';
            }
        }
        
        // Check for duplicate mobile
        if (!empty($data['mobile_number'])) {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM student_registrations 
                WHERE mobile_number = ? AND status != 'EXPIRED'
            ");
            $stmt->execute([$data['mobile_number']]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Mobile number is already registered';
            }
        }
        
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }
    }
    
    /**
     * Validate NIC format (Sri Lankan)
     */
    private function isValidNIC($nic) {
        // Old format: 9 digits + V/X
        if (preg_match('/^[0-9]{9}[vVxX]$/', $nic)) {
            return true;
        }
        
        // New format: 12 digits
        if (preg_match('/^[0-9]{12}$/', $nic)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Clean up expired registrations
     */
    public function cleanupExpiredRegistrations() {
        $stmt = $this->pdo->prepare("
            UPDATE student_registrations 
            SET status = 'EXPIRED' 
            WHERE status = 'PENDING_VERIFICATION' 
            AND expires_at < NOW()
        ");
        $stmt->execute();
        
        return $stmt->rowCount();
    }
}
?>
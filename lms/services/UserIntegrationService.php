<?php
/**
 * User Integration Service
 * Converts verified student registrations to user accounts
 * Based on Unit 2 and Unit 3 service designs
 */

require_once __DIR__ . '/EmailService.php';

class UserIntegrationService {
    private $pdo;
    private $emailService;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->emailService = new EmailService();
    }
    
    /**
     * Create user account from verified registration
     */
    public function createUserFromRegistration($registrationId) {
        // Get verified registration
        $stmt = $this->pdo->prepare("
            SELECT * FROM student_registrations 
            WHERE id = ? AND status = 'VERIFIED'
        ");
        $stmt->execute([$registrationId]);
        $registration = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$registration) {
            throw new Exception('Registration not found or not verified');
        }
        
        // Check if user already exists
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$registration['email']]);
        if ($stmt->fetch()) {
            throw new Exception('User account already exists for this email');
        }
        
        try {
            $this->pdo->beginTransaction();
            
            // Generate temporary password
            $tempPassword = $this->generateTemporaryPassword();
            $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
            
            // Get Student role ID
            $roleStmt = $this->pdo->prepare("SELECT id FROM roles WHERE name = 'User'");
            $roleStmt->execute();
            $studentRoleId = $roleStmt->fetchColumn();
            
            if (!$studentRoleId) {
                throw new Exception('Student role not found');
            }
            
            // Insert user
            $userStmt = $this->pdo->prepare("
                INSERT INTO users (email, password, role_id, status, created_at) 
                VALUES (?, ?, ?, 'ACTIVE', NOW())
            ");
            $userStmt->execute([$registration['email'], $hashedPassword, $studentRoleId]);
            $userId = $this->pdo->lastInsertId();
            
            // Create user profile
            $this->createUserProfile($userId, $registration);
            
            // Create user role assignment (if enhanced role system is available)
            $this->createUserRoleAssignment($userId, $studentRoleId);
            
            // Send welcome email
            $this->emailService->sendWelcomeEmail(
                $registration['email'], 
                $registration['full_name'], 
                $tempPassword
            );
            
            $this->pdo->commit();
            
            return [
                'user_id' => $userId,
                'temp_password' => $tempPassword,
                'student_id' => $registration['student_id']
            ];
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception('Failed to create user account: ' . $e->getMessage());
        }
    }
    
    /**
     * Create user profile from registration data
     */
    private function createUserProfile($userId, $registration) {
        // Check if user_profiles table exists
        $stmt = $this->pdo->prepare("SHOW TABLES LIKE 'user_profiles'");
        $stmt->execute();
        
        if (!$stmt->fetch()) {
            // user_profiles table doesn't exist yet, skip profile creation
            return;
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO user_profiles 
            (user_id, full_name, name_with_initials, gender, date_of_birth, nic,
             profile_photo, primary_email, mobile_number, whatsapp_number, 
             address_line1, address_line2, city, district, province, postal_code, 
             preferred_communication, email_verified, mobile_verified, 
             status, completion_percentage, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE, TRUE, 'ACTIVE', 100, NOW())
        ");
        
        $stmt->execute([
            $userId,
            $registration['full_name'],
            $registration['name_with_initials'],
            $registration['gender'],
            $registration['date_of_birth'],
            $registration['nic'],
            $registration['profile_photo'],
            $registration['email'],
            $registration['mobile_number'],
            $registration['whatsapp_number'],
            $registration['address_line1'],
            $registration['address_line2'],
            $registration['city'],
            $registration['district'],
            $registration['province'],
            $registration['postal_code'],
            $registration['preferred_communication']
        ]);
    }
    
    /**
     * Create user role assignment (for enhanced role system)
     */
    private function createUserRoleAssignment($userId, $roleId) {
        // Check if user_role_assignments table exists
        $stmt = $this->pdo->prepare("SHOW TABLES LIKE 'user_role_assignments'");
        $stmt->execute();
        
        if (!$stmt->fetch()) {
            // Enhanced role system not available yet
            return;
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO user_role_assignments 
            (user_id, role_id, assigned_by, assigned_at, is_active, assignment_reason)
            VALUES (?, ?, 1, NOW(), TRUE, 'Auto-assigned during registration')
        ");
        $stmt->execute([$userId, $roleId]);
    }
    
    /**
     * Generate temporary password
     */
    private function generateTemporaryPassword() {
        $prefix = 'LMS';
        $numbers = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        return $prefix . $numbers;
    }
    
    /**
     * Get registration statistics
     */
    public function getRegistrationStats() {
        $stats = [];
        
        // Total registrations
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM student_registrations");
        $stmt->execute();
        $stats['total_registrations'] = $stmt->fetchColumn();
        
        // Verified registrations
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM student_registrations WHERE status = 'VERIFIED'");
        $stmt->execute();
        $stats['verified_registrations'] = $stmt->fetchColumn();
        
        // Pending registrations
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM student_registrations WHERE status = 'PENDING_VERIFICATION'");
        $stmt->execute();
        $stats['pending_registrations'] = $stmt->fetchColumn();
        
        // Expired registrations
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM student_registrations WHERE status = 'EXPIRED'");
        $stmt->execute();
        $stats['expired_registrations'] = $stmt->fetchColumn();
        
        // Users created from registrations
        $stmt = $this->pdo->prepare("
            SELECT COUNT(DISTINCT u.id) 
            FROM users u 
            JOIN student_registrations sr ON u.email = sr.email 
            WHERE sr.status = 'VERIFIED'
        ");
        $stmt->execute();
        $stats['users_created'] = $stmt->fetchColumn();
        
        return $stats;
    }
    
    /**
     * Get pending registrations that need user account creation
     */
    public function getPendingUserCreations() {
        $stmt = $this->pdo->prepare("
            SELECT sr.* 
            FROM student_registrations sr
            LEFT JOIN users u ON sr.email = u.email
            WHERE sr.status = 'VERIFIED' AND u.id IS NULL
            ORDER BY sr.verified_at ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Bulk create users from verified registrations
     */
    public function bulkCreateUsers($limit = 10) {
        $pendingRegistrations = $this->getPendingUserCreations();
        $created = 0;
        $errors = [];
        
        foreach (array_slice($pendingRegistrations, 0, $limit) as $registration) {
            try {
                $this->createUserFromRegistration($registration['id']);
                $created++;
            } catch (Exception $e) {
                $errors[] = [
                    'registration_id' => $registration['id'],
                    'student_id' => $registration['student_id'],
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'created' => $created,
            'errors' => $errors,
            'total_pending' => count($pendingRegistrations)
        ];
    }
    
    /**
     * Sync registration data with user profile
     */
    public function syncRegistrationWithProfile($registrationId) {
        $stmt = $this->pdo->prepare("
            SELECT sr.*, u.id as user_id 
            FROM student_registrations sr
            JOIN users u ON sr.email = u.email
            WHERE sr.id = ? AND sr.status = 'VERIFIED'
        ");
        $stmt->execute([$registrationId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            throw new Exception('Registration or user not found');
        }
        
        // Update user profile if it exists
        $stmt = $this->pdo->prepare("SELECT id FROM user_profiles WHERE user_id = ?");
        $stmt->execute([$data['user_id']]);
        
        if ($stmt->fetch()) {
            // Update existing profile
            $stmt = $this->pdo->prepare("
                UPDATE user_profiles SET
                    full_name = ?, name_with_initials = ?, gender = ?, 
                    date_of_birth = ?, nic = ?, mobile_number = ?,
                    whatsapp_number = ?, address_line1 = ?, address_line2 = ?,
                    city = ?, district = ?, province = ?, postal_code = ?,
                    preferred_communication = ?, updated_at = NOW()
                WHERE user_id = ?
            ");
            
            $stmt->execute([
                $data['full_name'], $data['name_with_initials'], $data['gender'],
                $data['date_of_birth'], $data['nic'], $data['mobile_number'],
                $data['whatsapp_number'], $data['address_line1'], $data['address_line2'],
                $data['city'], $data['district'], $data['province'], $data['postal_code'],
                $data['preferred_communication'], $data['user_id']
            ]);
        } else {
            // Create new profile
            $this->createUserProfile($data['user_id'], $data);
        }
        
        return true;
    }
}
?>
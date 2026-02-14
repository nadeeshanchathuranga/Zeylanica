<?php

class PaymentService {
    private $pdo;
    private $gateways = [];

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function registerGateway($name, PaymentGatewayInterface $gateway) {
        $this->gateways[$name] = $gateway;
    }

    public function initiatePayment($studentId, $courseId, $gatewayName = 'stripe') {
        // Validate course and get price
        $course = $this->getCourse($courseId);
        if (!$course) {
            throw new Exception('Course not found');
        }

        // Check for existing pending payment
        if ($this->hasPendingPayment($studentId, $courseId)) {
            throw new Exception('Payment already in progress for this course');
        }

        // Check if already enrolled
        if ($this->isEnrolled($studentId, $courseId)) {
            throw new Exception('Already enrolled in this course');
        }

        $transactionId = $this->generateUUID();
        $amount = $course['price'] - ($course['discount_amount'] ?? 0);
        
        // Create transaction record
        $stmt = $this->pdo->prepare("
            INSERT INTO payment_transactions 
            (id, student_id, course_id, amount, currency, gateway_name, status, expires_at) 
            VALUES (?, ?, ?, ?, 'LKR', ?, 'Pending', DATE_ADD(NOW(), INTERVAL 15 MINUTE))
        ");
        $stmt->execute([$transactionId, $studentId, $courseId, $amount, $gatewayName]);

        // Create payment with gateway
        $gateway = $this->gateways[$gatewayName];
        $returnUrl = BASE_URL . "payment/success.php?transaction_id=" . $transactionId;
        $cancelUrl = BASE_URL . "payment/cancel.php?transaction_id=" . $transactionId;
        
        $paymentData = $gateway->createPayment($amount, 'LKR', $courseId, $studentId, $returnUrl, $cancelUrl);
        
        // Update transaction with gateway data
        $stmt = $this->pdo->prepare("
            UPDATE payment_transactions 
            SET gateway_transaction_id = ?, gateway_response = ?, status = 'Processing'
            WHERE id = ?
        ");
        $stmt->execute([$paymentData['id'], json_encode($paymentData), $transactionId]);

        return [
            'transaction_id' => $transactionId,
            'payment_url' => $paymentData['approval_url'],
            'amount' => $amount
        ];
    }

    public function updatePaymentStatus($transactionId, $status, $gatewayResponse = null) {
        $stmt = $this->pdo->prepare("
            UPDATE payment_transactions 
            SET status = ?, gateway_response = ?, completed_at = NOW(), updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$status, json_encode($gatewayResponse), $transactionId]);

        // If payment successful, trigger enrollment
        if ($status === 'Paid') {
            $this->triggerEnrollment($transactionId);
        }
    }

    public function getTransaction($transactionId) {
        $stmt = $this->pdo->prepare("
            SELECT pt.*, c.title as course_title, u.email as student_email
            FROM payment_transactions pt
            JOIN courses c ON pt.course_id = c.id
            JOIN users u ON pt.student_id = u.id
            WHERE pt.id = ?
        ");
        $stmt->execute([$transactionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTransactionHistory($studentId = null, $limit = 50, $offset = 0) {
        $sql = "
            SELECT pt.*, c.title as course_title, u.email as student_email
            FROM payment_transactions pt
            JOIN courses c ON pt.course_id = c.id
            JOIN users u ON pt.student_id = u.id
        ";
        $params = [];
        
        if ($studentId) {
            $sql .= " WHERE pt.student_id = ?";
            $params[] = $studentId;
        }
        
        $sql .= " ORDER BY pt.created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getCourse($courseId) {
        $stmt = $this->pdo->prepare("SELECT * FROM courses WHERE id = ? AND status = 'Published'");
        $stmt->execute([$courseId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function hasPendingPayment($studentId, $courseId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM payment_transactions 
            WHERE student_id = ? AND course_id = ? AND status IN ('Pending', 'Processing')
        ");
        $stmt->execute([$studentId, $courseId]);
        return $stmt->fetchColumn() > 0;
    }

    private function isEnrolled($studentId, $courseId) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM course_enrollments 
            WHERE student_id = ? AND course_id = ? AND status = 'Active'
        ");
        $stmt->execute([$studentId, $courseId]);
        return $stmt->fetchColumn() > 0;
    }

    private function triggerEnrollment($transactionId) {
        $transaction = $this->getTransaction($transactionId);
        if (!$transaction) return;

        $enrollmentId = $this->generateUUID();
        $course = $this->getCourse($transaction['course_id']);
        
        // Calculate expiry date
        $expiresAt = null;
        if ($course['validity_type'] === 'Fixed Duration' && $course['validity_months']) {
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$course['validity_months']} months"));
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO course_enrollments 
            (id, student_id, course_id, transaction_id, status, expires_at) 
            VALUES (?, ?, ?, ?, 'Active', ?)
        ");
        $stmt->execute([
            $enrollmentId, 
            $transaction['student_id'], 
            $transaction['course_id'], 
            $transactionId, 
            $expiresAt
        ]);
    }

    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
?>
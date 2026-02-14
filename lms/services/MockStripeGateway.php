<?php

class MockStripeGateway implements PaymentGatewayInterface {
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function createPayment($amount, $currency, $courseId, $studentId, $returnUrl, $cancelUrl) {
        // Generate mock session ID
        $sessionId = 'cs_mock_' . uniqid();
        
        // Create mock payment URL (redirect to success for demo)
        $mockPaymentUrl = str_replace('success.php', 'mock-payment.php', $returnUrl) . 
                         '&session_id=' . $sessionId . 
                         '&amount=' . $amount . 
                         '&cancel_url=' . urlencode($cancelUrl);
        
        return [
            'id' => $sessionId,
            'approval_url' => $mockPaymentUrl
        ];
    }

    public function verifyPayment($gatewayTransactionId) {
        // Mock verification - always return success for demo
        return [
            'status' => 'Paid',
            'transaction_id' => 'pi_mock_' . uniqid(),
            'amount' => 0 // Will be updated from database
        ];
    }

    public function handleWebhook($payload, $signature) {
        // Mock webhook handling
        $event = json_decode($payload, true);
        
        if ($event && isset($event['type']) && $event['type'] === 'checkout.session.completed') {
            return [
                'transaction_id' => $event['data']['object']['id'] ?? 'cs_mock_' . uniqid(),
                'status' => 'Paid',
                'metadata' => $event['data']['object']['metadata'] ?? []
            ];
        }

        return null;
    }

    public function refundPayment($gatewayTransactionId, $amount = null) {
        // Mock refund
        return [
            'id' => 're_mock_' . uniqid(),
            'status' => 'succeeded',
            'amount' => $amount * 100
        ];
    }
}
?>
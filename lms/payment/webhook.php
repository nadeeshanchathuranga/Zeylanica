<?php
require_once '../config.php';
require_once '../services/PaymentService.php';
require_once '../services/PaymentGatewayInterface.php';
require_once '../services/StripeGateway.php';

// Get webhook payload
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    // Get Stripe configuration
    $stmt = $pdo->prepare("SELECT configuration FROM payment_gateways WHERE name = 'stripe' AND is_active = 1");
    $stmt->execute();
    $gatewayConfig = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$gatewayConfig) {
        http_response_code(400);
        exit('Gateway not configured');
    }
    
    $config = json_decode($gatewayConfig['configuration'], true);
    
    // Use mock gateway for demo
    require_once '../services/MockStripeGateway.php';
    $gateway = new MockStripeGateway($config);
    
    // Handle webhook
    $webhookData = $gateway->handleWebhook($payload, $signature);
    
    if ($webhookData) {
        $paymentService = new PaymentService($pdo);
        
        // Find transaction by gateway transaction ID
        $stmt = $pdo->prepare("SELECT id FROM payment_transactions WHERE gateway_transaction_id = ?");
        $stmt->execute([$webhookData['transaction_id']]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($transaction) {
            $paymentService->updatePaymentStatus(
                $transaction['id'], 
                $webhookData['status'], 
                $webhookData
            );
        }
    }
    
    http_response_code(200);
    echo 'OK';
    
} catch (Exception $e) {
    error_log('Webhook error: ' . $e->getMessage());
    http_response_code(400);
    echo 'Error: ' . $e->getMessage();
}
?>
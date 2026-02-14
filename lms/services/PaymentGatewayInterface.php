<?php

interface PaymentGatewayInterface {
    public function createPayment($amount, $currency, $courseId, $studentId, $returnUrl, $cancelUrl);
    public function verifyPayment($gatewayTransactionId);
    public function handleWebhook($payload, $signature);
    public function refundPayment($gatewayTransactionId, $amount = null);
}
?>
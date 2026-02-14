<?php

class StripeGateway implements PaymentGatewayInterface {
    private $secretKey;
    private $publicKey;
    private $webhookSecret;

    public function __construct($config) {
        $this->secretKey = $config['secret_key'];
        $this->publicKey = $config['public_key'];
        $this->webhookSecret = $config['webhook_secret'];
    }

    public function createPayment($amount, $currency, $courseId, $studentId, $returnUrl, $cancelUrl) {
        $data = [
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($currency),
                    'product_data' => [
                        'name' => 'Course Enrollment',
                    ],
                    'unit_amount' => $amount * 100, // Stripe uses cents
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $returnUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'course_id' => $courseId,
                'student_id' => $studentId
            ]
        ];

        $response = $this->makeStripeRequest('POST', 'checkout/sessions', $data);
        
        return [
            'id' => $response['id'],
            'approval_url' => $response['url']
        ];
    }

    public function verifyPayment($gatewayTransactionId) {
        $response = $this->makeStripeRequest('GET', "checkout/sessions/{$gatewayTransactionId}");
        
        return [
            'status' => $response['payment_status'] === 'paid' ? 'Paid' : 'Failed',
            'transaction_id' => $response['payment_intent'],
            'amount' => $response['amount_total'] / 100
        ];
    }

    public function handleWebhook($payload, $signature) {
        // Verify webhook signature
        $computedSignature = hash_hmac('sha256', $payload, $this->webhookSecret);
        
        if (!hash_equals($signature, $computedSignature)) {
            throw new Exception('Invalid webhook signature');
        }

        $event = json_decode($payload, true);
        
        if ($event['type'] === 'checkout.session.completed') {
            $session = $event['data']['object'];
            return [
                'transaction_id' => $session['id'],
                'status' => 'Paid',
                'metadata' => $session['metadata']
            ];
        }

        return null;
    }

    public function refundPayment($gatewayTransactionId, $amount = null) {
        $data = ['payment_intent' => $gatewayTransactionId];
        if ($amount) {
            $data['amount'] = $amount * 100;
        }

        return $this->makeStripeRequest('POST', 'refunds', $data);
    }

    private function makeStripeRequest($method, $endpoint, $data = null) {
        $url = "https://api.stripe.com/v1/{$endpoint}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->secretKey,
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            throw new Exception("Stripe API error: {$response}");
        }

        return json_decode($response, true);
    }
}
?>
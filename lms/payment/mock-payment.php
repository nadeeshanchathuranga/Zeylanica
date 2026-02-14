<?php
require_once '../config.php';
require_once '../template.php';
require_once '../services/PaymentService.php';

$sessionId = $_GET['session_id'] ?? '';
$amount = $_GET['amount'] ?? 0;
$transactionId = $_GET['transaction_id'] ?? '';
$cancelUrl = $_GET['cancel_url'] ?? '../dashboard.php';

if (!$sessionId || !$transactionId) {
    header('Location: ../dashboard.php');
    exit;
}

// Handle payment simulation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $paymentService = new PaymentService($pdo);
    
    if ($action === 'pay') {
        // Simulate successful payment
        $paymentService->updatePaymentStatus($transactionId, 'Paid', [
            'session_id' => $sessionId,
            'payment_intent' => 'pi_mock_' . uniqid(),
            'amount_total' => $amount * 100
        ]);
        
        header('Location: success.php?transaction_id=' . $transactionId);
        exit;
    } elseif ($action === 'cancel') {
        // Simulate cancelled payment
        header('Location: ' . urldecode($cancelUrl));
        exit;
    }
}

ob_start();
?>
<div class="mock-payment-header">
    <div class="header-content">
        <div class="title-section">
            <h2 class="page-title">🔒 Secure Payment</h2>
            <p class="page-subtitle">Mock Stripe Checkout (Demo Mode)</p>
        </div>
    </div>
</div>

<div class="mock-payment-container">
    <div class="payment-card">
        <div class="stripe-header">
            <div class="stripe-logo">
                <span style="background: #635bff; color: white; padding: 0.5rem 1rem; border-radius: 4px; font-weight: bold;">stripe</span>
            </div>
            <div class="secure-badge">
                <i class="fas fa-lock"></i> Secure Payment
            </div>
        </div>
        
        <div class="payment-amount">
            <h3>Payment Amount</h3>
            <div class="amount-display">LKR <?= number_format($amount, 2) ?></div>
        </div>
        
        <div class="mock-form">
            <h4>💳 Payment Information</h4>
            <div class="demo-notice">
                <i class="fas fa-info-circle"></i>
                <strong>Demo Mode:</strong> This is a simulated payment for testing purposes.
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label>Card Number</label>
                    <input type="text" value="4242 4242 4242 4242" readonly class="form-control demo-input">
                    <small>Demo card number</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Expiry</label>
                        <input type="text" value="12/25" readonly class="form-control demo-input">
                    </div>
                    <div class="form-group">
                        <label>CVC</label>
                        <input type="text" value="123" readonly class="form-control demo-input">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Cardholder Name</label>
                    <input type="text" value="Demo User" readonly class="form-control demo-input">
                </div>
                
                <div class="payment-actions">
                    <button type="submit" name="action" value="pay" class="btn btn-primary btn-lg">
                        <i class="fas fa-credit-card"></i> Complete Payment - LKR <?= number_format($amount, 2) ?>
                    </button>
                    <button type="submit" name="action" value="cancel" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel Payment
                    </button>
                </div>
            </form>
        </div>
        
        <div class="security-info">
            <div class="security-items">
                <div class="security-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>256-bit SSL encryption</span>
                </div>
                <div class="security-item">
                    <i class="fas fa-lock"></i>
                    <span>PCI DSS compliant</span>
                </div>
                <div class="security-item">
                    <i class="fas fa-check-circle"></i>
                    <span>Secure processing</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.mock-payment-container { max-width: 500px; margin: 0 auto; }
.payment-card { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.stripe-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid #eee; }
.secure-badge { display: flex; align-items: center; gap: 0.5rem; color: #28a745; font-size: 0.9rem; }
.payment-amount { text-align: center; margin-bottom: 2rem; }
.payment-amount h3 { margin: 0 0 0.5rem 0; color: #666; }
.amount-display { font-size: 2rem; font-weight: bold; color: #333; }
.mock-form h4 { margin: 0 0 1rem 0; color: #333; }
.demo-notice { background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 6px; padding: 0.75rem; margin-bottom: 1.5rem; color: #0066cc; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.25rem; font-weight: 500; color: #333; }
.form-control { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; }
.demo-input { background: #f8f9fa; color: #666; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.payment-actions { display: flex; flex-direction: column; gap: 1rem; margin-top: 2rem; }
.btn-lg { padding: 1rem; font-size: 1.1rem; font-weight: 600; }
.security-info { margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #eee; }
.security-items { display: flex; justify-content: space-around; }
.security-item { display: flex; flex-direction: column; align-items: center; gap: 0.25rem; font-size: 0.8rem; color: #666; }
.security-item i { color: #28a745; }
</style>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Mock Payment', $content);
?>
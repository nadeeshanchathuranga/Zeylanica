<?php
require_once '../config.php';
require_once '../template.php';
require_once '../services/PaymentService.php';
require_once '../services/PaymentGatewayInterface.php';
require_once '../services/StripeGateway.php';

$transactionId = $_GET['transaction_id'] ?? '';
if (!$transactionId) {
    header('Location: ../dashboard.php');
    exit;
}

$paymentService = new PaymentService($pdo);
$transaction = $paymentService->getTransaction($transactionId);

if (!$transaction || $transaction['student_id'] != $_SESSION['user_id']) {
    header('Location: ../dashboard.php');
    exit;
}

// Verify payment with gateway if still processing
if ($transaction['status'] === 'Processing') {
    try {
        $stmt = $pdo->prepare("SELECT configuration FROM payment_gateways WHERE name = ?");
        $stmt->execute([$transaction['gateway_name']]);
        $gatewayConfig = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($gatewayConfig) {
            $config = json_decode($gatewayConfig['configuration'], true);
            
            // Use mock gateway for demo
            require_once '../services/MockStripeGateway.php';
            $gateway = new MockStripeGateway($config);
            
            $verification = $gateway->verifyPayment($transaction['gateway_transaction_id']);
            $paymentService->updatePaymentStatus($transactionId, $verification['status'], $verification);
            
            // Refresh transaction data
            $transaction = $paymentService->getTransaction($transactionId);
        }
    } catch (Exception $e) {
        // Log error but continue
    }
}

ob_start();
?>
<div class="payment-status-header">
    <div class="header-content">
        <div class="title-section">
            <h2 class="page-title">💳 Payment Status</h2>
            <p class="page-subtitle">Transaction details and enrollment status</p>
        </div>
    </div>
</div>

<div class="payment-status-container">
    <?php if ($transaction['status'] === 'Paid'): ?>
        <div class="status-card success">
            <div class="status-icon">✅</div>
            <div class="status-content">
                <h3>Payment Successful!</h3>
                <p>Your enrollment for <strong><?= htmlspecialchars($transaction['course_title']) ?></strong> has been confirmed.</p>
                
                <div class="transaction-details">
                    <div class="detail-row">
                        <span>Transaction ID:</span>
                        <span><?= htmlspecialchars(substr($transactionId, 0, 16)) ?>...</span>
                    </div>
                    <div class="detail-row">
                        <span>Amount Paid:</span>
                        <span>LKR <?= number_format($transaction['amount'], 2) ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Payment Date:</span>
                        <span><?= date('M j, Y \\a\\t g:i A', strtotime($transaction['completed_at'])) ?></span>
                    </div>
                </div>
                
                <div class="success-actions">
                    <a href="../lessons/course-view.php?id=<?= htmlspecialchars($transaction['course_id']) ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-play"></i> Start Learning
                    </a>
                    <a href="../dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    <?php elseif ($transaction['status'] === 'Failed'): ?>
        <div class="status-card error">
            <div class="status-icon">❌</div>
            <div class="status-content">
                <h3>Payment Failed</h3>
                <p>Your payment for <strong><?= htmlspecialchars($transaction['course_title']) ?></strong> could not be processed.</p>
                
                <?php if ($transaction['failure_reason']): ?>
                    <div class="error-details">
                        <p><strong>Reason:</strong> <?= htmlspecialchars($transaction['failure_reason']) ?></p>
                    </div>
                <?php endif; ?>
                
                <p>Please try again or contact support if the problem persists.</p>
                
                <div class="error-actions">
                    <a href="initiate.php?course_id=<?= htmlspecialchars($transaction['course_id']) ?>" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Try Again
                    </a>
                    <a href="../course/view.php?id=<?= htmlspecialchars($transaction['course_id']) ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Course
                    </a>
                </div>
            </div>
        </div>
    <?php elseif ($transaction['status'] === 'Cancelled'): ?>
        <div class="status-card warning">
            <div class="status-icon">⚠️</div>
            <div class="status-content">
                <h3>Payment Cancelled</h3>
                <p>Your payment for <strong><?= htmlspecialchars($transaction['course_title']) ?></strong> was cancelled.</p>
                <p>No charges have been made to your account.</p>
                
                <div class="warning-actions">
                    <a href="initiate.php?course_id=<?= htmlspecialchars($transaction['course_id']) ?>" class="btn btn-primary">
                        <i class="fas fa-credit-card"></i> Try Payment Again
                    </a>
                    <a href="../course/catalog.php" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Browse Other Courses
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="status-card processing">
            <div class="status-icon">⏳</div>
            <div class="status-content">
                <h3>Payment Processing</h3>
                <p>Your payment is being processed. Please wait...</p>
                
                <div class="processing-info">
                    <div class="spinner"></div>
                    <p>This page will automatically refresh in a few seconds.</p>
                </div>
            </div>
        </div>
        <script>
            setTimeout(() => location.reload(), 3000);
        </script>
    <?php endif; ?>
</div>

<style>
.payment-status-container { max-width: 600px; margin: 0 auto; }
.status-card { background: white; border-radius: 12px; padding: 2rem; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.status-card.success { border-left: 4px solid #28a745; }
.status-card.error { border-left: 4px solid #dc3545; }
.status-card.warning { border-left: 4px solid #ffc107; }
.status-card.processing { border-left: 4px solid #007bff; }
.status-icon { font-size: 4rem; margin-bottom: 1rem; }
.status-content h3 { margin: 0 0 1rem 0; color: #333; }
.status-content p { color: #666; margin-bottom: 1rem; }
.transaction-details { background: #f8f9fa; border-radius: 8px; padding: 1rem; margin: 1.5rem 0; text-align: left; }
.detail-row { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
.detail-row:last-child { margin-bottom: 0; }
.success-actions, .error-actions, .warning-actions { display: flex; gap: 1rem; justify-content: center; margin-top: 2rem; }
.processing-info { margin: 1.5rem 0; }
.spinner { width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #007bff; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
.error-details { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; padding: 0.75rem; margin: 1rem 0; color: #721c24; }
@media (max-width: 768px) {
    .success-actions, .error-actions, .warning-actions { flex-direction: column; }
}
</style>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Payment Status', $content);
?>
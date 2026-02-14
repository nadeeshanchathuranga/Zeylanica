<?php
require_once '../config.php';
require_once '../template.php';
require_once '../services/PaymentService.php';

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

// Update transaction status to cancelled
$paymentService->updatePaymentStatus($transactionId, 'Cancelled');

ob_start();
?>
<div class="payment-cancel-header">
    <div class="header-content">
        <div class="title-section">
            <h2 class="page-title">❌ Payment Cancelled</h2>
            <p class="page-subtitle">Your payment has been cancelled</p>
        </div>
    </div>
</div>

<div class="cancel-container">
    <div class="cancel-card">
        <div class="cancel-icon">❌</div>
        <div class="cancel-content">
            <h3>Payment Cancelled</h3>
            <p>Your payment for <strong><?= htmlspecialchars($transaction['course_title']) ?></strong> has been cancelled.</p>
            <p>No charges have been made to your account.</p>
            
            <div class="transaction-info">
                <div class="info-row">
                    <span>Course:</span>
                    <span><?= htmlspecialchars($transaction['course_title']) ?></span>
                </div>
                <div class="info-row">
                    <span>Amount:</span>
                    <span>LKR <?= number_format($transaction['amount'], 2) ?></span>
                </div>
                <div class="info-row">
                    <span>Transaction ID:</span>
                    <span><?= htmlspecialchars(substr($transactionId, 0, 16)) ?>...</span>
                </div>
            </div>
            
            <div class="cancel-actions">
                <a href="initiate.php?course_id=<?= htmlspecialchars($transaction['course_id']) ?>" class="btn btn-primary">
                    <i class="fas fa-credit-card"></i> Try Payment Again
                </a>
                <a href="../course/catalog.php" class="btn btn-secondary">
                    <i class="fas fa-search"></i> Browse Other Courses
                </a>
                <a href="../dashboard.php" class="btn btn-outline">
                    <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.cancel-container { max-width: 600px; margin: 0 auto; }
.cancel-card { background: white; border-radius: 12px; padding: 2rem; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-left: 4px solid #ffc107; }
.cancel-icon { font-size: 4rem; margin-bottom: 1rem; }
.cancel-content h3 { margin: 0 0 1rem 0; color: #333; }
.cancel-content p { color: #666; margin-bottom: 1rem; }
.transaction-info { background: #f8f9fa; border-radius: 8px; padding: 1rem; margin: 1.5rem 0; text-align: left; }
.info-row { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
.info-row:last-child { margin-bottom: 0; }
.cancel-actions { display: flex; gap: 1rem; justify-content: center; margin-top: 2rem; flex-wrap: wrap; }
.btn-outline { background: transparent; border: 1px solid #6c757d; color: #6c757d; }
.btn-outline:hover { background: #6c757d; color: white; }
@media (max-width: 768px) {
    .cancel-actions { flex-direction: column; }
}
</style>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Payment Cancelled', $content);
?>
<?php
require_once '../config.php';
require_once '../template.php';
require_once '../services/PaymentService.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role_name'] !== 'Admin') {
    header('Location: ../index.php');
    exit;
}

$transactionId = $_GET['id'] ?? '';
if (!$transactionId) {
    header('Location: history.php');
    exit;
}

$paymentService = new PaymentService($pdo);
$transaction = $paymentService->getTransaction($transactionId);

if (!$transaction) {
    header('Location: history.php');
    exit;
}

ob_start();
?>
<div class="transaction-details-header">
    <div class="header-content">
        <div class="title-section">
            <a href="history.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Payment History
            </a>
            <h2 class="page-title">💳 Transaction Details</h2>
            <p class="page-subtitle">Complete transaction information and status</p>
        </div>
    </div>
</div>

<div class="transaction-container">
    <div class="transaction-card">
        <div class="card-header">
            <div class="status-section">
                <span class="status-badge status-<?= strtolower($transaction['status']) ?>">
                    <?php 
                    $icons = ['paid' => '✅', 'failed' => '❌', 'pending' => '⏳', 'cancelled' => '⚠️', 'processing' => '🔄'];
                    echo $icons[strtolower($transaction['status'])] ?? '❓';
                    ?>
                    <?= htmlspecialchars($transaction['status']) ?>
                </span>
                <h3>Transaction #<?= htmlspecialchars(substr($transactionId, 0, 16)) ?>...</h3>
            </div>
        </div>
        
        <div class="details-grid">
            <div class="detail-section">
                <h4>💰 Payment Information</h4>
                <div class="detail-rows">
                    <div class="detail-row">
                        <span class="label">Amount:</span>
                        <span class="value">LKR <?= number_format($transaction['amount'], 2) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Currency:</span>
                        <span class="value"><?= htmlspecialchars($transaction['currency']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Gateway:</span>
                        <span class="value"><?= htmlspecialchars(ucfirst($transaction['gateway_name'])) ?></span>
                    </div>
                    <?php if ($transaction['gateway_transaction_id']): ?>
                    <div class="detail-row">
                        <span class="label">Gateway ID:</span>
                        <span class="value"><?= htmlspecialchars($transaction['gateway_transaction_id']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="detail-section">
                <h4>👤 Student Information</h4>
                <div class="detail-rows">
                    <div class="detail-row">
                        <span class="label">Email:</span>
                        <span class="value"><?= htmlspecialchars($transaction['student_email']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Student ID:</span>
                        <span class="value">#<?= $transaction['student_id'] ?></span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4>📚 Course Information</h4>
                <div class="detail-rows">
                    <div class="detail-row">
                        <span class="label">Course:</span>
                        <span class="value"><?= htmlspecialchars($transaction['course_title']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Course ID:</span>
                        <span class="value"><?= htmlspecialchars($transaction['course_id']) ?></span>
                    </div>
                </div>
            </div>
            
            <div class="detail-section">
                <h4>⏰ Timeline</h4>
                <div class="detail-rows">
                    <div class="detail-row">
                        <span class="label">Initiated:</span>
                        <span class="value"><?= date('M j, Y \a\t g:i A', strtotime($transaction['initiated_at'])) ?></span>
                    </div>
                    <?php if ($transaction['completed_at']): ?>
                    <div class="detail-row">
                        <span class="label">Completed:</span>
                        <span class="value"><?= date('M j, Y \a\t g:i A', strtotime($transaction['completed_at'])) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="detail-row">
                        <span class="label">Expires:</span>
                        <span class="value"><?= date('M j, Y \a\t g:i A', strtotime($transaction['expires_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($transaction['gateway_response']): ?>
        <div class="gateway-response-section">
            <h4>🔧 Gateway Response</h4>
            <div class="response-data">
                <pre><?= htmlspecialchars(json_encode(json_decode($transaction['gateway_response']), JSON_PRETTY_PRINT)) ?></pre>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($transaction['failure_reason']): ?>
        <div class="failure-section">
            <h4>❌ Failure Information</h4>
            <div class="failure-reason">
                <?= htmlspecialchars($transaction['failure_reason']) ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.transaction-container { max-width: 800px; margin: 0 auto; }
.transaction-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.card-header { background: #f8f9fa; padding: 1.5rem; border-bottom: 1px solid #eee; }
.status-section { display: flex; align-items: center; gap: 1rem; }
.status-badge { padding: 0.5rem 1rem; border-radius: 20px; font-weight: 600; }
.status-paid { background: #d4edda; color: #155724; }
.status-failed { background: #f8d7da; color: #721c24; }
.status-pending { background: #fff3cd; color: #856404; }
.status-cancelled { background: #f8d7da; color: #721c24; }
.status-processing { background: #d1ecf1; color: #0c5460; }
.details-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; padding: 2rem; }
.detail-section h4 { margin: 0 0 1rem 0; color: #333; display: flex; align-items: center; gap: 0.5rem; }
.detail-rows { space-y: 0.75rem; }
.detail-row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f0f0f0; }
.detail-row:last-child { border-bottom: none; }
.label { font-weight: 500; color: #666; }
.value { color: #333; font-weight: 600; }
.gateway-response-section, .failure-section { padding: 0 2rem 2rem; }
.response-data { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 1rem; overflow-x: auto; }
.response-data pre { margin: 0; font-size: 0.9rem; }
.failure-reason { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 6px; padding: 1rem; color: #721c24; }
</style>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Transaction Details', $content);
?>
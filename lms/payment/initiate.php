<?php
require_once '../config.php';
require_once '../template.php';
require_once '../services/PaymentService.php';
require_once '../services/PaymentGatewayInterface.php';
require_once '../services/StripeGateway.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $courseId = $_POST['course_id'];
        $gatewayName = $_POST['gateway'] ?? 'stripe';
        
        // Initialize payment service
        $paymentService = new PaymentService($pdo);
        
        // Get gateway config
        $stmt = $pdo->prepare("SELECT configuration FROM payment_gateways WHERE name = ? AND is_active = 1");
        $stmt->execute([$gatewayName]);
        $gatewayConfig = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$gatewayConfig) {
            throw new Exception('Payment gateway not available');
        }
        
        $config = json_decode($gatewayConfig['configuration'], true);
        
        // Use mock gateway for demo
        require_once '../services/MockStripeGateway.php';
        $gateway = new MockStripeGateway($config);
        $paymentService->registerGateway($gatewayName, $gateway);
        
        $result = $paymentService->initiatePayment($_SESSION['user_id'], $courseId, $gatewayName);
        
        // Redirect to payment gateway
        header('Location: ' . $result['payment_url']);
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get course details
$courseId = $_GET['course_id'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND status = 'Published'");
$stmt->execute([$courseId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    header('Location: ../course/index.php');
    exit;
}

$finalPrice = $course['price'] - ($course['discount_amount'] ?? 0);

ob_start();
?>
<div class="payment-header">
    <div class="header-content">
        <div class="title-section">
            <a href="../course/view.php?id=<?= htmlspecialchars($courseId) ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Course
            </a>
            <h2 class="page-title">💳 Complete Payment</h2>
            <p class="page-subtitle">Secure payment for course enrollment</p>
        </div>
    </div>
</div>

<?php if (isset($error)): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="payment-container">
    <div class="course-summary-card">
        <h3>📚 Course Summary</h3>
        <div class="course-details">
            <h4><?= htmlspecialchars($course['title']) ?></h4>
            <p><?= htmlspecialchars($course['description']) ?></p>
            
            <div class="course-meta">
                <span class="meta-item"><i class="fas fa-signal"></i> <?= $course['skill_level'] ?></span>
                <span class="meta-item"><i class="fas fa-clock"></i> <?= $course['validity_type'] ?></span>
            </div>
        </div>
        
        <div class="price-breakdown">
            <div class="price-row">
                <span>Course Price:</span>
                <span>LKR <?= number_format($course['price'], 2) ?></span>
            </div>
            <?php if ($course['discount_amount'] > 0): ?>
                <div class="price-row discount">
                    <span>Discount:</span>
                    <span>-LKR <?= number_format($course['discount_amount'], 2) ?></span>
                </div>
            <?php endif; ?>
            <div class="price-row total">
                <span><strong>Total Amount:</strong></span>
                <span><strong>LKR <?= number_format($finalPrice, 2) ?></strong></span>
            </div>
        </div>
    </div>
    
    <div class="payment-form-card">
        <h3>💳 Payment Method</h3>
        <form method="POST" class="payment-form">
            <input type="hidden" name="course_id" value="<?= htmlspecialchars($courseId) ?>">
            
            <div class="form-group">
                <label for="gateway">Select Payment Method:</label>
                <select name="gateway" id="gateway" class="form-control" required>
                    <option value="stripe">💳 Credit/Debit Card (Stripe)</option>
                </select>
            </div>
            
            <div class="security-info">
                <div class="security-badge">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <strong>Secure Payment</strong>
                        <p>Your payment information is encrypted and secure</p>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-credit-card"></i> Proceed to Payment
                </button>
                <a href="../course/view.php?id=<?= htmlspecialchars($courseId) ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.payment-container { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; max-width: 1000px; margin: 0 auto; }
.course-summary-card, .payment-form-card { background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
.course-details h4 { margin: 0 0 0.5rem 0; color: #333; }
.course-details p { color: #666; margin-bottom: 1rem; }
.course-meta { display: flex; gap: 1rem; margin-bottom: 1.5rem; }
.meta-item { font-size: 0.9rem; color: #888; }
.price-breakdown { border-top: 1px solid #eee; padding-top: 1rem; }
.price-row { display: flex; justify-content: space-between; margin-bottom: 0.5rem; }
.price-row.discount { color: #28a745; }
.price-row.total { border-top: 1px solid #eee; padding-top: 0.5rem; margin-top: 0.5rem; font-size: 1.1rem; }
.security-badge { display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: #f8f9fa; border-radius: 6px; margin: 1rem 0; }
.security-badge i { color: #28a745; font-size: 1.5rem; }
.security-badge p { margin: 0; font-size: 0.9rem; color: #666; }
.form-actions { display: flex; gap: 1rem; margin-top: 1.5rem; }
.btn-lg { padding: 0.75rem 1.5rem; font-size: 1.1rem; }
@media (max-width: 768px) {
    .payment-container { grid-template-columns: 1fr; }
}
</style>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Payment - ' . $course['title'], $content);
?>
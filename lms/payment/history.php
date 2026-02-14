<?php
require_once '../config.php';
require_once '../template.php';
require_once '../services/PaymentService.php';
require_once '../auth.php';

checkMenuPermission($pdo, 'payment');

$paymentService = new PaymentService($pdo);
$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$transactions = $paymentService->getTransactionHistory(null, $limit, $offset);

// Get total count for pagination
$stmt = $pdo->prepare("SELECT COUNT(*) FROM payment_transactions");
$stmt->execute();
$totalTransactions = $stmt->fetchColumn();
$totalPages = ceil($totalTransactions / $limit);

ob_start();
?>
<div class="payment-history-header">
    <div class="header-content">
        <div class="title-section">
            <h2 class="page-title">💳 Payment History</h2>
            <p class="page-subtitle">Monitor all payment transactions and enrollments</p>
        </div>
        <div class="header-actions">
            <a href="reports.php" class="btn btn-secondary">
                <i class="fas fa-chart-bar"></i> View Reports
            </a>
        </div>
    </div>
</div>

<div class="filters-card">
    <form method="GET" class="filters-form">
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" placeholder="Search by student email or course" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" class="filter-input">
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <option value="Paid" <?= ($_GET['status'] ?? '') === 'Paid' ? 'selected' : '' ?>>✅ Paid</option>
                <option value="Failed" <?= ($_GET['status'] ?? '') === 'Failed' ? 'selected' : '' ?>>❌ Failed</option>
                <option value="Pending" <?= ($_GET['status'] ?? '') === 'Pending' ? 'selected' : '' ?>>⏳ Pending</option>
                <option value="Cancelled" <?= ($_GET['status'] ?? '') === 'Cancelled' ? 'selected' : '' ?>>⚠️ Cancelled</option>
            </select>
        </div>
        <button type="submit" class="btn btn-filter">
            <i class="fas fa-filter"></i> Apply Filters
        </button>
        <?php if (!empty($_GET['search']) || !empty($_GET['status'])): ?>
            <a href="history.php" class="btn btn-clear">
                <i class="fas fa-times"></i> Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<?php if (empty($transactions)): ?>
<div class="empty-state">
    <div class="empty-icon">💳</div>
    <h3>No transactions found</h3>
    <p>No payment transactions match your current filters.</p>
</div>
<?php else: ?>
<div class="transactions-card">
    <div class="card-header">
        <h3>Transactions (<?= number_format($totalTransactions) ?>)</h3>
    </div>
    <div class="table-container">
        <table class="transactions-table">
            <thead>
                <tr>
                    <th class="col-transaction">Transaction</th>
                    <th class="col-student">Student</th>
                    <th class="col-course">Course</th>
                    <th class="col-amount">Amount</th>
                    <th class="col-status">Status</th>
                    <th class="col-gateway">Gateway</th>
                    <th class="col-date">Date</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                <tr class="transaction-row">
                    <td class="transaction-id">
                        <div class="id-display">
                            <span class="id-short"><?= htmlspecialchars(substr($transaction['id'], 0, 8)) ?>...</span>
                            <small class="id-full"><?= htmlspecialchars($transaction['id']) ?></small>
                        </div>
                    </td>
                    <td class="student-info">
                        <div class="student-email"><?= htmlspecialchars($transaction['student_email']) ?></div>
                    </td>
                    <td class="course-info">
                        <div class="course-title"><?= htmlspecialchars($transaction['course_title']) ?></div>
                    </td>
                    <td class="amount-info">
                        <span class="amount">LKR <?= number_format($transaction['amount'], 2) ?></span>
                    </td>
                    <td class="status-info">
                        <span class="status-badge status-<?= strtolower($transaction['status']) ?>">
                            <?php 
                            $icons = ['paid' => '✅', 'failed' => '❌', 'pending' => '⏳', 'cancelled' => '⚠️', 'processing' => '🔄'];
                            echo $icons[strtolower($transaction['status'])] ?? '❓';
                            ?>
                            <?= htmlspecialchars($transaction['status']) ?>
                        </span>
                    </td>
                    <td class="gateway-info">
                        <span class="gateway-name"><?= htmlspecialchars(ucfirst($transaction['gateway_name'])) ?></span>
                    </td>
                    <td class="date-info">
                        <div class="date-display">
                            <span class="date"><?= date('M j, Y', strtotime($transaction['created_at'])) ?></span>
                            <span class="time"><?= date('g:i A', strtotime($transaction['created_at'])) ?></span>
                        </div>
                    </td>
                    <td class="actions-info">
                        <div class="action-buttons">
                            <a href="transaction-details.php?id=<?= htmlspecialchars($transaction['id']) ?>" class="btn btn-view" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?><?= !empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '' ?><?= !empty($_GET['status']) ? '&status=' . urlencode($_GET['status']) : '' ?>" 
                   class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<style>
.filters-form { display: flex; gap: 1rem; align-items: end; flex-wrap: wrap; }
.filter-group { display: flex; flex-direction: column; }
.filter-group label { font-size: 0.9rem; color: #666; margin-bottom: 0.25rem; }
.filter-input, .filter-select { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
.transactions-table { width: 100%; border-collapse: collapse; }
.transactions-table th, .transactions-table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; }
.transactions-table th { background: #f8f9fa; font-weight: 600; color: #333; }
.transaction-row:hover { background: #f8f9fa; }
.id-display .id-full { display: block; color: #888; font-size: 0.8rem; }
.status-badge { padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.8rem; font-weight: 500; }
.status-paid { background: #d4edda; color: #155724; }
.status-failed { background: #f8d7da; color: #721c24; }
.status-pending { background: #fff3cd; color: #856404; }
.status-cancelled { background: #f8d7da; color: #721c24; }
.status-processing { background: #d1ecf1; color: #0c5460; }
.date-display .time { display: block; color: #888; font-size: 0.8rem; }
.pagination { display: flex; justify-content: center; gap: 0.5rem; margin-top: 1rem; }
.page-link { padding: 0.5rem 0.75rem; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333; }
.page-link:hover, .page-link.active { background: #007bff; color: white; border-color: #007bff; }
@media (max-width: 768px) {
    .filters-form { flex-direction: column; align-items: stretch; }
    .transactions-table { font-size: 0.9rem; }
    .transactions-table th, .transactions-table td { padding: 0.5rem; }
}
</style>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Payment History', $content);
?>
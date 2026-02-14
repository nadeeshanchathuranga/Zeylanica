<?php
require_once '../config.php';
require_once '../template.php';
require_once '../auth.php';

checkMenuPermission($pdo, 'payment');

// Get date range from filters
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today

// Payment statistics
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_transactions,
        SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END) as total_revenue,
        COUNT(CASE WHEN status = 'Paid' THEN 1 END) as successful_payments,
        COUNT(CASE WHEN status = 'Failed' THEN 1 END) as failed_payments,
        COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending_payments
    FROM payment_transactions 
    WHERE DATE(created_at) BETWEEN ? AND ?
");
$stmt->execute([$startDate, $endDate]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Daily revenue chart data
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END) as revenue,
        COUNT(CASE WHEN status = 'Paid' THEN 1 END) as transactions
    FROM payment_transactions 
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date
");
$stmt->execute([$startDate, $endDate]);
$dailyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Top courses by revenue
$stmt = $pdo->prepare("
    SELECT 
        c.title,
        COUNT(pt.id) as enrollments,
        SUM(CASE WHEN pt.status = 'Paid' THEN pt.amount ELSE 0 END) as revenue
    FROM payment_transactions pt
    JOIN courses c ON pt.course_id = c.id
    WHERE DATE(pt.created_at) BETWEEN ? AND ? AND pt.status = 'Paid'
    GROUP BY c.id, c.title
    ORDER BY revenue DESC
    LIMIT 10
");
$stmt->execute([$startDate, $endDate]);
$topCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="reports-header">
    <div class="header-content">
        <div class="title-section">
            <a href="history.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Payment History
            </a>
            <h2 class="page-title">📊 Payment Reports</h2>
            <p class="page-subtitle">Analytics and insights for payment transactions</p>
        </div>
    </div>
</div>

<div class="filters-card">
    <form method="GET" class="date-filter-form">
        <div class="filter-group">
            <label>Start Date</label>
            <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="filter-input">
        </div>
        <div class="filter-group">
            <label>End Date</label>
            <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="filter-input">
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-chart-line"></i> Generate Report
        </button>
    </form>
</div>

<div class="stats-grid">
    <div class="stat-card revenue">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <h3>Total Revenue</h3>
            <div class="stat-value">LKR <?= number_format($stats['total_revenue'], 2) ?></div>
        </div>
    </div>
    
    <div class="stat-card transactions">
        <div class="stat-icon">📊</div>
        <div class="stat-content">
            <h3>Total Transactions</h3>
            <div class="stat-value"><?= number_format($stats['total_transactions']) ?></div>
        </div>
    </div>
    
    <div class="stat-card success">
        <div class="stat-icon">✅</div>
        <div class="stat-content">
            <h3>Successful Payments</h3>
            <div class="stat-value"><?= number_format($stats['successful_payments']) ?></div>
        </div>
    </div>
    
    <div class="stat-card failed">
        <div class="stat-icon">❌</div>
        <div class="stat-content">
            <h3>Failed Payments</h3>
            <div class="stat-value"><?= number_format($stats['failed_payments']) ?></div>
        </div>
    </div>
</div>

<?php if (!empty($dailyData)): ?>
<div class="chart-section">
    <div class="chart-card">
        <h3>📈 Daily Revenue Trend</h3>
        <div class="chart-container">
            <canvas id="revenueChart" width="400" height="200"></canvas>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($topCourses)): ?>
<div class="top-courses-section">
    <div class="courses-card">
        <h3>🏆 Top Performing Courses</h3>
        <div class="table-container">
            <table class="courses-table">
                <thead>
                    <tr>
                        <th>Course Title</th>
                        <th>Enrollments</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topCourses as $course): ?>
                    <tr>
                        <td class="course-title"><?= htmlspecialchars($course['title']) ?></td>
                        <td class="enrollments"><?= number_format($course['enrollments']) ?></td>
                        <td class="revenue">LKR <?= number_format($course['revenue'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.date-filter-form { display: flex; gap: 1rem; align-items: end; }
.filter-group { display: flex; flex-direction: column; }
.filter-group label { font-size: 0.9rem; color: #666; margin-bottom: 0.25rem; }
.filter-input { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.stat-card { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 1rem; }
.stat-card.revenue { border-left: 4px solid #28a745; }
.stat-card.transactions { border-left: 4px solid #007bff; }
.stat-card.success { border-left: 4px solid #28a745; }
.stat-card.failed { border-left: 4px solid #dc3545; }
.stat-icon { font-size: 2.5rem; }
.stat-content h3 { margin: 0 0 0.5rem 0; color: #666; font-size: 0.9rem; }
.stat-value { font-size: 1.8rem; font-weight: bold; color: #333; }
.chart-section, .top-courses-section { margin-bottom: 2rem; }
.chart-card, .courses-card { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.chart-card h3, .courses-card h3 { margin: 0 0 1.5rem 0; color: #333; }
.chart-container { position: relative; height: 300px; }
.courses-table { width: 100%; border-collapse: collapse; }
.courses-table th, .courses-table td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #eee; }
.courses-table th { background: #f8f9fa; font-weight: 600; color: #333; }
.courses-table tr:hover { background: #f8f9fa; }
.revenue { font-weight: 600; color: #28a745; }
@media (max-width: 768px) {
    .date-filter-form { flex-direction: column; align-items: stretch; }
    .stats-grid { grid-template-columns: 1fr; }
}
</style>

<?php if (!empty($dailyData)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($dailyData, 'date')) ?>,
        datasets: [{
            label: 'Daily Revenue (LKR)',
            data: <?= json_encode(array_column($dailyData, 'revenue')) ?>,
            borderColor: '#007bff',
            backgroundColor: 'rgba(0, 123, 255, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'LKR ' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Revenue: LKR ' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});
</script>
<?php endif; ?>

<?php
$content = ob_get_clean();
echo renderAdminTemplate('Payment Reports', $content);
?>
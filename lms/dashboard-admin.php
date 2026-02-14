<?php
require_once 'config.php';
require_once 'template.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_name'] !== 'Admin') {
    header('Location: index.php');
    exit;
}

// System statistics
$user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$course_count = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$enrollment_count = $pdo->query("SELECT COUNT(*) FROM course_enrollments WHERE status = 'Active'")->fetchColumn();

// Revenue statistics
$stmt = $pdo->prepare("SELECT SUM(amount) FROM payment_transactions WHERE status = 'Paid' AND DATE(created_at) >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stmt->execute();
$monthly_revenue = $stmt->fetchColumn() ?: 0;

// Recent activities
$stmt = $pdo->prepare("
    SELECT 'enrollment' as type, ce.created_at, u.email, c.title as course_title
    FROM course_enrollments ce
    JOIN users u ON ce.student_id = u.id
    JOIN courses c ON ce.course_id = c.id
    WHERE ce.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    UNION ALL
    SELECT 'payment' as type, pt.created_at, u.email, c.title as course_title
    FROM payment_transactions pt
    JOIN users u ON pt.student_id = u.id
    JOIN courses c ON pt.course_id = c.id
    WHERE pt.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND pt.status = 'Paid'
    ORDER BY created_at DESC
    LIMIT 10
");
$stmt->execute();
$recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard-container admin-dashboard">
    <div class="welcome-section">
        <h2>🛠️ Admin Dashboard</h2>
        <p>System overview and management controls</p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card users">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3>Total Users</h3>
                <div class="stat-value"><?= number_format($user_count) ?></div>
                <a href="users.php" class="stat-link">Manage Users</a>
            </div>
        </div>
        
        <div class="stat-card courses">
            <div class="stat-icon">📚</div>
            <div class="stat-content">
                <h3>Total Courses</h3>
                <div class="stat-value"><?= number_format($course_count) ?></div>
                <a href="course/index.php" class="stat-link">Manage Courses</a>
            </div>
        </div>
        
        <div class="stat-card enrollments">
            <div class="stat-icon">🎓</div>
            <div class="stat-content">
                <h3>Active Enrollments</h3>
                <div class="stat-value"><?= number_format($enrollment_count) ?></div>
                <a href="payment/history.php" class="stat-link">View Details</a>
            </div>
        </div>
        
        <div class="stat-card revenue">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <h3>Monthly Revenue</h3>
                <div class="stat-value">LKR <?= number_format($monthly_revenue, 2) ?></div>
                <a href="payment/reports.php" class="stat-link">View Reports</a>
            </div>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <div class="quick-actions-section">
            <h3>⚡ Quick Actions</h3>
            <div class="actions-grid">
                <a href="users.php" class="action-card">
                    <div class="action-icon">👤</div>
                    <div class="action-content">
                        <h4>User Management</h4>
                        <p>Add, edit, or manage user accounts</p>
                    </div>
                </a>
                
                <a href="course/create.php" class="action-card">
                    <div class="action-icon">➕</div>
                    <div class="action-content">
                        <h4>Create Course</h4>
                        <p>Add new educational content</p>
                    </div>
                </a>
                
                <a href="payment/history.php" class="action-card">
                    <div class="action-icon">💳</div>
                    <div class="action-content">
                        <h4>Payment History</h4>
                        <p>Monitor transactions and payments</p>
                    </div>
                </a>
                
                <a href="roles.php" class="action-card">
                    <div class="action-icon">🔐</div>
                    <div class="action-content">
                        <h4>Role Management</h4>
                        <p>Configure user permissions</p>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="recent-activities-section">
            <h3>📊 Recent Activities</h3>
            <?php if (empty($recent_activities)): ?>
                <p>No recent activities.</p>
            <?php else: ?>
                <div class="activities-list">
                    <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <?= $activity['type'] === 'enrollment' ? '🎓' : '💳' ?>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">
                                    <strong><?= htmlspecialchars($activity['email']) ?></strong>
                                    <?= $activity['type'] === 'enrollment' ? 'enrolled in' : 'paid for' ?>
                                    <em><?= htmlspecialchars($activity['course_title']) ?></em>
                                </div>
                                <div class="activity-time">
                                    <?= date('M j, g:i A', strtotime($activity['created_at'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.admin-dashboard { max-width: 1200px; margin: 0 auto; }
.welcome-section { text-align: center; margin-bottom: 2rem; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.stat-card { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 1rem; }
.stat-card.users { border-left: 4px solid #007bff; }
.stat-card.courses { border-left: 4px solid #28a745; }
.stat-card.enrollments { border-left: 4px solid #ffc107; }
.stat-card.revenue { border-left: 4px solid #dc3545; }
.stat-icon { font-size: 2.5rem; }
.stat-content h3 { margin: 0 0 0.5rem 0; color: #666; font-size: 0.9rem; }
.stat-value { font-size: 1.8rem; font-weight: bold; color: #333; margin-bottom: 0.5rem; }
.stat-link { color: #007bff; text-decoration: none; font-size: 0.9rem; }
.dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; }
.quick-actions-section, .recent-activities-section { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.action-card { display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid #e0e0e0; border-radius: 8px; text-decoration: none; color: inherit; transition: all 0.2s; }
.action-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.action-icon { font-size: 2rem; }
.action-content h4 { margin: 0 0 0.25rem 0; color: #333; }
.action-content p { margin: 0; color: #666; font-size: 0.9rem; }
.activities-list { max-height: 400px; overflow-y: auto; }
.activity-item { display: flex; align-items: center; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0; }
.activity-item:last-child { border-bottom: none; }
.activity-icon { font-size: 1.5rem; }
.activity-text { font-size: 0.9rem; }
.activity-time { font-size: 0.8rem; color: #888; }
@media (max-width: 768px) {
    .dashboard-grid { grid-template-columns: 1fr; }
    .stats-grid { grid-template-columns: 1fr; }
}
</style>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Admin Dashboard', $content);
?>
<link rel="stylesheet" href="assets/css/dashboard.css">
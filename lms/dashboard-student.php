<?php
require_once 'config.php';
require_once 'template.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Allow access for students (User role) - no role restriction for student dashboard

// Get enrolled courses for students
$stmt = $pdo->prepare("
    SELECT ce.*, c.title, c.description, c.thumbnail_path, c.price, c.validity_type, c.validity_months
    FROM course_enrollments ce
    JOIN courses c ON ce.course_id = c.id
    WHERE ce.student_id = ? AND ce.status = 'Active'
    ORDER BY ce.enrolled_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$enrolledCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent transactions
$stmt = $pdo->prepare("
    SELECT pt.*, c.title as course_title
    FROM payment_transactions pt
    JOIN courses c ON pt.course_id = c.id
    WHERE pt.student_id = ?
    ORDER BY pt.created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recentTransactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recommended courses (not enrolled)
$stmt = $pdo->prepare("
    SELECT c.* FROM courses c
    WHERE c.status = 'Published' 
    AND c.id NOT IN (
        SELECT course_id FROM course_enrollments 
        WHERE student_id = ? AND status = 'Active'
    )
    ORDER BY c.created_at DESC
    LIMIT 4
");
$stmt->execute([$_SESSION['user_id']]);
$recommendedCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="dashboard-container student-dashboard">
    <div class="welcome-section">
        <h2>🎓 Welcome back, Student!</h2>
        <p>Continue your learning journey and explore new courses</p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card enrolled">
            <div class="stat-icon">📚</div>
            <div class="stat-content">
                <h3>Enrolled Courses</h3>
                <div class="stat-value"><?= count($enrolledCourses) ?></div>
                <div class="stat-detail">Active enrollments</div>
            </div>
        </div>
        
        <div class="stat-card transactions">
            <div class="stat-icon">💳</div>
            <div class="stat-content">
                <h3>Transactions</h3>
                <div class="stat-value"><?= count($recentTransactions) ?></div>
                <div class="stat-detail">Payment history</div>
            </div>
        </div>
        
        <div class="stat-card available">
            <div class="stat-icon">🔍</div>
            <div class="stat-content">
                <h3>Available Courses</h3>
                <div class="stat-value"><?= count($recommendedCourses) ?></div>
                <div class="stat-detail">Ready to enroll</div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <div class="quick-actions-section">
            <h3>⚡ Quick Actions</h3>
            <div class="actions-grid">
                <a href="course/catalog.php" class="action-card">
                    <div class="action-icon">🔍</div>
                    <div class="action-content">
                        <h4>Browse Courses</h4>
                        <p>Discover new learning opportunities</p>
                    </div>
                </a>
                
                <a href="profile.php" class="action-card">
                    <div class="action-icon">👤</div>
                    <div class="action-content">
                        <h4>Edit Profile</h4>
                        <p>Update your personal information</p>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="recent-activities-section">
            <h3>💳 Recent Transactions</h3>
            <?php if (empty($recentTransactions)): ?>
                <p>No transactions yet.</p>
            <?php else: ?>
                <div class="activities-list">
                    <?php foreach ($recentTransactions as $transaction): ?>
                        <div class="activity-item">
                            <div class="activity-icon">💳</div>
                            <div class="activity-content">
                                <div class="activity-text">
                                    <strong><?= htmlspecialchars($transaction['course_title']) ?></strong>
                                    - LKR <?= number_format($transaction['amount'], 2) ?>
                                </div>
                                <div class="activity-time">
                                    <?= date('M j, g:i A', strtotime($transaction['created_at'])) ?>
                                    <span class="status status-<?= strtolower($transaction['status']) ?>"><?= $transaction['status'] ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<link rel="stylesheet" href="assets/css/dashboard.css">
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Student Dashboard', $content);
?>
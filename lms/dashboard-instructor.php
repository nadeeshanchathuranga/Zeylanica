<?php
require_once 'config.php';
require_once 'template.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_name'] !== 'Instructor') {
    header('Location: index.php');
    exit;
}

// Instructor's courses
$stmt = $pdo->prepare("
    SELECT c.*, COUNT(DISTINCT ce.student_id) as enrolled_students
    FROM courses c
    LEFT JOIN course_instructors ci ON c.id = ci.course_id
    LEFT JOIN course_enrollments ce ON c.id = ce.course_id AND ce.status = 'Active'
    WHERE ci.instructor_id = ? OR c.created_by = ?
    GROUP BY c.id
    ORDER BY c.created_at DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$instructor_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Course statistics
$total_courses = count($instructor_courses);
$total_students = array_sum(array_column($instructor_courses, 'enrolled_students'));
$published_courses = count(array_filter($instructor_courses, fn($c) => $c['status'] === 'Published'));

// Recent enrollments
$stmt = $pdo->prepare("
    SELECT ce.*, c.title as course_title, u.email as student_email
    FROM course_enrollments ce
    JOIN courses c ON ce.course_id = c.id
    JOIN users u ON ce.student_id = u.id
    LEFT JOIN course_instructors ci ON c.id = ci.course_id
    WHERE (ci.instructor_id = ? OR c.created_by = ?) AND ce.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY ce.created_at DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$recent_enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="instructor-dashboard">
    <div class="welcome-section">
        <h2>👨‍🏫 Instructor Dashboard</h2>
        <p>Manage your courses and track student progress</p>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card courses">
            <div class="stat-icon">📚</div>
            <div class="stat-content">
                <h3>My Courses</h3>
                <div class="stat-value"><?= $total_courses ?></div>
                <div class="stat-detail"><?= $published_courses ?> published</div>
            </div>
        </div>
        
        <div class="stat-card students">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <h3>Total Students</h3>
                <div class="stat-value"><?= $total_students ?></div>
                <div class="stat-detail">Across all courses</div>
            </div>
        </div>
        
        <div class="stat-card enrollments">
            <div class="stat-icon">📈</div>
            <div class="stat-content">
                <h3>Recent Enrollments</h3>
                <div class="stat-value"><?= count($recent_enrollments) ?></div>
                <div class="stat-detail">This week</div>
            </div>
        </div>
    </div>
    
    <div class="dashboard-grid">
        <div class="quick-actions-section">
            <h3>⚡ Quick Actions</h3>
            <div class="actions-grid">
                <a href="course/create.php" class="action-card">
                    <div class="action-icon">➕</div>
                    <div class="action-content">
                        <h4>Create New Course</h4>
                        <p>Add new educational content</p>
                    </div>
                </a>
                
                <a href="course/index.php" class="action-card">
                    <div class="action-icon">📚</div>
                    <div class="action-content">
                        <h4>Manage Courses</h4>
                        <p>Edit and organize your courses</p>
                    </div>
                </a>
                
                <a href="profile.php" class="action-card">
                    <div class="action-icon">👤</div>
                    <div class="action-content">
                        <h4>Edit Profile</h4>
                        <p>Update your instructor profile</p>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="recent-activities-section">
            <h3>🎓 Recent Enrollments</h3>
            <?php if (empty($recent_enrollments)): ?>
                <p>No recent enrollments.</p>
            <?php else: ?>
                <div class="activities-list">
                    <?php foreach ($recent_enrollments as $enrollment): ?>
                        <div class="activity-item">
                            <div class="activity-icon">🎓</div>
                            <div class="activity-content">
                                <div class="activity-text">
                                    <strong><?= htmlspecialchars($enrollment['student_email']) ?></strong>
                                    enrolled in
                                    <em><?= htmlspecialchars($enrollment['course_title']) ?></em>
                                </div>
                                <div class="activity-time">
                                    <?= date('M j, g:i A', strtotime($enrollment['created_at'])) ?>
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
.instructor-dashboard { max-width: 1200px; margin: 0 auto; }
.welcome-section { text-align: center; margin-bottom: 2rem; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.stat-card { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 1rem; }
.stat-card.courses { border-left: 4px solid #28a745; }
.stat-card.students { border-left: 4px solid #007bff; }
.stat-card.enrollments { border-left: 4px solid #ffc107; }
.stat-icon { font-size: 2.5rem; }
.stat-content h3 { margin: 0 0 0.5rem 0; color: #666; font-size: 0.9rem; }
.stat-value { font-size: 1.8rem; font-weight: bold; color: #333; }
.stat-detail { font-size: 0.8rem; color: #888; }
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
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Instructor Dashboard', $content);
?>
<link rel="stylesheet" href="assets/css/dashboard.css">
<?php
require_once '../config.php';
require_once '../template.php';
require_once '../auth.php';

checkMenuPermission($pdo, 'lessons');

// Get enrolled courses (simplified view)
$stmt = $pdo->prepare("
    SELECT ce.*, c.title, c.description, c.thumbnail_path, c.price, 
           c.validity_type, c.validity_months,
           MAX(up.full_name) as instructor_name,
           COUNT(l.id) as total_lessons,
           COUNT(CASE WHEN la.completion_status = 1 THEN 1 END) as completed_lessons,
           SUM(l.video_duration) as total_duration
    FROM course_enrollments ce
    JOIN courses c ON ce.course_id = c.id
    LEFT JOIN course_instructors ci ON c.id = ci.course_id
    LEFT JOIN users u ON ci.instructor_id = u.id
    LEFT JOIN user_profiles up ON u.id = up.user_id
    LEFT JOIN lessons l ON c.id = l.course_id AND l.visibility_status = 'Visible'
    LEFT JOIN lesson_analytics la ON l.id = la.lesson_id AND la.student_id = ce.student_id
    WHERE ce.student_id = ?
    GROUP BY ce.id, c.id
    ORDER BY ce.enrolled_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<div class="payment-history-header">
    <div class="header-content">
        <div class="title-section">
            <h2 class="page-title">My Learning Dashboard</h2>
            <p class="page-subtitle">Track your progress and continue your learning journey</p>
        </div>
        <div class="header-actions">
            <a href="../course/catalog.php" class="btn btn-primary">
                <i class="fas fa-search"></i> Browse More Courses
            </a>
        </div>
    </div>
</div>

<div class="enrolled-courses-container">
    <?php if (empty($enrollments)): ?>
        <div class="empty-state">
            <div class="empty-icon">📚</div>
            <h3>No Enrolled Courses</h3>
            <p>You haven't enrolled in any courses yet. Start your learning journey today!</p>
            <a href="../course/catalog.php" class="btn btn-primary">Browse Courses</a>
        </div>
    <?php else: ?>
        <div class="courses-list">
            <?php foreach ($enrollments as $enrollment): ?>
                <?php
                $progress = $enrollment['total_lessons'] > 0 
                    ? round(($enrollment['completed_lessons'] / $enrollment['total_lessons']) * 100) 
                    : 0;
                $isExpired = $enrollment['expires_at'] && strtotime($enrollment['expires_at']) < time();
                ?>
                <div class="course-card <?= $isExpired ? 'expired' : '' ?>" onclick="viewCourse('<?= $enrollment['course_id'] ?>')">
                    <div class="course-thumbnail">
                        <?php if ($enrollment['thumbnail_path']): ?>
                            <img src="<?= BASE_URL ?>course/<?= htmlspecialchars($enrollment['thumbnail_path']) ?>" alt="Course thumbnail">
                        <?php else: ?>
                            <div class="thumbnail-placeholder">📚</div>
                        <?php endif; ?>
                    </div>
                    <div class="course-info">
                        <h3 class="course-title"><?= htmlspecialchars($enrollment['title']) ?></h3>
                        <div class="course-meta">
                            <span><i class="fas fa-video"></i> <?= $enrollment['total_lessons'] ?> lessons</span>
                            <span><i class="fas fa-clock"></i> <?= gmdate('H:i:s', $enrollment['total_duration'] ?? 0) ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                            <span class="progress-text"><?= $progress ?>% Complete</span>
                        </div>
                    </div>
                    <div class="course-status">
                        <span class="status-badge status-<?= strtolower($enrollment['status']) ?>">
                            <?= $enrollment['status'] ?>
                        </span>
                        <?php if ($isExpired): ?>
                            <span class="expiry-notice">Expired</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function viewCourse(courseId) {
    window.location.href = 'course-view.php?id=' + courseId;
}
</script>

<style>
<link rel="stylesheet" href="assets/css/common-headers.css">
<style>
.empty-state { text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.empty-icon { font-size: 4rem; margin-bottom: 1rem; }
.empty-state h3 { color: #374151; margin-bottom: 0.5rem; }
.empty-state p { color: #6b7280; margin-bottom: 2rem; }

.courses-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
@media (max-width: 768px) {
    .courses-list { grid-template-columns: 1fr; gap: 1rem; }
}
@media (min-width: 769px) and (max-width: 1024px) {
    .courses-list { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
}
@media (min-width: 1025px) {
    .courses-list { grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); }
}
.course-card { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; }
.course-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
.course-card.expired { opacity: 0.8; border: 2px solid #ef4444; }

.course-thumbnail { width: 100%; aspect-ratio: 16/9; background: #f3f4f6; position: relative; overflow: hidden; }
@media (max-width: 480px) {
    .course-thumbnail { aspect-ratio: 4/3; }
}
.course-thumbnail img { width: 100%; height: 100%; object-fit: cover; }
.thumbnail-placeholder { display: flex; align-items: center; justify-content: center; height: 100%; font-size: 4rem; color: #9ca3af; }

.course-info { padding: 1rem; }
@media (min-width: 769px) {
    .course-info { padding: 1.5rem; }
}
.course-title { font-size: 1.25rem; font-weight: 600; color: #111827; margin-bottom: 1rem; }
.course-meta { display: flex; flex-wrap: wrap; gap: 0.75rem; font-size: 0.875rem; color: #6b7280; margin-bottom: 1rem; }
@media (max-width: 480px) {
    .course-meta { flex-direction: column; gap: 0.5rem; }
}
.course-meta span { display: flex; align-items: center; gap: 0.25rem; }

.progress-bar { background: #e5e7eb; border-radius: 8px; height: 8px; position: relative; margin-bottom: 1rem; }
.progress-fill { background: #10b981; height: 100%; border-radius: 8px; transition: width 0.3s; }
.progress-text { font-size: 0.875rem; color: #6b7280; margin-top: 0.5rem; }

.course-status { padding: 0 1.5rem 1.5rem; }
.status-badge { padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.875rem; font-weight: 600; }
.status-active { background: #10b981; color: white; }
.status-expired { background: #ef4444; color: white; }
.expiry-notice { color: #ef4444; font-size: 0.875rem; margin-left: 0.5rem; }
</style>

<?php
$content = ob_get_clean();
echo renderAdminTemplate('My Courses', $content);
?>
<?php
require_once '../config.php';
require_once '../template.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}
$hasPermission = false;
if (isset($_SESSION['role_id'])) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM role_permissions rp 
        JOIN menu_items mi ON rp.menu_item_id = mi.id 
        WHERE rp.role_id = ?");
    $stmt->execute([$_SESSION['role_id']]);
    $hasPermission = $stmt->fetchColumn() > 0;
}

if (!$hasPermission) {
    header('Location: ../dashboard.php');
    exit();
}

$courseId = $_GET['id'] ?? '';
if (!$courseId) {
    header('Location: enrolled-courses.php');
    exit;
}

// Check if user is enrolled in this course
$stmt = $pdo->prepare("
    SELECT ce.*, c.title, c.description, c.thumbnail_path, c.price, 
           c.validity_type, c.validity_months, c.target_audience, c.skill_level,
           MAX(up.full_name) as instructor_name
    FROM course_enrollments ce
    JOIN courses c ON ce.course_id = c.id
    LEFT JOIN course_instructors ci ON c.id = ci.course_id
    LEFT JOIN users u ON ci.instructor_id = u.id
    LEFT JOIN user_profiles up ON u.id = up.user_id
    WHERE ce.course_id = ? AND ce.student_id = ?
    GROUP BY ce.id, c.id
");
$stmt->execute([$courseId, $_SESSION['user_id']]);
$enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$enrollment) {
    header('Location: enrolled-courses.php');
    exit;
}

// Get course lessons with progress
$stmt = $pdo->prepare("
    SELECT l.*, 
           COALESCE(la.completion_status, 0) as is_completed,
           la.last_viewed_at,
           la.total_time_spent,
           la.view_count
    FROM lessons l
    LEFT JOIN lesson_analytics la ON l.id = la.lesson_id AND la.student_id = ?
    WHERE l.course_id = ? AND l.visibility_status = 'Visible'
    ORDER BY l.lesson_order ASC
");
$stmt->execute([$_SESSION['user_id'], $courseId]);
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate progress
$totalLessons = count($lessons);
$completedLessons = array_sum(array_column($lessons, 'is_completed'));
$progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

$isExpired = $enrollment['expires_at'] && strtotime($enrollment['expires_at']) < time();

ob_start();
?>
<div class="payment-history-header">
    <div class="header-content">
        <div class="title-section">
            <a href="enrolled-courses.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to My Courses</a>
            <h2 class="page-title"><?= htmlspecialchars($enrollment['title']) ?></h2>
        </div>
        <div class="header-actions">
            <div class="progress-info">
                <span class="progress-text"><?= $progress ?>% Complete</span>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="course-content">
    <div class="course-details-section">
        <div class="course-overview">
            <div class="course-thumbnail">
                <?php if ($enrollment['thumbnail_path']): ?>
                    <img src="<?= BASE_URL ?>course/<?= htmlspecialchars($enrollment['thumbnail_path']) ?>" alt="Course thumbnail">
                <?php else: ?>
                    <div class="thumbnail-placeholder">📚</div>
                <?php endif; ?>
            </div>
            <div class="course-info">
                <h3>Course Overview</h3>
                <p class="description"><?= nl2br(htmlspecialchars($enrollment['description'])) ?></p>
                <div class="course-stats">
                    <div class="stat-item">
                        <i class="fas fa-video"></i>
                        <span><?= $totalLessons ?> Lessons</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-signal"></i>
                        <span><?= htmlspecialchars($enrollment['skill_level']) ?></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-users"></i>
                        <span><?= htmlspecialchars($enrollment['target_audience']) ?></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-calendar"></i>
                        <span>Enrolled <?= date('M j, Y', strtotime($enrollment['enrolled_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="lessons-section">
        <div class="lessons-header">
            <h3><i class="fas fa-play-circle"></i> Course Lessons</h3>
            <span class="lessons-count"><?= $completedLessons ?> of <?= $totalLessons ?> completed</span>
        </div>
        
        <?php if (empty($lessons)): ?>
            <div class="no-lessons">
                <i class="fas fa-video-slash"></i>
                <p>No lessons available for this course yet.</p>
            </div>
        <?php else: ?>
            <div class="lessons-list">
                <?php foreach ($lessons as $index => $lesson): ?>
                    <div class="lesson-card <?= $lesson['is_completed'] ? 'completed' : '' ?>">
                        <div class="lesson-number"><?= $index + 1 ?></div>
                        <div class="lesson-info">
                            <h4 class="lesson-title"><?= htmlspecialchars($lesson['title']) ?></h4>
                            <div class="lesson-meta">
                                <span><i class="fas fa-clock"></i> <?= gmdate('i:s', $lesson['video_duration'] ?? 0) ?></span>
                                <?php if ($lesson['access_permissions'] === 'Free Preview'): ?>
                                    <span class="free-badge">🆓 Free</span>
                                <?php endif; ?>
                                <?php if ($lesson['view_count'] > 0): ?>
                                    <span><i class="fas fa-eye"></i> Viewed <?= $lesson['view_count'] ?> times</span>
                                <?php endif; ?>
                                <?php if ($lesson['last_viewed_at']): ?>
                                    <span class="last-viewed">Last watched: <?= date('M j, Y', strtotime($lesson['last_viewed_at'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="lesson-status">
                            <?php if ($lesson['is_completed']): ?>
                                <i class="fas fa-check-circle completed"></i>
                            <?php else: ?>
                                <i class="fas fa-play-circle"></i>
                            <?php endif; ?>
                        </div>
                        <div class="lesson-actions">
                            <?php if (!$isExpired && $enrollment['status'] === 'Active'): ?>
                                <a href="lesson-watch.php?id=<?= $lesson['id'] ?>" class="btn-watch">
                                    <i class="fas fa-play"></i> <?= $lesson['is_completed'] ? 'Rewatch' : 'Watch' ?>
                                </a>
                            <?php else: ?>
                                <span class="btn-disabled">
                                    <i class="fas fa-lock"></i> Locked
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
<link rel="stylesheet" href="../assets/css/common-headers.css">
<style>
.course-details-section { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.course-overview { display: flex; gap: 2rem; }
.course-thumbnail { width: 200px; aspect-ratio: 4/3; border-radius: 8px; overflow: hidden; background: #f3f4f6; flex-shrink: 0; }
@media (max-width: 768px) {
    .course-thumbnail { width: 100%; aspect-ratio: 16/9; }
}
.course-thumbnail img { width: 100%; height: 100%; object-fit: cover; }
.thumbnail-placeholder { display: flex; align-items: center; justify-content: center; height: 100%; font-size: 3rem; color: #9ca3af; }
.course-info h3 { font-size: 1.5rem; font-weight: 600; color: #111827; margin-bottom: 1rem; }
.description { color: #6b7280; line-height: 1.6; margin-bottom: 1.5rem; }
.course-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.stat-item { display: flex; align-items: center; gap: 0.5rem; color: #6b7280; }

.lessons-section { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.lessons-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.lessons-header h3 { font-size: 1.5rem; font-weight: 600; color: #111827; }
.lessons-count { color: #6b7280; font-size: 0.875rem; }

.no-lessons { text-align: center; padding: 3rem; color: #6b7280; }
.no-lessons i { font-size: 3rem; margin-bottom: 1rem; }

.lessons-list { display: flex; flex-direction: column; gap: 1rem; }
.lesson-card { display: flex; align-items: center; gap: 1rem; padding: 1.5rem; border-radius: 8px; background: #f9fafb; transition: all 0.2s; }
.lesson-card:hover { background: #f3f4f6; }
.lesson-card.completed { background: #f0fdf4; border-left: 4px solid #10b981; }

.lesson-number { width: 40px; height: 40px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-weight: 600; color: #374151; }
.lesson-card.completed .lesson-number { background: #10b981; color: white; }

.lesson-info { flex: 1; }
.lesson-title { font-size: 1.125rem; font-weight: 600; color: #111827; margin-bottom: 0.5rem; }
.lesson-meta { display: flex; gap: 1rem; font-size: 0.875rem; color: #6b7280; }
.lesson-meta span { display: flex; align-items: center; gap: 0.25rem; }
.free-badge { background: #4f46e5; color: white; padding: 0.125rem 0.5rem; border-radius: 4px; }
.last-viewed { font-style: italic; }

.lesson-status { margin-right: 1rem; }
.lesson-status i { font-size: 1.5rem; }
.lesson-status .completed { color: #10b981; }
.lesson-status .fas.fa-play-circle { color: #6b7280; }

.btn-watch { background: #4f46e5; color: white; padding: 0.75rem 1.5rem; border-radius: 6px; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; transition: background 0.2s; }
.btn-watch:hover { background: #4338ca; }
.btn-disabled { background: #e5e7eb; color: #9ca3af; padding: 0.75rem 1.5rem; border-radius: 6px; display: flex; align-items: center; gap: 0.5rem; }

.progress-info { text-align: right; }
.progress-text { font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem; }
.progress-bar { width: 200px; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
.progress-fill { height: 100%; background: #10b981; transition: width 0.3s; }

.back-link { color: #4f46e5; text-decoration: none; margin-bottom: 1rem; display: inline-block; }
.back-link:hover { text-decoration: underline; }

@media (max-width: 768px) {
    .course-overview { flex-direction: column; }
    .course-thumbnail { width: 100%; height: 200px; }
    .lesson-card { flex-direction: column; align-items: flex-start; }
    .lesson-actions { width: 100%; }
}
</style>

<?php
$content = ob_get_clean();
echo renderAdminTemplate('Course Details - ' . $enrollment['title'], $content);
?>
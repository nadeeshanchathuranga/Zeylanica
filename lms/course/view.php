<?php
require_once '../config.php';
require_once '../template.php';
require_once '../services/CourseManagementService.php';
require_once '../services/LessonManagementService.php';
require_once '../auth.php';

checkMenuPermission($pdo, 'course');

$courseService = new CourseManagementService($pdo);
$lessonService = new LessonManagementService($pdo);

$course = $courseService->getCourse($courseId);
if (!$course) {
    header('Location: index.php');
    exit;
}
$isStudent = $_SESSION['role_name'] === 'User';
$isAdminOrInstructor = in_array($_SESSION['role_name'], ['Admin', 'Instructor']);
$isEnrolled = false;

if ($isStudent) {
    $stmt = $pdo->prepare("
        SELECT 1 FROM course_enrollments 
        WHERE course_id = ? AND student_id = ? AND status = 'Active'
    ");
    $stmt->execute([$courseId, $_SESSION['user_id']]);
    $isEnrolled = (bool)$stmt->fetch();
    
    // If not enrolled and not admin/instructor, show payment option
    if (!$isEnrolled && !$isAdminOrInstructor) {
        // Allow viewing course details but not lessons
    }
}

try {
    $lessons = $isEnrolled || $isAdminOrInstructor ? $lessonService->getLessons($courseId, $isAdminOrInstructor) : [];
} catch (Exception $e) {
    $lessons = [];
    $error = 'Error loading lessons: ' . $e->getMessage();
}

$success = $_GET['success'] ?? null;
if ($success === 'created') {
    $success = 'Lesson created successfully';
} elseif ($success === 'deleted') {
    $success = 'Lesson deleted successfully';
}

// Handle lesson deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && $isAdminOrInstructor) {
    try {
        $lessonService->deleteLesson($_POST['lesson_id']);
        header('Location: view.php?id=' . $courseId . '&success=deleted');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

ob_start();
?>
<div class="course-view-header">
    <div class="header-content">
        <div class="title-section">
            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Courses</a>
            <h2 class="page-title">📚 <?= htmlspecialchars($course['title']) ?></h2>
            <p class="page-subtitle"><?= htmlspecialchars($course['description']) ?></p>
            <div class="course-meta">
                <span class="meta-item"><i class="fas fa-layer-group"></i> <?= htmlspecialchars($course['category_name'] ?? 'Uncategorized') ?></span>
                <span class="meta-item"><i class="fas fa-signal"></i> <?= $course['skill_level'] ?></span>
                <span class="meta-item"><i class="fas fa-video"></i> <?= count($lessons) ?> Lessons</span>
                <span class="meta-item"><i class="fas fa-clock"></i> <?= gmdate('H:i:s', $course['total_duration_seconds'] ?? 0) ?></span>
            </div>
        </div>
        <div class="header-actions">
            <?php if ($isStudent && !$isEnrolled): ?>
            <div class="enrollment-section">
                <div class="price-display">
                    <?php if ($course['discount_amount'] > 0): ?>
                        <span class="original-price">LKR <?= number_format($course['price'], 2) ?></span>
                        <span class="discounted-price">LKR <?= number_format($course['price'] - $course['discount_amount'], 2) ?></span>
                    <?php else: ?>
                        <span class="current-price">LKR <?= number_format($course['price'], 2) ?></span>
                    <?php endif; ?>
                </div>
                <a href="../payment/initiate.php?course_id=<?= $courseId ?>" class="btn btn-primary btn-enroll">
                    <i class="fas fa-credit-card"></i> Enroll Now
                </a>
            </div>
            <?php elseif ($isStudent && $isEnrolled): ?>
            <div class="enrolled-badge">
                <i class="fas fa-check-circle"></i> Enrolled
            </div>
            <?php endif; ?>
            
            <?php if ($isAdminOrInstructor): ?>
            <a href="edit.php?id=<?= $courseId ?>" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Edit Course
            </a>
            <a href="lesson-create.php?course_id=<?= $courseId ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Lesson
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (isset($error)): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if (empty($lessons) && ($isEnrolled || $isAdminOrInstructor)): ?>
<div class="empty-state">
    <div class="empty-icon">🎬</div>
    <h3>No lessons <?= $isStudent ? 'available' : 'yet' ?></h3>
    <p><?= $isStudent ? 'Check back later for course content' : 'Start building your course content by adding your first lesson' ?></p>
    <?php if ($isAdminOrInstructor): ?>
    <a href="lesson-create.php?course_id=<?= $courseId ?>" class="btn btn-primary">
        <i class="fas fa-plus-circle"></i> Create First Lesson
    </a>
    <?php endif; ?>
</div>
<?php elseif ($isStudent && !$isEnrolled): ?>
<div class="enrollment-required">
    <div class="enrollment-icon">🔒</div>
    <h3>Enrollment Required</h3>
    <p>Please enroll in this course to access the lessons and materials.</p>
    <a href="../payment/initiate.php?course_id=<?= $courseId ?>" class="btn btn-primary">
        <i class="fas fa-credit-card"></i> Enroll Now - LKR <?= number_format($course['price'] - ($course['discount_amount'] ?? 0), 2) ?>
    </a>
</div>
<?php elseif (!empty($lessons)): ?>
<div class="lessons-container">
    <div class="lessons-header">
        <h3>Course Lessons (<?= count($lessons) ?>)</h3>
        <div class="view-toggle">
            <button class="view-btn active" data-view="grid"><i class="fas fa-th-large"></i></button>
            <button class="view-btn" data-view="list"><i class="fas fa-list"></i></button>
        </div>
    </div>
    
    <div class="lessons-grid" id="lessonsView">
        <?php foreach ($lessons as $lesson): ?>
        <div class="lesson-card">
            <div class="lesson-order">#<?= $lesson['lesson_order'] ?></div>
            <div class="lesson-thumbnail">
                <iframe src="<?= htmlspecialchars($lesson['embed_url']) ?>" frameborder="0" allowfullscreen></iframe>
                <div class="lesson-duration">
                    <i class="fas fa-clock"></i> <?= gmdate('i:s', $lesson['video_duration'] ?? 0) ?>
                </div>
            </div>
            <div class="lesson-content">
                <h4 class="lesson-title"><?= htmlspecialchars($lesson['title']) ?></h4>
                <div class="lesson-meta">
                    <?php if ($isAdminOrInstructor): ?>
                    <span class="meta-badge <?= strtolower($lesson['visibility_status']) ?>">
                        <?= $lesson['visibility_status'] === 'Visible' ? '👁️' : '🔒' ?> <?= $lesson['visibility_status'] ?>
                    </span>
                    <?php endif; ?>
                    <span class="meta-badge"><?= $lesson['access_permissions'] ?></span>
                    <?php if ($lesson['material_count'] > 0): ?>
                        <span class="meta-badge"><i class="fas fa-paperclip"></i> <?= $lesson['material_count'] ?> files</span>
                    <?php endif; ?>
                </div>
                <div class="lesson-stats">
                    <div class="stat-item">
                        <i class="fas fa-eye"></i>
                        <span><?= $lesson['student_views'] ?? 0 ?> views</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-calendar"></i>
                        <span><?= date('M j, Y', strtotime($lesson['created_at'])) ?></span>
                    </div>
                </div>
            </div>
            <div class="lesson-actions">
                <a href="lesson-watch.php?id=<?= $lesson['id'] ?>" class="action-btn view" title="Watch Lesson">
                    <i class="fas fa-play"></i> <?= $isStudent ? 'Watch' : 'Preview' ?>
                </a>
                <?php if ($isAdminOrInstructor): ?>
                <a href="lesson-edit.php?id=<?= $lesson['id'] ?>" class="action-btn edit" title="Edit Lesson">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <form method="POST" class="delete-form" onsubmit="return confirm('Delete this lesson?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="lesson_id" value="<?= $lesson['id'] ?>">
                    <button type="submit" class="action-btn delete" title="Delete Lesson">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
document.querySelectorAll('.view-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const view = this.dataset.view;
        const container = document.getElementById('lessonsView');
        container.className = view === 'grid' ? 'lessons-grid' : 'lessons-list';
    });
});
</script>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Course Lessons - ' . $course['title'], $content, $success ?? null);
?>

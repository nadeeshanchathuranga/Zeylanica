<?php
require_once '../config.php';
require_once '../template.php';
require_once '../services/LessonManagementService.php';
require_once '../auth.php';

checkMenuPermission($pdo, 'course');

$courseId = $_GET['course_id'] ?? null;
if (!$courseId) {
    header('Location: index.php');
    exit();
}

$lessonService = new LessonManagementService($pdo);
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = $lessonService->createLesson($_POST, $_FILES);
        header('Location: view.php?id=' . urlencode($courseId) . '&success=created');
        exit();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get course info
$stmt = $pdo->prepare("SELECT title FROM courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

ob_start();
?>
<div class="form-header">
    <div class="header-content">
        <div class="title-section">
            <a href="view.php?id=<?= $courseId ?>" class="back-link"><i class="fas fa-arrow-left"></i> Back to Course</a>
            <h2 class="page-title">🎬 Create New Lesson</h2>
            <p class="page-subtitle">Add video content to <?= htmlspecialchars($course['title']) ?></p>
        </div>
    </div>
</div>

<div class="form-container">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="course-form">
        <input type="hidden" name="course_id" value="<?= htmlspecialchars($courseId) ?>">
        
        <div class="form-section">
            <div class="section-header">
                <h3><i class="fas fa-info-circle"></i> Lesson Information</h3>
                <p>Basic details about the lesson</p>
            </div>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="title">Lesson Title *</label>
                    <input type="text" id="title" name="title" placeholder="Enter lesson title" required maxlength="200">
                </div>
                <div class="form-group">
                    <label for="lesson_order">Lesson Order</label>
                    <input type="number" id="lesson_order" name="lesson_order" placeholder="Auto" min="1">
                    <small>Leave empty for auto-assignment</small>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-header">
                <h3><i class="fab fa-vimeo"></i> Video Details</h3>
                <p>Vimeo video information</p>
            </div>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="vimeo_id">Vimeo Video ID/URL *</label>
                    <input type="text" id="vimeo_id" name="vimeo_id" placeholder="123456789 or https://vimeo.com/123456789" required>
                    <small>Enter Vimeo video ID or full URL</small>
                </div>
                <div class="form-group">
                    <label for="video_duration">Duration (seconds)</label>
                    <input type="number" id="video_duration" name="video_duration" placeholder="Auto-detect" min="0">
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="section-header">
                <h3><i class="fas fa-cog"></i> Settings</h3>
                <p>Visibility and access control</p>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="visibility_status">Visibility</label>
                    <select id="visibility_status" name="visibility_status">
                        <option value="Visible">👁️ Visible</option>
                        <option value="Hidden">🔒 Hidden</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="access_permissions">Access</label>
                    <select id="access_permissions" name="access_permissions">
                        <option value="Enrolled Only">Enrolled Only</option>
                        <option value="Free Preview">Free Preview</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-large">
                <i class="fas fa-plus-circle"></i> Create Lesson
            </button>
            <a href="view.php?id=<?= $courseId ?>" class="btn btn-secondary btn-large">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Create Lesson', $content, $success ?? null);
?>
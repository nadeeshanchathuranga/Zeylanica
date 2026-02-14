<?php
require_once '../config.php';
require_once '../template.php';
require_once '../services/LessonManagementService.php';
require_once '../services/VideoUploadService.php';
require_once '../auth.php';

checkMenuPermission($pdo, 'course');

$lessonId = $_GET['id'] ?? $_GET['lesson'] ?? '';
if (!$lessonId) {
    header('Location: ../enrolled-courses.php');
    exit;
}

try {
    $lessonService = new LessonManagementService($pdo);
    $videoService = new VideoUploadService();
    
    $lesson = $lessonService->getLesson($lessonId);
    if (!$lesson) {
        header('Location: ../enrolled-courses.php');
        exit;
    }
    
    $hasAccess = false;
    
    if ($_SESSION['role_name'] === 'Admin') {
        $hasAccess = true;
    } elseif ($_SESSION['role_name'] === 'Instructor') {
        $stmt = $pdo->prepare("
            SELECT 1 FROM course_instructors ci
            JOIN lessons l ON ci.course_id = l.course_id
            WHERE l.id = ? AND ci.instructor_id = ?
        ");
        $stmt->execute([$lessonId, $_SESSION['user_id']]);
        $hasAccess = $stmt->fetch() !== false;
    } elseif ($_SESSION['role_name'] === 'User') {
        if ($lesson['access_permissions'] === 'Free Preview') {
            $hasAccess = true;
        } else {
            $stmt = $pdo->prepare("
                SELECT 1 FROM course_enrollments ce
                JOIN lessons l ON ce.course_id = l.course_id
                WHERE l.id = ? AND ce.student_id = ? AND ce.status = 'Active'
            ");
            $stmt->execute([$lessonId, $_SESSION['user_id']]);
            $hasAccess = $stmt->fetch() !== false;
        }
    }
    
    if (!$hasAccess) {
        header('Location: ../dashboard.php');
        exit;
    }
    
    if (!in_array($_SESSION['role_name'], ['Admin', 'Instructor']) && $lesson['visibility_status'] !== 'Visible') {
        header('Location: ../dashboard.php');
        exit;
    }
    
    $sessionId = null;
    if ($_SESSION['role_name'] === 'User') {
        $sessionId = $lessonService->trackLessonView($lessonId, $_SESSION['user_id']);
    }
    
    $embedUrl = $videoService->getSecureEmbedUrl($lesson['vimeo_id'], $lessonId, $_SESSION['user_id']);
    
} catch (Exception $e) {
    header('Location: ../enrolled-courses.php');
    exit;
}

$stmt = $pdo->prepare("SELECT course_id FROM lessons WHERE id = ?");
$stmt->execute([$lessonId]);
$courseId = $stmt->fetchColumn();

ob_start();
?>
<div class="course-view-header">
    <div class="header-content">
        <div class="title-section">
            <a href="view.php?id=<?= $courseId ?>" class="back-link"><i class="fas fa-arrow-left"></i> Back to Course</a>
            <h2 class="page-title">🎬 <?= htmlspecialchars($lesson['title']) ?></h2>
            <p class="page-subtitle">Course: <?= htmlspecialchars($lesson['course_title']) ?></p>
        </div>
    </div>
</div>

<div class="watch-container">
    <div class="video-section">
        <div class="video-player">
            <iframe 
                src="<?= htmlspecialchars($embedUrl) ?>"
                frameborder="0" 
                allow="autoplay; fullscreen; picture-in-picture" 
                allowfullscreen
                id="vimeoPlayer">
            </iframe>
        </div>
        <div class="video-info">
            <span><i class="fas fa-clock"></i> <?= gmdate("H:i:s", $lesson['video_duration']) ?></span>
            <?php if ($lesson['access_permissions'] === 'Free Preview'): ?>
            <span class="badge-preview">🆓 Free Preview</span>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($lesson['materials']) || ($_SESSION['role_name'] === 'Student' && isset($lesson['analytics']))): ?>
    <div class="sidebar-section">
        <?php if (!empty($lesson['materials'])): ?>
        <div class="materials-card">
            <h3><i class="fas fa-paperclip"></i> Materials</h3>
            <div class="materials-list">
                <?php foreach ($lesson['materials'] as $material): ?>
                <div class="material-item">
                    <i class="fas fa-file"></i>
                    <a href="download-material.php?id=<?= $material['id'] ?>">
                        <?= htmlspecialchars($material['file_name']) ?>
                    </a>
                    <span class="file-size"><?= number_format($material['file_size'] / 1024, 1) ?> KB</span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($_SESSION['role_name'] === 'User' && isset($lesson['analytics'])): ?>
        <div class="progress-card">
            <h3><i class="fas fa-chart-line"></i> Your Progress</h3>
            <div class="progress-stats">
                <div class="stat-item">
                    <div class="stat-label">Completion</div>
                    <div class="stat-value"><?= $lesson['analytics']['completion_status'] ? '100%' : '0%' ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Views</div>
                    <div class="stat-value"><?= $lesson['analytics']['view_count'] ?? 0 ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Watch Time</div>
                    <div class="stat-value"><?= gmdate("H:i:s", $lesson['analytics']['total_time_spent'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php if ($_SESSION['role_name'] === 'User' && $sessionId): ?>
<script src="https://player.vimeo.com/api/player.js"></script>
<script>
const iframe = document.querySelector('#vimeoPlayer');
const player = new Vimeo.Player(iframe);
const sessionId = <?= $sessionId ?>;
let startTime = Date.now();
let lastPosition = 0;

player.on('timeupdate', function(data) {
    lastPosition = Math.floor(data.seconds);
});

setInterval(function() {
    const watchDuration = Math.floor((Date.now() - startTime) / 1000);
    if (watchDuration > 5) {
        startTime = Date.now();
    }
}, 30000);
</script>
<?php endif; ?>

<style>
.watch-container { display: grid; grid-template-columns: 1fr 350px; gap: 2rem; }
.video-section { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.video-player { position: relative; padding-bottom: 56.25%; height: 0; background: #000; }
.video-player iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
.video-info { display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-top: 1px solid #e2e8f0; }
.badge-preview { background: #4f46e5; color: white; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.875rem; }
.sidebar-section { display: flex; flex-direction: column; gap: 1.5rem; }
.materials-card, .progress-card { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.materials-card h3, .progress-card h3 { font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem; color: #1f2937; }
.materials-list { display: flex; flex-direction: column; gap: 0.75rem; }
.material-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: #f9fafb; border-radius: 8px; }
.material-item a { flex: 1; color: #4f46e5; text-decoration: none; }
.material-item a:hover { text-decoration: underline; }
.file-size { color: #6b7280; font-size: 0.875rem; }
.progress-stats { display: flex; flex-direction: column; gap: 1rem; }
.stat-item { display: flex; justify-content: space-between; padding: 0.75rem; background: #f9fafb; border-radius: 8px; }
.stat-label { color: #6b7280; font-size: 0.875rem; }
.stat-value { font-weight: 600; color: #1f2937; }
@media (max-width: 768px) {
    .watch-container { grid-template-columns: 1fr; }
}
</style>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Watch Lesson - ' . $lesson['title'], $content);
?>

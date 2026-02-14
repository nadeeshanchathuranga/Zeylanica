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

$lessonId = $_GET['id'] ?? '';
if (!$lessonId) {
    header('Location: ../enrolled-courses.php');
    exit;
}

// Get lesson details and check access
$stmt = $pdo->prepare("
    SELECT l.*, c.title as course_title
    FROM lessons l
    JOIN courses c ON l.course_id = c.id
    WHERE l.id = ?
");
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    header('Location: ../enrolled-courses.php');
    exit;
}

// Check enrollment access
$stmt = $pdo->prepare("
    SELECT 1 FROM course_enrollments ce
    WHERE ce.course_id = ? AND ce.student_id = ? AND ce.status = 'Active'
");
$stmt->execute([$lesson['course_id'], $_SESSION['user_id']]);
if (!$stmt->fetch() && $lesson['access_permissions'] !== 'Free Preview') {
    header('Location: ../enrolled-courses.php');
    exit;
}

// Get or create lesson analytics
$stmt = $pdo->prepare("
    SELECT * FROM lesson_analytics 
    WHERE lesson_id = ? AND student_id = ?
");
$stmt->execute([$lessonId, $_SESSION['user_id']]);
$analytics = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$analytics) {
    $stmt = $pdo->prepare("
        INSERT INTO lesson_analytics (lesson_id, student_id, first_viewed_at, last_viewed_at)
        VALUES (?, ?, NOW(), NOW())
    ");
    $stmt->execute([$lessonId, $_SESSION['user_id']]);
    
    $stmt = $pdo->prepare("
        SELECT * FROM lesson_analytics 
        WHERE lesson_id = ? AND student_id = ?
    ");
    $stmt->execute([$lessonId, $_SESSION['user_id']]);
    $analytics = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    // Update view count and last viewed
    $stmt = $pdo->prepare("
        UPDATE lesson_analytics 
        SET view_count = view_count + 1, last_viewed_at = NOW()
        WHERE lesson_id = ? AND student_id = ?
    ");
    $stmt->execute([$lessonId, $_SESSION['user_id']]);
}

ob_start();
?>
<div class="payment-history-header">
    <div class="header-content">
        <div class="title-section">
            <h2 class="page-title"><?= htmlspecialchars($lesson['title']) ?></h2>
            <p class="page-subtitle">Course: <?= htmlspecialchars($lesson['course_title']) ?></p>
        </div>
        <div class="header-actions">
            <a href="course-view.php?id=<?= $lesson['course_id'] ?>" class="back-link"><i class="fas fa-arrow-left"></i> Back to Course</a>
        </div>
    </div>
</div>

<div class="watch-container">
    <div class="video-section">
        <div class="video-player">
            <?php if ($lesson['vimeo_url']): ?>
                <?php 
                // Extract Vimeo ID from URL and create proper embed URL
                $vimeoId = $lesson['vimeo_id'] ?? '';
                if (!$vimeoId && $lesson['vimeo_url']) {
                    preg_match('/vimeo\.com\/(\d+)/', $lesson['vimeo_url'], $matches);
                    $vimeoId = $matches[1] ?? '';
                }
                $embedUrl = $vimeoId ? "https://player.vimeo.com/video/{$vimeoId}" : $lesson['vimeo_url'];
                ?>
                <iframe 
                    src="<?= htmlspecialchars($embedUrl) ?>"
                    frameborder="0" 
                    allow="autoplay; fullscreen; picture-in-picture" 
                    allowfullscreen
                    id="vimeoPlayer"
                    onload="console.log('Video loaded successfully')"
                    onerror="showVideoError()">
                </iframe>
                <div id="videoError" class="no-video" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Video temporarily unavailable</p>
                    <small>Vimeo content may be blocked by your network</small>
                </div>
            <?php else: ?>
                <div class="no-video">
                    <i class="fas fa-video-slash"></i>
                    <p>Video not available</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="video-info">
            <span><i class="fas fa-clock"></i> <?= gmdate("H:i:s", $lesson['video_duration'] ?? 0) ?></span>
            <?php if ($lesson['access_permissions'] === 'Free Preview'): ?>
            <span class="badge-preview">🆓 Free Preview</span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="sidebar-section">
        <div class="progress-card">
            <h3><i class="fas fa-chart-line"></i> Your Progress</h3>
            <div class="progress-stats">
                <div class="stat-item">
                    <div class="stat-label">Completion</div>
                    <div class="stat-value"><?= $analytics['completion_status'] ? '100%' : '0%' ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Views</div>
                    <div class="stat-value"><?= $analytics['view_count'] ?? 0 ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Watch Time</div>
                    <div class="stat-value"><?= gmdate("H:i:s", $analytics['total_time_spent'] ?? 0) ?></div>
                </div>
            </div>
            <button id="markComplete" class="btn-complete <?= $analytics['completion_status'] ? 'completed' : '' ?>">
                <i class="fas fa-check"></i> 
                <?= $analytics['completion_status'] ? 'Completed' : 'Mark as Complete' ?>
            </button>
        </div>
    </div>
</div>

<script>
function showVideoError() {
    document.getElementById('vimeoPlayer').style.display = 'none';
    document.getElementById('videoError').style.display = 'block';
}

// Check if iframe loads properly
setTimeout(() => {
    const iframe = document.getElementById('vimeoPlayer');
    if (iframe && !iframe.contentDocument && !iframe.contentWindow) {
        showVideoError();
    }
}, 3000);

document.getElementById('markComplete').addEventListener('click', function() {
    if (this.classList.contains('completed')) return;
    
    fetch('../api/update-progress.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            lesson_id: '<?= $lessonId ?>',
            action: 'complete'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            this.classList.add('completed');
            this.innerHTML = '<i class="fas fa-check"></i> Completed';
            document.querySelector('.stat-value').textContent = '100%';
        }
    });
});

// Track watch time
let watchStartTime = Date.now();
setInterval(() => {
    const watchTime = Math.floor((Date.now() - watchStartTime) / 1000);
    if (watchTime >= 30) {
        fetch('../api/update-progress.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                lesson_id: '<?= $lessonId ?>',
                action: 'time',
                duration: watchTime
            })
        });
        watchStartTime = Date.now();
    }
}, 30000);
</script>

<?php
$content = ob_get_clean();
echo renderAdminTemplate('Watch Lesson - ' . $lesson['title'], $content);
?>

<link rel="stylesheet" href="../assets/css/common-headers.css">
<style>
.watch-container { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 1fr 350px; gap: 2rem; }
.video-section { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.video-player { position: relative; padding-bottom: 56.25%; height: 0; background: #000; }
.video-player iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
.no-video { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; color: #6b7280; }
.no-video i { font-size: 3rem; margin-bottom: 1rem; color: #ef4444; }
.no-video small { display: block; margin-top: 0.5rem; color: #9ca3af; }
.video-info { display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-top: 1px solid #e2e8f0; }
.badge-preview { background: #4f46e5; color: white; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.875rem; }
.sidebar-section { display: flex; flex-direction: column; gap: 1.5rem; }
.progress-card { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.progress-card h3 { font-size: 1.125rem; font-weight: 600; margin-bottom: 1rem; color: #1f2937; }
.progress-stats { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem; }
.stat-item { display: flex; justify-content: space-between; padding: 0.75rem; background: #f9fafb; border-radius: 8px; }
.stat-label { color: #6b7280; font-size: 0.875rem; }
.stat-value { font-weight: 600; color: #1f2937; }
.btn-complete { width: 100%; padding: 0.75rem; background: #4f46e5; color: white; border: none; border-radius: 8px; cursor: pointer; transition: background 0.2s; }
.btn-complete:hover { background: #4338ca; }
.btn-complete.completed { background: #10b981; }
@media (max-width: 768px) {
    .watch-container { grid-template-columns: 1fr; }
}
</style>
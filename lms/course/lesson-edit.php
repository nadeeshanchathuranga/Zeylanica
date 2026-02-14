<?php
require_once '../config.php';
require_once '../template.php';
require_once '../services/LessonManagementService.php';
require_once '../auth.php';

checkMenuPermission($pdo, 'course');

$lessonService = new LessonManagementService($pdo);
$success = null;

// Get lesson ID
$lessonId = $_GET['id'] ?? '';
if (!$lessonId) {
    header('Location: index.php');
    exit();
}

// Get lesson details
try {
    $lesson = $lessonService->getLesson($lessonId);
    if (!$lesson) {
        throw new Exception('Lesson not found');
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    $lesson = null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update' && $lesson) {
    try {
        $result = $lessonService->updateLesson($lessonId, $_POST, $_FILES);
        $success = $result['message'];
        
        // Refresh lesson data
        $lesson = $lessonService->getLesson($lessonId);
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

ob_start();
?>
<div class="course-header">
    <div class="header-content">
        <div class="title-section">
            <h2 class="page-title">✏️ Edit Lesson</h2>
            <p class="page-subtitle">Update lesson content and settings</p>
        </div>
        <a href="view.php?id=<?= htmlspecialchars($lesson['course_id'] ?? '') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Course
        </a>
    </div>
</div>

<?php if ($lesson): ?>
<div class="form-container">
    <form method="POST" enctype="multipart/form-data" id="lessonForm" class="lesson-form">
        <input type="hidden" name="action" value="update">
        
        <!-- Course Info -->
        <div class="form-section">
            <h3 class="section-title">Course Information</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Course</label>
                    <div class="course-info-card">
                        <div class="course-name"><?= htmlspecialchars($lesson['course_title']) ?></div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Current Order</label>
                    <div class="order-badge-large"><?= $lesson['lesson_order'] ?></div>
                </div>
            </div>
        </div>

        <!-- Lesson Details -->
        <div class="form-section">
            <h3 class="section-title">Lesson Details</h3>
            <div class="form-grid">
                <div class="form-group col-span-2">
                    <label for="title" class="form-label required">Lesson Title</label>
                    <input type="text" name="title" id="title" class="form-input" 
                           value="<?= htmlspecialchars($lesson['title']) ?>" 
                           maxlength="200" required>
                </div>
                <div class="form-group">
                    <label for="lesson_order" class="form-label">Lesson Order</label>
                    <input type="number" name="lesson_order" id="lesson_order" class="form-input" 
                           value="<?= $lesson['lesson_order'] ?>" min="1">
                </div>
            </div>
        </div>

        <!-- Current Video Info -->
        <div class="form-section">
            <h3 class="section-title">Current Video</h3>
            <div class="video-info-card">
                <div class="video-stats">
                    <div class="stat-item">
                        <div class="stat-label">Duration</div>
                        <div class="stat-value"><?= gmdate("H:i:s", $lesson['video_duration']) ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Resolution</div>
                        <div class="stat-value"><?= $lesson['video_width'] ?>x<?= $lesson['video_height'] ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Source</div>
                        <div class="stat-value">Vimeo</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Created</div>
                        <div class="stat-value"><?= date('M j, Y', strtotime($lesson['created_at'])) ?></div>
                    </div>
                </div>
                <div class="video-actions">
                    <a href="watch.php?lesson=<?= htmlspecialchars($lesson['id']) ?>" 
                       class="btn btn-primary" target="_blank">
                        <i class="fas fa-play"></i> Preview Video
                    </a>
                </div>
            </div>
        </div>

        <!-- Replace Video -->
        <div class="form-section">
            <h3 class="section-title">Replace Video (Optional)</h3>
            <div class="form-group">
                <label for="video_file" class="form-label">New Video File</label>
                <div class="file-upload-area">
                    <input type="file" name="video_file" id="video_file" class="file-input" 
                           accept="video/mp4,video/avi,video/mov,video/wmv">
                    <div class="file-upload-text">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Choose new video file or drag and drop</span>
                        <small>Leave empty to keep current video. Max: 500MB</small>
                    </div>
                </div>
                <div id="videoPreview" class="video-preview" style="display: none;">
                    <video controls></video>
                </div>
            </div>
        </div>

        <!-- Settings -->
        <div class="form-section">
            <h3 class="section-title">Access Settings</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label for="visibility_status" class="form-label">Visibility</label>
                    <select name="visibility_status" id="visibility_status" class="form-select">
                        <option value="Visible" <?= $lesson['visibility_status'] === 'Visible' ? 'selected' : '' ?>>👁️ Visible</option>
                        <option value="Hidden" <?= $lesson['visibility_status'] === 'Hidden' ? 'selected' : '' ?>>🙈 Hidden</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="access_permissions" class="form-label">Access</label>
                    <select name="access_permissions" id="access_permissions" class="form-select">
                        <option value="Enrolled Only" <?= $lesson['access_permissions'] === 'Enrolled Only' ? 'selected' : '' ?>>🔒 Enrolled Only</option>
                        <option value="Free Preview" <?= $lesson['access_permissions'] === 'Free Preview' ? 'selected' : '' ?>>🆓 Free Preview</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Current Materials -->
        <?php if (!empty($lesson['materials'])): ?>
        <div class="form-section">
            <h3 class="section-title">Current Materials</h3>
            <div class="materials-grid">
                <?php foreach ($lesson['materials'] as $material): ?>
                <div class="material-card">
                    <div class="material-info">
                        <div class="material-name"><?= htmlspecialchars($material['file_name']) ?></div>
                        <div class="material-size"><?= number_format($material['file_size'] / 1024, 1) ?> KB</div>
                    </div>
                    <button type="button" class="btn btn-delete-small" 
                            onclick="deleteMaterial(<?= $material['id'] ?>, '<?= htmlspecialchars($material['file_name']) ?>')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Add Materials -->
        <div class="form-section">
            <h3 class="section-title">Add New Materials</h3>
            <div class="form-group">
                <label for="materials" class="form-label">Upload New Materials (Optional)</label>
                <div class="file-upload-area">
                    <input type="file" name="materials[]" id="materials" class="file-input" 
                           accept=".pdf,.jpg,.jpeg,.png,.mp3,.wav" multiple>
                    <div class="file-upload-text">
                        <i class="fas fa-paperclip"></i>
                        <span>Choose files or drag and drop</span>
                        <small>PDF, Images, Audio files (Max: 10MB each)</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="form-actions">
            <a href="view.php?id=<?= htmlspecialchars($lesson['course_id']) ?>" 
               class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="fas fa-save"></i> Update Lesson
            </button>
        </div>
    </form>
</div>
<?php else: ?>
<div class="empty-state">
    <div class="empty-icon">⚠️</div>
    <h3>Lesson not found</h3>
    <p>The requested lesson could not be found or you don't have permission to edit it.</p>
    <a href="index.php" class="btn btn-primary">Back to Lessons</a>
</div>
<?php endif; ?>

<!-- Delete Material Modal -->
<div class="modal fade" id="deleteMaterialModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete "<span id="materialName"></span>"?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete_material">
                    <input type="hidden" name="material_id" id="deleteMaterialId">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Video preview
document.getElementById('video_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('videoPreview');
    const video = preview.querySelector('video');
    
    if (file) {
        if (file.size > 500 * 1024 * 1024) {
            alert('Video file is too large. Maximum size is 500MB.');
            this.value = '';
            return;
        }
        const url = URL.createObjectURL(file);
        video.src = url;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
});

// Materials validation
document.getElementById('materials').addEventListener('change', function(e) {
    const files = e.target.files;
    for (let file of files) {
        if (file.size > 10 * 1024 * 1024) {
            alert('Material file "' + file.name + '" is too large. Maximum size is 10MB per file.');
            this.value = '';
            break;
        }
    }
});

function deleteMaterial(materialId, materialName) {
    document.getElementById('deleteMaterialId').value = materialId;
    document.getElementById('materialName').textContent = materialName;
    new bootstrap.Modal(document.getElementById('deleteMaterialModal')).show();
}
</script>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Edit Lesson', $content, $success, $error ?? null);
?>
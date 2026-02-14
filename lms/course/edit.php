<?php
require_once '../config.php';
require_once '../template.php';
require_once '../services/CourseManagementService.php';
require_once '../auth.php';

checkMenuPermission($pdo, 'course');

$courseService = new CourseManagementService($pdo);
$success = null;

// Get course ID
$courseId = $_GET['id'] ?? null;
if (!$courseId) {
    header('Location: index.php');
    exit;
}

// Get course data
$course = $courseService->getCourse($courseId);
if (!$course) {
    header('Location: index.php');
    exit;
}

$categories = $courseService->getCategories();
$instructors = $courseService->getInstructors();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $courseService->updateCourse($courseId, $_POST, $_FILES);
        $success = 'Course updated successfully';
        // Refresh course data
        $course = $courseService->getCourse($courseId);
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

ob_start();
?>
<div class="form-header">
    <div class="header-content">
        <div class="title-section">
            <h2 class="page-title">✏️ Edit Course</h2>
            <p class="page-subtitle">Update your course information and settings</p>
        </div>
        <div class="header-actions">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Courses
            </a>
        </div>
    </div>
</div>

<div class="form-container">
    <form method="POST" enctype="multipart/form-data" class="course-form">
        <!-- Basic Information -->
        <div class="form-section">
            <div class="section-header">
                <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                <p>Essential details about your course</p>
            </div>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="title">Course Title *</label>
                    <input type="text" id="title" name="title" placeholder="Enter an engaging course title" required maxlength="200" 
                           value="<?= htmlspecialchars($course['title']) ?>">
                    <small>Make it descriptive and appealing to potential students</small>
                </div>
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id">
                        <option value="">Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= $course['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="skill_level">Skill Level *</label>
                    <select id="skill_level" name="skill_level" required>
                        <option value="">Choose difficulty level</option>
                        <option value="Beginner" <?= $course['skill_level'] === 'Beginner' ? 'selected' : '' ?>>🌱 Beginner</option>
                        <option value="Intermediate" <?= $course['skill_level'] === 'Intermediate' ? 'selected' : '' ?>>🌿 Intermediate</option>
                        <option value="Advanced" <?= $course['skill_level'] === 'Advanced' ? 'selected' : '' ?>>🌳 Advanced</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label for="description">Course Description *</label>
                    <textarea id="description" name="description" placeholder="Describe what students will learn and achieve..." required rows="4"><?= htmlspecialchars($course['description']) ?></textarea>
                    <small>Minimum 50 characters. Be specific about learning outcomes.</small>
                </div>
            </div>
        </div>

        <!-- Pricing & Access -->
        <div class="form-section">
            <div class="section-header">
                <h3><i class="fas fa-dollar-sign"></i> Pricing & Access</h3>
                <p>Set your course pricing and access terms</p>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="price">Course Price *</label>
                    <div class="input-with-icon">
                        <span class="input-icon">$</span>
                        <input type="number" id="price" name="price" placeholder="0.00" step="0.01" min="0" required 
                               value="<?= $course['price'] ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="discount_amount">Discount Amount</label>
                    <div class="input-with-icon">
                        <span class="input-icon">$</span>
                        <input type="number" id="discount_amount" name="discount_amount" placeholder="0.00" step="0.01" min="0" 
                               value="<?= $course['discount_amount'] ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="validity_type">Access Type *</label>
                    <select id="validity_type" name="validity_type" required>
                        <option value="Lifetime" <?= $course['validity_type'] === 'Lifetime' ? 'selected' : '' ?>>♾️ Lifetime Access</option>
                        <option value="Fixed Duration" <?= $course['validity_type'] === 'Fixed Duration' ? 'selected' : '' ?>>⏰ Fixed Duration</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="validity_months">Duration (Months)</label>
                    <input type="number" id="validity_months" name="validity_months" placeholder="12" min="1" max="120" 
                           value="<?= $course['validity_months'] ?>">
                    <small>Only required for fixed duration access</small>
                </div>
            </div>
        </div>

        <!-- Additional Details -->
        <div class="form-section">
            <div class="section-header">
                <h3><i class="fas fa-cogs"></i> Additional Details</h3>
                <p>Target audience and course materials</p>
            </div>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="target_audience">Target Audience</label>
                    <input type="text" id="target_audience" name="target_audience" placeholder="Who is this course designed for?" 
                           value="<?= htmlspecialchars($course['target_audience']) ?>">
                    <small>e.g., "Beginners in web development", "Marketing professionals"</small>
                </div>
                <div class="form-group">
                    <label for="thumbnail">Course Thumbnail</label>
                    <input type="file" id="thumbnail" name="thumbnail" accept="image/jpeg,image/png" class="file-input">
                    <small>Upload a new image to replace current thumbnail (JPEG/PNG)</small>
                </div>
                <div class="form-group">
                    <label for="status">Publication Status</label>
                    <select id="status" name="status">
                        <option value="Draft" <?= $course['status'] === 'Draft' ? 'selected' : '' ?>>📝 Draft</option>
                        <option value="Published" <?= $course['status'] === 'Published' ? 'selected' : '' ?>>✅ Published</option>
                        <option value="Archived" <?= $course['status'] === 'Archived' ? 'selected' : '' ?>>📦 Archived</option>
                    </select>
                </div>
            </div>
        </div>
        <!-- Instructor Assignment -->
        <?php if (!empty($instructors)): ?>
        <div class="form-section">
            <div class="section-header">
                <h3><i class="fas fa-chalkboard-teacher"></i> Instructor Assignment</h3>
                <p>Select instructors who will teach this course</p>
            </div>
            <div class="instructor-grid">
                <?php 
                $assignedInstructors = array_column($course['instructors'] ?? [], 'id');
                foreach ($instructors as $instructor): 
                ?>
                    <div class="instructor-card">
                        <input type="checkbox" name="instructors[]" value="<?= $instructor['id'] ?>" 
                               id="inst_<?= $instructor['id'] ?>" 
                               <?= in_array($instructor['id'], $assignedInstructors) ? 'checked' : '' ?>>
                        <label for="inst_<?= $instructor['id'] ?>" class="instructor-label">
                            <div class="instructor-avatar">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div class="instructor-info">
                                <span class="instructor-name"><?= htmlspecialchars($instructor['email']) ?></span>
                                <span class="instructor-role">Instructor</span>
                            </div>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-large">
                <i class="fas fa-save"></i> Update Course
            </button>
            <a href="index.php" class="btn btn-secondary btn-large">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

<!-- Current Thumbnail -->
<?php if ($course['thumbnail_path']): ?>
<div class="thumbnail-preview">
    <div class="section-header">
        <h3><i class="fas fa-image"></i> Current Thumbnail</h3>
        <p>This is the current course thumbnail image</p>
    </div>
    <div class="thumbnail-container">
        <img src="<?= htmlspecialchars($course['thumbnail_path']) ?>" alt="Course Thumbnail" class="current-thumbnail">
    </div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Edit Course', $content, $success);
?>
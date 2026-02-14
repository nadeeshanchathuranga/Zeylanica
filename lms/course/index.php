<?php
require_once '../config.php';
require_once '../template.php';
require_once '../services/CourseManagementService.php';
require_once '../auth.php';

checkMenuPermission($pdo, 'course');

$courseService = new CourseManagementService($pdo);
$success = null;

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'update_status') {
            $courseService->updateCourse($_POST['course_id'], [
                'status' => $_POST['status']
            ]);
            $success = 'Course status updated successfully';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get filters
$filters = [];
if (!empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}
if (!empty($_GET['category'])) {
    $filters['category_id'] = $_GET['category'];
}

$courses = $courseService->getCourses($filters);
$categories = $courseService->getCategories();

ob_start();
?>
<div class="payment-history-header">
    <div class="header-content">
        <div class="title-section">
            <h2 class="page-title">Course Management</h2>
            <p class="page-subtitle">Manage and organize your educational content</p>
        </div>
        <div class="header-actions">
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Course
            </a>
        </div>
    </div>
</div>

<div class="filters-card">
    <form method="GET" class="filters-form">
        <div class="filter-group">
            <label>Status</label>
            <select name="status" class="filter-select">
                <option value="">All Statuses</option>
                <option value="Draft" <?= ($_GET['status'] ?? '') === 'Draft' ? 'selected' : '' ?>>📝 Draft</option>
                <option value="Published" <?= ($_GET['status'] ?? '') === 'Published' ? 'selected' : '' ?>>✅ Published</option>
                <option value="Archived" <?= ($_GET['status'] ?? '') === 'Archived' ? 'selected' : '' ?>>📦 Archived</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Category</label>
            <select name="category" class="filter-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= ($_GET['category'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-filter">
            <i class="fas fa-filter"></i> Apply Filters
        </button>
        <?php if (!empty($_GET['status']) || !empty($_GET['category'])): ?>
            <a href="index.php" class="btn btn-clear">
                <i class="fas fa-times"></i> Clear
            </a>
        <?php endif; ?>
    </form>
</div>

<?php if (empty($courses)): ?>
<div class="empty-state">
    <div class="empty-icon">📚</div>
    <h3>No courses found</h3>
    <p>Start building your educational content by creating your first course</p>
    <a href="create.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create Your First Course
    </a>
</div>
<?php else: ?>
<div class="courses-grid">
    <div class="grid-header">
        <h3>Courses (<?= count($courses) ?>)</h3>
    </div>
    <div class="table-container">
        <table class="courses-table">
            <thead>
                <tr>
                    <th class="col-title">Course Details</th>
                    <th class="col-category">Category</th>
                    <th class="col-status">Status</th>
                    <th class="col-price">Pricing</th>
                    <th class="col-date">Created</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                <tr class="course-row">
                    <td class="course-details">
                        <div class="course-title"><?= htmlspecialchars($course['title']) ?></div>
                        <div class="course-description"><?= htmlspecialchars(substr($course['description'], 0, 80)) ?>...</div>
                        <div class="course-meta">
                            <span class="skill-level"><?= $course['skill_level'] ?></span>
                        </div>
                    </td>
                    <td class="course-category">
                        <span class="category-tag"><?= htmlspecialchars($course['category_name'] ?? 'Uncategorized') ?></span>
                    </td>
                    <td class="course-status">
                        <span class="status-badge status-<?= strtolower($course['status']) ?>">
                            <?php 
                            $icons = ['draft' => '📝', 'published' => '✅', 'archived' => '📦'];
                            echo $icons[strtolower($course['status'])] ?? '❓';
                            ?>
                            <?= $course['status'] ?>
                        </span>
                    </td>
                    <td class="course-price">
                        <div class="price-info">
                            <span class="price">$<?= number_format($course['price'], 2) ?></span>
                            <?php if ($course['discount_amount'] > 0): ?>
                                <span class="discount">-$<?= number_format($course['discount_amount'], 2) ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="course-date">
                        <div class="date-info">
                            <span class="date"><?= date('M j, Y', strtotime($course['created_at'])) ?></span>
                            <span class="time"><?= date('g:i A', strtotime($course['created_at'])) ?></span>
                        </div>
                    </td>
                    <td class="course-actions">
                        <div class="action-buttons">
                            <a href="view.php?id=<?= $course['id'] ?>" class="btn btn-view" title="View Course">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit.php?id=<?= $course['id'] ?>" class="btn btn-edit" title="Edit Course">
                                <i class="fas fa-edit"></i>
                            </a>
                            
                            <?php if ($course['status'] === 'Draft'): ?>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                    <input type="hidden" name="status" value="Published">
                                    <button type="submit" class="btn btn-publish" title="Publish Course">
                                        <i class="fas fa-rocket"></i>
                                    </button>
                                </form>
                            <?php elseif ($course['status'] === 'Published'): ?>
                                <form method="POST" class="inline-form">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                    <input type="hidden" name="status" value="Archived">
                                    <button type="submit" class="btn btn-archive" title="Archive Course">
                                        <i class="fas fa-archive"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Course Management', $content, $success);
?>

<link rel="stylesheet" href="../assets/css/common-headers.css">
<style>
.filters-card { background: white; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.filters-form { display: flex; gap: 1rem; align-items: end; flex-wrap: wrap; }
.filter-group { display: flex; flex-direction: column; }
.filter-group label { font-size: 0.9rem; color: #666; margin-bottom: 0.25rem; }
.filter-input, .filter-select { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
.btn-filter { background: #4f46e5; color: white; }
.btn-clear { background: #6b7280; color: white; }
.empty-state { text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.empty-icon { font-size: 4rem; margin-bottom: 1rem; }
.courses-grid { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.grid-header { padding: 1.5rem; border-bottom: 1px solid #e5e7eb; }
.grid-header h3 { margin: 0; font-size: 1.25rem; font-weight: 600; }
.table-container { overflow-x: auto; }
.courses-table { width: 100%; border-collapse: collapse; }
.courses-table th, .courses-table td { padding: 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
.courses-table th { background: #f9fafb; font-weight: 600; color: #374151; }
.course-row:hover { background: #f9fafb; }
.course-title { font-weight: 600; color: #111827; margin-bottom: 0.25rem; }
.course-description { color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem; }
.skill-level { background: #e0e7ff; color: #3730a3; padding: 0.125rem 0.5rem; border-radius: 4px; font-size: 0.75rem; }
.category-tag { background: #f3f4f6; color: #374151; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.875rem; }
.status-badge { padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.875rem; font-weight: 500; }
.status-draft { background: #fef3c7; color: #92400e; }
.status-published { background: #d1fae5; color: #065f46; }
.status-archived { background: #e5e7eb; color: #374151; }
.price-info { display: flex; flex-direction: column; }
.price { font-weight: 600; color: #111827; }
.discount { color: #ef4444; font-size: 0.875rem; }
.date-info .time { display: block; color: #6b7280; font-size: 0.875rem; }
.action-buttons { display: flex; gap: 0.5rem; }
.btn-view { background: #3b82f6; color: white; padding: 0.5rem; border-radius: 4px; }
.btn-edit { background: #f59e0b; color: white; padding: 0.5rem; border-radius: 4px; }
.btn-publish { background: #10b981; color: white; padding: 0.5rem; border-radius: 4px; }
.btn-archive { background: #6b7280; color: white; padding: 0.5rem; border-radius: 4px; }
.inline-form { display: inline; }
@media (max-width: 768px) {
    .filters-form { flex-direction: column; align-items: stretch; }
    .courses-table { font-size: 0.875rem; }
}
</style>
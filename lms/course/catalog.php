<?php
require_once '../config.php';
require_once '../template.php';
require_once '../services/CourseManagementService.php';
require_once '../auth.php';

checkMenuPermission($pdo, 'course');

$courseService = new CourseManagementService($pdo);

// Get published courses only
$filters = ['status' => 'Published'];
if (!empty($_GET['category'])) {
    $filters['category_id'] = $_GET['category'];
}

$courses = $courseService->getCourses($filters);
$categories = $courseService->getCategories();

// Get user's enrollments
$stmt = $pdo->prepare("
    SELECT course_id FROM course_enrollments 
    WHERE student_id = ? AND status = 'Active'
");
$stmt->execute([$_SESSION['user_id']]);
$enrolledCourses = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'course_id');

ob_start();
?>
<div class="catalog-header">
    <div class="header-content">
        <div class="title-section">
            <h2 class="page-title">📚 Course Catalog</h2>
            <p class="page-subtitle">Discover and enroll in our educational courses</p>
        </div>
    </div>
</div>

<div class="filters-card">
    <form method="GET" class="filters-form">
        <div class="filter-group">
            <label>Category</label>
            <select name="category" class="filter-select" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= ($_GET['category'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if (!empty($_GET['category'])): ?>
            <a href="catalog.php" class="btn btn-clear">
                <i class="fas fa-times"></i> Clear Filter
            </a>
        <?php endif; ?>
    </form>
</div>

<?php if (empty($courses)): ?>
<div class="empty-state">
    <div class="empty-icon">📚</div>
    <h3>No courses available</h3>
    <p>Check back later for new courses in this category.</p>
</div>
<?php else: ?>
<div class="courses-section">
    <div class="section-header">
        <h3>Available Courses (<?= count($courses) ?>)</h3>
    </div>
    <div class="courses-grid">
        <?php foreach ($courses as $course): ?>
            <div class="course-card">
                <div class="course-thumbnail">
                    <?php if ($course['thumbnail_path']): ?>
                        <img src="<?= htmlspecialchars($course['thumbnail_path']) ?>" alt="Course thumbnail">
                    <?php else: ?>
                        <div class="thumbnail-placeholder">
                            <i class="fas fa-book"></i>
                            <span><?= htmlspecialchars(substr($course['title'], 0, 20)) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($course['discount_amount'] > 0): ?>
                        <div class="discount-badge">
                            <?= round((($course['discount_amount'] / $course['price']) * 100)) ?>% OFF
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="course-content">
                    <h4 class="course-title"><?= htmlspecialchars($course['title']) ?></h4>
                    <p class="course-description"><?= htmlspecialchars(substr($course['description'], 0, 100)) ?>...</p>
                    
                    <div class="course-meta">
                        <span class="meta-item">
                            <i class="fas fa-signal"></i> <?= $course['skill_level'] ?>
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-folder"></i> <?= htmlspecialchars($course['category_name'] ?? 'General') ?>
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-graduation-cap"></i> <?= $course['total_lessons'] ?> lessons
                        </span>
                    </div>
                    
                    <div class="course-footer">
                        <div class="price-section">
                            <?php if ($course['discount_amount'] > 0): ?>
                                <span class="original-price">LKR <?= number_format($course['price'], 2) ?></span>
                                <span class="current-price">LKR <?= number_format($course['price'] - $course['discount_amount'], 2) ?></span>
                            <?php else: ?>
                                <span class="current-price">LKR <?= number_format($course['price'], 2) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="course-actions">
                            <?php if (in_array($course['id'], $enrolledCourses)): ?>
                                <span class="enrolled-badge">
                                    <i class="fas fa-check-circle"></i> Enrolled
                                </span>
                                <a href="view.php?id=<?= $course['id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-play"></i> Continue
                                </a>
                            <?php else: ?>
                                <a href="../payment/initiate.php?course_id=<?= $course['id'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-shopping-cart"></i> Enroll
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<style>
.filters-form { display: flex; gap: 1rem; align-items: end; }
.filter-group { display: flex; flex-direction: column; }
.filter-group label { font-size: 0.9rem; color: #666; margin-bottom: 0.25rem; }
.filter-select { padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
.courses-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
.course-card { background: white; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden; transition: all 0.3s ease; }
.course-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
.course-thumbnail { height: 200px; position: relative; overflow: hidden; }
.course-thumbnail img { width: 100%; height: 100%; object-fit: cover; }
.thumbnail-placeholder { height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; flex-direction: column; align-items: center; justify-content: center; color: white; text-align: center; }
.thumbnail-placeholder i { font-size: 3rem; margin-bottom: 0.5rem; }
.discount-badge { position: absolute; top: 0.5rem; right: 0.5rem; background: #ff4757; color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.8rem; font-weight: bold; }
.course-content { padding: 1.25rem; }
.course-title { margin: 0 0 0.5rem 0; font-size: 1.1rem; font-weight: 600; color: #333; }
.course-description { color: #666; margin-bottom: 1rem; line-height: 1.4; }
.course-meta { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem; }
.meta-item { font-size: 0.85rem; color: #888; display: flex; align-items: center; gap: 0.25rem; }
.course-footer { display: flex; justify-content: space-between; align-items: center; }
.price-section { display: flex; flex-direction: column; }
.original-price { text-decoration: line-through; color: #999; font-size: 0.9rem; }
.current-price { font-weight: bold; color: #28a745; font-size: 1.1rem; }
.course-actions { display: flex; gap: 0.5rem; align-items: center; }
.enrolled-badge { background: #28a745; color: white; padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.8rem; display: flex; align-items: center; gap: 0.25rem; }
.btn-outline { background: transparent; border: 1px solid #007bff; color: #007bff; }
.btn-outline:hover { background: #007bff; color: white; }
@media (max-width: 768px) {
    .courses-grid { grid-template-columns: 1fr; }
    .course-footer { flex-direction: column; gap: 1rem; align-items: stretch; }
    .course-actions { justify-content: center; }
}
</style>
<?php
$content = ob_get_clean();
echo renderAdminTemplate('Course Catalog', $content);
?>
<?php 
require_once 'lms/config.php';
$pageTitle = 'Zeylanica - Learning Portal'; 
include 'includes/header.php';

// Fetch latest 3 published courses
$stmt = $pdo->prepare("
    SELECT 
        c.id,
        c.title,
        c.description,
        c.thumbnail_path,
        c.price,
        c.total_duration_hours,
        c.skill_level,
        COUNT(DISTINCT l.id) as lesson_count
    FROM courses c
    LEFT JOIN lessons l ON c.id = l.course_id AND l.visibility_status = 'Visible'
    WHERE c.status = 'PUBLISHED'
    GROUP BY c.id
    ORDER BY c.created_at DESC
    LIMIT 3
");
$stmt->execute();
$courses = $stmt->fetchAll();
?>

<section class="hero">
     <div class="book-image">
        <img src="assets/images/book.png" alt="Book" onerror="this.style.display='none'">
    </div>
    <div class="benson-image">
        <img src="assets/images/benson.png" alt="Benson" onerror="this.style.display='none'">
    </div>
    <div class="pin-image">
        <img src="assets/images/pin.png" alt="Pin" onerror="this.style.display='none'">
    </div>
    <div class="spec-image">
        <img src="assets/images/spec.png" alt="Spec" onerror="this.style.display='none'">
    </div>
    <div class="microscope-image">
        <img src="assets/images/microscope.png" alt="Microscope" onerror="this.style.display='none'">
    </div>
    <div class="bottle-image">
        <img src="assets/images/bottle.png" alt="Bottle" onerror="this.style.display='none'">
    </div>
    <div class="brand-content">
        <h1><?= $customTitle ?? 'Learn Anywhere, Anytime' ?></h1>
        <h2><?= $customSubtitle ?? 'Empower Your Future' ?></h2>
        <p><?= $customP ?? 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500s, when an unknown printer took.' ?></p>
        <div class="search-wrapper">
            <div class="search-box">
                <input type="text" placeholder="Search" />
                <button type="submit">🔍</button>
            </div>
        </div>
    </div>
  <div class="lecture-image">
        <img src="assets/images/lecture-home-image.png" alt="Lecture" onerror="this.style.display='none'">
  </div>
</section>

<section class="center">
  <h2>Explore Our Top Lessons</h2>
  <p style="color:#555;margin-top:8px">Learn From the Top Experts</p>

  <div class="grid-3">
    <?php foreach ($courses as $course): ?>
    <div class="card">
      <?php if ($course['thumbnail_path']): ?>
      <img src="<?= BASE_URL ?>course/<?= htmlspecialchars($course['thumbnail_path']) ?>" alt="<?= htmlspecialchars($course['title']) ?>">
      <?php endif; ?>
      <h3><?= htmlspecialchars($course['title']) ?></h3>
      <div class="price">LKR <?= number_format($course['price'], 2) ?> / lifetime</div>
      <div class="lesson-meta">
        <span><?= $course['lesson_count'] ?> Lessons</span>
        <span><?= $course['total_duration_hours'] ?>h</span>
      </div>
      <a class="btn" href="course-details.php?id=<?= $course['id'] ?>">Enroll Now</a>
    </div>
    <?php endforeach; ?>
    
    <?php if (empty($courses)): ?>
    <div class="card" style="grid-column:1/-1;text-align:center;padding:3rem">
      <p style="color:#6B7280">No courses available at the moment. Check back soon!</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="center">
  <h2>Today Quotes</h2>
  <div class="quote">“Lorem Ipsum is simply dummy text of the printing and typesetting industry...”</div>
  <p style="margin-top:12px">— Tissa Jananayake</p>
</section>

<?php include 'includes/faq.php'; ?>

<section class="newsletter">
  <div>
    <h2>Let’s Connect with us</h2>
    <p style="margin-top:8px">Subscribe our newsletter for updates</p>
  </div>
  <div style="display:flex">
    <input type="email" placeholder="Enter your email" />
    <button>Subscribe Now</button>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

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
<!-- <section class="hero">
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
      <h1><?php echo $customTitle ?? 'Learn Anywhere, Anytime' ?></h1>
      <h2><?php echo $customSubtitle ?? 'Empower Your Future' ?></h2>
      <p><?php echo $customP ?? 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry standard dummy text ever since the 1500s, when an unknown printer took.' ?></p>
      <div class="search-wrapper">
          <div class="search-box">
              <input type="text" placeholder="Search" />
              <button type="submit">🔍</button>
          </div>
      </div>
   </div>
   <div class="lecture-image">
      <img src="assets/images/lecture-image.png" alt="Lecture" onerror="this.style.display='none'">
   </div>
   </section>

   <section class="center">
   <h2>Explore Our Top Lessons</h2>
   <p style="color:#555;margin-top:8px">Learn From the Top Experts</p>

   <div class="grid-3">
   <?php foreach ($courses as $course): ?>
   <div class="card">
    <?php if ($course['thumbnail_path']): ?>
    <img src="<?php echo BASE_URL ?>course/<?php echo htmlspecialchars($course['thumbnail_path']) ?>" alt="<?php echo htmlspecialchars($course['title']) ?>">
    <?php endif; ?>
    <h3><?php echo htmlspecialchars($course['title']) ?></h3>
    <div class="price">LKR <?php echo number_format($course['price'], 2) ?> / lifetime</div>
    <div class="lesson-meta">
      <span><?php echo $course['lesson_count'] ?> Lessons</span>
      <span><?php echo $course['total_duration_hours'] ?>h</span>
    </div>
    <a class="btn" href="course-details.php?id=<?php echo $course['id'] ?>">Enroll Now</a>
   </div>
   <?php endforeach; ?>

   <?php if (empty($courses)): ?>
   <div class="card" style="grid-column:1/-1;text-align:center;padding:3rem">
    <p style="color:#6B7280">No courses available at the moment. Check back soon!</p>
   </div>
   <?php endif; ?>
   </div>
   </section>

   <section class="quote">
   <h2>Today Quotes</h2>
   <div>
    <div>"Lorem Ipsum is simply dummy text of the printing and typesetting industry..."</div>
    <p>— Tissa Jananayake</p>
   </div>
   <div>
    <img src="assets/images/lecture-home-image.png" alt="Lecturer" onerror="this.style.display='none'">
   </div>
   </section>



   <section class="newsletter">
   <div>
   <h2>Let's Connect with us</h2>
   <p style="margin-top:8px">Subscribe our newsletter for updates</p>
   </div>
   <div style="display:flex">
   <input type="email" placeholder="Enter your email" />
   <button>Subscribe Now</button>
   </div>
   </section> -->


<div class="social-glass">
  <ul>
    <li><a href="#" class="hvr-grow"><i class="fab fa-facebook-f font-16"></i></a></li>
    <li><a href="#" class="hvr-grow"><i class="fab fa-instagram font-16"></i></a></li>
    <li><a href="#" class="hvr-grow"><i class="fab fa-x-twitter font-16"></i></a></li>
    <li><a href="#" class="hvr-grow"><i class="fab fa-whatsapp font-16"></i></a></li>
  </ul>
</div>

<div class="container-fluid slider-section">
   <div class="container">
      <div class="row">
         <div class="col-lg-3 col-sm-6 mx-auto px-lg-0 pb-5">
            <p class="bg-white px-2 py-2 rounded-pill"> <i class="fa-solid fa-graduation-cap text-blue font-22 pe-3"></i>
               <span class="text-black font-16 fw-bold manrope-font"> Learn From the Top Experts</span>
            </p>
         </div>
      </div>
      <div class="row">
         <div class="col-lg-8 col-sm-12 mx-auto  text-center pb-5">
            <h1 class="optima-font font-60 text-white pb-3">
               Learn Anywhere, Anytime <br class="d-none  d-sm-block">
               Empower Your Future
            </h1>
            <p class="text-white manrope-font font-16 lh-base-1">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took.</p>
         </div>
      </div>
      <div class="row">
         <div class="col-lg-4 col-sm-6 mx-auto   pb-5 down-bottom">
            <div class="search-container">
               <input type="text" class="form-control search-input manrope-font text-black w-100" placeholder="Search...">
               <i class="fas fa-search search-icon "></i>
            </div>
         </div>
      </div>
   </div>
   <div class="container image-carousel-section">
      <div class="row">
         <div class="col-lg-9 col-sm-10 mx-auto">
            <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
               <div class="carousel-inner">
                  <div class="carousel-item active">
                     <img src="assets/images/slider.png" class="d-block w-100" alt="Zeylanica Education">
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
</div>
<div class="container-fluid category-section py-5">
   <div class="container pb-lg-4">
      <div class="row align-items-center">
         <div class="col-lg-8 text-center mx-auto pb-lg-4">
            <h2 class="text-blue1 optima-font font-60 text-center ">Explore Our Top Lessons <br class="d-none  d-sm-block">
               Categories
            </h2>
            <p class="text-black manrope-font font-16 lh-base-1 pt-2 pb-3">
               Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took.
            </p>
         </div>
         <div class="col-12">
            <div class="owl-carousel category-carousel">
               <div class="category-card text-center">
                  <img src="assets/images/2.png" class="w-100" alt="Zeylanica Education">
                  <p class="font-28 arimo-font fw-bold py-3 text-white">
                     Biology Lessons 01
                  </p>
               </div>
               <div class="category-card text-center">
                  <img src="assets/images/1.png" class="w-100" alt="Zeylanica Education">
                  <p class="font-28 arimo-font fw-bold py-3 text-white">
                     Biology Lessons 01
                  </p>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-8 px-lg-0 mx-auto pt-3">
            <a class="btn hvr-wobble-skew bg-red font-28 arimo-font fw-bold py-2 text-white text-center rounded-pill w-100">
            See More
            </a>
         </div>
      </div>
   </div>
</div>
<div class="container-fluid quote-section py-5">
   <p class="manrope-font font-400 quo-icon">“</p>
   <div class="container">
      <div class="row align-items-center">
         <div class="col-lg-1"></div>
         <div class="col-lg-6">
            <h2 class="text-white optima-font font-60 pb-3 text-center">
               Today Quotes
            </h2>
            <p class="text-white manrope-font font-16 lh-base-1 text-center">
               "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took."
            </p>
            <h3 class="font-48 text-white optima-font text-center pt-4">
               Tissa Jananayake
            </h3>
         </div>
      </div>
   </div>
   <!-- END IMAGE -->
   <img src="assets/images/ti.png" class="quote-end-img" alt="Quote Image">
</div>
<div class="container-fluid py-5 top-lesson-section">
   <div class="container py-lg-5">
      <div class="row align-items-center">
         <div class="col-lg-8 text-center mx-auto pb-4">
            <h2 class="text-blue1 optima-font font-60 text-center ">  Explore Our Top <br class="d-none  d-sm-block">
               Most Rated Lessons
            </h2>
            <p class="text-black manrope-font font-16 lh-base-1 pt-2 pb-3">
               Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took.
            </p>
         </div>
      </div>
      <div class="row">
         <div class="col-lg-4 col-sm-4 pb-lg-0 pb-4">
            <img src="assets/images/4.png" class="w-100 mb-4 rounded-3" alt="Zeylanica Education">
            <div class="lesson-div px-lg-3 px-2 py-4">
               <div class="row">
                  <div class="col-3">
                     <img src="assets/images/i-1.png" class="w-100 mb-3 rounded-3" alt="Zeylanica Education">
                  </div>
                  <div class="col-12">
                     <h4 class="font-28 fw-bold text-black pb-3 arimo-font ">Explore Our Top</h4>
                     <p class="text-black manrope-font font-16 lh-base">
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text
                     </p>
                  </div>
               </div>
            </div>
         </div>
          <div class="col-lg-4 col-sm-4 pb-lg-0 pb-4">
            <div class="lesson-div p-lg-4 p-2">
               <img src="assets/images/5.png" class="w-100 my-5 " alt="Zeylanica Education">
               <div class="row">
                  <div class="col-3">
                     <img src="assets/images/i-2.png" class="w-100 mb-3 rounded-3" alt="Zeylanica Education">
                  </div>
                  <div class="col-12">
                     <h4 class="font-28 fw-bold text-black pb-3 arimo-font ">Explore Our Top</h4>
                     <p class="text-black manrope-font font-16 lh-base">
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text
                     </p>
                  </div>
               </div>
            </div>
         </div>
               <div class="col-lg-4 col-sm-4 pb-lg-0 pb-4">
            <div class="lesson-div px-lg-3 px-2 py-4 d-none d-sm-block">
               <div class="row">
                  <div class="col-3">
                     <img src="assets/images/i-3.png" class="w-100 mb-3 rounded-3" alt="Zeylanica Education">
                  </div>
                  <div class="col-12">
                     <h4 class="font-28 fw-bold text-black pb-3 arimo-font ">Explore Our Top</h4>
                     <p class="text-black manrope-font font-16 lh-base">
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text
                     </p>
                  </div>
               </div>
            </div>
            <img src="assets/images/3.png" class="w-100 mt-4 rounded-3" alt="Zeylanica Education">
        
        <div class="lesson-div px-3 py-4 d-block  mt-4 d-sm-none">
               <div class="row">
                  <div class="col-3">
                     <img src="assets/images/i-3.png" class="w-100 mb-3 rounded-3" alt="Zeylanica Education">
                  </div>
                  <div class="col-12">
                     <h4 class="font-28 fw-bold text-black pb-3 arimo-font ">Explore Our Top</h4>
                     <p class="text-black manrope-font font-16 lh-base">
                        Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text
                     </p>
                  </div>
               </div>
            </div>
        
         </div>
      </div>
      <div class="row py-4">
         <div class="col-lg-4 col-8 px-lg-0 mx-auto pt-3">
            <a class="btn hvr-wobble-skew bg-red font-28 arimo-font fw-bold py-2 text-white text-center rounded-pill w-100">
            See More
            </a>
         </div>
      </div>
   </div>
</div>
<div class="container-fluid">
   <div class="row">
      <div class="col-12 px-0">
         <img src="assets/images/banner.png" class="w-100" alt="Quote Image">
      </div>
   </div>
</div>
<div class="container-fluid related-lesson-section py-5">
   <div class="container ">
      <div class="row align-items-center">
         <div class="col-lg-8 text-center mx-auto pt-lg-5 pb-lg-4">
            <h2 class="text-blue1 optima-font font-60 text-center ">  Explore Our Top <br class="d-none  d-sm-block">
               Most Rated Lessons
            </h2>
            <p class="text-black manrope-font font-16 lh-base-1 pt-2 pb-3">
               Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took.
            </p>
         </div>
         <div class="row">
            <div class="col-12 lesson-list">
               <ul class="nav nav-pills mb-lg-5 mb-3 justify-content-center" id="tablist" role="tablist">
                  <li class="nav-item mx-lg-3 mx-1 mb-lg-0 mb-3" role="presentation">
                     <button class="nav-link active rounded-pill font-22 arimo-font text-black px-5 py-2 bg-white " id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">All Lessons</button>
                  </li>
                  <li class="nav-item mx-lg-3 mx-1 mb-lg-0 mb-3" role="presentation">
                     <button class="nav-link rounded-pill font-22 arimo-font text-black px-5 py-2 bg-white" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Lessons</button>
                  </li>
                  <li class="nav-item mx-lg-3 mx-1 mb-lg-0 mb-3" role="presentation">
                     <button class="nav-link rounded-pill font-22 arimo-font text-black px-5 py-2 bg-white" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Lessons</button>
                  </li>
                  <li class="nav-item mx-lg-3 mx-1 mb-lg-0 mb-3" role="presentation">
                     <button class="nav-link rounded-pill font-22 arimo-font text-black px-5 py-2 bg-white" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Lessons</button>
                  </li>
                  <li class="nav-item mx-lg-3 mx-1 mb-lg-0 mb-3" role="presentation">
                     <button class="nav-link rounded-pill font-22 arimo-font text-black px-5 py-2 bg-white" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Lessons</button>
                  </li>
               </ul>
               <div class="tab-content pt-2" id="pills-tabContent">
                  <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                     <div class="lesson-carousel owl-carousel
                        ">

                        <div class="row">
   <div class="col-12">
      <div class="bg-white rounded-3 p-2  mb-4">
         <img src="assets/images/7.png" class="w-100" alt="Quote Image">
         <h4 class="font-28 fw-bold text-black pt-2 arimo-font ">Lessons Biology</h4>
         <p class="text-dark font-20 arimo-font">by <span class="text-blue">Tissa Jananayake</span> </p>
         <div class="row pt-3 px-2 g-2">
            <div class="col-6">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-book"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        17 Lessons
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-1"></div>
            <div class="col-4">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-clock"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        2h 16m
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-9 pt-2">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fas fa-user-graduate"></i>
                  </div>
                  <div class="col-9 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        1000+ Student Enrolled
                     </p>
                  </div>
               </div>
            </div>
         </div>
         <div   class="row align-items-center py-4">
            <div class="col-7">
               <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
            </div>
            <div class="col-5">
               <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
            </div>
         </div>
      </div>
   </div>
   <div class="col-12">
      <div class="bg-white rounded-3 p-2  mb-4">
         <img src="assets/images/6.png" class="w-100" alt="Quote Image">
         <h4 class="font-28 fw-bold text-black pt-2 arimo-font ">Lessons Biology</h4>
         <p class="text-dark font-20 arimo-font">by <span class="text-blue">Tissa Jananayake</span> </p>
         <div class="row pt-3 px-2 g-2">
            <div class="col-6">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-book"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        17 Lessons
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-1"></div>
            <div class="col-4">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-clock"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        2h 16m
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-9 pt-2">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fas fa-user-graduate"></i>
                  </div>
                  <div class="col-9 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        1000+ Student Enrolled
                     </p>
                  </div>
               </div>
            </div>
         </div>
         <div   class="row align-items-center py-4">
            <div class="col-7">
               <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
            </div>
            <div class="col-5">
               <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-12">
      <div class="bg-white rounded-3 p-2  mb-4">
         <img src="assets/images/6.png" class="w-100" alt="Quote Image">
         <h4 class="font-28 fw-bold text-black pt-2 arimo-font ">Lessons Biology</h4>
         <p class="text-dark font-20 arimo-font">by <span class="text-blue">Tissa Jananayake</span> </p>
         <div class="row pt-3 px-2 g-2">
            <div class="col-6">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-book"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        17 Lessons
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-1"></div>
            <div class="col-4">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-clock"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        2h 16m
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-9 pt-2">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fas fa-user-graduate"></i>
                  </div>
                  <div class="col-9 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        1000+ Student Enrolled
                     </p>
                  </div>
               </div>
            </div>
         </div>
         <div   class="row align-items-center py-4">
            <div class="col-7">
               <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
            </div>
            <div class="col-5">
               <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
            </div>
         </div>
      </div>
   </div>
   <div class="col-12">
      <div class="bg-white rounded-3 p-2  mb-4">
         <img src="assets/images/7.png" class="w-100" alt="Quote Image">
         <h4 class="font-28 fw-bold text-black pt-2 arimo-font ">Lessons Biology</h4>
         <p class="text-dark font-20 arimo-font">by <span class="text-blue">Tissa Jananayake</span> </p>
         <div class="row pt-3 px-2 g-2">
            <div class="col-6">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-book"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        17 Lessons
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-1"></div>
            <div class="col-4">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-clock"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        2h 16m
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-9 pt-2">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fas fa-user-graduate"></i>
                  </div>
                  <div class="col-9 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        1000+ Student Enrolled
                     </p>
                  </div>
               </div>
            </div>
         </div>
         <div   class="row align-items-center py-4">
            <div class="col-7">
               <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
            </div>
            <div class="col-5">
               <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-12">
      <div class="bg-white rounded-3 p-2  mb-4">
         <img src="assets/images/7.png" class="w-100" alt="Quote Image">
         <h4 class="font-28 fw-bold text-black pt-2 arimo-font ">Lessons Biology</h4>
         <p class="text-dark font-20 arimo-font">by <span class="text-blue">Tissa Jananayake</span> </p>
         <div class="row pt-3 px-2 g-2">
            <div class="col-6">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-book"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        17 Lessons
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-1"></div>
            <div class="col-4">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-clock"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        2h 16m
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-9 pt-2">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fas fa-user-graduate"></i>
                  </div>
                  <div class="col-9 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        1000+ Student Enrolled
                     </p>
                  </div>
               </div>
            </div>
         </div>
         <div   class="row align-items-center py-4">
            <div class="col-7">
               <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
            </div>
            <div class="col-5">
               <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
            </div>
         </div>
      </div>
   </div>
   <div class="col-12">
      <div class="bg-white rounded-3 p-2  mb-4">
         <img src="assets/images/6.png" class="w-100" alt="Quote Image">
         <h4 class="font-28 fw-bold text-black pt-2 arimo-font ">Lessons Biology</h4>
         <p class="text-dark font-20 arimo-font">by <span class="text-blue">Tissa Jananayake</span> </p>
         <div class="row pt-3 px-2 g-2">
            <div class="col-6">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-book"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        17 Lessons
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-1"></div>
            <div class="col-4">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-clock"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        2h 16m
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-9 pt-2">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fas fa-user-graduate"></i>
                  </div>
                  <div class="col-9 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        1000+ Student Enrolled
                     </p>
                  </div>
               </div>
            </div>
         </div>
         <div   class="row align-items-center py-4">
            <div class="col-7">
               <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
            </div>
            <div class="col-5">
               <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-12">
      <div class="bg-white rounded-3 p-2  mb-4">
         <img src="assets/images/6.png" class="w-100" alt="Quote Image">
         <h4 class="font-28 fw-bold text-black pt-2 arimo-font ">Lessons Biology</h4>
         <p class="text-dark font-20 arimo-font">by <span class="text-blue">Tissa Jananayake</span> </p>
         <div class="row pt-3 px-2 g-2">
            <div class="col-6">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-book"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        17 Lessons
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-1"></div>
            <div class="col-4">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-clock"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        2h 16m
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-9 pt-2">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fas fa-user-graduate"></i>
                  </div>
                  <div class="col-9 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        1000+ Student Enrolled
                     </p>
                  </div>
               </div>
            </div>
         </div>
         <div   class="row align-items-center py-4">
            <div class="col-7">
               <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
            </div>
            <div class="col-5">
               <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
            </div>
         </div>
      </div>
   </div>
   <div class="col-12">
      <div class="bg-white rounded-3 p-2  mb-4">
         <img src="assets/images/7.png" class="w-100" alt="Quote Image">
         <h4 class="font-28 fw-bold text-black pt-2 arimo-font ">Lessons Biology</h4>
         <p class="text-dark font-20 arimo-font">by <span class="text-blue">Tissa Jananayake</span> </p>
         <div class="row pt-3 px-2 g-2">
            <div class="col-6">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-book"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        17 Lessons
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-1"></div>
            <div class="col-4">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-clock"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        2h 16m
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-9 pt-2">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fas fa-user-graduate"></i>
                  </div>
                  <div class="col-9 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        1000+ Student Enrolled
                     </p>
                  </div>
               </div>
            </div>
         </div>
         <div   class="row align-items-center py-4">
            <div class="col-7">
               <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
            </div>
            <div class="col-5">
               <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
            </div>
         </div>
      </div>
   </div>
</div>

 <div class="row">
   <div class="col-12">
      <div class="bg-white rounded-3 p-2  mb-4">
         <img src="assets/images/6.png" class="w-100" alt="Quote Image">
         <h4 class="font-28 fw-bold text-black pt-2 arimo-font ">Lessons Biology</h4>
         <p class="text-dark font-20 arimo-font">by <span class="text-blue">Tissa Jananayake</span> </p>
         <div class="row pt-3 px-2 g-2">
            <div class="col-6">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-book"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        17 Lessons
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-1"></div>
            <div class="col-4">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-clock"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        2h 16m
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-9 pt-2">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fas fa-user-graduate"></i>
                  </div>
                  <div class="col-9 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        1000+ Student Enrolled
                     </p>
                  </div>
               </div>
            </div>
         </div>
         <div   class="row align-items-center py-4">
            <div class="col-7">
               <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
            </div>
            <div class="col-5">
               <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
            </div>
         </div>
      </div>
   </div>
   <div class="col-12">
      <div class="bg-white rounded-3 p-2  mb-4">
         <img src="assets/images/7.png" class="w-100" alt="Quote Image">
         <h4 class="font-28 fw-bold text-black pt-2 arimo-font ">Lessons Biology</h4>
         <p class="text-dark font-20 arimo-font">by <span class="text-blue">Tissa Jananayake</span> </p>
         <div class="row pt-3 px-2 g-2">
            <div class="col-6">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-book"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        17 Lessons
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-1"></div>
            <div class="col-4">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-clock"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        2h 16m
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-9 pt-2">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fas fa-user-graduate"></i>
                  </div>
                  <div class="col-9 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        1000+ Student Enrolled
                     </p>
                  </div>
               </div>
            </div>
         </div>
         <div   class="row align-items-center py-4">
            <div class="col-7">
               <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
            </div>
            <div class="col-5">
               <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-12">
      <div class="bg-white rounded-3 p-2  mb-4">
         <img src="assets/images/7.png" class="w-100" alt="Quote Image">
         <h4 class="font-28 fw-bold text-black pt-2 arimo-font ">Lessons Biology</h4>
         <p class="text-dark font-20 arimo-font">by <span class="text-blue">Tissa Jananayake</span> </p>
         <div class="row pt-3 px-2 g-2">
            <div class="col-6">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-book"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        17 Lessons
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-1"></div>
            <div class="col-4">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-clock"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        2h 16m
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-9 pt-2">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fas fa-user-graduate"></i>
                  </div>
                  <div class="col-9 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        1000+ Student Enrolled
                     </p>
                  </div>
               </div>
            </div>
         </div>
         <div   class="row align-items-center py-4">
            <div class="col-7">
               <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
            </div>
            <div class="col-5">
               <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
            </div>
         </div>
      </div>
   </div>
   <div class="col-12">
      <div class="bg-white rounded-3 p-2  mb-4">
         <img src="assets/images/6.png" class="w-100" alt="Quote Image">
         <h4 class="font-28 fw-bold text-black pt-2 arimo-font ">Lessons Biology</h4>
         <p class="text-dark font-20 arimo-font">by <span class="text-blue">Tissa Jananayake</span> </p>
         <div class="row pt-3 px-2 g-2">
            <div class="col-6">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-book"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        17 Lessons
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-1"></div>
            <div class="col-4">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-clock"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        2h 16m
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-9 pt-2">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fas fa-user-graduate"></i>
                  </div>
                  <div class="col-9 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        1000+ Student Enrolled
                     </p>
                  </div>
               </div>
            </div>
         </div>
         <div   class="row align-items-center py-4">
            <div class="col-7">
               <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
            </div>
            <div class="col-5">
               <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-12">
      <div class="bg-white rounded-3 p-2  mb-4">
         <img src="assets/images/6.png" class="w-100" alt="Quote Image">
         <h4 class="font-28 fw-bold text-black pt-2 arimo-font ">Lessons Biology</h4>
         <p class="text-dark font-20 arimo-font">by <span class="text-blue">Tissa Jananayake</span> </p>
         <div class="row pt-3 px-2 g-2">
            <div class="col-6">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-book"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        17 Lessons
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-1"></div>
            <div class="col-4">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-clock"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        2h 16m
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-9 pt-2">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fas fa-user-graduate"></i>
                  </div>
                  <div class="col-9 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        1000+ Student Enrolled
                     </p>
                  </div>
               </div>
            </div>
         </div>
         <div   class="row align-items-center py-4">
            <div class="col-7">
               <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
            </div>
            <div class="col-5">
               <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
            </div>
         </div>
      </div>
   </div>
   <div class="col-12">
      <div class="bg-white rounded-3 p-2  mb-4">
         <img src="assets/images/7.png" class="w-100" alt="Quote Image">
         <h4 class="font-28 fw-bold text-black pt-2 arimo-font ">Lessons Biology</h4>
         <p class="text-dark font-20 arimo-font">by <span class="text-blue">Tissa Jananayake</span> </p>
         <div class="row pt-3 px-2 g-2">
            <div class="col-6">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-book"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        17 Lessons
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-1"></div>
            <div class="col-4">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fa fa-clock"></i>
                  </div>
                  <div class="col-8 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        2h 16m
                     </p>
                  </div>
               </div>
            </div>
            <div class="col-9 pt-2">
               <div class="row border py-2 text-center bg-white-light rounded-pill align-items-center">
                  <div class="col-1"></div>
                  <div class="col-1 px-0">
                     <i class="fas fa-user-graduate"></i>
                  </div>
                  <div class="col-9 px-0">
                     <p class="text-dark font-16 fw-bold arimo-font mb-0">
                        1000+ Student Enrolled
                     </p>
                  </div>
               </div>
            </div>
         </div>
         <div   class="row align-items-center py-4">
            <div class="col-7">
               <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
            </div>
            <div class="col-5">
               <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
            </div>
         </div>
      </div>
   </div>
</div>


                        
                     </div>
                  </div>
                  <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">...</div>
                  <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">...</div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>

<div class="container-fluid storie-section py-5 bg-white">
   <div class="container">
      <div class="row align-items-center">
         <div class="col-lg-8 text-center mx-auto pb-4">
            <h2 class="text-blue1 optima-font font-60 text-center ">   Stories from Our Successful <br class="d-none  d-sm-block">
               Learners
            </h2>
            <p class="text-black manrope-font font-16 lh-base-1 pt-2 pb-3">
               Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took.
            </p>
         </div>
      </div>
   </div>
   <div class="row">
      <div class="col-12 px-0 student-carousel owl-carousel">
         <div class="image-container">
            <img src="assets/images/s1.png" class="w-100" alt="Zeylanica Education">
            <div class="image-footer">
               <p class="text-white font-22 fw-bold arimo-font">Devinda Adikari</p>
               <p class="text-white font-20 fw-normal arimo-font">2020 (A/L)</p>
            </div>
         </div>
         <div class="image-container">
            <img src="assets/images/s2.png" class="w-100" alt="Zeylanica Education">
            <div class="image-footer">
               <p class="text-white font-22 fw-bold arimo-font">Devinda Adikari</p>
               <p class="text-white font-20 fw-normal arimo-font">2020 (A/L)</p>
            </div>
         </div>
      </div>
   </div>




</div>







<?php include 'includes/faq.php'; ?>


<?php include 'includes/footer.php'; ?>
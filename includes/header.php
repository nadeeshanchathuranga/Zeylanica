<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $pageTitle ?? 'Zeylanica - Learning Portal'; ?></title>
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
      integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer" />

      <!-- Owl Carousel CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css" />

    <link
        href="https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=Manrope:wght@200..800&display=swap"
        rel="stylesheet">
 <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/hover.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/style-new.css">
    <link rel="stylesheet" href="assets/css/inner-style-new.css">

    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">

</head>

<body>
<!-- <header>
  <a href="index.php" style="display:flex;align-items:center;gap:14px">
    <img src="assets/images/logo.png" alt="Zeylanica Education" style="height:48px">
  </a>
  <nav style="display:flex;align-items:center;gap:32px;color:white">
    <a href="index.php">Home</a>
    <a href="about.php">About Us</a>
    <a href="classes.php">Class</a>
    <a href="news.php">News</a>
    <a href="contact.php">Contact Us</a>
  </nav>
  <div style="display:flex;align-items:center;gap:16px">
    <a href="<//?php echo APP_BASE_URL ?>index.php" style="color:white;padding:10px 18px;font-weight:600">Login</a>
    <a href="<//?php echo BASE_URL ?>../register.php" style="display:flex;align-items:center;gap:12px;border:2px solid #ef4444;color:white;padding:10px 18px;border-radius:999px;font-weight:600">
      Register
      <span style="background:white;color:#0f172a;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:16px">↗</span>
    </a>
  </div>
</header> -->

 
<?php
$page = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

$classes = [
    'index.php' => 'main-cover',
    'register.php' => 'reg-cover',
    'single-news.php' => 'bg-image-none',
    'student-dashboard.php' => 'bg-image-none',
    'student-details.php' => 'bg-image-none',
    'news.php' => 'inner-cover',
    'about.php' => 'inner-cover',
];

$coverClass = $classes[$page] ?? 'inner-cover';
?>

<div class="<?php echo $coverClass; ?>">
 

 <div class="container-fluid header-wrapper py-4" id="mainHeader">
      <div class="container">
         <div class="row align-items-center">
            <div class="col-lg-2 col-sm-3 col-5">
               <a href="index.php" title="Zeylanica Education">
               <img src="assets/images/logo.png" alt="Zeylanica Education" class="w-75">
               </a>
            </div>
           
            <div class="col-lg-8">
               <nav class="navbar navbar-expand-lg navbar-light bg-transparent">
                  <button class="navbar-toggler border-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                  <i class="fa-solid fa-bars text-white font-28"></i>
                  </button>
                  <div class="collapse navbar-collapse" id="navbarNav">
                     <ul class="navbar-nav mx-auto">
                        <li class="nav-item px-3">
                           <a class="nav-link text-white font-16 manrope-font active" href="#">Home</a>
                        </li>
                        <li class="nav-item px-3">
                           <a class="nav-link text-white font-16 manrope-font" href="#">About Us</a>
                        </li>
                        <li class="nav-item px-3">
                           <a class="nav-link text-white font-16 manrope-font" href="#">Class</a>
                        </li>
                        <li class="nav-item px-3">
                           <a class="nav-link text-white font-16 manrope-font" href="#">News</a>
                        </li>
                        <li class="nav-item px-3">
                           <a class="nav-link text-white font-16 manrope-font" href="#">Contact Us</a>
                        </li>


<li class="nav-item px-3 py-4 d-block d-sm-none v-tab-display">
                       <a href="#"
                  class="w-100 text-white font-16 manrope-font reg-btn py-2 px-3 rounded-pill d-flex align-items-center justify-content-center">
               Register
               <img src="assets/images/eslipe.png" alt="Zeylanica Education" class="ms-3" width="25">
               </a>
                        </li>

                     </ul>
                  </div>
               </nav>
            </div>
            <div class="col-lg-2 col-8 pt-lg-0 pt-3 d-none d-sm-block v-tab-none">
               <a href="#"
                  class="w-100 text-white font-16 manrope-font reg-btn py-2 px-3 rounded-pill d-flex align-items-center justify-content-center">
               Register
               <img src="assets/images/eslipe.png" alt="Zeylanica Education" class="ms-3" width="25">
               </a>
            </div>
         </div>
      </div>
   </div>  

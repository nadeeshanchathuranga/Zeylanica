<?php 
require_once 'lms/config.php';
$pageTitle = 'About Us - Zeylanica Learning Portal'; 
include 'includes/header.php';
?>

<!-- <section class="hero" style="min-height: 60vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="brand-content" style="text-align: center; color: white;">
        <h1>About ASIDS LMS</h1>
        <h2>Advanced Student Information & Development System</h2>
        <p>Empowering education through innovative technology and comprehensive learning management solutions.</p>
    </div>
</section>

<section class="center">
    <h2>Our Learning Management System</h2>
    <p style="color:#555;margin-top:8px">Comprehensive educational platform for modern learning</p>
    
    <div class="grid-3" style="margin-top: 3rem;">
        <div class="card">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🎓</div>
            <h3>Learning Management</h3>
            <p>Comprehensive course management with video lessons, progress tracking, and interactive learning experiences.</p>
        </div>
        <div class="card">
            <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
            <h3>Multi-Role System</h3>
            <p>Support for Students, Instructors, and Administrators with role-based access control and permissions.</p>
        </div>
        <div class="card">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
            <h3>Analytics & Progress</h3>
            <p>Real-time progress tracking, lesson analytics, and comprehensive reporting for better learning outcomes.</p>
        </div>
    </div>
</section>

<section class="center" style="background: #f8fafc; padding: 4rem 0;">
    <h2>System Capabilities</h2>
    <div class="grid-3" style="margin-top: 2rem;">
        <div class="card">
            <h4 style="color: #4f46e5; margin-bottom: 1rem;">For Students</h4>
            <ul style="text-align: left; list-style: none; padding: 0;">
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">✓ Browse and enroll in courses</li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">✓ Watch video lessons with progress tracking</li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">✓ Mark lessons as complete</li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">✓ View learning analytics</li>
                <li style="padding: 0.5rem 0;">✓ Manage course enrollments</li>
            </ul>
        </div>
        <div class="card">
            <h4 style="color: #10b981; margin-bottom: 1rem;">For Instructors</h4>
            <ul style="text-align: left; list-style: none; padding: 0;">
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">✓ Create and manage courses</li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">✓ Upload and organize video lessons</li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">✓ Set course pricing and access</li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">✓ Track student progress</li>
                <li style="padding: 0.5rem 0;">✓ Manage course content</li>
            </ul>
        </div>
        <div class="card">
            <h4 style="color: #f59e0b; margin-bottom: 1rem;">For Administrators</h4>
            <ul style="text-align: left; list-style: none; padding: 0;">
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">✓ Full system management</li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">✓ User and role management</li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">✓ Payment processing</li>
                <li style="padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">✓ System configuration</li>
                <li style="padding: 0.5rem 0;">✓ Analytics and monitoring</li>
            </ul>
        </div>
    </div>
</section>

<section class="center">
    <h2>Technical Architecture</h2>
    <div class="grid-3" style="margin-top: 2rem;">
        <div class="card">
            <strong style="color: #4f46e5;">Backend:</strong>
            <p style="margin-top: 0.5rem;">PHP 8+ with PDO for secure database operations</p>
        </div>
        <div class="card">
            <strong style="color: #10b981;">Database:</strong>
            <p style="margin-top: 0.5rem;">MySQL with optimized schema and migrations</p>
        </div>
        <div class="card">
            <strong style="color: #f59e0b;">Frontend:</strong>
            <p style="margin-top: 0.5rem;">Responsive HTML5/CSS3 with modern JavaScript</p>
        </div>
        <div class="card">
            <strong style="color: #ef4444;">Video:</strong>
            <p style="margin-top: 0.5rem;">Vimeo integration for secure video streaming</p>
        </div>
        <div class="card">
            <strong style="color: #8b5cf6;">Security:</strong>
            <p style="margin-top: 0.5rem;">Session management and data validation</p>
        </div>
        <div class="card">
            <strong style="color: #06b6d4;">Payments:</strong>
            <p style="margin-top: 0.5rem;">Multiple gateway support with secure processing</p>
        </div>
    </div>
</section>


<section class="center" style="background: #f8fafc; padding: 4rem 0;">
    <h2>System Information</h2>
    <div style="max-width: 600px; margin: 2rem auto;">
        <div class="card" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2rem; text-align: left;">
            <div>
                <strong>Version:</strong>
                <p style="margin-top: 0.25rem; color: #6b7280;">1.0.0</p>
            </div>
            <div>
                <strong>Last Updated:</strong>
                <p style="margin-top: 0.25rem; color: #6b7280;"><?= date('F Y') ?></p>
            </div>
            <div>
                <strong>Platform:</strong>
                <p style="margin-top: 0.25rem; color: #6b7280;">Web-based LMS</p>
            </div>
            <div>
                <strong>License:</strong>
                <p style="margin-top: 0.25rem; color: #6b7280;">Educational Use</p>
            </div>
        </div>
    </div>
    
    <div style="margin-top: 3rem;">
        <a class="btn" href="lms/index.php" style="background: #4f46e5; color: white; padding: 1rem 2rem; border-radius: 8px; text-decoration: none; font-weight: 600;">Access Learning Portal</a>
    </div>
</section> -->


<div class="container-fluid inner-hero">
   <div class="container">
      <div class="row align-items-center pt-lg-5 pt-sm-5 pt-2">
         <div class="col-lg-8 col-sm-10 align-items-center pt-3">
            <h1 class="optima-font font-60 text-white pb-3">
               Learn Anywhere, Anytime
               Empower Your Future
            </h1>
            <p class="text-white manrope-font font-16 lh-base-1">
               Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took.
            </p>
         </div>
      </div>
   </div>
</div>


 
</div>

<div class="container-fluid inner-about-1 pt-4">
   <div class="container">
      <div class="row align-items-center pb-lg-0 pb-sm-4 pb-4">
<div class="col-lg-4 col-sm-4">
     <img src="assets/images/t2.png" class="w-100" alt="Zeylanica Education">
</div>
<div class="col-lg-8 col-sm-8 pt-sm-0 pt-4">
    <h2 class="optima-font font-60  text-blue pb-2">About Tissa Jananayake  </h2>
    <p class="text-black manrope-font font-16 lh-base-1">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing</p>
</div>

 </div>
   </div>
</div>


<div class="container-fluid inner-about-2 bg-white-light py-5">
   <div class="container py-lg-5 py-sm-5 py-0">
      <div class="row align-items-center">

<div class="col-lg-9 col-sm-9 col-12 ">
    <h2 class="optima-font font-60  text-blue pb-2">Zeylanica Education</h2>
    <p class="text-black manrope-font font-16 lh-base-1">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing</p>
</div>
<div class="col-lg-3 col-sm-3 col-6 mx-auto pt-lg-0 pt-sm-0 pt-4">
     <img src="assets/images/logo-2.png" class="w-100" alt="Zeylanica Education">
</div>
 </div>
   </div>
</div>


<div class="container-fluid inner-about-3   py-5">
   <div class="container ">
      <div class="row align-items-center">
<div class="col-12">
        <h2 class="optima-font font-60 text-center text-blue pb-4">Memorable Days</h2>
</div>
 <div class="col-lg-3 col-sm-3 col-12 pb-4">
        <img src="assets/images/m1.png" class="w-100" alt="Zeylanica Education">
 </div>

  <div class="col-lg-3 col-sm-3 col-12 pb-4">
        <img src="assets/images/m2.png" class="w-100" alt="Zeylanica Education">
 </div>

<div class="col-lg-3 col-sm-3 col-12 pb-4">
        <img src="assets/images/m3.png" class="w-100" alt="Zeylanica Education">
 </div>

  <div class="col-lg-3 col-sm-3 col-12 pb-4">
        <img src="assets/images/m4.png" class="w-100" alt="Zeylanica Education">
 </div>

<div class="col-lg-3 col-sm-3 col-12 pb-4">
        <img src="assets/images/m5.png" class="w-100" alt="Zeylanica Education">
 </div>

  <div class="col-lg-3 col-sm-3 col-12 pb-4">
        <img src="assets/images/m6.png" class="w-100" alt="Zeylanica Education">
 </div>

<div class="col-lg-3 col-sm-3 col-12 pb-4">
        <img src="assets/images/m7.png" class="w-100" alt="Zeylanica Education">
 </div>

  <div class="col-lg-3 col-sm-3 col-12 pb-4">
        <img src="assets/images/m8.png" class="w-100" alt="Zeylanica Education">
 </div>


 
 </div>
   </div>
</div>


<?php include 'includes/faq.php'; ?>


<?php include 'includes/footer.php'; ?>
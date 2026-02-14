<?php 
   require_once 'lms/config.php';
   $pageTitle = 'Class - Zeylanica Learning Portal'; 
   include 'includes/header.php';
   ?>
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
<div class="container-fluid class-section py-5">
   <div class="container">
      <div class="row pb-5 g-3 align-items-end">
         <!-- Academic Year -->
         <div class="col-lg-3 col-sm-6">
            <select name="academic_year" id="academic_year" class="form-select rounded-pill font-18 border border-primary fw-bold  manrope-font  text-blue px-3">
               <option value="">Academic Year</option>
               <option value="2023">2023 / 2024</option>
               <option value="2024">2024 / 2025</option>
               <option value="2025">2025 / 2026</option>
            </select>
         </div>
         <!-- Class Type -->
         <div class="col-lg-3 col-sm-6">
            <select name="class_type" id="class_type"  class="form-select rounded-pill font-18 border border-primary fw-bold  manrope-font  text-blue px-3">
               <option value="">Class Type</option>
               <option value="online">Online</option>
               <option value="physical">Physical</option>
               <option value="hybrid">Hybrid</option>
            </select>
         </div>
         <!-- Search -->
         <div class="col-lg-4 col-sm-8">
            <input
               type="text"
               class="form-control rounded-pill font-18 border border-primary fw-bold  manrope-font  text-blue"
               name="search"
               placeholder="Search"
               >
         </div>
         <!-- Buttons -->
         <div class="col-lg-2 col-sm-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100 rounded-pill font-14 border border-primary fw-bold  manrope-font px-4 py-2  text-white">
            Submit
            </button>
            <button type="reset" class="btn btn-danger fw-bold px-4 py-2  manrope-font font-14 w-100 rounded-pill">
            Reset
            </button>
         </div>
      </div>
      <div class="row ">
         <div class="col-lg-4 col-sm-6  mb-4 course-card">
            <div class="bg-white rounded-3 p-2 shadow ">
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
               <div class="row align-items-center py-4">
                  <div class="col-7">
                     <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
                  </div>
                  <div class="col-5">
                     <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-sm-6  mb-4 course-card">
            <div class="bg-white rounded-3 p-2 shadow ">
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
               <div class="row align-items-center py-4">
                  <div class="col-7">
                     <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
                  </div>
                  <div class="col-5">
                     <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-sm-6  mb-4 course-card">
            <div class="bg-white rounded-3 p-2 shadow ">
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
               <div class="row align-items-center py-4">
                  <div class="col-7">
                     <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
                  </div>
                  <div class="col-5">
                     <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-sm-6  mb-4 course-card">
            <div class="bg-white rounded-3 p-2 shadow ">
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
               <div class="row align-items-center py-4">
                  <div class="col-7">
                     <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
                  </div>
                  <div class="col-5">
                     <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-sm-6  mb-4 course-card">
            <div class="bg-white rounded-3 p-2 shadow ">
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
               <div class="row align-items-center py-4">
                  <div class="col-7">
                     <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
                  </div>
                  <div class="col-5">
                     <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-sm-6  mb-4 course-card">
            <div class="bg-white rounded-3 p-2 shadow ">
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
               <div class="row align-items-center py-4">
                  <div class="col-7">
                     <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
                  </div>
                  <div class="col-5">
                     <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-sm-6  mb-4 course-card">
            <div class="bg-white rounded-3 p-2 shadow ">
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
               <div class="row align-items-center py-4">
                  <div class="col-7">
                     <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
                  </div>
                  <div class="col-5">
                     <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-sm-6  mb-4 course-card">
            <div class="bg-white rounded-3 p-2 shadow ">
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
               <div class="row align-items-center py-4">
                  <div class="col-7">
                     <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
                  </div>
                  <div class="col-5">
                     <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-sm-6  mb-4 course-card">
            <div class="bg-white rounded-3 p-2 shadow ">
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
               <div class="row align-items-center py-4">
                  <div class="col-7">
                     <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
                  </div>
                  <div class="col-5">
                     <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-sm-6  mb-4 course-card">
            <div class="bg-white rounded-3 p-2 shadow ">
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
               <div class="row align-items-center py-4">
                  <div class="col-7">
                     <p class="text-dark font-19 fw-bold arimo-font">LKR 20000.00/<span class="fw-normal font-16">lifetime</span></p>
                  </div>
                  <div class="col-5">
                     <a href="" class="btn btn-blue w-100 rounded-pill text-white px-1 font-16 fw-bold arimo-font">Enroll Now</a>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-sm-6  mb-4 course-card">
            <div class="bg-white rounded-3 p-2 shadow ">
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
               <div class="row align-items-center py-4">
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
      <div class="row py-4">
         <div class="col-lg-4 col-8  mx-auto pt-3">
            <a id="loadMoreBtn" class="btn hvr-wobble-skew bg-red font-16 arimo-font fw-bold py-3 text-white text-center rounded-pill w-100 text-uppercase">
            show all
            </a>
         </div>
      </div>
   </div>
</div>
<?php include 'includes/faq.php'; ?>
<?php include 'includes/footer.php'; ?>
<script>
   $(document).ready(function(){
       // Initially show only 6 cards
       var cardsPerPage = 6;
       var currentPage = 1;
       
       // Hide all cards initially
       $('.course-card').hide();
       
       // Show first 6 cards
       showCards(1);
       
       // Handle "Show All" button click
       $('#loadMoreBtn').click(function(e){
           e.preventDefault();
           currentPage++;
           showCards(currentPage);
       });
       
       function showCards(page){
           var startIndex = (page - 1) * cardsPerPage;
           var endIndex = startIndex + cardsPerPage;
           
           // Show cards for current page
           $('.course-card').each(function(index){
               if(index >= startIndex && index < endIndex){
                   $(this).fadeIn(300);
               }
           });
           
           // Hide the button if all cards are shown
           var totalCards = $('.course-card').length;
           if(endIndex >= totalCards){
               $('#loadMoreBtn').text('No more courses').prop('disabled', true).css('opacity', '0.6');
           }
       }
   });
</script>
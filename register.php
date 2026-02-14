<?php 
   require_once 'lms/config.php';
   $pageTitle = 'Register - Zeylanica Learning Portal'; 
   include 'includes/header.php';
   ?>
<div class="container-fluid register-section py-5">
   <div class="container">
      <div class="row align-items-center">
         <div class="col-12">
            <h1 class="optima-font font-60 text-white pb-lg-5 pb-3 text-center">Student Registration Form </h1>
            <h4 class="font-30 text-white manrope-font fw-bold ">Personal Information </h4>
         </div>
         <form action="">
            <div class="row pb-2">
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Full Name (As per NIC / Birth Certificate) *</label>
                  <input type="text" id="fullName" name="fullName" placeholder="Enter your full name" class="form-control text-black font-16 py-2 manrope-font">
               </div>
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Name with Initials * </label>
                  <input type="text" id="nameWithInitials" name="nameWithInitials" placeholder="e.g., J.D.S. Silva" class="form-control text-black font-16 py-2 manrope-font">
               </div>
            </div>
            <div class="row pb-2">
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Date of Birth *</label>
                  <input type="text" id="dateOfBirth" name="dateOfBirth" placeholder="YYYY-MM-DD" class="form-control text-black font-16 py-2 manrope-font">
               </div>
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Age </label>
                  <input type="text" id="age" name="age" placeholder="Age" class="form-control text-black font-16 py-2 manrope-font">
               </div>
            </div>
            <div class="row pb-2">
               <div class="col-lg-6">
                  <label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Gender </label>
                  <select id="gender" name="gender" class="form-select text-black font-16 py-2 manrope-font">
                     <option value="" disabled selected>Select Gender</option>
                     <option value="male">Male</option>
                     <option value="female">Female</option>
                     <option value="other">Other</option>
                  </select>
               </div>
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">NIC </label>
                  <input type="text" id="nic" name="nic" placeholder="Enter NIC number" class="form-control text-black font-16 py-2 manrope-font">
               </div>
            </div>
            <div class="row pb-2">
               <div class="col-lg-6">
                  <label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">
                  Profile Photograph
                  </label>
                  <div class="custom-file-upload manrope-font ">
                     <input type="file" id="formFile">
                     <label for="formFile" class="upload-btn">
                     <i class="fa-solid fa-user"></i>
                     <span>Choose File</span>
                     <small>No file chosen</small>
                     </label>
                  </div>
               </div>
            </div>


            <div class="row pt-5">
                <div class="col-12">
                    <h4 class="font-30 text-white manrope-font fw-bold ">Contact Information </h4>
                </div>
            </div>


             <div class="row pb-2">
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Mobile Number * </label>
                  <input type="text" id="mobileNumber" name="mobileNumber" placeholder="Enter mobile number" class="form-control text-black font-16 py-2 manrope-font">
               </div>
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">WhatsApp Number </label>
                  <input type="text" id="whatsappNumber" name="whatsappNumber" placeholder="Enter WhatsApp number" class="form-control text-black font-16 py-2 manrope-font">
               </div>
            </div>


                <div class="row pb-2">
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Email Address * </label>
                  <input type="email" id="emailAddress" name="emailAddress" placeholder="Enter email address" class="form-control text-black font-16 py-2 manrope-font">
               </div>
               
            </div>





 <div class="row pt-5">
                <div class="col-12">
                    <h4 class="font-30 text-white manrope-font fw-bold ">Residential Address</h4>
                </div>
            </div>


             <div class="row pb-2">
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Address Line 1 * </label>
                  <input type="text" id="addressLine1" name="addressLine1" placeholder="Enter address line 1" class="form-control text-black font-16 py-2 manrope-font">
               </div>
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Address Line 2 </label>
                  <input type="text" id="addressLine2" name="addressLine2" placeholder="Enter address line 2" class="form-control text-black font-16 py-2 manrope-font">
               </div>
            </div>

  <div class="row pb-2">
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Province  </label>
                   <select class="form-select text-black font-16 py-2 manrope-font" id="province" name="province">
            <option value="">Select Province</option>
            <option value="western">Western</option>
            <option value="central">Central</option>
            <option value="southern">Southern</option>
            <option value="northern">Northern</option>
            <option value="eastern">Eastern</option>
            <option value="north_western">North Western</option>
            <option value="north_central">North Central</option>
            <option value="uva">Uva</option>
            <option value="sabaragamuwa">Sabaragamuwa</option>
        </select>
               </div>
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">District </label>
                  <select id="district" name="district" class="form-select text-black font-16 py-2 manrope-font">
        <option value="">Select District</option>
        <option value="Colombo">Colombo</option>
        <option value="Gampaha">Gampaha</option>
        <option value="Kalutara">Kalutara</option>
        <option value="Kandy">Kandy</option>
        <option value="Matale">Matale</option>
        <option value="Galle">Galle</option>
        <option value="Matara">Matara</option>
        <option value="Hambantota">Hambantota</option>
        <option value="Kurunegala">Kurunegala</option>
        <option value="Anuradhapura">Anuradhapura</option>
    </select>
               </div>
            </div>


       <div class="row pb-4">
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">City * </label>
                  <input type="text" id="city" name="city" placeholder="Enter city" class="form-control text-black font-16 py-2 manrope-font">
               </div>
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Postal Code  </label>
                  <input type="text" id="postalCode" name="postalCode" placeholder="Enter postal code" class="form-control text-black font-16 py-2 manrope-font">
               </div>
            </div>

 <div class="row pb-2">
    <div class="col-lg-6 reg-communication-method">
        <label class="fw-bold text-white font-20 manrope-font pt-4 pb-4 d-block">
            Preferred Communication Method
        </label>

        <div class="form-check form-check-inline me-51">
            <input class="form-check-input" type="radio" name="communication_method" id="commSms" value="sms">
            <label class="form-check-label text-white font-16 fw-bolder manrope-font" for="commSms">
                SMS
            </label>
        </div>

        <div class="form-check form-check-inline me-51">
            <input class="form-check-input" type="radio" name="communication_method" id="commWhatsapp" value="whatsapp">
            <label class="form-check-label text-white font-16 fw-bolder manrope-font" for="commWhatsapp">
                WhatsApp
            </label>
        </div>

        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="communication_method" id="commEmail" value="email">
            <label class="form-check-label text-white font-16 fw-bolder manrope-font" for="commEmail">
                Email
            </label>
        </div>
    </div>
</div>

 <div class="row pt-5">
                <div class="col-12">
                    <h4 class="font-30 text-white manrope-font fw-bold ">Academic Information </h4>
                </div>
            </div>


<div class="row pb-2">
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Current School / Institute *  </label>
                  <input type="text" id="schoolInstitute" name="schoolInstitute" placeholder="Enter school/institute name" class="form-control text-black font-16 py-2 manrope-font">
               </div>
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Grade / Year of Study * </label>
                  <input type="text" id="gradeYear" name="gradeYear" placeholder="Enter grade or year of study" class="form-control text-black font-16 py-2 manrope-font">
               </div>
            </div>



            <div class="row pb-2">
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Examination Year * </label>
                  <input type="text" id="examinationYear" name="examinationYear" placeholder="Enter examination year" class="form-control text-black font-16 py-2 manrope-font">
               </div>
               
            </div>








 <div class="row pt-5">
                <div class="col-12">
                    <h4 class="font-30 text-white manrope-font fw-bold ">Parent / Guardian Information</h4>
                </div>
            </div>


<div class="row pb-2">
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Parent / Guardian Full Name *  </label>
                  <input type="text" id="parentGuardianName" name="parentGuardianName" placeholder="Enter parent/guardian name" class="form-control text-black font-16 py-2 manrope-font">
               </div>
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Relationship to Student * </label>
                  <input type="text" id="relationship" name="relationship" placeholder="e.g., Father, Mother, Guardian" class="form-control text-black font-16 py-2 manrope-font">
               </div>
            </div>



            <div class="row pb-2">
               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Address </label>
                  <input type="text" id="parentGuardianAddress" name="parentGuardianAddress" placeholder="Enter address" class="form-control text-black font-16 py-2 manrope-font">
               </div>

               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">Mobile Number * </label>
                  <input type="text" id="parentGuardianMobile" name="parentGuardianMobile" placeholder="Enter mobile number" class="form-control text-black font-16 py-2 manrope-font">
               </div>
               
            </div>

 <div class="row pb-2">
              

               <div class="col-lg-6"><label class="fw-bold text-white font-20 manrope-font pt-4 pb-3">WhatsApp Number  </label>
                  <input type="text" id="parentGuardianWhatsapp" name="parentGuardianWhatsapp" placeholder="Enter WhatsApp number" class="form-control text-black font-16 py-2 manrope-font">
               </div>
               
            </div>


<div class="row pt-5">
               <div class="col-lg-6 col-8 mx-auto">
                
               
              <a class="btn hvr-wobble-skew bg-red font-28 arimo-font fw-bold py-2 text-white text-center rounded-pill w-100">
        Submit
            </a>
               </div>

             
               
            </div>


      </form>
   </div>
</div>





</div>
<?php include 'includes/faq.php'; ?>
<?php include 'includes/footer.php'; ?>
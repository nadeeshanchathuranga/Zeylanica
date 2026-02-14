<?php
require_once 'lms/config.php';
require_once 'lms/services/FAQService.php';

$faqService = new FAQService($pdo);
$faqs = $faqService->getAllFAQs(true);
?>
<!-- <section class="faq">
    <h2 class="center">Frequently Asked Questions</h2>
    <div style="max-width:900px;margin:40px auto">
        <//?php foreach ($faqs as $faq): ?>
            <div class="card" style="text-align: left; margin-bottom: 1rem;">
                <strong style="color: #4f46e5;"><//?= htmlspecialchars($faq['question']) ?></strong>
                <p style="margin-top: 0.5rem; color: #6b7280;"><//?= htmlspecialchars($faq['answer']) ?></p>
            </div>
        <//?php endforeach; ?>
    </div>
</section> -->



<div class="container-fluid faq-section pt-5 bg-white">
   <div class="container">

 <div class="row align-items-center">
         <div class="col-lg-8 text-center mx-auto pb-4">
            <h2 class="text-blue1 optima-font font-60 text-center "> Frequently Asked Questions
            </h2>
            <p class="text-black manrope-font font-16 lh-base-1 pt-2 pb-3">
               Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took.
            </p>
         </div>
      </div>

   <div class="row align-items-center py-5">
         <div class="col-lg-10 mx-auto">
            <div class="accordion" id="faqAccordion">
               <div class="accordion-item" id="accordionItem1">
                  <h2 class="accordion-header" id="headingOne">
                     <button class="accordion-button text-black font-22 fw-bold arimo-font" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Where does it come from?
                     </button>
                  </h2>
                  <div id="collapseOne" class="accordion-collapse collapse show bg-white-light" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                     <div class="accordion-body py-0 px-0">
                      <div class="row">
                           <div class="col-11">
<p class="text-black manrope-font font-16 lh-base">
                         It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.
                       </p>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="accordion-item" id="accordionItem2">
                  <h2 class="accordion-header" id="headingTwo">
                     <button class="accordion-button collapsed text-black font-22 fw-bold arimo-font" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
            Why do we use it?
                     </button>
                  </h2>
                  <div id="collapseTwo" class="accordion-collapse collapse bg-white-light" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                     <div class="accordion-body py-0 px-0 ">
                        <div class="row">
                           <div class="col-11">
<p class="text-black manrope-font font-16 lh-base">
                         It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.
                       </p>
                           </div>
                        </div>

                     </div>
                  </div>
               </div>
            </div>
         </div>

      </div>




      <div class="row align-items-center">
         <div class="col-lg-10 text-center mx-auto">
             <img src="assets/images/9.png" class="w-100" alt="Quote Image">
         </div>
      </div>

   </div>
</div>

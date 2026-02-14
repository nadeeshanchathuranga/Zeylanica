<?php 
require_once 'lms/config.php';
require_once 'lms/services/StudentRegistrationService.php';
$pageTitle = 'Zeylanica - Learning Portal'; 
include 'includes/header.php';

$success = null;
$error = null;
$registrationId = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $registrationService = new StudentRegistrationService($pdo);
        $result = $registrationService->registerStudent($_POST, $_FILES);
        
        $_SESSION['registration_id'] = $result['registration_id'];
        $_SESSION['student_id'] = $result['student_id'];
        
        header('Location: lms/verify.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Load geographic data
$provinces = $pdo->query("SELECT * FROM provinces ORDER BY name")->fetchAll();
$districts = $pdo->query("SELECT * FROM districts ORDER BY name")->fetchAll();
$cities = $pdo->query("SELECT * FROM cities ORDER BY name")->fetchAll();


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
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        min-height: 100vh;
    }
    
    .brand-content {
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem;
        backdrop-filter: blur(10px);
        border-radius: 20px;
        margin-top: 2rem;
    }
    
    .brand-content h1 {
        font-size: 2.5rem;
        font-weight: 700;
        background: white;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 2rem;
    }

    .form-section {
        text-align: left;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }


    .section-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .section-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea, #ffffff);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.1rem;
    }

    .section-header h3 {
        font-size: 1.3rem;
        font-weight: 600;
    }

    .bottle-image {

        top: 650px;
        left: 20px;
    }
    .spec-image {
        left: auto !important;
        top: 1800px;
        right: 15px;
        
    }
    
    .microscope-image
    {
        left: auto !important;
        top: 500px;
        right: 18px;
    }
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    .form-grid.single {
        grid-template-columns: 1fr;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8fafc;
        color: #1e293b;
    }
    .form-group small {
        color: #6b7280;
        font-size: 0.8rem;
        margin-top: 0.25rem;
        display: block;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .btn {
        padding: 14px 28px;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
       width: 300px;
        height: 50px;
        border-radius: 130px;
        opacity: 1;
        background-color:#FF0000;
        align-items: center;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
    }

    .error {
        background: linear-gradient(135deg, #fee2e2, #fecaca);
        color: #991b1b;
        padding: 1rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
        border-left: 4px solid #dc2626;
    }

    </style>

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
        <h1><?= $customTitle ?? 'Student Registration Form' ?></h1>
        <?php if ($error): ?>
                    <div class="error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" id="registerForm">
            <!-- Personal Information -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Personal Information</h3>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name (As per NIC/Birth Certificate) <span class="required">*</span></label>
                        <input type="text" name="full_name" required maxlength="150" pattern="[A-Za-z\s]+" 
                               value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" placeholder="Enter your full name">
                        <small>Only letters and spaces allowed</small>
                    </div>
                    <div class="form-group">
                        <label>Name with Initials <span class="required">*</span></label>
                        <input type="text" name="name_with_initials" required maxlength="100"
                               value="<?= htmlspecialchars($_POST['name_with_initials'] ?? '') ?>" placeholder="e.g., J.A. Smith">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="">Select Gender</option>
                            <option value="Male" <?= ($_POST['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($_POST['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth <span class="required">*</span></label>
                        <input type="date" name="date_of_birth" required max="<?= date('Y-m-d') ?>"
                               value="<?= htmlspecialchars($_POST['date_of_birth'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>NIC Number</label>
                        <input type="text" name="nic" pattern="[0-9]{9}[vVxX]|[0-9]{12}" 
                               placeholder="123456789V or 200012345678"
                               value="<?= htmlspecialchars($_POST['nic'] ?? '') ?>">
                        <small>Enter valid NIC format</small>
                    </div>
                    <div class="form-group">
                        <label>Profile Photograph</label>
                        <div class="file-input-wrapper">
                            <input type="file" name="profile_photo" accept="image/jpeg,image/png" id="profile_photo">
                            <label for="profile_photo" class="file-input-label">
                                <i class="fas fa-camera"></i>
                                Choose Photo
                            </label>
                        </div>
                        <small>Max 5MB, JPEG/PNG only</small>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <h3>Contact Information</h3>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Mobile Number <span class="required">*</span></label>
                        <input type="tel" name="mobile_number" required pattern="[0-9]{10}" 
                               placeholder="0771234567"
                               value="<?= htmlspecialchars($_POST['mobile_number'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>WhatsApp Number</label>
                        <input type="tel" name="whatsapp_number" pattern="[0-9]{10}" 
                               placeholder="0771234567"
                               value="<?= htmlspecialchars($_POST['whatsapp_number'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-grid single">
                    <div class="form-group">
                        <label>Email Address <span class="required">*</span></label>
                        <input type="email" name="email" required maxlength="255" placeholder="your.email@example.com"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Address Line 1 <span class="required">*</span></label>
                        <input type="text" name="address_line1" required maxlength="200" placeholder="Street address"
                               value="<?= htmlspecialchars($_POST['address_line1'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Address Line 2</label>
                        <input type="text" name="address_line2" maxlength="200" placeholder="Apartment, suite, etc."
                               value="<?= htmlspecialchars($_POST['address_line2'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>City <span class="required">*</span></label>
                        <select name="city" required>
                            <option value="">Select City</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?= $city['name'] ?>" <?= ($_POST['city'] ?? '') === $city['name'] ? 'selected' : '' ?>>
                                    <?= $city['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>District</label>
                        <select name="district">
                            <option value="">Select District</option>
                            <?php foreach ($districts as $district): ?>
                                <option value="<?= $district['name'] ?>" <?= ($_POST['district'] ?? '') === $district['name'] ? 'selected' : '' ?>>
                                    <?= $district['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Province</label>
                        <select name="province">
                            <option value="">Select Province</option>
                            <?php foreach ($provinces as $province): ?>
                                <option value="<?= $province['name'] ?>" <?= ($_POST['province'] ?? '') === $province['name'] ? 'selected' : '' ?>>
                                    <?= $province['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Preferred Communication <span class="required">*</span></label>
                        <select name="preferred_communication" required>
                            <option value="">Select Method</option>
                            <option value="SMS" <?= ($_POST['preferred_communication'] ?? '') === 'SMS' ? 'selected' : '' ?>>SMS</option>
                            <option value="WhatsApp" <?= ($_POST['preferred_communication'] ?? '') === 'WhatsApp' ? 'selected' : '' ?>>WhatsApp</option>
                            <option value="Email" <?= ($_POST['preferred_communication'] ?? '') === 'Email' ? 'selected' : '' ?>>Email</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Academic Information -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-school"></i>
                    </div>
                    <h3>Academic Information</h3>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Current School/Institute <span class="required">*</span></label>
                        <input type="text" name="current_school" required maxlength="150" placeholder="Name of your school/institute"
                               value="<?= htmlspecialchars($_POST['current_school'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Grade/Year of Study <span class="required">*</span></label>
                        <input type="text" name="grade_year" required maxlength="50" 
                               placeholder="e.g., Grade 10, Year 2"
                               value="<?= htmlspecialchars($_POST['grade_year'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-grid single">
                    <div class="form-group">
                        <label>Examination Year <span class="required">*</span></label>
                        <input type="number" name="examination_year" required 
                               min="<?= date('Y') ?>" max="<?= date('Y') + 10 ?>"
                               value="<?= htmlspecialchars($_POST['examination_year'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Guardian Information -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Parent/Guardian Information</h3>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Parent/Guardian Full Name <span class="required">*</span></label>
                        <input type="text" name="guardian_name" required maxlength="150" placeholder="Full name of parent/guardian"
                               value="<?= htmlspecialchars($_POST['guardian_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Relationship to Student <span class="required">*</span></label>
                        <input type="text" name="guardian_relationship" required maxlength="50" 
                               placeholder="e.g., Father, Mother, Guardian"
                               value="<?= htmlspecialchars($_POST['guardian_relationship'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-grid single">
                    <div class="form-group">
                        <label>Guardian Address</label>
                        <textarea name="guardian_address" maxlength="255" rows="3" placeholder="Guardian's address (if different from student)"><?= htmlspecialchars($_POST['guardian_address'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Guardian Mobile Number <span class="required">*</span></label>
                        <input type="tel" name="guardian_mobile" required pattern="[0-9]{10}" 
                               placeholder="0771234567"
                               value="<?= htmlspecialchars($_POST['guardian_mobile'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Alternative Mobile Number</label>
                        <input type="tel" name="guardian_mobile_alt" pattern="[0-9]{10}" 
                               placeholder="0771234567"
                               value="<?= htmlspecialchars($_POST['guardian_mobile_alt'] ?? '') ?>">
                    </div>
                </div>
            </div>

                </form>
                
                <div class="button-group">
                    <button type="submit" form="registerForm" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i>
                        Register Now
                    </button>                    
                </div>
            </div>
    </div>
</section>

<section class="faq">
  <h2 class="center">Frequently Asked Questions</h2>
  <div style="max-width:900px;margin:40px auto">
    <div class="card"><strong>Where does it come from?</strong><p style="margin-top:8px">Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.</p></div>
    <div class="card"><strong>Where does it come from?</strong><p style="margin-top:8px">Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.</p></div>
    <div class="card"><strong>Where does it come from?</strong><p style="margin-top:8px">Lorem Ipsum has been the industry's standard dummy text ever since the 1500s.</p></div>
  </div>
</section>

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
<script>
        // Form progress tracking
        const sections = document.querySelectorAll('.form-section');
        const progressBar = document.querySelector('.progress-bar');
        let completedSections = 0;


        // Check form completion and update section status
        function checkSectionCompletion(section) {
            const requiredFields = section.querySelectorAll('input[required], select[required]');
            const filledFields = Array.from(requiredFields).filter(f => f.value.trim() !== '');
            
            if (filledFields.length === requiredFields.length) {
                section.classList.add('completed');
                return true;
            } else {
                section.classList.remove('completed');
                return false;
            }
        }

        // Add event listeners to all form fields
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.addEventListener('input', () => {
                const section = field.closest('.form-section');
                if (section) {
                    checkSectionCompletion(section);
                    
                    // Update progress
                    completedSections = document.querySelectorAll('.form-section.completed').length;
                }
            });
        });

        // Auto-format mobile numbers
        document.querySelectorAll('input[type="tel"]').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });

        // File input enhancement
        document.getElementById('profile_photo').addEventListener('change', function() {
            const label = this.nextElementSibling;
            const fileName = this.files[0]?.name || 'Choose Photo';
            label.innerHTML = `<i class="fas fa-camera"></i> ${fileName}`;
        });

        // Form validation enhancement
        document.querySelector('form').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('input[required], select[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#ef4444';
                    isValid = false;
                } else {
                    field.style.borderColor = '#e5e7eb';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });

        // Initial progress check
        sections.forEach(section => {
            if (checkSectionCompletion(section)) {
                completedSections++;
            }
        });
        updateProgress();
    </script>

<?php include 'includes/footer.php'; ?>

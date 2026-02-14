<?php
require_once 'config.php';
require_once 'services/StudentRegistrationService.php';

$success = null;
$error = null;
$registrationId = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $registrationService = new StudentRegistrationService($pdo);
        $result = $registrationService->registerStudent($_POST, $_FILES);
        
        $_SESSION['registration_id'] = $result['registration_id'];
        $_SESSION['student_id'] = $result['student_id'];
        
        header('Location: verify.php');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Load geographic data
$provinces = $pdo->query("SELECT * FROM provinces ORDER BY name")->fetchAll();
$districts = $pdo->query("SELECT * FROM districts ORDER BY name")->fetchAll();
$cities = $pdo->query("SELECT * FROM cities ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Zeylanica Education</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <?php $showProgress = true; $progressWidth = 25; include 'includes/auth-styles.php'; ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; height: 100vh; overflow: hidden; }
        .register-container { display: flex; height: 100vh; }
        
        .right-panel {
            flex: 1;
        }
        .form-container {
            width: 100%;
            padding: 1rem;
            position: relative;
            z-index: 10;
        }
        .form-header {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .form-header h2 { color: #111827; font-size: 1.75rem; font-weight: 600; margin-bottom: 0.5rem; }
        .form-header p { color: #6B7280; }
        .form-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #4F46E5;
            transition: all 0.3s ease;
        }
        .form-section.completed {
            border-left-color: #10B981;
        }
        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #F3F4F6;
        }
        .section-icon {
            width: 32px;
            height: 32px;
            background: #4F46E5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.95rem;
        }
        .section-header h3 {
            color: #111827;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.25rem;
        }
        .form-grid.single {
            grid-template-columns: 1fr;
        }
        .form-group {
            margin-bottom: 0;
        }
        .form-group label {
            display: block;
            color: #374151;
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        .required {
            color: #ef4444;
            margin-left: 2px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #D1D5DB;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            color: #374151;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        .form-group small {
            color: #6b7280;
            font-size: 0.8rem;
            margin-top: 0.25rem;
            display: block;
        }
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }
        .file-input-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 12px 16px;
            border: 1px dashed #d1d5db;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fafafa;
            color: #6b7280;
            font-size: 0.95rem;
        }
        .file-input-label:hover {
            border-color: #4F46E5;
            background: #EEF2FF;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
        }
        .btn-primary {
            background: #4F46E5;
            color: white;
        }
        .btn-primary:hover {
            background: #4338CA;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 2px solid #e5e7eb;
        }
        .btn-secondary:hover {
            background: #e5e7eb;
            transform: translateY(-1px);
        }
        .button-group {
            padding: 1.5rem;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        .back-link {
            color: #4F46E5;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .error {
            background: #FEE2E2;
            color: #991B1B;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #DC2626;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        @media (max-width: 968px) {
            .register-container { flex-direction: column; }ing: 2rem 1rem; }
            .logo { width: 80px; height: 80px; }
            .logo i { font-size: 2.5rem; }
            .brand-title { font-size: 2rem; }
            .form-container { padding: 1.5rem; }
            .form-section { padding: 1.5rem; }
            .form-grid { grid-template-columns: 1fr; }
            .button-group { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <?php 
        $customTitle = 'Join Our Learning Community';
        $customSubtitle = 'Start Your Journey Today';
        $customMessage = 'Fill in your details to create your account and unlock access to expert-led courses';
        include 'includes/auth-left-panel.php'; 
        ?>
        
        <div class="right-panel scrollable">
            <div class="form-container">
                <div class="auth-header">
                    <h2>Student Registration</h2>
                    <p>Fill in your details to create your account</p>
                </div>                
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
                    <a href="index.php" class="back-link">
                        <i class="fas fa-arrow-left"></i>
                        Back to Login
                    </a>
                    
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form progress tracking
        const sections = document.querySelectorAll('.form-section');
        const progressBar = document.querySelector('.progress-bar');
        let completedSections = 0;

        function updateProgress() {
            const progress = Math.min((completedSections / (sections.length - 1)) * 100, 100);
            progressBar.style.width = progress + '%';
        }

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
                    updateProgress();
                }
            });
        });

        // Auto-format mobile numbers
        document.querySelectorAll('input[type="tel"]').forEach(input => {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });

        // File input enhancement with preview and validation
        document.getElementById('profile_photo').addEventListener('change', function() {
            const label = this.nextElementSibling;
            const file = this.files[0];
            
            if (file) {
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                const fileType = file.type.toLowerCase();
                
                if (!allowedTypes.includes(fileType)) {
                    alert('Please select a valid image file (JPEG or PNG)');
                    this.value = '';
                    label.innerHTML = '<i class="fas fa-camera"></i> Choose Photo';
                    return;
                }
                
                // Validate file size (1MB max for Base64)
                const maxSize = 1 * 1024 * 1024; // 1MB
                if (file.size > maxSize) {
                    alert('File size must be less than 1MB');
                    this.value = '';
                    label.innerHTML = '<i class="fas fa-camera"></i> Choose Photo';
                    return;
                }
                
                // Update label with file name
                const fileName = file.name.length > 20 ? file.name.substring(0, 20) + '...' : file.name;
                label.innerHTML = `<i class="fas fa-check-circle" style="color: #10B981;"></i> ${fileName}`;
                label.style.borderColor = '#10B981';
                label.style.backgroundColor = '#F0FDF4';
                
                // Create preview
                createImagePreview(file);
            } else {
                label.innerHTML = '<i class="fas fa-camera"></i> Choose Photo';
                label.style.borderColor = '#d1d5db';
                label.style.backgroundColor = '#fafafa';
                removeImagePreview();
            }
        });
        
        // Image preview function
        function createImagePreview(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let preview = document.getElementById('photo-preview');
                if (!preview) {
                    preview = document.createElement('div');
                    preview.id = 'photo-preview';
                    preview.style.cssText = `
                        margin-top: 10px;
                        text-align: center;
                        padding: 10px;
                        border: 1px solid #e5e7eb;
                        border-radius: 8px;
                        background: #f9fafb;
                    `;
                    document.getElementById('profile_photo').parentNode.appendChild(preview);
                }
                
                preview.innerHTML = `
                    <img src="${e.target.result}" alt="Preview" style="
                        max-width: 150px;
                        max-height: 150px;
                        border-radius: 8px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    ">
                    <p style="margin: 5px 0 0 0; font-size: 0.8rem; color: #6b7280;">Preview</p>
                `;
            };
            reader.readAsDataURL(file);
        }
        
        // Remove image preview
        function removeImagePreview() {
            const preview = document.getElementById('photo-preview');
            if (preview) {
                preview.remove();
            }
        }

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
</body>
</html>
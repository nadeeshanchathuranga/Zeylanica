<?php
require_once 'config.php';
require_once 'utils/ProfilePhotoHelper.php';

// Check if user is logged in
if (!isset($_SESSION['registration_id'])) {
    header('Location: index.php');
    exit;
}

$registrationId = $_SESSION['registration_id'];

// Get student registration data
$stmt = $pdo->prepare("SELECT * FROM student_registrations WHERE id = ?");
$stmt->execute([$registrationId]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - <?= htmlspecialchars($student['full_name']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; }
        .container { max-width: 800px; margin: 2rem auto; padding: 0 1rem; }
        .profile-card { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden; }
        .profile-header { background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%); padding: 2rem; text-align: center; color: white; }
        .profile-photo { width: 120px; height: 120px; border-radius: 50%; border: 4px solid white; margin: 0 auto 1rem; overflow: hidden; }
        .profile-photo img { width: 100%; height: 100%; object-fit: cover; }
        .profile-info { padding: 2rem; }
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
        .info-section { background: #f8fafc; padding: 1.5rem; border-radius: 8px; }
        .info-section h3 { color: #374151; font-size: 1.1rem; font-weight: 600; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .info-item { margin-bottom: 0.75rem; }
        .info-label { font-weight: 500; color: #6b7280; font-size: 0.9rem; }
        .info-value { color: #374151; margin-top: 0.25rem; }
        .status-badge { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem; font-weight: 500; }
        .status-verified { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .btn { padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-primary { background: #4F46E5; color: white; }
        .btn-secondary { background: #f3f4f6; color: #374151; }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-photo">
                    <?= ProfilePhotoHelper::displayProfilePhoto($student['profile_photo'], $student['full_name'], '') ?>
                </div>
                <h1><?= htmlspecialchars($student['full_name']) ?></h1>
                <p><?= htmlspecialchars($student['name_with_initials']) ?></p>
                <div class="status-badge <?= $student['status'] === 'VERIFIED' ? 'status-verified' : 'status-pending' ?>">
                    <i class="fas <?= $student['status'] === 'VERIFIED' ? 'fa-check-circle' : 'fa-clock' ?>"></i>
                    <?= ucfirst(str_replace('_', ' ', strtolower($student['status']))) ?>
                </div>
            </div>
            
            <div class="profile-info">
                <div class="info-grid">
                    <div class="info-section">
                        <h3><i class="fas fa-user"></i> Personal Information</h3>
                        <div class="info-item">
                            <div class="info-label">Student ID</div>
                            <div class="info-value"><?= htmlspecialchars($student['student_id']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Gender</div>
                            <div class="info-value"><?= htmlspecialchars($student['gender'] ?? 'Not specified') ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Date of Birth</div>
                            <div class="info-value"><?= date('F j, Y', strtotime($student['date_of_birth'])) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">NIC</div>
                            <div class="info-value"><?= htmlspecialchars($student['nic'] ?? 'Not provided') ?></div>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <h3><i class="fas fa-phone"></i> Contact Information</h3>
                        <div class="info-item">
                            <div class="info-label">Mobile Number</div>
                            <div class="info-value"><?= htmlspecialchars($student['mobile_number']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">WhatsApp</div>
                            <div class="info-value"><?= htmlspecialchars($student['whatsapp_number'] ?? 'Not provided') ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?= htmlspecialchars($student['email']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Address</div>
                            <div class="info-value">
                                <?= htmlspecialchars($student['address_line1']) ?><br>
                                <?php if ($student['address_line2']): ?>
                                    <?= htmlspecialchars($student['address_line2']) ?><br>
                                <?php endif; ?>
                                <?= htmlspecialchars($student['city']) ?>
                                <?php if ($student['district']): ?>, <?= htmlspecialchars($student['district']) ?><?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <h3><i class="fas fa-school"></i> Academic Information</h3>
                        <div class="info-item">
                            <div class="info-label">Current School</div>
                            <div class="info-value"><?= htmlspecialchars($student['current_school']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Grade/Year</div>
                            <div class="info-value"><?= htmlspecialchars($student['grade_year']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Examination Year</div>
                            <div class="info-value"><?= htmlspecialchars($student['examination_year']) ?></div>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <h3><i class="fas fa-users"></i> Guardian Information</h3>
                        <div class="info-item">
                            <div class="info-label">Guardian Name</div>
                            <div class="info-value"><?= htmlspecialchars($student['guardian_name']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Relationship</div>
                            <div class="info-value"><?= htmlspecialchars($student['guardian_relationship']) ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Guardian Mobile</div>
                            <div class="info-value"><?= htmlspecialchars($student['guardian_mobile']) ?></div>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 2rem; text-align: center; gap: 1rem; display: flex; justify-content: center;">
                    <a href="dashboard.php" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt"></i>
                        Go to Dashboard
                    </a>
                    <a href="logout.php" class="btn btn-secondary">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
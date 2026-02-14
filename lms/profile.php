<?php
require_once 'config.php';
require_once 'template.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$success = null;
$error = null;
$userId = $_SESSION['user_id'];

// Get user profile data
$stmt = $pdo->prepare("
    SELECT u.*, up.*, r.name as role_name
    FROM users u
    LEFT JOIN user_profiles up ON u.id = up.user_id
    LEFT JOIN roles r ON u.role_id = r.id
    WHERE u.id = ?
");
$stmt->execute([$userId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    $error = "Profile not found";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $profile) {
    try {
        $pdo->beginTransaction();
        
        // Update basic user info if changed
        if ($profile['email'] !== $_POST['email']) {
            // Email change requires verification (simplified for now)
            $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->execute([$_POST['email'], $userId]);
        }
        
        // Update or create user profile
        if ($profile['user_id']) {
            // Update existing profile
            $stmt = $pdo->prepare("
                UPDATE user_profiles SET
                    full_name = ?, name_with_initials = ?, gender = ?, 
                    date_of_birth = ?, nic = ?, mobile_number = ?,
                    whatsapp_number = ?, primary_email = ?, secondary_email = ?,
                    address_line1 = ?, address_line2 = ?, city = ?, 
                    district = ?, province = ?, postal_code = ?,
                    preferred_communication = ?, updated_at = NOW(),
                    last_updated_by = ?, version = version + 1
                WHERE user_id = ?
            ");
            
            $stmt->execute([
                $_POST['full_name'], $_POST['name_with_initials'], $_POST['gender'],
                $_POST['date_of_birth'], $_POST['nic'], $_POST['mobile_number'],
                $_POST['whatsapp_number'], $_POST['primary_email'], $_POST['secondary_email'],
                $_POST['address_line1'], $_POST['address_line2'], $_POST['city'],
                $_POST['district'], $_POST['province'], $_POST['postal_code'],
                $_POST['preferred_communication'], $userId, $userId
            ]);
        } else {
            // Create new profile
            $stmt = $pdo->prepare("
                INSERT INTO user_profiles 
                (user_id, full_name, name_with_initials, gender, date_of_birth, nic,
                 mobile_number, whatsapp_number, primary_email, secondary_email,
                 address_line1, address_line2, city, district, province, postal_code,
                 preferred_communication, status, completion_percentage, last_updated_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ACTIVE', 90, ?)
            ");
            
            $stmt->execute([
                $userId, $_POST['full_name'], $_POST['name_with_initials'], $_POST['gender'],
                $_POST['date_of_birth'], $_POST['nic'], $_POST['mobile_number'],
                $_POST['whatsapp_number'], $_POST['primary_email'], $_POST['secondary_email'],
                $_POST['address_line1'], $_POST['address_line2'], $_POST['city'],
                $_POST['district'], $_POST['province'], $_POST['postal_code'],
                $_POST['preferred_communication'], $userId
            ]);
        }
        
        $pdo->commit();
        $success = "Profile updated successfully";
        
        // Refresh profile data
        $stmt = $pdo->prepare("
            SELECT u.*, up.*, r.name as role_name
            FROM users u
            LEFT JOIN user_profiles up ON u.id = up.user_id
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to update profile: " . $e->getMessage();
    }
}

// Load geographic data
$provinces = $pdo->query("SELECT * FROM provinces ORDER BY name")->fetchAll();
$districts = $pdo->query("SELECT * FROM districts ORDER BY name")->fetchAll();
$cities = $pdo->query("SELECT * FROM cities ORDER BY name")->fetchAll();

ob_start();
?>

<style>
.profile-container { max-width: 800px; margin: 0 auto; }
.profile-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 12px 12px 0 0; text-align: center; }
.profile-avatar { width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem; }
.profile-form { background: white; padding: 2rem; border-radius: 0 0 12px 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.form-section { margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid #eee; }
.form-section:last-child { border-bottom: none; }
.form-section h3 { color: #333; margin-bottom: 1.5rem; font-size: 1.25rem; border-left: 4px solid #007bff; padding-left: 1rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.form-row.single { grid-template-columns: 1fr; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: #333; }
.form-group input, .form-group select, .form-group textarea { width: 100%; padding: 0.75rem; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 1rem; transition: border-color 0.3s; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #007bff; }
.form-group small { color: #666; font-size: 0.875rem; margin-top: 0.25rem; display: block; }
.btn { padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s; margin-right: 1rem; }
.btn-primary { background: #007bff; color: white; }
.btn-primary:hover { background: #0056b3; transform: translateY(-1px); }
.btn-secondary { background: #6c757d; color: white; }
.btn-secondary:hover { background: #545b62; }
.profile-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.stat-card { background: #f8f9fa; padding: 1.5rem; border-radius: 8px; text-align: center; }
.stat-number { font-size: 2rem; font-weight: bold; color: #007bff; }
.stat-label { color: #666; font-size: 0.9rem; }
.completion-bar { background: #e9ecef; height: 8px; border-radius: 4px; overflow: hidden; margin-top: 0.5rem; }
.completion-progress { background: #28a745; height: 100%; transition: width 0.3s; }
@media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
</style>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            <?= strtoupper(substr($profile['full_name'] ?? $profile['email'], 0, 1)) ?>
        </div>
        <h2><?= htmlspecialchars($profile['full_name'] ?? 'Complete Your Profile') ?></h2>
        <p><?= htmlspecialchars($profile['email']) ?> • <?= htmlspecialchars($profile['role_name'] ?? 'User') ?></p>
        <?php if (isset($profile['completion_percentage'])): ?>
            <div>
                <small>Profile Completion: <?= $profile['completion_percentage'] ?>%</small>
                <div class="completion-bar">
                    <div class="completion-progress" style="width: <?= $profile['completion_percentage'] ?>%"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="profile-form">
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="profile-stats">
            <div class="stat-card">
                <div class="stat-number"><?= htmlspecialchars($profile['role_name'] ?? 'User') ?></div>
                <div class="stat-label">Account Type</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= date('M Y', strtotime($profile['created_at'])) ?></div>
                <div class="stat-label">Member Since</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $profile['status'] ?? 'ACTIVE' ?></div>
                <div class="stat-label">Account Status</div>
            </div>
        </div>

        <form method="POST">
            <!-- Personal Information -->
            <div class="form-section">
                <h3>Personal Information</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" maxlength="150" 
                               value="<?= htmlspecialchars($profile['full_name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Name with Initials</label>
                        <input type="text" name="name_with_initials" maxlength="100"
                               value="<?= htmlspecialchars($profile['name_with_initials'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Gender</label>
                        <select name="gender">
                            <option value="">Select Gender</option>
                            <option value="Male" <?= ($profile['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($profile['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" max="<?= date('Y-m-d') ?>"
                               value="<?= htmlspecialchars($profile['date_of_birth'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row single">
                    <div class="form-group">
                        <label>NIC Number</label>
                        <input type="text" name="nic" pattern="[0-9]{9}[vVxX]|[0-9]{12}"
                               value="<?= htmlspecialchars($profile['nic'] ?? '') ?>">
                        <small>Enter valid NIC format (123456789V or 200012345678)</small>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="form-section">
                <h3>Contact Information</h3>
                
                <div class="form-row single">
                    <div class="form-group">
                        <label>Account Email</label>
                        <input type="email" name="email" maxlength="255" required
                               value="<?= htmlspecialchars($profile['email']) ?>">
                        <small>This is your login email address</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Primary Email</label>
                        <input type="email" name="primary_email" maxlength="255"
                               value="<?= htmlspecialchars($profile['primary_email'] ?? $profile['email']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Secondary Email</label>
                        <input type="email" name="secondary_email" maxlength="255"
                               value="<?= htmlspecialchars($profile['secondary_email'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Mobile Number</label>
                        <input type="tel" name="mobile_number" pattern="[0-9]{10}"
                               value="<?= htmlspecialchars($profile['mobile_number'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>WhatsApp Number</label>
                        <input type="tel" name="whatsapp_number" pattern="[0-9]{10}"
                               value="<?= htmlspecialchars($profile['whatsapp_number'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Address Line 1</label>
                        <input type="text" name="address_line1" maxlength="200"
                               value="<?= htmlspecialchars($profile['address_line1'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Address Line 2</label>
                        <input type="text" name="address_line2" maxlength="200"
                               value="<?= htmlspecialchars($profile['address_line2'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>City</label>
                        <select name="city">
                            <option value="">Select City</option>
                            <?php foreach ($cities as $city): ?>
                                <option value="<?= $city['name'] ?>" <?= ($profile['city'] ?? '') === $city['name'] ? 'selected' : '' ?>>
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
                                <option value="<?= $district['name'] ?>" <?= ($profile['district'] ?? '') === $district['name'] ? 'selected' : '' ?>>
                                    <?= $district['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Province</label>
                        <select name="province">
                            <option value="">Select Province</option>
                            <?php foreach ($provinces as $province): ?>
                                <option value="<?= $province['name'] ?>" <?= ($profile['province'] ?? '') === $province['name'] ? 'selected' : '' ?>>
                                    <?= $province['name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Preferred Communication</label>
                        <select name="preferred_communication">
                            <option value="">Select Method</option>
                            <option value="EMAIL" <?= ($profile['preferred_communication'] ?? '') === 'EMAIL' ? 'selected' : '' ?>>Email</option>
                            <option value="SMS" <?= ($profile['preferred_communication'] ?? '') === 'SMS' ? 'selected' : '' ?>>SMS</option>
                            <option value="WHATSAPP" <?= ($profile['preferred_communication'] ?? '') === 'WHATSAPP' ? 'selected' : '' ?>>WhatsApp</option>
                            <option value="PHONE_CALL" <?= ($profile['preferred_communication'] ?? '') === 'PHONE_CALL' ? 'selected' : '' ?>>Phone Call</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <button type="submit" class="btn btn-primary">Update Profile</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
echo renderAdminTemplate('My Profile', $content, $success);
?>
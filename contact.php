<?php 
require_once 'lms/config.php';
$pageTitle = 'Contact Us - Zeylanica Learning Portal'; 
include 'includes/header.php';

// Handle form submission
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Here you would typically send an email or save to database
        $success = true;
    }
}
?>

<section class="hero" style="min-height: 60vh; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="brand-content" style="text-align: center; color: white;">
        <h1>Contact Us</h1>
        <h2>Get in Touch with Zeylanica</h2>
        <p>We're here to help you with your learning journey. Reach out to us for any questions or support.</p>
    </div>
</section>

<section class="center">
    <h2>Contact Information</h2>
    <p style="color:#555;margin-top:8px">Multiple ways to reach us</p>
    
    <div class="grid-3" style="margin-top: 3rem;">
        <div class="card">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📞</div>
            <h3>Phone</h3>
            <p style="color: #4f46e5; font-weight: 600;">+94 123 456 789</p>
            <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
        </div>
        <div class="card">
            <div style="font-size: 3rem; margin-bottom: 1rem;">✉️</div>
            <h3>Email</h3>
            <p style="color: #4f46e5; font-weight: 600;">support@zeylanica.com</p>
            <p>We'll respond within 24 hours</p>
        </div>
        <div class="card">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📍</div>
            <h3>Address</h3>
            <p style="color: #4f46e5; font-weight: 600;">105 | Navalapitiya</p>
            <p>Buthgamuwa Rd, Colombo 07<br>Sri Lanka</p>
        </div>
    </div>
</section>

<section class="center" style="background: #f8fafc; padding: 4rem 0;">
    <h2>Send us a Message</h2>
    <p style="color:#555;margin-top:8px">Fill out the form below and we'll get back to you</p>
    
    <div style="max-width: 600px; margin: 2rem auto;">
        <?php if ($success): ?>
            <div class="card" style="background: #d1fae5; border: 1px solid #10b981; color: #065f46; margin-bottom: 2rem;">
                <p style="margin: 0;"><strong>✓ Message sent successfully!</strong> We'll get back to you soon.</p>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="card" style="background: #fee2e2; border: 1px solid #ef4444; color: #991b1b; margin-bottom: 2rem;">
                <p style="margin: 0;"><strong>⚠ Error:</strong> <?= htmlspecialchars($error) ?></p>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST" style="display: grid; gap: 1.5rem;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Name *</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" 
                               style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;" required>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Email *</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                               style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;" required>
                    </div>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Subject *</label>
                    <input type="text" name="subject" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" 
                           style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;" required>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Message *</label>
                    <textarea name="message" rows="5" 
                              style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; resize: vertical;" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn" style="background: #4f46e5; color: white; padding: 1rem 2rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    Send Message
                </button>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/faq.php'; ?>

<section class="newsletter">
    <div>
        <h2>Stay Updated</h2>
        <p style="margin-top:8px">Subscribe to our newsletter for course updates and educational content</p>
    </div>
    <div style="display:flex">
        <input type="email" placeholder="Enter your email" />
        <button>Subscribe Now</button>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
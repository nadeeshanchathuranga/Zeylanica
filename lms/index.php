<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("
        SELECT u.*, r.name as role_name 
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        WHERE u.email = ?
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['role_name'] = $user['role_name'];
        header('Location: dashboard.php');
        exit;
    }
    $error = "Invalid credentials";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Zeylanica Education</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php include 'includes/auth-styles.php'; ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; height: 100vh; overflow: hidden; }
        .login-container { display: flex; height: 100vh; }
        
        .forgot-password {
            text-align: right;
            margin-bottom: 1.5rem;
        }
        
        .forgot-password a {
            color: #4F46E5;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .register-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
        }
        
        .register-link p {
            color: #6B7280;
            margin-bottom: 1rem;
        }
        
        .register-link a {
            display: inline-block;
            padding: 10px 20px;
            background: #059669;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .register-link a:hover {
            background: #047857;
            transform: translateY(-1px);
        }
        
        @media (max-width: 768px) {
            .login-container { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <?php include 'includes/auth-left-panel.php'; ?>
        
        <div class="right-panel">
            <div class="auth-card">
                <div class="auth-header">
                    <h2>Login with Email</h2>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="email">Username</label>
                        <div class="input-wrapper">
                            <input type="email" id="email" name="email" placeholder="thisuix@mail.com" required>
                            <img src="assets/icons/icon-email.svg" alt="Email" class="icon" onerror="this.outerHTML='📧'">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" required>
                            <img src="assets/icons/icon-lock.svg" alt="Lock" class="icon" onerror="this.outerHTML='🔒'">
                        </div>
                    </div>
                    
                    <div class="forgot-password">
                        <a href="#">Forgot your password?</a>
                    </div>
                    
                    <button type="submit" class="auth-btn primary">Login</button>
                </form>
                
                <div class="register-link">
                    <p>New to Zeylanica Education?</p>
                    <a href="../register.php">Create Student Account</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all fields.');
                return;
            }
            
            if (!email.includes('@')) {
                e.preventDefault();
                alert('Please enter a valid email address.');
                return;
            }
        });
        
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.querySelector('.icon').style.color = '#4F46E5';
            });
            
            input.addEventListener('blur', function() {
                this.parentNode.querySelector('.icon').style.color = '#6B7280';
            });
        });
    </script>
</body>
</html>
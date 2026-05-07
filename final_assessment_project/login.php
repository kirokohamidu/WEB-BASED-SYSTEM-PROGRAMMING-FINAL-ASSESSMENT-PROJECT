<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(APP_URL . '/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $result = loginUser($email, $password);
        
        if ($result['success']) {
            setFlashMessage('success', 'Welcome back, ' . $result['user']['full_name'] . '!');
            redirect(APP_URL . '/index.php');
        } else {
            $errors[] = $result['message'];
        }
    }
}

$csrfToken = generateCSRFToken();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo APP_URL; ?>/index.php" class="nav-brand">
                <span class="brand-icon">&#9733;</span>
                <span class="brand-text">UMU Events</span>
            </a>
            <ul class="nav-menu">
                <li><a href="<?php echo APP_URL; ?>/index.php" class="nav-link">Home</a></li>
                <li><a href="<?php echo APP_URL; ?>/events.php" class="nav-link">Events</a></li>
                <li><a href="<?php echo APP_URL; ?>/register.php" class="btn btn-primary btn-sm">Register</a></li>
            </ul>
        </div>
    </nav>
    
    <main class="auth-page">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Welcome Back</h1>
                    <p>Log in to access UMU events</p>
                </div>
                
                <?php if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>">
                        <?php echo $flash['message']; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="auth-form" id="loginForm" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               value="" autocomplete="off">
                        <span class="error-message" id="email_error"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required autocomplete="off">
                        <span class="error-message" id="password_error"></span>
                    </div>
                    
                    <div class="form-group" style="text-align: right; margin-top: -10px;">
                        <a href="forgot_password.php" style="font-size: 0.9rem; color: var(--umu-navy);">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Log In</button>
                </form>
                
                <div class="auth-footer">
                    <p>Don't have an account? <a href="register.php">Register</a></p>                
                </div>
        </div>
    </main>
    
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand">
                <span class="brand-icon">&#9733;</span>
                <span>Uganda Martyrs University</span>
            </div>
            <div class="footer-links">
                <a href="<?php echo APP_URL; ?>/index.php">Home</a>
                <a href="<?php echo APP_URL; ?>/events.php">Events</a>
                <a href="<?php echo APP_URL; ?>/register.php">Register</a>
            </div>
            <div class="footer-copy">
                &copy; <?php echo date('Y'); ?> Uganda Martyrs University. All rights reserved.
            </div>
    </footer>
    
    <script src="js/main.js"></script>
    <script>
        // Clear login fields on page load to prevent browser autofill after logout
        document.addEventListener('DOMContentLoaded', function() {
            const emailField = document.getElementById('email');
            const passwordField = document.getElementById('password');
            if (emailField) emailField.value = '';
            if (passwordField) passwordField.value = '';
        });
    </script>
</body>
</html>

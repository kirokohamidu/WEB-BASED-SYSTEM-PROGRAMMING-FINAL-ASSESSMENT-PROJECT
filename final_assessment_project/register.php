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
        $fullName = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = 'student';
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
        
        if (empty($errors)) {
            $result = registerUser($fullName, $email, $password, $role);
            if ($result['success']) {
                setFlashMessage('success', $result['message']);
                redirect(APP_URL . '/login.php');
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}

$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
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
                <li><a href="<?php echo APP_URL; ?>/login.php" class="btn btn-outline btn-sm">Login</a></li>
            </ul>
        </div>
    </nav>
    
    <main class="auth-page">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Create Account</h1>
                    <p>Join Uganda Martyrs University events platform</p>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="auth-form" id="registerForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" required 
                               minlength="3" value="<?php echo $_POST['full_name'] ?? ''; ?>">
                        <span class="error-message" id="full_name_error"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo $_POST['email'] ?? ''; ?>">
                        <span class="error-message" id="email_error"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required minlength="6">
                        <span class="error-message" id="password_error"></span>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <span class="error-message" id="confirm_password_error"></span>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                </form>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php">Log in</a></p>
                </div>
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
                <a href="<?php echo APP_URL; ?>/login.php">Login</a>
            </div>
            <div class="footer-copy">
                &copy; <?php echo date('Y'); ?> Uganda Martyrs University. All rights reserved.
            </div>
        </div>
    </footer>
    
    <script src="js/main.js"></script>
</body>
</html>

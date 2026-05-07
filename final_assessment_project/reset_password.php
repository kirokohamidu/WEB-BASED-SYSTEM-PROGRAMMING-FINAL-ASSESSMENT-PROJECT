<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(APP_URL . '/index.php');
}

$errors = [];
$success = '';
$token = trim($_GET['token'] ?? '');

// Validate token on GET request
$tokenValid = false;
if (!empty($token)) {
    $validation = validateResetToken($token);
    $tokenValid = $validation['success'];
    if (!$tokenValid) {
        $errors[] = $validation['message'];
    }
} else {
    $tokenValid = false;
    $errors[] = 'No reset token provided.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        } else {
            $result = resetPassword($token, $password);
            
            if ($result['success']) {
                $success = $result['message'];
                $tokenValid = false; // Hide form after success
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
    <title>Reset Password - <?php echo APP_NAME; ?></title>
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
                <li><a href="<?php echo APP_URL; ?>/login.php" class="btn btn-primary btn-sm">Log In</a></li>
            </ul>
        </div>
    </nav>
    
    <main class="auth-page">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h1>Reset Password</h1>
                    <p>Create a new password for your account</p>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <p><?php echo $success; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if ($tokenValid): ?>
                    <form method="POST" action="" class="auth-form" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" required 
                                   minlength="6" autocomplete="off">
                            <span class="error-message" id="password_error"></span>
                            <small class="form-hint">Minimum 6 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required 
                                   minlength="6" autocomplete="off">
                            <span class="error-message" id="confirm_password_error"></span>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                    </form>
                <?php elseif (empty($success)): ?>
                    <div class="text-center" style="padding: 20px 0;">
                        <p>The reset link is invalid or has expired.</p>
                        <a href="forgot_password.php" class="btn btn-primary" style="margin-top: 16px;">Request New Link</a>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="auth-footer">
                        <a href="login.php" class="btn btn-primary btn-block">Go to Login</a>
                    </div>
                <?php endif; ?>
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
                <a href="<?php echo APP_URL; ?>/login.php">Log In</a>
            </div>
            <div class="footer-copy">
                &copy; <?php echo date('Y'); ?> Uganda Martyrs University. All rights reserved.
            </div>
    </footer>
    
    <script src="js/main.js"></script>
</body>
</html>

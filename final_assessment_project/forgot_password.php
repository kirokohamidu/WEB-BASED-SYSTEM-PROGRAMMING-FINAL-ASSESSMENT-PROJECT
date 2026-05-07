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
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $result = generateResetToken($email);
        
        if ($result['success']) {
            if (isset($result['reset_url'])) {
                $success = 'Password reset link generated! Use the link below to reset your password.';
                $resetLink = $result['reset_url'];
            } else {
                $success = $result['message'];
            }
        } else {
            $errors[] = $result['message'];
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
    <title>Forgot Password - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
    <style>
        .reset-link-box {
            background: #e8f4fd;
            border: 1px solid #b8daff;
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
            word-break: break-all;
        }
        .reset-link-box a {
            color: var(--umu-navy);
            font-weight: 600;
        }
        .reset-link-box p {
            color: var(--umu-gray-dark);
            font-size: 0.85rem;
            margin-top: 8px;
        }
    </style>
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
                    <h1>Forgot Password</h1>
                    <p>Enter your email to receive a password reset link</p>
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
                    <?php if (!empty($resetLink)): ?>
                        <div class="reset-link-box">
                            <a href="<?php echo htmlspecialchars($resetLink); ?>"><?php echo htmlspecialchars($resetLink); ?></a>
                            <p><em>In a production environment, this link would be sent to your email address.</em></p>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <form method="POST" action="" class="auth-form" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo $_POST['email'] ?? ''; ?>" autocomplete="off">
                        <span class="error-message" id="email_error"></span>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
                </form>
                
                <div class="auth-footer">
                    <p>Remember your password? <a href="login.php">Log In</a></p>
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
                <a href="<?php echo APP_URL; ?>/login.php">Log In</a>
            </div>
            <div class="footer-copy">
                &copy; <?php echo date('Y'); ?> Uganda Martyrs University. All rights reserved.
            </div>
        </div>
    </footer>
    
    <script src="js/main.js"></script>
</body>
</html>


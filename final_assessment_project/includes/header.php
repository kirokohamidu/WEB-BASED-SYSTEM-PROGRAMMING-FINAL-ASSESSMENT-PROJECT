<?php
// Ensure config is loaded
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../includes/functions.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Merriweather:wght@700&display=swap" rel="stylesheet">
</head>
<body<?php echo !empty($bodyClass) ? ' class="' . htmlspecialchars($bodyClass) . '"' : ''; ?>>
    <nav class="navbar">
        <div class="nav-container">
            <a href="<?php echo APP_URL; ?>/index.php" class="nav-brand">
                <span class="brand-icon">&#9733;</span>
                <span class="brand-text">UMU Events</span>
            </a>
            
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>
            
            <ul class="nav-menu" id="navMenu">
                <li><a href="<?php echo APP_URL; ?>/index.php" class="nav-link">Home</a></li>
                <li><a href="<?php echo APP_URL; ?>/events.php" class="nav-link">Events</a></li>
                
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <li><a href="<?php echo APP_URL; ?>/admin/events.php" class="nav-link">Manage Events</a></li>
                    <?php endif; ?>
                    
                    <?php if (isStudent()): ?>
                        <li><a href="<?php echo APP_URL; ?>/my_rsvps.php" class="nav-link">My RSVPs</a></li>
                    <?php endif; ?>
                    
                    <li><a href="<?php echo APP_URL; ?>/dashboard.php" class="nav-link">Dashboard</a></li>
                    <li class="nav-user">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                        <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-outline btn-sm">Logout</a>
                    </li>
                <?php else: ?>
                    <li><a href="<?php echo APP_URL; ?>/login.php" class="nav-link">Login</a></li>
                    <li><a href="<?php echo APP_URL; ?>/register.php" class="btn btn-primary btn-sm">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    
    <div class="main-content">


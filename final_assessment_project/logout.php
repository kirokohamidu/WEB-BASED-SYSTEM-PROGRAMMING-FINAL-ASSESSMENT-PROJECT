<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

logoutUser();
setFlashMessage('success', 'You have been logged out successfully.');
redirect(APP_URL . '/index.php');


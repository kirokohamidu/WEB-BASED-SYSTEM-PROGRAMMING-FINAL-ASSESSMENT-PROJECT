<?php
/**
 * Uganda Martyrs University Event Management System
 * Configuration File
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'umu_event_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('APP_NAME', 'UMU Event Management System');
define('APP_URL', 'http://localhost/final_assessment_project');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

// UMU Brand Colors
define('UMU_NAVY', '#003366');
define('UMU_GOLD', '#D4AF37');
define('UMU_RED', '#C41E3A');
define('UMU_WHITE', '#FFFFFF');

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

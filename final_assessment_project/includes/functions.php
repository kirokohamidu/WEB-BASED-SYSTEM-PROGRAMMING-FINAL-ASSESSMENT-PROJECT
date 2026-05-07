<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Sanitize user input to prevent XSS
 * @param string $data
 * @return string
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * @param string $token
 * @return bool
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Display flash messages
 * @param string $type success|error|warning
 * @param string $message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 * @return array|null
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Format date for display
 * @param string $date
 * @return string
 */
function formatDate($date) {
    return date('F j, Y \a\t g:i A', strtotime($date));
}

/**
 * Redirect to URL
 * @param string $url
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if current user is admin
 * @return bool
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Check if current user is student
 * @return bool
 */
function isStudent() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student';
}

/**
 * Require authentication
 */
function requireAuth() {
    if (!isLoggedIn()) {
        setFlashMessage('error', 'Please log in to access this page.');
        redirect(APP_URL . '/login.php');
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        setFlashMessage('error', 'Access denied. Admin privileges required.');
        redirect(APP_URL . '/index.php');
    }
}

/**
 * Require student role
 */
function requireStudent() {
    requireAuth();
    if (!isStudent()) {
        setFlashMessage('error', 'Access denied. Student privileges required.');
        redirect(APP_URL . '/index.php');
    }
}


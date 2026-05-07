<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

/**
 * Register a new user
 * @param string $fullName
 * @param string $email
 * @param string $password
 * @param string $role
 * @return array [success, message, user_id]
 */
function registerUser($fullName, $email, $password, $role = 'student') {
    $pdo = getDBConnection();
    
    // Validation
    $errors = [];
    
    if (empty($fullName) || strlen($fullName) < 3) {
        $errors[] = "Full name must be at least 3 characters.";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    
    if (!in_array($role, ['student', 'admin'])) {
        $role = 'student';
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'message' => implode(' ', $errors)];
    }
    
    try {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already registered. Please use a different email or log in.'];
        }
        
        // Hash password and insert
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$fullName, $email, $passwordHash, $role]);
        
        $userId = $pdo->lastInsertId();
        
        return ['success' => true, 'message' => 'Registration successful! Please log in.', 'user_id' => $userId];
        
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed. Please try again later.'];
    }
}

/**
 * Log in a user
 * @param string $email
 * @param string $password
 * @return array [success, message, user]
 */
function loginUser($email, $password) {
    $pdo = getDBConnection();
    
    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Please enter both email and password.'];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }
        
        // Admin passwords may be plain text OR hashed (hashed after password reset)
        if ($user['role'] === 'admin') {
            $passwordValid = password_verify($password, $user['password_hash']) || ($password === $user['password_hash']);
        } else {
            $passwordValid = password_verify($password, $user['password_hash']);
        }
        
        if (!$passwordValid) {
            return ['success' => false, 'message' => 'Invalid email or password.'];
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['login_time'] = time();
        
        return ['success' => true, 'message' => 'Login successful!', 'user' => $user];
        
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Login failed. Please try again later.'];
    }
}

/**
 * Generate a password reset token for a user
 * @param string $email
 * @return array [success, message, token, reset_url]
 */
function generateResetToken($email) {
    $pdo = getDBConnection();
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Please enter a valid email address.'];
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Don't reveal if email exists for security
            return ['success' => true, 'message' => 'If this email exists in our system, a reset link has been generated.'];
        }
        
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $user['id']]);
        
        $resetUrl = APP_URL . '/reset_password.php?token=' . $token;
        
        return [
            'success' => true, 
            'message' => 'Password reset link generated successfully.',
            'token' => $token,
            'reset_url' => $resetUrl,
            'user_name' => $user['full_name']
        ];
        
    } catch (PDOException $e) {
        error_log("Reset token error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to generate reset token. Please try again.'];
    }
}

/**
 * Validate a password reset token
 * @param string $token
 * @return array [success, message, user_id]
 */
function validateResetToken($token) {
    $pdo = getDBConnection();
    
    if (empty($token) || strlen($token) !== 64) {
        return ['success' => false, 'message' => 'Invalid reset token format.'];
    }
    
    try {
        // Use PHP time to avoid timezone mismatches with MySQL NOW()
        $now = date('Y-m-d H:i:s');
        $stmt = $pdo->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ? AND reset_expires > ?");
        $stmt->execute([$token, $now]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Check if token exists at all for better diagnostics
            $stmt2 = $pdo->prepare("SELECT reset_expires FROM users WHERE reset_token = ?");
            $stmt2->execute([$token]);
            $row = $stmt2->fetch();
            
            if ($row) {
                error_log("Token found but expired. Expires: " . $row['reset_expires'] . " | Now: " . $now);
                return ['success' => false, 'message' => 'Reset token has expired. Please request a new one.'];
            } else {
                error_log("Token not found in database: " . substr($token, 0, 8) . "...");
                return ['success' => false, 'message' => 'Invalid reset token. Please request a new one.'];
            }
        }
        
        return ['success' => true, 'message' => 'Token is valid.', 'user_id' => $user['id']];
        
    } catch (PDOException $e) {
        error_log("Token validation error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to validate token. Please try again.'];
    }
}

/**
 * Reset user password using token
 * @param string $token
 * @param string $newPassword
 * @return array [success, message]
 */
function resetPassword($token, $newPassword) {
    $pdo = getDBConnection();
    
    if (empty($newPassword) || strlen($newPassword) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters.'];
    }
    
    $validation = validateResetToken($token);
    if (!$validation['success']) {
        return $validation;
    }
    
    try {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->execute([$passwordHash, $validation['user_id']]);
        
        return ['success' => true, 'message' => 'Password reset successful! You can now log in with your new password.'];
        
    } catch (PDOException $e) {
        error_log("Password reset error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to reset password. Please try again.'];
    }
}

/**
 * Log out current user
 */
function logoutUser() {
    // Clear session data
    $_SESSION = [];
    
    // Destroy session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    session_destroy();
}

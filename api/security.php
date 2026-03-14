<?php
/**
 * Security Utilities for Student Management System
 * Handles: CSRF tokens, input validation, rate limiting, logging
 */

// Configuration
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_ATTEMPT_WINDOW', 3600); // 1 hour
define('SESSION_TIMEOUT', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF Token
 */
function verifyCSRFToken($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF Token Field for Forms
 */
function getCSRFField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCSRFToken()) . '">';
}

/**
 * Validate Username Format
 */
function validateUsername($username) {
    // Username must be 3-50 characters, alphanumeric and underscores only
    if (empty($username) || strlen($username) < 3 || strlen($username) > 50) {
        return false;
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        return false;
    }
    return true;
}

/**
 * Validate Password Strength
 */
function validatePassword($password) {
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return [
            'valid' => false,
            'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.'
        ];
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one uppercase letter.'
        ];
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one lowercase letter.'
        ];
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one number.'
        ];
    }
    
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\|`~]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one special character (!@#$%^&* etc).'
        ];
    }
    
    return ['valid' => true, 'message' => ''];
}

/**
 * Check and Enforce Rate Limiting
 */
function checkRateLimit($identifier, $conn) {
    // Create log table if not exists
    if (!tableExists('login_attempts', $conn)) {
        $conn->query("CREATE TABLE login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            identifier VARCHAR(255),
            attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45),
            user_agent TEXT
        )");
    }
    
    $ip = getClientIP();
    $cutoff_time = time() - LOGIN_ATTEMPT_WINDOW;
    
    // Count failed attempts in the window
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM login_attempts WHERE identifier = ? AND attempt_time > FROM_UNIXTIME(?);");
    $stmt->bind_param("si", $identifier, $cutoff_time);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'] < MAX_LOGIN_ATTEMPTS;
}

/**
 * Log Failed Login Attempt
 */
function logFailedAttempt($identifier, $conn) {
    $ip = getClientIP();
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO login_attempts (identifier, ip_address, user_agent) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $identifier, $ip, $user_agent);
    $stmt->execute();
    $stmt->close();
}

/**
 * Get Client IP Address
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

/**
 * Sanitize Input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if Table Exists
 */
function tableExists($tableName, $conn) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result && $result->num_rows > 0;
}

/**
 * Setup Session Security
 */
function setupSessionSecurity() {
    // Prevent session fixation
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    }
    
    // Regenerate session ID periodically (Disabled for Vercel stability)
    /*
    if (time() - $_SESSION['CREATED'] > SESSION_TIMEOUT) {
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();
    }
    */
    
    // Verify session hasn't been hijacked
    $user_agent = sha1($_SERVER['HTTP_USER_AGENT']);
    if (isset($_SESSION['USER_AGENT']) && $_SESSION['USER_AGENT'] !== $user_agent) {
        session_destroy();
        header("Location: login.php?error=session_hijack");
        exit();
    }
    $_SESSION['USER_AGENT'] = $user_agent;
}

/**
 * Check if User is Authenticated
 */
function isAuthenticated() {
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Check session timeout
    if (time() - $_SESSION['CREATED'] > SESSION_TIMEOUT) {
        session_destroy();
        return false;
    }
    
    return true;
}

/**
 * Require Authentication
 */
function requireAuth() {
    if (!isAuthenticated()) {
        header("Location: login.php?error=login_required");
        exit();
    }
}

/**
 * Check Authorization by Role
 */
function requireRole($required_roles) {
    requireAuth();
    
    if (!is_array($required_roles)) {
        $required_roles = [$required_roles];
    }
    
    if (!in_array($_SESSION['role'], $required_roles)) {
        header("Location: dashboard.php?error=unauthorized");
        exit();
    }
}

/**
 * Log Activity
 */
function logActivity($user_id, $action, $details, $conn) {
    // Create audit log table if not exists
    if (!tableExists('audit_log', $conn)) {
        $conn->query("CREATE TABLE audit_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            action VARCHAR(255),
            details TEXT,
            ip_address VARCHAR(45),
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");
    }
    
    $ip = getClientIP();
    $stmt = $conn->prepare("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $details, $ip);
    $stmt->execute();
    $stmt->close();
}
?>

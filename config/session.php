<?php
// Session management for NSBMunch
// Start session with secure settings
if (session_status() == PHP_SESSION_NONE) {
    // Configure session settings for security
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS
    ini_set('session.use_strict_mode', 1);
    
    session_start();
}

// Session timeout (30 minutes)
define('SESSION_TIMEOUT', 30 * 60); // 30 minutes in seconds

// Check if session has expired
function isSessionExpired() {
    if (isset($_SESSION['last_activity'])) {
        return (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT;
    }
    return false;
}

// Update last activity time
function updateLastActivity() {
    $_SESSION['last_activity'] = time();
}

// Initialize session for user
function initUserSession($user_data) {
    $_SESSION['user_id'] = $user_data['id'];
    $_SESSION['username'] = $user_data['username'];
    $_SESSION['campus_id'] = $user_data['campus_id'];
    $_SESSION['email'] = $user_data['email'];
    $_SESSION['user_category'] = $user_data['user_category'];
    $_SESSION['user_type'] = 'user';
    updateLastActivity();
}

// Initialize session for shop owner
function initShopOwnerSession($shop_data) {
    $_SESSION['shop_owner_id'] = $shop_data['id'];
    $_SESSION['shop_id'] = $shop_data['shop_id'];
    $_SESSION['owner_name'] = $shop_data['owner_name'];
    $_SESSION['shop_name'] = $shop_data['shop_name'];
    $_SESSION['email'] = $shop_data['email'];
    $_SESSION['location'] = $shop_data['location'];
    $_SESSION['user_type'] = 'shop_owner';
    updateLastActivity();
}

// Initialize session for admin
function initAdminSession($admin_data) {
    $_SESSION['admin_id'] = $admin_data['id'];
    $_SESSION['email'] = $admin_data['email'];
    $_SESSION['user_type'] = 'admin';
    updateLastActivity();
}

// Check if user is logged in and session is valid
function checkUserSession() {
    if (isSessionExpired()) {
        destroySession();
        return false;
    }
    
    if (isset($_SESSION['user_type'])) {
        updateLastActivity();
        return true;
    }
    
    return false;
}

// Check if user has specific role
function checkUserRole($required_role) {
    if (!checkUserSession()) {
        return false;
    }
    
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === $required_role;
}

// Require user login
function requireUserLogin() {
    if (!checkUserRole('user')) {
        redirect(SITE_URL . '/auth/login.php');
        exit();
    }
}

// Require shop owner login
function requireShopOwnerLogin() {
    if (!checkUserRole('shop_owner')) {
        redirect(SITE_URL . '/auth/login.php');
        exit();
    }
}

// Require admin login
function requireAdminLogin() {
    if (!checkUserRole('admin')) {
        redirect(SITE_URL . '/auth/login.php');
        exit();
    }
}

// Redirect if already logged in
function redirectIfLoggedIn() {
    if (checkUserSession()) {
        switch ($_SESSION['user_type']) {
            case 'user':
                redirect(SITE_URL . '/user/home.php');
                break;
            case 'shop_owner':
                redirect(SITE_URL . '/shop_owner/home.php');
                break;
            case 'admin':
                redirect(SITE_URL . '/admin/home.php');
                break;
        }
        exit();
    }
}

// Destroy session and logout
function destroySession() {
    session_unset();
    session_destroy();
    
    // Delete session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
}

// Get current user info
function getCurrentUser() {
    if (!checkUserSession()) {
        return null;
    }
    
    $user_info = [];
    
    switch ($_SESSION['user_type']) {
        case 'user':
            $user_info = [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'campus_id' => $_SESSION['campus_id'],
                'email' => $_SESSION['email'],
                'user_category' => $_SESSION['user_category'],
                'type' => 'user'
            ];
            break;
            
        case 'shop_owner':
            $user_info = [
                'id' => $_SESSION['shop_owner_id'],
                'shop_id' => $_SESSION['shop_id'],
                'owner_name' => $_SESSION['owner_name'],
                'shop_name' => $_SESSION['shop_name'],
                'email' => $_SESSION['email'],
                'location' => $_SESSION['location'],
                'type' => 'shop_owner'
            ];
            break;
            
        case 'admin':
            $user_info = [
                'id' => $_SESSION['admin_id'],
                'email' => $_SESSION['email'],
                'type' => 'admin'
            ];
            break;
    }
    
    return $user_info;
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Auto-cleanup expired sessions
function cleanupSessions() {
    if (isSessionExpired()) {
        destroySession();
    }
}

// Call cleanup on every page load
cleanupSessions();
?>
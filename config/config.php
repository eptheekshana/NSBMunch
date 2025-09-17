<?php
// General configuration file for NSBMunch
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Site configuration
define('SITE_NAME', 'NSBMunch');
define('SITE_URL', 'http://localhost/NSBMunch');
define('SITE_EMAIL', 'info@nsbmunch.com');
define('ADMIN_EMAIL', 'nsbm@gmail.com');

// File upload configuration
define('UPLOAD_DIR', 'assets/uploads/food_images/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Pagination settings
define('ITEMS_PER_PAGE', 12);

// Password requirements
define('MIN_PASSWORD_LENGTH', 6);

// Order settings
define('ORDER_PREFIX', 'ORD');

// Food categories
$food_categories = [
    'Breakfast & Brunch',
    'American Food',
    'Burgers',
    'Cafe',
    'Chinese Food',
    'Desserts',
    'Indian Food',
    'Italian Food',
    'Japanese Food',
    'Pizza',
    'Sandwiches',
    'Seafood',
    'Thai Food',
    'Vegetarian Food / Vegan Food',
    'Healthy Food',
    'Late Night Food',
    'Street Food',
    'Drinks & Smoothies'
];

// User categories
$user_categories = [
    'student' => 'Student',
    'lecturer' => 'Lecturer', 
    'staff' => 'Staff'
];

// Order statuses
$user_order_statuses = [
    'pending' => 'Pending',
    'confirmed' => 'Confirmed',
    'cancelled' => 'Cancelled'
];

$shop_order_statuses = [
    'pending' => 'Pending',
    'confirmed' => 'Order Confirmed',
    'rejected' => 'Order Rejected',
    'ready' => 'Order Ready',
    'pickup' => 'Order Pickup'
];

// Payment methods
$payment_methods = [
    'pay_at_canteen' => 'Pay at Canteen'
];

// Helper functions
function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) || isset($_SESSION['shop_id']) || isset($_SESSION['admin_id']);
}

function isUser() {
    return isset($_SESSION['user_id']);
}

function isShopOwner() {
    return isset($_SESSION['shop_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentShopId() {
    return $_SESSION['shop_id'] ?? null;
}

function generateOrderId() {
    return ORDER_PREFIX . date('Ymd') . rand(1000, 9999);
}

function formatPrice($price) {
    return 'Rs. ' . number_format($price, 2);
}

function formatDate($date) {
    return date('Y-m-d', strtotime($date));
}

function formatTime($time) {
    return date('h:i A', strtotime($time));
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateNSBMEmail($email) {
    return validateEmail($email) && str_ends_with($email, '@nsbm.ac.lk');
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Error handling
function setError($message) {
    $_SESSION['error'] = $message;
}

function setSuccess($message) {
    $_SESSION['success'] = $message;
}

function getError() {
    if (isset($_SESSION['error'])) {
        $error = $_SESSION['error'];
        unset($_SESSION['error']);
        return $error;
    }
    return null;
}

function getSuccess() {
    if (isset($_SESSION['success'])) {
        $success = $_SESSION['success'];
        unset($_SESSION['success']);
        return $success;
    }
    return null;
}

// File upload helper
function uploadImage($file, $folder = UPLOAD_DIR) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        setError('File size too large. Maximum 5MB allowed.');
        return false;
    }
    
    // Check file type
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, ALLOWED_IMAGE_TYPES)) {
        setError('Invalid file type. Only JPG, JPEG, PNG, GIF allowed.');
        return false;
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $fileExtension;
    $targetPath = $folder . $filename;
    
    // Create directory if it doesn't exist
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $filename;
    }
    
    return false;
}
?>
<?php
// Add food item functionality for shop owners
require_once '../../config/database.php';
require_once '../../config/config.php';
require_once '../../config/session.php';

// Require shop owner login
requireShopOwnerLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setError('Invalid request method');
    redirect('../home.php');
    exit();
}

$current_user = getCurrentUser();

// Get form data
$food_name = sanitizeInput($_POST['food_name'] ?? '');
$description = sanitizeInput($_POST['description'] ?? '');
$price = (float)($_POST['price'] ?? 0);
$category = sanitizeInput($_POST['category'] ?? '');

// Validation
$errors = [];

if (empty($food_name)) {
    $errors[] = 'Food name is required';
}

if ($price <= 0) {
    $errors[] = 'Price must be greater than 0';
}

if (empty($category)) {
    $errors[] = 'Food category is required';
}

if (!in_array($category, $food_categories)) {
    $errors[] = 'Invalid food category selected';
}

// Check if food name already exists for this shop
$existing_food = fetchOne("SELECT id FROM food_items WHERE shop_id = ? AND food_name = ?", [$current_user['shop_id'], $food_name]);
if ($existing_food) {
    $errors[] = 'A food item with this name already exists in your shop';
}

if (!empty($errors)) {
    setError(implode('. ', $errors));
    redirect('../home.php');
    exit();
}

try {
    // Handle image upload
    $image_path = 'default_food.jpg';
    if (isset($_FILES['food_image']) && $_FILES['food_image']['error'] === UPLOAD_ERR_OK) {
        $uploaded_image = uploadImage($_FILES['food_image'], '../../assets/uploads/food_images/');
        if ($uploaded_image) {
            $image_path = $uploaded_image;
        } else {
            // If upload fails, we still proceed but with default image
            setError('Food item added but image upload failed');
        }
    }
    
    // Insert food item
    $sql = "INSERT INTO food_items (shop_id, food_name, description, price, category, image_path) VALUES (?, ?, ?, ?, ?, ?)";
    $params = [
        $current_user['shop_id'],
        $food_name,
        $description,
        $price,
        $category,
        $image_path
    ];
    
    if (executeQuery($sql, $params)) {
        setSuccess('Food item "' . $food_name . '" added successfully!');
        
        // Log the activity
        error_log("Food item added - Shop: {$current_user['shop_id']}, Item: {$food_name}, Price: {$price}");
    } else {
        setError('Failed to add food item. Please try again.');
    }
    
} catch (Exception $e) {
    error_log("Add food item error: " . $e->getMessage());
    setError('An error occurred while adding the food item. Please try again.');
}

redirect('../home.php');

/**
 * Upload and process food image
 */
function uploadFoodImage($file) {
    $upload_dir = '../../assets/uploads/food_images/';
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            return false;
        }
    }
    
    // Validate file
    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, ALLOWED_IMAGE_TYPES)) {
        return false;
    }
    
    // Validate image
    $image_info = getimagesize($file['tmp_name']);
    if ($image_info === false) {
        return false;
    }
    
    // Generate unique filename
    $filename = 'food_' . uniqid() . '_' . time() . '.' . $file_extension;
    $target_path = $upload_dir . $filename;
    
    // Move and resize image
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Resize image to standard dimensions
        resizeImageFile($target_path, 800, 600);
        return $filename;
    }
    
    return false;
}

/**
 * Resize image file
 */
function resizeImageFile($file_path, $max_width = 800, $max_height = 600) {
    $image_info = getimagesize($file_path);
    if (!$image_info) return false;
    
    $width = $image_info[0];
    $height = $image_info[1];
    $type = $image_info[2];
    
    // Calculate new dimensions
    $ratio = min($max_width / $width, $max_height / $height);
    
    if ($ratio >= 1) return true; // Image is already small enough
    
    $new_width = (int)($width * $ratio);
    $new_height = (int)($height * $ratio);
    
    // Create image resource
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($file_path);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($file_path);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($file_path);
            break;
        default:
            return false;
    }
    
    if (!$source) return false;
    
    // Create new image
    $new_image = imagecreatetruecolor($new_width, $new_height);
    
    // Preserve transparency
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagecolortransparent($new_image, imagecolorallocatealpha($new_image, 0, 0, 0, 127));
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
    }
    
    // Resize
    imagecopyresampled($new_image, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Save
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($new_image, $file_path, 85);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($new_image, $file_path, 8);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($new_image, $file_path);
            break;
    }
    
    // Clean up
    imagedestroy($source);
    imagedestroy($new_image);
    
    return $result;
}
?>
<?php
// Image upload functionality for food items
require_once '../../config/database.php';
require_once '../../config/config.php';
require_once '../../config/session.php';

header('Content-Type: application/json');

// Require shop owner login
if (!isShopOwner()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit();
}

$file = $_FILES['image'];
$upload_dir = '../../assets/uploads/food_images/';

// Create upload directory if it doesn't exist
if (!file_exists($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit();
    }
}

// Validate file size (5MB max)
if ($file['size'] > MAX_FILE_SIZE) {
    echo json_encode(['success' => false, 'message' => 'File size too large. Maximum 5MB allowed.']);
    exit();
}

// Get file extension
$file_info = pathinfo($file['name']);
$file_extension = strtolower($file_info['extension']);

// Validate file type
if (!in_array($file_extension, ALLOWED_IMAGE_TYPES)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG, GIF allowed.']);
    exit();
}

// Validate file is actually an image
$image_info = getimagesize($file['tmp_name']);
if ($image_info === false) {
    echo json_encode(['success' => false, 'message' => 'File is not a valid image.']);
    exit();
}

// Generate unique filename
$filename = uniqid('food_') . '_' . time() . '.' . $file_extension;
$target_path = $upload_dir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $target_path)) {
    // Resize image if needed (optional)
    resizeImage($target_path, 800, 600);
    
    echo json_encode([
        'success' => true,
        'message' => 'Image uploaded successfully',
        'filename' => $filename,
        'path' => 'assets/uploads/food_images/' . $filename
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
}

/**
 * Resize image while maintaining aspect ratio
 */
function resizeImage($source, $max_width = 800, $max_height = 600) {
    $image_info = getimagesize($source);
    $width = $image_info[0];
    $height = $image_info[1];
    $type = $image_info[2];
    
    // Calculate new dimensions
    $ratio = min($max_width / $width, $max_height / $height);
    
    // If image is already smaller, don't resize
    if ($ratio >= 1) {
        return true;
    }
    
    $new_width = (int)($width * $ratio);
    $new_height = (int)($height * $ratio);
    
    // Create image resource based on type
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }
    
    if (!$source_image) {
        return false;
    }
    
    // Create new image
    $new_image = imagecreatetruecolor($new_width, $new_height);
    
    // Preserve transparency for PNG and GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagecolortransparent($new_image, imagecolorallocatealpha($new_image, 0, 0, 0, 127));
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
    }
    
    // Resize image
    imagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    // Save resized image
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($new_image, $source, 85);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($new_image, $source, 8);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($new_image, $source);
            break;
    }
    
    // Clean up memory
    imagedestroy($source_image);
    imagedestroy($new_image);
    
    return $result;
}
?>
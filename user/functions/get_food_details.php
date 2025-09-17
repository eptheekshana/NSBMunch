<?php
// Get food details for modal display
require_once '../../config/database.php';
require_once '../../config/config.php';
require_once '../../config/session.php';

header('Content-Type: application/json');

// Require user login
if (!isUser()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Food ID is required']);
    exit();
}

$food_id = (int)$_GET['id'];

// Get food details with shop information
$sql = "SELECT f.*, s.shop_name, s.location 
        FROM food_items f 
        JOIN shop_owners s ON f.shop_id = s.shop_id 
        WHERE f.id = ? AND s.status = 'approved'";

$food = fetchOne($sql, [$food_id]);

if ($food) {
    echo json_encode([
        'success' => true,
        'data' => $food
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Food item not found'
    ]);
}
?>
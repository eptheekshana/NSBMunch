<?php
// Add item to cart (session-based cart system)
require_once '../../config/database.php';
require_once '../../config/config.php';
require_once '../../config/session.php';

header('Content-Type: application/json');

// Require user login
if (!isUser()) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$food_id = (int)($_POST['food_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 1);

if ($food_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid food ID or quantity']);
    exit();
}

// Get food details
$sql = "SELECT f.*, s.shop_name, s.location, s.shop_id 
        FROM food_items f 
        JOIN shop_owners s ON f.shop_id = s.shop_id 
        WHERE f.id = ? AND s.status = 'approved'";

$food = fetchOne($sql, [$food_id]);

if (!$food) {
    echo json_encode(['success' => false, 'message' => 'Food item not found']);
    exit();
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if item already exists in cart
$found = false;
foreach ($_SESSION['cart'] as &$cart_item) {
    if ($cart_item['food_id'] == $food_id) {
        $cart_item['quantity'] += $quantity;
        $cart_item['total_price'] = $cart_item['price'] * $cart_item['quantity'];
        $found = true;
        break;
    }
}

// Add new item to cart if not found
if (!$found) {
    $_SESSION['cart'][] = [
        'food_id' => $food_id,
        'food_name' => $food['food_name'],
        'price' => $food['price'],
        'quantity' => $quantity,
        'total_price' => $food['price'] * $quantity,
        'shop_id' => $food['shop_id'],
        'shop_name' => $food['shop_name'],
        'location' => $food['location'],
        'category' => $food['category']
    ];
}

echo json_encode([
    'success' => true,
    'message' => 'Item added to cart successfully',
    'cart_count' => count($_SESSION['cart'])
]);
?>
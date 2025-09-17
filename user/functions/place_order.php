<?php
// Place order functionality
require_once '../../config/database.php';
require_once '../../config/config.php';
require_once '../../config/session.php';

// Require user login
requireUserLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setError('Invalid request method');
    redirect('../cart.php');
    exit();
}

$current_user = getCurrentUser();
$ordering_time = $_POST['ordering_time'] ?? '';
$payment_method = $_POST['payment_method'] ?? 'pay_at_canteen';

// Validation
if (empty($ordering_time)) {
    setError('Please select an ordering time');
    redirect('../cart.php');
    exit();
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    setError('Your cart is empty');
    redirect('../cart.php');
    exit();
}

$cart_items = $_SESSION['cart'];

try {
    // Start transaction
    $pdo = getConnection();
    $pdo->beginTransaction();
    
    $success_count = 0;
    $error_messages = [];
    
    // Process each cart item as separate order
    foreach ($cart_items as $item) {
        // Generate unique order ID
        $order_id = generateOrderId();
        
        // Check if order ID already exists
        while (fetchOne("SELECT id FROM orders WHERE order_id = ?", [$order_id])) {
            $order_id = generateOrderId();
        }
        
        // Insert order
        $sql = "INSERT INTO orders (
                    order_id, user_campus_id, shop_id, food_id, food_name, 
                    quantity, total_price, ordering_time, payment_method,
                    user_status, shop_status, order_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending', CURRENT_DATE)";
        
        $params = [
            $order_id,
            $current_user['campus_id'],
            $item['shop_id'],
            $item['food_id'],
            $item['food_name'],
            $item['quantity'],
            $item['total_price'],
            $ordering_time,
            $payment_method
        ];
        
        if (executeQuery($sql, $params)) {
            $success_count++;
        } else {
            $error_messages[] = "Failed to place order for " . $item['food_name'];
        }
    }
    
    if ($success_count > 0) {
        // Commit transaction
        $pdo->commit();
        
        // Clear cart
        $_SESSION['cart'] = [];
        
        if ($success_count === count($cart_items)) {
            setSuccess('All orders placed successfully! You can track your orders in the Orders section.');
        } else {
            setSuccess($success_count . ' out of ' . count($cart_items) . ' orders placed successfully.');
            if (!empty($error_messages)) {
                setError(implode(', ', $error_messages));
            }
        }
        
        redirect('../orders.php');
    } else {
        // Rollback transaction
        $pdo->rollback();
        setError('Failed to place any orders. Please try again.');
        redirect('../cart.php');
    }
    
} catch (Exception $e) {
    // Rollback transaction
    if (isset($pdo)) {
        $pdo->rollback();
    }
    
    error_log("Order placement error: " . $e->getMessage());
    setError('An error occurred while placing your order. Please try again.');
    redirect('../cart.php');
}
?>
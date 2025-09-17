<?php
// User cart page
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';

// Require user login
requireUserLogin();

$page_title = "Shopping Cart";
$current_user = getCurrentUser();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart_items = $_SESSION['cart'];
$cart_total = 0;

// Calculate total
foreach ($cart_items as $item) {
    $cart_total += $item['total_price'];
}

// Handle form submission for updating quantities or removing items
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_quantity':
                $index = (int)$_POST['index'];
                $quantity = (int)$_POST['quantity'];
                
                if (isset($_SESSION['cart'][$index]) && $quantity > 0) {
                    $_SESSION['cart'][$index]['quantity'] = $quantity;
                    $_SESSION['cart'][$index]['total_price'] = $_SESSION['cart'][$index]['price'] * $quantity;
                    setSuccess('Cart updated successfully');
                }
                redirect('cart.php');
                break;
                
            case 'remove_item':
                $index = (int)$_POST['index'];
                if (isset($_SESSION['cart'][$index])) {
                    unset($_SESSION['cart'][$index]);
                    $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
                    setSuccess('Item removed from cart');
                }
                redirect('cart.php');
                break;
                
            case 'clear_cart':
                $_SESSION['cart'] = [];
                setSuccess('Cart cleared');
                redirect('cart.php');
                break;
        }
    }
}
?>

<?php require_once '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 px-0">
            <div class="sidebar">
                <div class="p-3 text-white">
                    <h5><i class="fas fa-user me-2"></i><?php echo $current_user['username']; ?></h5>
                    <small><?php echo ucfirst($current_user['user_category']); ?> - <?php echo $current_user['campus_id']; ?></small>
                </div>
                <nav class="nav flex-column">
                    <a href="home.php" class="nav-link">
                        <i class="fas fa-home me-2"></i>Home
                    </a>
                    <a href="cart.php" class="nav-link active">
                        <i class="fas fa-shopping-cart me-2"></i>Cart
                        <?php if (count($cart_items) > 0): ?>
                            <span class="badge bg-warning ms-auto"><?php echo count($cart_items); ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="orders.php" class="nav-link">
                        <i class="fas fa-list-alt me-2"></i>Orders
                    </a>
                    <a href="profile.php" class="nav-link">
                        <i class="fas fa-user-cog me-2"></i>Profile
                    </a>
                    <a href="../auth/logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="p-4">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-shopping-cart me-2"></i>Shopping Cart</h2>
                        <p class="text-muted mb-0">Review your selected items before placing order</p>
                    </div>
                    <div>
                        <a href="home.php" class="btn btn-primary">
                            <i class="fas fa-utensils me-2"></i>Find Food
                        </a>
                    </div>
                </div>
                
                <?php if (empty($cart_items)): ?>
                    <!-- Empty Cart -->
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                            <h4>Your cart is empty</h4>
                            <p class="text-muted mb-4">Add some delicious food items to your cart to get started!</p>
                            <a href="home.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-utensils me-2"></i>Browse Food Items
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Cart Items -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Order Food List</h5>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to clear all items from cart?');">
                                <input type="hidden" name="action" value="clear_cart">
                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                    <i class="fas fa-trash me-1"></i>Clear Cart
                                </button>
                            </form>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Food Name</th>
                                            <th>Price</th>
                                            <th>Shop Name</th>
                                            <th>Quantity</th>
                                            <th>Total Price</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cart_items as $index => $item): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($item['food_name']); ?></strong>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($item['category']); ?></small>
                                                </td>
                                                <td class="price" data-price="<?php echo $item['price']; ?>">
                                                    <?php echo formatPrice($item['price']); ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($item['shop_name']); ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($item['location']); ?></small>
                                                </td>
                                                <td>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="update_quantity">
                                                        <input type="hidden" name="index" value="<?php echo $index; ?>">
                                                        <div class="input-group" style="max-width: 120px;">
                                                            <input type="number" class="form-control quantity-input" 
                                                                   name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                                   min="1" max="10">
                                                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                                                <i class="fas fa-sync"></i>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </td>
                                                <td class="total-price">
                                                    <?php echo formatPrice($item['total_price']); ?>
                                                </td>
                                                <td>
                                                    <form method="POST" style="display: inline;" 
                                                          onsubmit="return confirm('Remove this item from cart?');">
                                                        <input type="hidden" name="action" value="remove_item">
                                                        <input type="hidden" name="index" value="<?php echo $index; ?>">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4">Grand Total:</th>
                                            <th id="grand-total"><?php echo formatPrice($cart_total); ?></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Details Form -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">Order Details</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="functions/place_order.php">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ordering_time" class="form-label">Ordering Time <span class="text-danger">*</span></label>
                                            <input type="time" class="form-control" id="ordering_time" name="ordering_time" required>
                                            <div class="form-text">Select your preferred pickup time</div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="payment_method" class="form-label">Payment Method</label>
                                            <select class="form-select" id="payment_method" name="payment_method">
                                                <option value="pay_at_canteen">Pay at Canteen</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Order Summary</label>
                                    <div class="bg-light p-3 rounded">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Total Items:</strong> <?php echo count($cart_items); ?>
                                            </div>
                                            <div class="col-md-6 text-md-end">
                                                <strong>Total Amount:</strong> <?php echo formatPrice($cart_total); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="home.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                                    </a>
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="fas fa-check me-2"></i>Place Order
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Set default ordering time to 30 minutes from now
$(document).ready(function() {
    var now = new Date();
    now.setMinutes(now.getMinutes() + 30); // Add 30 minutes
    var hours = now.getHours().toString().padStart(2, '0');
    var minutes = now.getMinutes().toString().padStart(2, '0');
    $('#ordering_time').val(hours + ':' + minutes);
});
</script>

<?php require_once '../includes/footer.php'; ?>
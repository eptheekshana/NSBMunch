<?php
// User orders page
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';

// Require user login
requireUserLogin();

$page_title = "My Orders";
$current_user = getCurrentUser();

// Handle order confirmation/cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $order_id = $_POST['order_id'] ?? '';
    $action = $_POST['action'];
    
    if ($action === 'confirm' || $action === 'cancel') {
        $new_status = $action === 'confirm' ? 'confirmed' : 'cancelled';
        $sql = "UPDATE orders SET user_status = ? WHERE order_id = ? AND user_campus_id = ?";
        $params = [$new_status, $order_id, $current_user['campus_id']];
        
        if (executeQuery($sql, $params)) {
            setSuccess('Order ' . $action . 'ed successfully');
        } else {
            setError('Failed to ' . $action . ' order');
        }
    }
    
    redirect('orders.php');
}

// Get current orders (not completed)
$current_orders_sql = "SELECT o.*, s.shop_name, s.location 
                       FROM orders o 
                       JOIN shop_owners s ON o.shop_id = s.shop_id 
                       WHERE o.user_campus_id = ? AND o.shop_status NOT IN ('pickup', 'rejected') 
                       ORDER BY o.created_at DESC";
$current_orders = fetchAll($current_orders_sql, [$current_user['campus_id']]);

// Get order history (completed orders)
$history_orders_sql = "SELECT o.*, s.shop_name, s.location 
                       FROM orders o 
                       JOIN shop_owners s ON o.shop_id = s.shop_id 
                       WHERE o.user_campus_id = ? AND o.shop_status IN ('pickup', 'rejected') 
                       ORDER BY o.created_at DESC 
                       LIMIT 50";
$history_orders = fetchAll($history_orders_sql, [$current_user['campus_id']]);
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
                    <a href="cart.php" class="nav-link">
                        <i class="fas fa-shopping-cart me-2"></i>Cart
                    </a>
                    <a href="orders.php" class="nav-link active">
                        <i class="fas fa-list-alt me-2"></i>Orders
                        <?php if (count($current_orders) > 0): ?>
                            <span class="badge bg-primary ms-auto"><?php echo count($current_orders); ?></span>
                        <?php endif; ?>
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
                        <h2><i class="fas fa-list-alt me-2"></i>My Orders</h2>
                        <p class="text-muted mb-0">Track your food orders and order history</p>
                    </div>
                    <div>
                        <a href="home.php" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
                
                <!-- Current Orders -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>User Order List
                            <?php if (count($current_orders) > 0): ?>
                                <span class="badge bg-primary ms-2"><?php echo count($current_orders); ?> Active</span>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($current_orders)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-shopping-bag fa-4x text-muted mb-4"></i>
                                <h5>No active orders</h5>
                                <p class="text-muted mb-4">You don't have any active orders at the moment.</p>
                                <a href="home.php" class="btn btn-primary">
                                    <i class="fas fa-utensils me-2"></i>Order Food
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Food Name</th>
                                            <th>Order ID</th>
                                            <th>Shop Location</th>
                                            <th>Shop Name</th>
                                            <th>Total Price</th>
                                            <th>Quantity</th>
                                            <th>Date</th>
                                            <th>User Status</th>
                                            <th>Ordering Time</th>
                                            <th>Payment Method</th>
                                            <th>Shop Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($current_orders as $order): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($order['food_name']); ?></strong></td>
                                                <td><?php echo $order['order_id']; ?></td>
                                                <td><?php echo htmlspecialchars($order['location']); ?></td>
                                                <td><?php echo htmlspecialchars($order['shop_name']); ?></td>
                                                <td><?php echo formatPrice($order['total_price']); ?></td>
                                                <td><?php echo $order['quantity']; ?></td>
                                                <td><?php echo formatDate($order['order_date']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $order['user_status'] === 'confirmed' ? 'success' : ($order['user_status'] === 'cancelled' ? 'danger' : 'warning'); ?>">
                                                        <?php echo ucfirst($order['user_status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatTime($order['ordering_time']); ?></td>
                                                <td><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></td>
                                                <td>
                                                    <?php
                                                    $shop_status_text = $order['shop_status'];
                                                    $badge_class = 'warning';
                                                    
                                                    switch($order['shop_status']) {
                                                        case 'confirmed':
                                                            $shop_status_text = 'Order Confirmed';
                                                            $badge_class = 'success';
                                                            break;
                                                        case 'rejected':
                                                            $shop_status_text = 'Order Rejected';
                                                            $badge_class = 'danger';
                                                            break;
                                                        case 'ready':
                                                            $shop_status_text = 'Order Ready';
                                                            $badge_class = 'info';
                                                            break;
                                                        case 'pickup':
                                                            $shop_status_text = 'Order Pickup';
                                                            $badge_class = 'primary';
                                                            break;
                                                        default:
                                                            $shop_status_text = 'Pending';
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?php echo $badge_class; ?>">
                                                        <?php echo $shop_status_text; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($order['user_status'] === 'pending'): ?>
                                                        <div class="btn-group" role="group">
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="action" value="confirm">
                                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                                <button type="submit" class="btn btn-success btn-sm" 
                                                                        onclick="return confirm('Confirm this order?')">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            </form>
                                                            
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="action" value="cancel">
                                                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                                        onclick="return confirm('Cancel this order?')">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    <?php else: ?>
                                                        <span class="text-muted">
                                                            <?php echo $order['user_status'] === 'confirmed' ? 'Confirmed' : 'Cancelled'; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Order History -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>User Order History
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($history_orders)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <h5>No order history</h5>
                                <p class="text-muted">Your completed orders will appear here.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>User Status</th>
                                            <th>Order ID</th>
                                            <th>Shop Name</th>
                                            <th>Food Name</th>
                                            <th>Total Price</th>
                                            <th>Quantity</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($history_orders as $order): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-<?php echo $order['shop_status'] === 'pickup' ? 'success' : 'danger'; ?>">
                                                        <?php echo $order['shop_status'] === 'pickup' ? 'Order Pickup' : 'Order Rejected'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $order['order_id']; ?></td>
                                                <td><?php echo htmlspecialchars($order['shop_name']); ?></td>
                                                <td><?php echo htmlspecialchars($order['food_name']); ?></td>
                                                <td><?php echo formatPrice($order['total_price']); ?></td>
                                                <td><?php echo $order['quantity']; ?></td>
                                                <td><?php echo formatDate($order['order_date']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto refresh orders every 15 seconds to get status updates
setInterval(function() {
    if (!document.querySelector('.modal.show')) {
        location.reload();
    }
}, 15000);

// Enhanced confirmation messages
function confirmOrder(orderId, foodName) {
    return confirm('Confirm your order for "' + foodName + '"?\n\nOnce confirmed, you can only cancel if the shop hasn\'t started preparing your order.');
}

function cancelOrder(orderId, foodName, userStatus, shopStatus) {
    let message = 'Cancel your order for "' + foodName + '"?';
    
    if (userStatus === 'confirmed') {
        if (shopStatus === 'ready') {
            alert('Cannot cancel - your order is ready for pickup!');
            return false;
        } else if (shopStatus === 'confirmed') {
            message += '\n\nWarning: The shop may have already started preparing your order.';
        }
    }
    
    return confirm(message);
}

// Add click handlers to forms
document.addEventListener('DOMContentLoaded', function() {
    // Add handlers for confirm buttons
    document.querySelectorAll('button[onclick*="confirm"]').forEach(function(button) {
        const form = button.closest('form');
        const foodName = button.closest('tr').querySelector('td:first-child strong').textContent;
        
        form.addEventListener('submit', function(e) {
            if (!confirmOrder('', foodName)) {
                e.preventDefault();
            }
        });
    });
    
    // Add handlers for cancel buttons  
    document.querySelectorAll('button[onclick*="Cancel"]').forEach(function(button) {
        const form = button.closest('form');
        const row = button.closest('tr');
        const foodName = row.querySelector('td:first-child strong').textContent;
        const userStatus = row.querySelector('td:nth-child(8) .badge').textContent.toLowerCase();
        const shopStatus = row.querySelector('td:nth-child(11) .badge').textContent.toLowerCase();
        
        form.addEventListener('submit', function(e) {
            if (!cancelOrder('', foodName, userStatus, shopStatus)) {
                e.preventDefault();
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
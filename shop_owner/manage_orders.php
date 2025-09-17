<?php
// Shop owner order management
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';

// Require shop owner login
requireShopOwnerLogin();

$page_title = "Manage Orders";
$current_user = getCurrentUser();

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order_status') {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['shop_status'];
    
    $sql = "UPDATE orders SET shop_status = ? WHERE order_id = ? AND shop_id = ?";
    if (executeQuery($sql, [$new_status, $order_id, $current_user['shop_id']])) {
        setSuccess('Order status updated successfully');
        
        // If status is pickup or rejected, move to history
        if ($new_status === 'pickup' || $new_status === 'rejected') {
            // Order will automatically appear in history due to status change
        }
    } else {
        setError('Failed to update order status');
    }
    
    redirect('manage_orders.php');
}

// Get current orders (not pickup or rejected)
$current_orders_sql = "SELECT o.*, u.username, u.campus_id 
                       FROM orders o 
                       JOIN users u ON o.user_campus_id = u.campus_id 
                       WHERE o.shop_id = ? AND o.shop_status NOT IN ('pickup', 'rejected')
                       ORDER BY o.created_at DESC";
$current_orders = fetchAll($current_orders_sql, [$current_user['shop_id']]);

// Get order history (pickup or rejected)
$history_orders_sql = "SELECT o.*, u.username, u.campus_id 
                       FROM orders o 
                       JOIN users u ON o.user_campus_id = u.campus_id 
                       WHERE o.shop_id = ? AND o.shop_status IN ('pickup', 'rejected')
                       ORDER BY o.created_at DESC LIMIT 50";
$history_orders = fetchAll($history_orders_sql, [$current_user['shop_id']]);

// Get statistics
$stats = [
    'pending' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE shop_id = ? AND shop_status = 'pending'", [$current_user['shop_id']])['count'],
    'confirmed' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE shop_id = ? AND shop_status = 'confirmed'", [$current_user['shop_id']])['count'],
    'ready' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE shop_id = ? AND shop_status = 'ready'", [$current_user['shop_id']])['count'],
    'today_total' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE shop_id = ? AND order_date = CURRENT_DATE", [$current_user['shop_id']])['count']
];
?>

<?php require_once '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 px-0">
            <div class="sidebar">
                <div class="p-3 text-white">
                    <h5><i class="fas fa-store me-2"></i><?php echo $current_user['shop_name']; ?></h5>
                    <small><?php echo $current_user['owner_name']; ?></small><br>
                    <small><?php echo $current_user['shop_id']; ?></small>
                </div>
                <nav class="nav flex-column">
                    <a href="home.php" class="nav-link">
                        <i class="fas fa-home me-2"></i>Dashboard
                    </a>
                    <a href="manage_orders.php" class="nav-link active">
                        <i class="fas fa-list-alt me-2"></i>Manage Orders
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
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-list-alt me-2"></i>Manage Orders</h2>
                        <p class="text-muted mb-0">Process customer orders and track order history</p>
                    </div>
                    <a href="home.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Shop Home
                    </a>
                </div>
                
                <!-- Order Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4><?php echo $stats['pending']; ?></h4>
                                        <p class="mb-0">Pending</p>
                                    </div>
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4><?php echo $stats['confirmed']; ?></h4>
                                        <p class="mb-0">Confirmed</p>
                                    </div>
                                    <i class="fas fa-check fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4><?php echo $stats['ready']; ?></h4>
                                        <p class="mb-0">Ready</p>
                                    </div>
                                    <i class="fas fa-utensils fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4><?php echo $stats['today_total']; ?></h4>
                                        <p class="mb-0">Today Total</p>
                                    </div>
                                    <i class="fas fa-calendar-day fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Current Orders -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-shopping-bag me-2"></i>Users Ordering Table
                            <span class="badge bg-primary ms-2"><?php echo count($current_orders); ?> Orders</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($current_orders)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-shopping-bag fa-4x text-muted mb-4"></i>
                                <h5>No current orders</h5>
                                <p class="text-muted">New orders from customers will appear here.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>User Campus ID</th>
                                            <th>Food Name</th>
                                            <th>Quantity</th>
                                            <th>Total Price</th>
                                            <th>Ordering Time</th>
                                            <th>Date</th>
                                            <th>User Status</th>
                                            <th>Shop Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($current_orders as $order): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($order['order_id']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($order['user_campus_id']); ?></td>
                                                <td><?php echo htmlspecialchars($order['food_name']); ?></td>
                                                <td><?php echo $order['quantity']; ?></td>
                                                <td><?php echo formatPrice($order['total_price']); ?></td>
                                                <td><?php echo formatTime($order['ordering_time']); ?></td>
                                                <td><?php echo formatDate($order['order_date']); ?></td>
                                                <td>
                                                    <?php if ($order['user_status'] === 'confirmed'): ?>
                                                        <span class="badge bg-success">Confirmed</span>
                                                    <?php elseif ($order['user_status'] === 'cancelled'): ?>
                                                        <span class="badge bg-danger">Cancelled</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_class = match($order['shop_status']) {
                                                        'confirmed' => 'success',
                                                        'rejected' => 'danger',
                                                        'ready' => 'info',
                                                        'pickup' => 'dark',
                                                        default => 'warning'
                                                    };
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class; ?>">
                                                        <?php echo $shop_order_statuses[$order['shop_status']]; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($order['user_status'] === 'confirmed'): ?>
                                                        <form method="POST" style="display: inline-block;">
                                                            <input type="hidden" name="action" value="update_order_status">
                                                            <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                            <select name="shop_status" class="form-select form-select-sm" style="width: auto; display: inline-block;">
                                                                <?php foreach (['confirmed', 'rejected', 'ready', 'pickup'] as $status): ?>
                                                                    <option value="<?php echo $status; ?>" 
                                                                            <?php echo ($order['shop_status'] === $status) ? 'selected' : ''; ?>>
                                                                        <?php echo $shop_order_statuses[$status]; ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <button type="submit" class="btn btn-primary btn-sm ms-1">
                                                                <i class="fas fa-save"></i> Update
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted small">Waiting for user confirmation</span>
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
                            <i class="fas fa-history me-2"></i>Orders Reports History
                            <span class="badge bg-secondary ms-2"><?php echo count($history_orders); ?> Records</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($history_orders)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <h5>No order history</h5>
                                <p class="text-muted">Completed and rejected orders will appear here.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>User Campus ID</th>
                                            <th>Food Name</th>
                                            <th>Quantity</th>
                                            <th>Total Price</th>
                                            <th>Ordering Time</th>
                                            <th>Date</th>
                                            <th>Shop Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($history_orders as $order): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($order['order_id']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($order['user_campus_id']); ?></td>
                                                <td><?php echo htmlspecialchars($order['food_name']); ?></td>
                                                <td><?php echo $order['quantity']; ?></td>
                                                <td><?php echo formatPrice($order['total_price']); ?></td>
                                                <td><?php echo formatTime($order['ordering_time']); ?></td>
                                                <td><?php echo formatDate($order['order_date']); ?></td>
                                                <td>
                                                    <?php if ($order['shop_status'] === 'pickup'): ?>
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check me-1"></i>Order Pickup
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times me-1"></i>Order Rejected
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
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
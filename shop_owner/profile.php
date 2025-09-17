<?php
// Shop owner profile page
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';

// Require shop owner login
requireShopOwnerLogin();

$page_title = "Shop Owner Profile";
$current_user = getCurrentUser();

// Get full shop owner details from database
$shop_details = fetchOne("SELECT * FROM shop_owners WHERE shop_id = ?", [$current_user['shop_id']]);

if (!$shop_details) {
    setError('Shop details not found');
    redirect('home.php');
    exit();
}
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
                    <a href="manage_orders.php" class="nav-link">
                        <i class="fas fa-list-alt me-2"></i>Manage Orders
                    </a>
                    <a href="profile.php" class="nav-link active">
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
                        <h2><i class="fas fa-user-cog me-2"></i>Shop Owner Profile</h2>
                        <p class="text-muted mb-0">View your shop information and statistics</p>
                    </div>
                    <div>
                        <a href="home.php" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
                
                <!-- Profile Information -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-id-card me-2"></i>Shop Owner Profile Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <td><strong>Shop ID:</strong></td>
                                                <td><?php echo htmlspecialchars($shop_details['shop_id']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Owner Name:</strong></td>
                                                <td><?php echo htmlspecialchars($shop_details['owner_name']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Shop Name:</strong></td>
                                                <td><?php echo htmlspecialchars($shop_details['shop_name']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email Address:</strong></td>
                                                <td><?php echo htmlspecialchars($shop_details['email']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Location:</strong></td>
                                                <td><?php echo htmlspecialchars($shop_details['location']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Registration Date:</strong></td>
                                                <td><?php echo date('F j, Y', strtotime($shop_details['created_at'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Account Status:</strong></td>
                                                <td>
                                                    <?php
                                                    $status_class = 'success';
                                                    $status_icon = 'check-circle';
                                                    $status_text = 'Approved';
                                                    
                                                    if ($shop_details['status'] === 'pending') {
                                                        $status_class = 'warning';
                                                        $status_icon = 'clock';
                                                        $status_text = 'Pending Approval';
                                                    } elseif ($shop_details['status'] === 'rejected') {
                                                        $status_class = 'danger';
                                                        $status_icon = 'times-circle';
                                                        $status_text = 'Rejected';
                                                    }
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_class; ?>">
                                                        <i class="fas fa-<?php echo $status_icon; ?> me-1"></i><?php echo $status_text; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="text-center">
                                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                         style="width: 120px; height: 120px;">
                                        <i class="fas fa-store fa-4x text-muted"></i>
                                    </div>
                                    <h5><?php echo htmlspecialchars($shop_details['shop_name']); ?></h5>
                                    <p class="text-muted"><?php echo htmlspecialchars($shop_details['shop_id']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Shop Statistics -->
                        <hr class="my-4">
                        
                        <?php
                        // Get shop statistics
                        $stats = [
                            'total_items' => fetchOne("SELECT COUNT(*) as count FROM food_items WHERE shop_id = ?", [$shop_details['shop_id']])['count'],
                            'total_orders' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE shop_id = ?", [$shop_details['shop_id']])['count'],
                            'completed_orders' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE shop_id = ? AND shop_status = 'pickup'", [$shop_details['shop_id']])['count'],
                            'pending_orders' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE shop_id = ? AND shop_status = 'pending'", [$shop_details['shop_id']])['count'],
                            'total_revenue' => fetchOne("SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE shop_id = ? AND shop_status = 'pickup'", [$shop_details['shop_id']])['total'],
                            'today_orders' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE shop_id = ? AND order_date = CURRENT_DATE", [$shop_details['shop_id']])['count'],
                            'most_popular_item' => fetchOne("SELECT food_name, COUNT(*) as order_count FROM orders WHERE shop_id = ? GROUP BY food_id ORDER BY order_count DESC LIMIT 1", [$shop_details['shop_id']])
                        ];
                        ?>
                        
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <div class="bg-primary text-white p-3 rounded">
                                    <h4><?php echo $stats['total_items']; ?></h4>
                                    <p class="mb-0">Food Items</p>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="bg-info text-white p-3 rounded">
                                    <h4><?php echo $stats['total_orders']; ?></h4>
                                    <p class="mb-0">Total Orders</p>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="bg-success text-white p-3 rounded">
                                    <h4><?php echo $stats['completed_orders']; ?></h4>
                                    <p class="mb-0">Completed</p>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="bg-warning text-white p-3 rounded">
                                    <h4><?php echo $stats['pending_orders']; ?></h4>
                                    <p class="mb-0">Pending</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row text-center mt-3">
                            <div class="col-md-4 mb-3">
                                <div class="bg-dark text-white p-3 rounded">
                                    <h4><?php echo formatPrice($stats['total_revenue']); ?></h4>
                                    <p class="mb-0">Total Revenue</p>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="bg-secondary text-white p-3 rounded">
                                    <h4><?php echo $stats['today_orders']; ?></h4>
                                    <p class="mb-0">Today's Orders</p>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="bg-danger text-white p-3 rounded">
                                    <h6><?php echo $stats['most_popular_item']['food_name'] ?? 'None'; ?></h6>
                                    <p class="mb-0">Popular Item</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="home.php" class="btn btn-outline-primary btn-lg w-100">
                                    <i class="fas fa-plus fa-2x mb-2"></i><br>
                                    Add Food Item
                                </a>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <a href="manage_orders.php" class="btn btn-outline-success btn-lg w-100">
                                    <i class="fas fa-list-alt fa-2x mb-2"></i><br>
                                    Manage Orders
                                    <?php if ($stats['pending_orders'] > 0): ?>
                                        <span class="badge bg-warning"><?php echo $stats['pending_orders']; ?></span>
                                    <?php endif; ?>
                                </a>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <a href="home.php" class="btn btn-outline-info btn-lg w-100">
                                    <i class="fas fa-chart-bar fa-2x mb-2"></i><br>
                                    View Dashboard
                                </a>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <a href="../auth/logout.php" class="btn btn-outline-danger btn-lg w-100">
                                    <i class="fas fa-sign-out-alt fa-2x mb-2"></i><br>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Business Information -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Business Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Tips for Success:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Keep your food items updated with accurate descriptions and prices</li>
                                <li>Respond to orders quickly to maintain customer satisfaction</li>
                                <li>Upload attractive food images to increase orders</li>
                                <li>Maintain consistent quality and service</li>
                            </ul>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-clock me-2"></i>Operating Hours</h6>
                                <p class="text-muted">Please ensure you're available during campus hours to process orders effectively.</p>
                            </div>
                            
                            <div class="col-md-6">
                                <h6><i class="fas fa-phone me-2"></i>Contact Support</h6>
                                <p class="text-muted">If you need assistance, contact the NSBMunch support team at <?php echo SITE_EMAIL; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
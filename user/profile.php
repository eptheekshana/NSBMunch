<?php
// User profile page
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';

// Require user login
requireUserLogin();

$page_title = "User Profile";
$current_user = getCurrentUser();

// Get full user details from database
$user_details = fetchOne("SELECT * FROM users WHERE campus_id = ?", [$current_user['campus_id']]);

if (!$user_details) {
    setError('User details not found');
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
                    <a href="orders.php" class="nav-link">
                        <i class="fas fa-list-alt me-2"></i>Orders
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
                        <h2><i class="fas fa-user-cog me-2"></i>User Profile</h2>
                        <p class="text-muted mb-0">View your profile information</p>
                    </div>
                    <div>
                        <a href="home.php" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
                
                <!-- Profile Information -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-id-card me-2"></i>User Profile Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <td><strong>Full Name:</strong></td>
                                                <td><?php echo htmlspecialchars($user_details['username']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Campus ID:</strong></td>
                                                <td><?php echo htmlspecialchars($user_details['campus_id']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email Address:</strong></td>
                                                <td><?php echo htmlspecialchars($user_details['email']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>User Category:</strong></td>
                                                <td>
                                                    <span class="badge bg-primary">
                                                        <?php echo ucfirst($user_details['user_category']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Member Since:</strong></td>
                                                <td><?php echo date('F j, Y', strtotime($user_details['created_at'])); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Account Status:</strong></td>
                                                <td>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i>Active
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
                                        <i class="fas fa-user fa-4x text-muted"></i>
                                    </div>
                                    <h5><?php echo htmlspecialchars($user_details['username']); ?></h5>
                                    <p class="text-muted"><?php echo htmlspecialchars($user_details['campus_id']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Account Statistics -->
                        <hr class="my-4">
                        
                        <?php
                        // Get user statistics
                        $stats = [
                            'total_orders' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE user_campus_id = ?", [$user_details['campus_id']])['count'],
                            'completed_orders' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE user_campus_id = ? AND shop_status = 'pickup'", [$user_details['campus_id']])['count'],
                            'total_spent' => fetchOne("SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE user_campus_id = ? AND shop_status = 'pickup'", [$user_details['campus_id']])['total'],
                            'favorite_shop' => fetchOne("SELECT s.shop_name, COUNT(*) as order_count FROM orders o JOIN shop_owners s ON o.shop_id = s.shop_id WHERE o.user_campus_id = ? GROUP BY o.shop_id ORDER BY order_count DESC LIMIT 1", [$user_details['campus_id']])
                        ];
                        ?>
                        
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <div class="bg-primary text-white p-3 rounded">
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
                                <div class="bg-info text-white p-3 rounded">
                                    <h4><?php echo formatPrice($stats['total_spent']); ?></h4>
                                    <p class="mb-0">Total Spent</p>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="bg-warning text-white p-3 rounded">
                                    <h6><?php echo $stats['favorite_shop']['shop_name'] ?? 'None'; ?></h6>
                                    <p class="mb-0">Favorite Shop</p>
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
                                    <i class="fas fa-utensils fa-2x mb-2"></i><br>
                                    Browse Food
                                </a>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <a href="cart.php" class="btn btn-outline-success btn-lg w-100">
                                    <i class="fas fa-shopping-cart fa-2x mb-2"></i><br>
                                    View Cart
                                </a>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <a href="orders.php" class="btn btn-outline-info btn-lg w-100">
                                    <i class="fas fa-list-alt fa-2x mb-2"></i><br>
                                    My Orders
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
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
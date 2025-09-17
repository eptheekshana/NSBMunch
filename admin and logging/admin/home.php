<?php
// Admin dashboard
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';

// Require admin login
requireAdminLogin();

$page_title = "Admin Dashboard";

// Handle shop approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $shop_id = $_POST['shop_id'] ?? '';
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $sql = "UPDATE shop_owners SET status = 'approved' WHERE shop_id = ?";
        if (executeQuery($sql, [$shop_id])) {
            setSuccess('Shop approved successfully');
        } else {
            setError('Failed to approve shop');
        }
    } elseif ($action === 'reject') {
        $sql = "UPDATE shop_owners SET status = 'rejected' WHERE shop_id = ?";
        if (executeQuery($sql, [$shop_id])) {
            setSuccess('Shop rejected successfully');
        } else {
            setError('Failed to reject shop');
        }
    }
    
    redirect('home.php');
}

// Get pending shop registrations
$pending_shops = fetchAll("SELECT * FROM shop_owners WHERE status = 'pending' ORDER BY created_at DESC");

// Get shop history (approved and rejected)
$shop_history = fetchAll("SELECT * FROM shop_owners WHERE status != 'pending' ORDER BY created_at DESC LIMIT 20");

// Get statistics
$stats = [
    'total_users' => fetchOne("SELECT COUNT(*) as count FROM users")['count'],
    'total_shops' => fetchOne("SELECT COUNT(*) as count FROM shop_owners WHERE status = 'approved'")['count'],
    'pending_approvals' => fetchOne("SELECT COUNT(*) as count FROM shop_owners WHERE status = 'pending'")['count'],
    'total_orders' => fetchOne("SELECT COUNT(*) as count FROM orders")['count']
];
?>

<?php require_once '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Admin Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-user-shield me-2"></i>Admin Dashboard</h2>
                    <p class="text-muted mb-0">Manage NSBMunch system and shop approvals</p>
                </div>
                <div>
                    <a href="../auth/logout.php" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4><?php echo $stats['total_users']; ?></h4>
                                    <p class="mb-0">Total Users</p>
                                </div>
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4><?php echo $stats['total_shops']; ?></h4>
                                    <p class="mb-0">Active Shops</p>
                                </div>
                                <i class="fas fa-store fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4><?php echo $stats['pending_approvals']; ?></h4>
                                    <p class="mb-0">Pending Approvals</p>
                                </div>
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 mb-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4><?php echo $stats['total_orders']; ?></h4>
                                    <p class="mb-0">Total Orders</p>
                                </div>
                                <i class="fas fa-shopping-bag fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pending Shop Approvals -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>Approve and Reject Shop Registrations
                        <?php if (count($pending_shops) > 0): ?>
                            <span class="badge bg-warning ms-2"><?php echo count($pending_shops); ?> Pending</span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($pending_shops)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>No pending shop registrations</h5>
                            <p class="text-muted">All shop registrations have been processed.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Shop Name</th>
                                        <th>Shop ID</th>
                                        <th>Owner Email</th>
                                        <th>Owner Name</th>
                                        <th>Location</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pending_shops as $shop): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($shop['shop_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($shop['shop_id']); ?></td>
                                            <td><?php echo htmlspecialchars($shop['email']); ?></td>
                                            <td><?php echo htmlspecialchars($shop['owner_name']); ?></td>
                                            <td><?php echo htmlspecialchars($shop['location']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($shop['created_at'])); ?></td>
                                            <td>
                                                <form method="POST" style="display: inline-block;">
                                                    <input type="hidden" name="shop_id" value="<?php echo $shop['shop_id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-success btn-sm" 
                                                            onclick="return confirm('Approve this shop registration?')">
                                                        <i class="fas fa-check"></i> Approve
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" style="display: inline-block;">
                                                    <input type="hidden" name="shop_id" value="<?php echo $shop['shop_id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-danger btn-sm" 
                                                            onclick="return confirm('Reject this shop registration?')">
                                                        <i class="fas fa-times"></i> Reject
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Shop History -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Shop History
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($shop_history)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5>No shop history</h5>
                            <p class="text-muted">Shop approval history will appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Shop Name</th>
                                        <th>Shop ID</th>
                                        <th>Owner Email</th>
                                        <th>Owner Name</th>
                                        <th>Location</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($shop_history as $shop): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($shop['shop_name']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($shop['shop_id']); ?></td>
                                            <td><?php echo htmlspecialchars($shop['email']); ?></td>
                                            <td><?php echo htmlspecialchars($shop['owner_name']); ?></td>
                                            <td><?php echo htmlspecialchars($shop['location']); ?></td>
                                            <td><?php echo date('Y-m-d', strtotime($shop['created_at'])); ?></td>
                                            <td>
                                                <?php if ($shop['status'] === 'approved'): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>Approved
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-times me-1"></i>Rejected
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

<?php require_once '../includes/footer.php'; ?>
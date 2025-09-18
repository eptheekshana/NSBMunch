<?php
// User dashboard/home page
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';

// Require user login
requireUserLogin();

$page_title = "User Dashboard";
$current_user = getCurrentUser();

// Get food items with shop details
$selected_category = $_GET['category'] ?? '';
$selected_shop = $_GET['shop_id'] ?? '';

$sql = "SELECT f.*, s.shop_name, s.location 
        FROM food_items f 
        JOIN shop_owners s ON f.shop_id = s.shop_id 
        WHERE s.status = 'approved'";

$params = [];

if ($selected_category) {
    $sql .= " AND f.category = ?";
    $params[] = $selected_category;
}

if ($selected_shop) {
    $sql .= " AND f.shop_id = ?";
    $params[] = $selected_shop;
}

$sql .= " ORDER BY f.created_at DESC";
$food_items = fetchAll($sql, $params);

// Get approved shops for dropdown
$shops = fetchAll("SELECT shop_id, shop_name FROM shop_owners WHERE status = 'approved' ORDER BY shop_name");
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
                    <a href="home.php" class="nav-link active">
                        <i class="fas fa-home me-2"></i>Home
                    </a>
                    <a href="cart.php" class="nav-link">
                        <i class="fas fa-shopping-cart me-2"></i>Cart
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
                <!-- Welcome Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2>Welcome, <?php echo $current_user['username']; ?>!</h2>
                        <p class="text-muted mb-0">Browse and order delicious food from campus restaurants</p>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">Campus ID: <?php echo $current_user['campus_id']; ?></small>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Food Items</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <label for="category" class="form-label">Food Category</label>
                                <select class="form-select" id="category" name="category" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    <?php foreach ($food_categories as $category): ?>
                                        <option value="<?php echo $category; ?>" 
                                                <?php echo ($selected_category === $category) ? 'selected' : ''; ?>>
                                            <?php echo $category; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="shop_id" class="form-label">Shop</label>
                                <select class="form-select" id="shop_id" name="shop_id" onchange="this.form.submit()">
                                    <option value="">All Shops</option>
                                    <?php foreach ($shops as $shop): ?>
                                        <option value="<?php echo $shop['shop_id']; ?>" 
                                                <?php echo ($selected_shop === $shop['shop_id']) ? 'selected' : ''; ?>>
                                            <?php echo $shop['shop_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Food Items Grid -->
                <div class="row">
                    <?php if (empty($food_items)): ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <h5>No food items found</h5>
                                <p>Try adjusting your filters or check back later for new items.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($food_items as $item): ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card food-card h-100" data-category="<?php echo $item['category']; ?>" data-shop="<?php echo $item['shop_id']; ?>">
                                    <div class="position-relative">
                                        <?php if ($item['image_path'] && file_exists('../assets/uploads/food_images/' . $item['image_path'])): ?>
                                            <img src="../assets/uploads/food_images/<?php echo $item['image_path']; ?>" class="card-img-top" alt="<?php echo $item['food_name']; ?>">
                                        <?php else: ?>
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <i class="fas fa-utensils fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="position-absolute top-0 end-0 m-2">
                                            <span class="badge bg-primary"><?php echo $item['category']; ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title"><?php echo $item['food_name']; ?></h5>
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-store me-1"></i><?php echo $item['shop_name']; ?>
                                        </p>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-map-marker-alt me-1"></i><?php echo $item['location']; ?>
                                        </p>
                                        
                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h4 class="text-primary mb-0"><?php echo formatPrice($item['price']); ?></h4>
                                            </div>
                                            
                                            <div class="d-grid gap-2 d-md-block">
                                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                                        onclick="showFoodDetails(<?php echo $item['id']; ?>)">
                                                    <i class="fas fa-info-circle me-1"></i>More
                                                </button>
                                                <button type="button" class="btn btn-primary btn-sm" 
                                                        onclick="addToCart(<?php echo $item['id']; ?>)">
                                                    <i class="fas fa-cart-plus me-1"></i>Add to Cart
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Food Details Modal -->
<div class="modal fade" id="foodDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFoodName">Food Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="modalAddToCart">
                    <i class="fas fa-cart-plus me-1"></i>Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Show food details in modal
function showFoodDetails(foodId) {
    $.ajax({
        url: 'functions/get_food_details.php',
        method: 'GET',
        data: { id: foodId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const food = response.data;
                $('#modalFoodName').text(food.food_name);
                
                let imageHtml = '';
                if (food.image_path) {
                    imageHtml = `<img src="../assets/uploads/food_images/${food.image_path}" class="img-fluid mb-3" alt="${food.food_name}">`;
                } else {
                    imageHtml = '<div class="bg-light p-5 text-center mb-3"><i class="fas fa-utensils fa-3x text-muted"></i></div>';
                }
                
                $('#modalBody').html(`
                    ${imageHtml}
                    <h5>Price: <span class="text-primary">${formatPrice(food.price)}</span></h5>
                    <p><strong>Shop:</strong> ${food.shop_name}</p>
                    <p><strong>Location:</strong> ${food.location}</p>
                    <p><strong>Category:</strong> <span class="badge bg-primary">${food.category}</span></p>
                    <p><strong>Description:</strong></p>
                    <p>${food.description || 'No description available.'}</p>
                `);
                
                $('#modalAddToCart').off('click').on('click', function() {
                    addToCart(foodId);
                    $('#foodDetailsModal').modal('hide');
                });
                
                $('#foodDetailsModal').modal('show');
            } else {
                alert('Error loading food details: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while loading food details.');
        }
    });
}

// Add to cart functionality
function addToCart(foodId) {
    $.ajax({
        url: 'functions/add_to_cart.php',
        method: 'POST',
        data: {
            food_id: foodId,
            quantity: 1
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('Item added to cart successfully!');
                // Show success notification
                showNotification('Item added to cart!', 'success');
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
        }
    });
}

// Format price for display
function formatPrice(price) {
    return 'Rs. ' + parseFloat(price).toFixed(2);
}

// Show notification
function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
    
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="${icon} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(notification);
    
    setTimeout(function() {
        notification.fadeOut();
    }, 3000);
}
</script>

<?php require_once '../includes/footer.php'; ?>
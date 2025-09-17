<?php
// Shop owner dashboard - FIXED VERSION
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';

// Require shop owner login
requireShopOwnerLogin();

$page_title = "Shop Owner Dashboard";
$current_user = getCurrentUser();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // ADD FOOD ITEM
    if ($_POST['action'] === 'add_food') {
        $food_name = sanitizeInput($_POST['food_name'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $category = sanitizeInput($_POST['category'] ?? '');
        
        // Validation
        if (empty($food_name)) {
            setError('Food name is required');
        } elseif ($price <= 0) {
            setError('Price must be greater than 0');
        } elseif (empty($category)) {
            setError('Food category is required');
        } else {
            // Handle image upload
            $image_path = 'default_food.jpg';
            $upload_error = '';
            
            if (isset($_FILES['food_image']) && $_FILES['food_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../assets/uploads/food_images/';
                
                // Ensure directory exists
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                    chmod($upload_dir, 0777);
                }
                
                $file = $_FILES['food_image'];
                
                // Validate file
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (!in_array($file_ext, $allowed_types)) {
                    $upload_error = 'Only JPG, JPEG, PNG, GIF files are allowed';
                } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
                    $upload_error = 'File size must be less than 5MB';
                } elseif (!getimagesize($file['tmp_name'])) {
                    $upload_error = 'File is not a valid image';
                } else {
                    // Generate unique filename
                    $filename = 'food_' . $current_user['shop_id'] . '_' . uniqid() . '.' . $file_ext;
                    $target_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $target_path)) {
                        $image_path = $filename;
                    } else {
                        $upload_error = 'Failed to upload image';
                    }
                }
            }
            
            // Insert food item
            $sql = "INSERT INTO food_items (shop_id, food_name, description, price, category, image_path) VALUES (?, ?, ?, ?, ?, ?)";
            $params = [$current_user['shop_id'], $food_name, $description, $price, $category, $image_path];
            
            if (executeQuery($sql, $params)) {
                if ($upload_error) {
                    setError('Food item added successfully but ' . $upload_error);
                } else {
                    setSuccess('Food item "' . $food_name . '" added successfully!');
                }
            } else {
                setError('Failed to add food item');
            }
        }
        
        header('Location: home.php');
        exit();
    }
    
    // DELETE FOOD ITEM
    elseif ($_POST['action'] === 'delete_food') {
        $food_id = (int)($_POST['food_id'] ?? 0);
        
        if ($food_id > 0) {
            // Get food item details first
            $food_item = fetchOne("SELECT * FROM food_items WHERE id = ? AND shop_id = ?", [$food_id, $current_user['shop_id']]);
            
            if ($food_item) {
                // Delete food item from database
                if (executeQuery("DELETE FROM food_items WHERE id = ? AND shop_id = ?", [$food_id, $current_user['shop_id']])) {
                    // Delete image file if exists and not default
                    if ($food_item['image_path'] && $food_item['image_path'] !== 'default_food.jpg') {
                        $image_file = '../assets/uploads/food_images/' . $food_item['image_path'];
                        if (file_exists($image_file)) {
                            unlink($image_file);
                        }
                    }
                    setSuccess('Food item "' . $food_item['food_name'] . '" deleted successfully');
                } else {
                    setError('Failed to delete food item');
                }
            } else {
                setError('Food item not found');
            }
        }
        
        header('Location: home.php');
        exit();
    }
    
    // EDIT FOOD ITEM
    elseif ($_POST['action'] === 'edit_food') {
        $food_id = (int)($_POST['food_id'] ?? 0);
        $food_name = sanitizeInput($_POST['food_name'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $category = sanitizeInput($_POST['category'] ?? '');
        
        if ($food_id > 0 && !empty($food_name) && $price > 0 && !empty($category)) {
            // Check if food belongs to this shop
            $existing_food = fetchOne("SELECT * FROM food_items WHERE id = ? AND shop_id = ?", [$food_id, $current_user['shop_id']]);
            
            if ($existing_food) {
                $sql = "UPDATE food_items SET food_name = ?, description = ?, price = ?, category = ? WHERE id = ? AND shop_id = ?";
                $params = [$food_name, $description, $price, $category, $food_id, $current_user['shop_id']];
                
                if (executeQuery($sql, $params)) {
                    setSuccess('Food item updated successfully');
                } else {
                    setError('Failed to update food item');
                }
            } else {
                setError('Food item not found');
            }
        } else {
            setError('Please fill in all required fields');
        }
        
        header('Location: home.php');
        exit();
    }
}

// Get shop's food items
$food_items = fetchAll("SELECT * FROM food_items WHERE shop_id = ? ORDER BY created_at DESC", [$current_user['shop_id']]);

// Get shop statistics
$stats = [
    'total_items' => count($food_items),
    'pending_orders' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE shop_id = ? AND shop_status = 'pending'", [$current_user['shop_id']])['count'],
    'today_orders' => fetchOne("SELECT COUNT(*) as count FROM orders WHERE shop_id = ? AND order_date = CURRENT_DATE", [$current_user['shop_id']])['count'],
    'total_revenue' => fetchOne("SELECT COALESCE(SUM(total_price), 0) as total FROM orders WHERE shop_id = ? AND shop_status = 'pickup'", [$current_user['shop_id']])['total']
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
                    <a href="home.php" class="nav-link active">
                        <i class="fas fa-home me-2"></i>Dashboard
                    </a>
                    <a href="manage_orders.php" class="nav-link">
                        <i class="fas fa-list-alt me-2"></i>Manage Orders
                        <?php if ($stats['pending_orders'] > 0): ?>
                            <span class="badge bg-warning ms-auto"><?php echo $stats['pending_orders']; ?></span>
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
                <!-- Welcome Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2>Welcome, <?php echo $current_user['owner_name']; ?>!</h2>
                        <p class="text-muted mb-0">Manage your shop and food items</p>
                    </div>
                    <div class="text-end">
                        <small class="text-muted"><?php echo $current_user['location']; ?></small>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4><?php echo $stats['total_items']; ?></h4>
                                        <p class="mb-0">Food Items</p>
                                    </div>
                                    <i class="fas fa-utensils fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4><?php echo $stats['pending_orders']; ?></h4>
                                        <p class="mb-0">Pending Orders</p>
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
                                        <h4><?php echo $stats['today_orders']; ?></h4>
                                        <p class="mb-0">Today's Orders</p>
                                    </div>
                                    <i class="fas fa-shopping-bag fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4><?php echo formatPrice($stats['total_revenue']); ?></h4>
                                        <p class="mb-0">Total Revenue</p>
                                    </div>
                                    <i class="fas fa-dollar-sign fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Add Food Item Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Add New Food Item</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add_food">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="food_name" class="form-label">Food Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="food_name" name="food_name" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price (Rs.) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="price" name="price" step="0.01" min="0.01" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Food Category <span class="text-danger">*</span></label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($food_categories as $category): ?>
                                                <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Food Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" 
                                                  placeholder="Describe your food item..."></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="food_image" class="form-label">Upload Food Picture</label>
                                        <input type="file" class="form-control" id="food_image" name="food_image" 
                                               accept="image/*" onchange="previewImage(this)">
                                        <div class="form-text">Max size: 5MB. Formats: JPG, PNG, GIF</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <img id="image-preview" src="#" alt="Preview" class="img-thumbnail" 
                                             style="display: none; max-width: 200px; max-height: 200px;">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add Food Item
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Current Food Items -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-utensils me-2"></i>Your Food Items
                            <span class="badge bg-primary ms-2"><?php echo count($food_items); ?> Items</span>
                        </h5>
                        <a href="test_upload.php" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-cog me-1"></i>Test Upload
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($food_items)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-utensils fa-4x text-muted mb-4"></i>
                                <h5>No food items yet</h5>
                                <p class="text-muted">Add your first food item using the form above to start receiving orders.</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($food_items as $item): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card food-card h-100">
                                            <div class="position-relative">
                                                <?php 
                                                $image_path = '../assets/uploads/food_images/' . $item['image_path'];
                                                $show_image = false;
                                                
                                                if ($item['image_path'] && $item['image_path'] !== 'default_food.jpg' && file_exists($image_path)) {
                                                    $show_image = true;
                                                }
                                                ?>
                                                
                                                <?php if ($show_image): ?>
                                                    <img src="<?php echo $image_path; ?>" class="card-img-top" alt="<?php echo $item['food_name']; ?>" style="height: 200px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                        <div class="text-center">
                                                            <i class="fas fa-utensils fa-3x text-muted mb-2"></i>
                                                            <div class="small text-muted">No Image</div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="position-absolute top-0 end-0 m-2">
                                                    <span class="badge bg-primary"><?php echo $item['category']; ?></span>
                                                </div>
                                            </div>
                                            
                                            <div class="card-body d-flex flex-column">
                                                <h5 class="card-title"><?php echo htmlspecialchars($item['food_name']); ?></h5>
                                                <p class="text-muted small flex-grow-1">
                                                    <?php echo htmlspecialchars($item['description'] ?: 'No description available'); ?>
                                                </p>
                                                <div class="mt-auto">
                                                    <h4 class="text-primary"><?php echo formatPrice($item['price']); ?></h4>
                                                    <small class="text-muted">Added: <?php echo date('Y-m-d', strtotime($item['created_at'])); ?></small>
                                                    
                                                    <!-- Action buttons -->
                                                    <div class="d-flex justify-content-between mt-3">
                                                        <button type="button" class="btn btn-outline-primary btn-sm" 
                                                                onclick="editFoodItem(<?php echo $item['id']; ?>)">
                                                            <i class="fas fa-edit me-1"></i>Edit
                                                        </button>
                                                        
                                                        <form method="POST" style="display: inline;" 
                                                              onsubmit="return confirm('Are you sure you want to delete &quot;<?php echo htmlspecialchars($item['food_name']); ?>&quot;? This action cannot be undone.')">
                                                            <input type="hidden" name="action" value="delete_food">
                                                            <input type="hidden" name="food_id" value="<?php echo $item['id']; ?>">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                                <i class="fas fa-trash me-1"></i>Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Food Item Modal -->
<div class="modal fade" id="editFoodModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Food Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editFoodForm">
                <input type="hidden" name="action" value="edit_food">
                <input type="hidden" name="food_id" id="edit_food_id">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_food_name" class="form-label">Food Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_food_name" name="food_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_price" class="form-label">Price (Rs.) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_price" name="price" step="0.01" min="0.01" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_category" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_category" name="category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($food_categories as $category): ?>
                                        <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" 
                                  placeholder="Describe your food item..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Food Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Image preview function
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('image-preview').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Edit food item function
function editFoodItem(foodId) {
    // Get food item data and populate modal
    <?php foreach ($food_items as $item): ?>
    if (<?php echo $item['id']; ?> === foodId) {
        document.getElementById('edit_food_id').value = '<?php echo $item['id']; ?>';
        document.getElementById('edit_food_name').value = '<?php echo htmlspecialchars($item['food_name'], ENT_QUOTES); ?>';
        document.getElementById('edit_description').value = '<?php echo htmlspecialchars($item['description'], ENT_QUOTES); ?>';
        document.getElementById('edit_price').value = '<?php echo $item['price']; ?>';
        document.getElementById('edit_category').value = '<?php echo htmlspecialchars($item['category'], ENT_QUOTES); ?>';
    }
    <?php endforeach; ?>
    
    // Show modal
    var modal = new bootstrap.Modal(document.getElementById('editFoodModal'));
    modal.show();
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    // Add food form validation
    const addForm = document.querySelector('form[method="POST"]');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            const foodName = document.getElementById('food_name').value.trim();
            const price = parseFloat(document.getElementById('price').value);
            const category = document.getElementById('category').value;
            
            if (!foodName || price <= 0 || !category) {
                e.preventDefault();
                alert('Please fill in all required fields correctly.');
            }
        });
    }
    
    // Edit form validation
    const editForm = document.getElementById('editFoodForm');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            const foodName = document.getElementById('edit_food_name').value.trim();
            const price = parseFloat(document.getElementById('edit_price').value);
            const category = document.getElementById('edit_category').value;
            
            if (!foodName || price <= 0 || !category) {
                e.preventDefault();
                alert('Please fill in all required fields correctly.');
            }
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
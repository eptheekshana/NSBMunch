<?php
// Shop owner registration page
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';

// Redirect if already logged in
redirectIfLoggedIn();

$page_title = "Shop Registration";
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shop_id = sanitizeInput($_POST['shop_id']);
    $owner_name = sanitizeInput($_POST['owner_name']);
    $shop_name = sanitizeInput($_POST['shop_name']);
    $email = sanitizeInput($_POST['email']);
    $location = sanitizeInput($_POST['location']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($shop_id) || empty($owner_name) || empty($shop_name) || empty($email) || empty($password) || empty($location)) {
        $error = 'Please fill in all fields.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < MIN_PASSWORD_LENGTH) {
        $error = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if shop_id or email already exists
        $existing_shop = fetchOne("SELECT id FROM shop_owners WHERE shop_id = ? OR email = ?", [$shop_id, $email]);
        
        if ($existing_shop) {
            $error = 'Shop ID or Email already exists.';
        } else {
            // Insert new shop owner
            $hashed_password = hashPassword($password);
            $sql = "INSERT INTO shop_owners (shop_id, owner_name, shop_name, email, location, password, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')";
            
            if (executeQuery($sql, [$shop_id, $owner_name, $shop_name, $email, $location, $hashed_password])) {
                $success = 'Registration successful! Your shop registration is pending admin approval. You will be notified once approved.';
                // Clear form data
                $_POST = [];
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<?php require_once '../includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header text-center">
                    <h3><i class="fas fa-store me-2"></i>Shop Registration</h3>
                    <p class="mb-0 text-muted">Register your campus restaurant or canteen</p>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="shop_id" class="form-label">Shop ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="shop_id" name="shop_id" required 
                                   value="<?php echo isset($_POST['shop_id']) ? htmlspecialchars($_POST['shop_id']) : ''; ?>"
                                   placeholder="e.g., SHOP001, CAFE001">
                            <div class="form-text">Enter a unique identifier for your shop</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="owner_name" class="form-label">Owner Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="owner_name" name="owner_name" required 
                                   value="<?php echo isset($_POST['owner_name']) ? htmlspecialchars($_POST['owner_name']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="shop_name" class="form-label">Shop Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="shop_name" name="shop_name" required 
                                   value="<?php echo isset($_POST['shop_name']) ? htmlspecialchars($_POST['shop_name']) : ''; ?>"
                                   placeholder="e.g., Campus Cafe, Spice Garden">
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   placeholder="your@email.com">
                            <div class="form-text">Any valid email address can be used</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="location" class="form-label">Shop Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="location" name="location" required 
                                   value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>"
                                   placeholder="e.g., Building A - Ground Floor, Main Canteen">
                            <div class="form-text">Specify your shop location on campus</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required
                                   minlength="<?php echo MIN_PASSWORD_LENGTH; ?>">
                            <div class="form-text">Minimum <?php echo MIN_PASSWORD_LENGTH; ?> characters</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> Your shop registration will be reviewed by admin. You will be notified once approved and can then login to manage your shop.
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-store me-2"></i>Register Shop
                            </button>
                        </div>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="text-center">
                        <p class="mb-2">Already have an account?</p>
                        <a href="login.php" class="btn btn-outline-primary">
                            <i class="fas fa-sign-in-alt me-1"></i>Login Here
                        </a>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="../index.php" class="btn btn-secondary">
                        <i class="fas fa-home me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    var password = document.getElementById('password').value;
    var confirmPassword = this.value;
    
    if (password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
    }
});

// Shop ID validation (uppercase and alphanumeric)
document.getElementById('shop_id').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>

<?php require_once '../includes/footer.php'; ?>
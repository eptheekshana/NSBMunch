<?php
// User registration page
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';

// Redirect if already logged in
redirectIfLoggedIn();

$page_title = "User Registration";
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $campus_id = sanitizeInput($_POST['campus_id']);
    $user_category = sanitizeInput($_POST['user_category']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($username) || empty($campus_id) || empty($user_category) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } elseif (!validateNSBMEmail($email)) {
        $error = 'Please use a valid NSBM email address (@nsbm.ac.lk).';
    } elseif (strlen($password) < MIN_PASSWORD_LENGTH) {
        $error = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email or campus ID already exists
        $existing_user = fetchOne("SELECT id FROM users WHERE email = ? OR campus_id = ?", [$email, $campus_id]);
        
        if ($existing_user) {
            $error = 'Email or Campus ID already exists.';
        } else {
            // Insert new user
            $hashed_password = hashPassword($password);
            $sql = "INSERT INTO users (username, campus_id, email, password, user_category) VALUES (?, ?, ?, ?, ?)";
            
            if (executeQuery($sql, [$username, $campus_id, $email, $hashed_password, $user_category])) {
                $success = 'Registration successful! You can now login with your credentials.';
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
                    <h3><i class="fas fa-user-plus me-2"></i>User Registration</h3>
                    <p class="mb-0 text-muted">Register with your NSBM email</p>
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
                            <label for="username" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" required 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="campus_id" class="form-label">Campus ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="campus_id" name="campus_id" required 
                                   value="<?php echo isset($_POST['campus_id']) ? htmlspecialchars($_POST['campus_id']) : ''; ?>"
                                   placeholder="e.g., STU001, LEC001, STF001">
                            <div class="form-text">Enter your unique campus ID</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="user_category" class="form-label">User Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="user_category" name="user_category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($user_categories as $key => $value): ?>
                                    <option value="<?php echo $key; ?>" 
                                            <?php echo (isset($_POST['user_category']) && $_POST['user_category'] === $key) ? 'selected' : ''; ?>>
                                        <?php echo $value; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">NSBM Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   placeholder="yourname@nsbm.ac.lk">
                            <div class="form-text">Must be a valid @nsbm.ac.lk email address</div>
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
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-user-plus me-2"></i>Register
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

// Email validation for NSBM domain
document.getElementById('email').addEventListener('input', function() {
    var email = this.value;
    if (email && !email.endsWith('@nsbm.ac.lk')) {
        this.setCustomValidity('Please use an @nsbm.ac.lk email address');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
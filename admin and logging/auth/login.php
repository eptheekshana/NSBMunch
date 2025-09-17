<?php
// Login page
require_once '../config/database.php';
require_once '../config/config.php';
require_once '../config/session.php';

// Redirect if already logged in
redirectIfLoggedIn();

$page_title = "Login";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        // Check admin login first
        if ($email === 'nsbm@gmail.com') {
            $admin = fetchOne("SELECT * FROM admin WHERE email = ?", [$email]);
            if ($admin && $password === '123123') {
                initAdminSession($admin);
                redirect(SITE_URL . '/admin/home.php');
            } else {
                $error = 'Invalid admin credentials.';
            }
        } else {
            // Check user login
            $user = fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
            if ($user && verifyPassword($password, $user['password'])) {
                initUserSession($user);
                redirect(SITE_URL . '/user/home.php');
            } else {
                // Check shop owner login
                $shop_owner = fetchOne("SELECT * FROM shop_owners WHERE email = ?", [$email]);
                if ($shop_owner && verifyPassword($password, $shop_owner['password'])) {
                    if ($shop_owner['status'] === 'approved') {
                        initShopOwnerSession($shop_owner);
                        redirect(SITE_URL . '/shop_owner/home.php');
                    } elseif ($shop_owner['status'] === 'pending') {
                        $error = 'Your shop registration is still pending approval.';
                    } else {
                        $error = 'Your shop registration has been rejected.';
                    }
                } else {
                    $error = 'Invalid email or password.';
                }
            }
        }
    }
}
?>

<?php require_once '../includes/header.php'; ?>

<body class="d-flex flex-column min-vh-100">

<main class="flex-grow-1">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h3><i class="fas fa-sign-in-alt me-2"></i>Login to NSBMunch</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                <div class="form-text">
                                    Users: Use your @nsbm.ac.lk email<br>
                                    Shop Owners: Use your registered email
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Approval Status</label>
                                <div class="p-2 bg-light rounded">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Shop owners need admin approval to access their account
                                    </small>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-2">Don't have an account?</p>
                            <div class="d-grid gap-2 d-md-block">
                                <a href="user_register.php" class="btn btn-outline-primary">
                                    <i class="fas fa-user-plus me-1"></i>Register as User
                                </a>
                                <a href="shop_register.php" class="btn btn-outline-success">
                                    <i class="fas fa-store me-1"></i>Register Shop
                                </a>
                            </div>
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
</main>

<?php require_once '../includes/footer.php'; ?>

</body>

<?php
// Main entry page for NSBMunch
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'config/session.php';

// Redirect if already logged in
redirectIfLoggedIn();

$page_title = "Welcome to NSBMunch";
?>

<?php require_once 'includes/header.php'; ?>

<!-- Hero Section with Auto-Sliding Advertisements -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Welcome to NSBMunch</h1>
                <p class="lead mb-4">Your ultimate campus food ordering solution at NSBM Green University. Order delicious meals from campus canteens and restaurants with just a few clicks!</p>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                    <a href="auth/user_register.php" class="btn btn-primary btn-lg px-4 me-md-2">
                        <i class="fas fa-user-plus me-2"></i>User Register
                    </a>
                    <a href="auth/shop_register.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-store me-2"></i>Shop Register
                    </a>
                </div>
                
                <div class="mt-4">
                    <a href="auth/login.php" class="btn btn-warning btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </a>
                </div>
            </div>
            
            <div class="col-lg-6">
                <!-- Auto-sliding Advertisement Carousel -->
                <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
                    </div>
                    
                    <div class="carousel-inner rounded-3">
                        <div class="carousel-item active">
                            <div class="bg-light p-4 text-dark text-center" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                                <div>
                                    <i class="fas fa-hamburger fa-3x text-primary mb-3"></i>
                                    <h4>Delicious Burgers</h4>
                                    <p>Fresh, juicy burgers made with quality ingredients</p>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="bg-light p-4 text-dark text-center" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                                <div>
                                    <i class="fas fa-pizza-slice fa-3x text-danger mb-3"></i>
                                    <h4>Hot Pizza</h4>
                                    <p>Wood-fired pizzas with fresh toppings</p>
                                </div>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <div class="bg-light p-4 text-dark text-center" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                                <div>
                                    <i class="fas fa-coffee fa-3x text-warning mb-3"></i>
                                    <h4>Fresh Coffee</h4>
                                    <p>Premium coffee and refreshing beverages</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="mb-4">About NSBMunch</h2>
                <p class="lead">NSBMunch is designed specifically for NSBM Green University campus community. We connect students, lecturers, and staff with campus restaurants and canteens, making food ordering convenient and efficient.</p>
            </div>
        </div>
        
        <div class="row mt-5">
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-users fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">For Students & Staff</h5>
                        <p class="card-text">Easy registration with your NSBM email. Browse, order, and track your favorite campus meals.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-store fa-3x text-success mb-3"></i>
                        <h5 class="card-title">For Shop Owners</h5>
                        <p class="card-text">Register your campus restaurant or canteen. Manage orders and grow your business digitally.</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body">
                        <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Quick & Easy</h5>
                        <p class="card-text">Order in advance, set pickup time, and pay at the canteen. No more waiting in long queues!</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="mb-4">How It Works</h2>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="text-center">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <span class="text-white fw-bold">1</span>
                    </div>
                    <h5 class="mt-3">Register</h5>
                    <p>Sign up with your NSBM email or register your shop</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="text-center">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <span class="text-white fw-bold">2</span>
                    </div>
                    <h5 class="mt-3">Browse</h5>
                    <p>Explore food items from various campus restaurants</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="text-center">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <span class="text-white fw-bold">3</span>
                    </div>
                    <h5 class="mt-3">Order</h5>
                    <p>Add items to cart, set pickup time and place order</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="text-center">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <span class="text-white fw-bold">4</span>
                    </div>
                    <h5 class="mt-3">Pickup</h5>
                    <p>Get notified when ready and pickup at scheduled time</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- User Categories Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5">
                <h2>Who Can Use NSBMunch?</h2>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">Students</h5>
                        <p class="card-text">NSBM students can register with their student email (@nsbm.ac.lk) and enjoy convenient food ordering.</p>
                        <a href="auth/user_register.php" class="btn btn-primary">Register as Student</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-chalkboard-teacher fa-3x text-success mb-3"></i>
                        <h5 class="card-title">Lecturers</h5>
                        <p class="card-text">Faculty members can use their NSBM email to access the platform and order meals.</p>
                        <a href="auth/user_register.php" class="btn btn-success">Register as Lecturer</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-user-tie fa-3x text-warning mb-3"></i>
                        <h5 class="card-title">Staff</h5>
                        <p class="card-text">NSBM staff members are welcome to join and make their campus dining experience better.</p>
                        <a href="auth/user_register.php" class="btn btn-warning">Register as Staff</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary text-white">
    <div class="container text-center">
        <h2 class="mb-4">Ready to Start Ordering?</h2>
        <p class="lead mb-4">Join NSBMunch today and experience the future of campus dining!</p>
        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
            <a href="auth/user_register.php" class="btn btn-light btn-lg px-4 me-md-2">
                <i class="fas fa-user-plus me-2"></i>Join as User
            </a>
            <a href="auth/shop_register.php" class="btn btn-outline-light btn-lg px-4">
                <i class="fas fa-store me-2"></i>Register Your Shop
            </a>
        </div>
    </div>
</section>

<script>
    // Auto-slide carousel every 4 seconds
    var carousel = new bootstrap.Carousel(document.getElementById('heroCarousel'), {
        interval: 4000,
        wrap: true
    });
</script>

<?php require_once 'includes/footer.php'; ?>
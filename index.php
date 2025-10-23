<?php
include 'includes/header.php';
include 'includes/currency.php';
?>
<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <h1 class="hero-title">Welcome to CozyBean Café</h1>
        <p class="hero-subtitle">Experience the finest blend of premium coffee and cozy atmosphere. Start your day with the perfect cup.</p>
        <a href="/coffeeshop/menu.php" class="btn hero-btn">Explore Our Menu</a>
    </div>
</div>

<!-- Features Section -->
<div class="container my-5">
    <h2 class="section-title">Why Choose Us</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="feature-box">
                <div class="feature-icon">☕</div>
                <h3 class="feature-title">Premium Quality</h3>
                <p>We source the finest beans from sustainable farms to ensure the best taste in every cup.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-box">
                <div class="feature-icon">🌿</div>
                <h3 class="feature-title">Fresh Ingredients</h3>
                <p>All our ingredients are fresh and locally sourced whenever possible for maximum flavor.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-box">
                <div class="feature-icon">🏠</div>
                <h3 class="feature-title">Cozy Atmosphere</h3>
                <p>Relax in our welcoming environment, perfect for work, meetings, or casual hangouts.</p>
            </div>
        </div>
    </div>
</div>

<!-- Popular Menu Items Preview -->
<div class="container my-5">
    <h2 class="section-title">Popular Items</h2>
    <div class="row">
        <?php
        include 'includes/db.php';
        $result = mysqli_query($conn, "SELECT * FROM products WHERE is_active = 1 LIMIT 3");
        if ($result && mysqli_num_rows($result) > 0) {
            $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
            foreach ($products as $product) {
                echo '<div class="col-md-4">';
                echo '<div class="card">';
                echo '<img src="/coffeeshop/assets/images/' . htmlspecialchars($product['image']) . '" class="card-img-top product-image" alt="' . htmlspecialchars($product['name']) . '" onerror="this.src=\'/coffeeshop/assets/images/latte.jpg\';">';
                echo '<div class="card-body text-center">';
                echo '<h5 class="card-title">' . htmlspecialchars($product['name']) . '</h5>';
                echo '<p class="card-text">' . htmlspecialchars($product['description']) . '</p>';
                echo '<p class="card-text"><strong>' . format_currency($product['sell_rate']) . '</strong></p>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p class="text-center">No products available at the moment.</p>';
        }
        ?>
    </div>
    <div class="text-center mt-4">
        <a href="/coffeeshop/menu.php" class="btn btn-outline-dark">View Full Menu</a>
    </div>
</div>

<!-- Call to Action -->
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center menu-preview">
            <h2 class="section-title">Ready to Experience Great Coffee at CozyBean Café?</h2>
            <p class="lead">Join us today and discover why we're the favorite coffee spot in town.</p>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/coffeeshop/menu.php" class="btn btn-primary btn-lg">Order Now</a>
            <?php else: ?>
                <a href="/coffeeshop/register.php" class="btn btn-primary btn-lg">Create Account</a>
                <a href="/coffeeshop/login.php" class="btn btn-outline-dark btn-lg">Login</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
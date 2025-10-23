<?php
// Improved session handling for cPanel compatibility
if (session_status() === PHP_SESSION_NONE) {
    // Set session cookie parameters for better compatibility
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}
include_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CozyBean Café</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/coffeeshop/assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="/coffeeshop/index.php">
                <img src="/coffeeshop/assets/images/logo.png" alt="CozyBean Café Logo" style="height: 40px; margin-right: 10px;">
                CozyBean Café
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="/coffeeshop/index.php">Home</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="/coffeeshop/admin/dashboard.php">Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link" href="/coffeeshop/admin/orders.php">Orders</a></li>
                            <li class="nav-item"><a class="nav-link" href="/coffeeshop/admin/products.php">Manage Products</a></li>
                            <li class="nav-item"><a class="nav-link" href="/coffeeshop/admin/profit.php">Profits</a></li>
                            <li class="nav-item"><a class="nav-link" href="/coffeeshop/admin/returns.php">Returns/Refunds</a></li>
                            <li class="nav-item"><a class="nav-link" href="/coffeeshop/logout.php">Logout</a></li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="/coffeeshop/menu.php">Menu</a></li>
                            <li class="nav-item"><a class="nav-link" href="/coffeeshop/cart.php">Cart</a></li>
                            <li class="nav-item"><a class="nav-link" href="/coffeeshop/orders.php">My Orders</a></li>
                            <li class="nav-item"><a class="nav-link" href="/coffeeshop/logout.php">Logout</a></li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/coffeeshop/menu.php">Menu</a></li>
                        <li class="nav-item"><a class="nav-link" href="/coffeeshop/cart.php">Cart</a></li>
                        <li class="nav-item"><a class="nav-link" href="/coffeeshop/login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="/coffeeshop/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
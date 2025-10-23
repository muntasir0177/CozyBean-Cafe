<?php
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/header.php';
include 'includes/currency.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $user_id = (int)$_SESSION['user_id'];
    $total = 0;
    
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_map('intval', $ids));
    $result = mysqli_query($conn, "SELECT * FROM products WHERE id IN ($placeholders) AND is_active = 1");
    if (!$result) {
        echo '<div class="alert alert-danger">Error fetching products: ' . mysqli_error($conn) . '</div>';
        exit;
    }
    $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    foreach ($products as $product) {
        $total += $product['sell_rate'] * $_SESSION['cart'][$product['id']];
    }
    
    $total = mysqli_real_escape_string($conn, $total);
    $query = "INSERT INTO orders (user_id, total, status) VALUES ('$user_id', '$total', 'pending')";
    if (!mysqli_query($conn, $query)) {
        echo '<div class="alert alert-danger">Error creating order: ' . mysqli_error($conn) . '</div>';
        exit;
    }
    $order_id = mysqli_insert_id($conn);
    
    foreach ($products as $product) {
        $product_id = (int)$product['id'];
        $quantity = (int)$_SESSION['cart'][$product['id']];
        $sell_rate = mysqli_real_escape_string($conn, $product['sell_rate']);
        $query = "INSERT INTO order_items (order_id, product_id, quantity, sell_rate) VALUES ('$order_id', '$product_id', '$quantity', '$sell_rate')";
        if (!mysqli_query($conn, $query)) {
            echo '<div class="alert alert-danger">Error adding order item: ' . mysqli_error($conn) . '</div>';
            exit;
        }
    }
    
    unset($_SESSION['cart']);
    echo '<div class="alert alert-success">Order placed successfully! <a href="orders.php">View your orders</a></div>';
}
?>
<div class="container my-5">
    <h2>Checkout</h2>
    <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
        <?php
        $total = 0;
        $ids = array_keys($_SESSION['cart']);
        $placeholders = implode(',', array_map('intval', $ids));
        $result = mysqli_query($conn, "SELECT * FROM products WHERE id IN ($placeholders) AND is_active = 1");
        if (!$result) {
            echo '<div class="alert alert-danger">Error fetching products: ' . mysqli_error($conn) . '</div>';
            exit;
        }
        $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
        ?>
        <form method="POST">
            <h4>Order Summary</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): 
                        $subtotal = $product['sell_rate'] * $_SESSION['cart'][$product['id']];
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo format_currency($product['sell_rate']); ?></td>
                            <td><?php echo $_SESSION['cart'][$product['id']]; ?></td>
                            <td><?php echo format_currency($subtotal); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <h4>Total: <?php echo format_currency($total); ?></h4>
            <button type="submit" class="btn btn-primary">Place Order</button>
        </form>
    <?php else: ?>
        <p>Your cart is empty. <a href="menu.php">Browse our menu</a>.</p>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
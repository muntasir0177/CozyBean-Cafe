<?php
include 'includes/db.php';
include 'includes/header.php';
include 'includes/currency.php';

// Handle remove item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $product_id = (int)$_POST['product_id'];
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        echo '<div class="alert alert-success">Item removed from cart!</div>';
    }
}

$cart_items = [];
$total = 0;

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $placeholders = implode(',', array_map('intval', $ids));
    $result = mysqli_query($conn, "SELECT * FROM products WHERE id IN ($placeholders) AND is_active = 1");
    if (!$result) {
        echo '<div class="alert alert-danger">Error fetching cart items: ' . mysqli_error($conn) . '</div>';
        exit;
    }
    $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    foreach ($products as $product) {
        $cart_items[] = [
            'product' => $product,
            'quantity' => (int)$_SESSION['cart'][$product['id']]
        ];
        $total += $product['sell_rate'] * $_SESSION['cart'][$product['id']];
    }
}
?>
<div class="container my-5">
    <h2>Your Cart</h2>
    <?php if (empty($cart_items)): ?>
        <p>Your cart is empty. <a href="menu.php">Browse our menu</a>.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product']['name']); ?></td>
                        <td><?php echo format_currency($item['product']['sell_rate']); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo format_currency($item['product']['sell_rate'] * $item['quantity']); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Remove <?php echo htmlspecialchars($item['product']['name']); ?> from cart?');">
                                <input type="hidden" name="product_id" value="<?php echo $item['product']['id']; ?>">
                                <button type="submit" name="remove_item" class="btn btn-danger btn-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h4>Total: <?php echo format_currency($total); ?></h4>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
        <?php else: ?>
            <p>Please <a href="login.php">login</a> to proceed to checkout.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
<?php
include 'includes/db.php';
include 'includes/header.php';
include 'includes/currency.php';

$result = mysqli_query($conn, "SELECT * FROM products WHERE is_active = 1");
if (!$result) {
    echo '<div class="alert alert-danger">Error fetching products: ' . mysqli_error($conn) . '</div>';
    exit;
}
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity < 1) {
        echo '<div class="alert alert-danger">Invalid quantity!</div>';
    } else {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + $quantity;
        echo '<div class="alert alert-success">Item added to cart!</div>';
    }
}
?>
<div class="container my-5">
    <h2>Our Menu</h2>
    <?php if (empty($products)): ?>
        <p>No products available.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="/coffeeshop/assets/images/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='/coffeeshop/assets/images/latte.jpg';">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                            <p class="card-text"><?php echo format_currency($product['sell_rate']); ?></p>
                            <form method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="number" name="quantity" value="1" min="1" class="form-control d-inline w-25">
                                <button type="submit" name="add_to_cart" class="btn btn-primary mt-2">Add to Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
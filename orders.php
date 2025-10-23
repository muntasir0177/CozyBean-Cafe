<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'includes/db.php';
include 'includes/auth.php';
include 'includes/header.php';
include 'includes/currency.php';

// Verify session
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">Session error: Please log in again.</div>';
    exit;
}

// Handle order cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = (int)$_POST['order_id'];
    $user_id = (int)$_SESSION['user_id'];
    $query = "UPDATE orders SET status = 'cancelled' WHERE id = '$order_id' AND user_id = '$user_id' AND status = 'pending'";
    
    if (mysqli_query($conn, $query)) {
        if (mysqli_affected_rows($conn) > 0) {
            echo '<div class="alert alert-success">Order cancelled successfully!</div>';
        } else {
            echo '<div class="alert alert-danger">Error: Order not found, not pending, or not yours.</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Database error: ' . mysqli_error($conn) . '</div>';
    }
}

// Fetch user orders
$user_id = (int)$_SESSION['user_id'];
$result = mysqli_query($conn, "
    SELECT o.id, o.total, o.status, o.created_at, p.name, oi.quantity, oi.sell_rate
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = '$user_id'
    ORDER BY o.created_at DESC
");

if (!$result) {
    echo '<div class="alert alert-danger">Error fetching orders: ' . mysqli_error($conn) . '</div>';
    exit;
}

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    $orders[$row['id']]['total'] = (float)$row['total'];
    $orders[$row['id']]['status'] = $row['status'];
    $orders[$row['id']]['created_at'] = $row['created_at'];
    $orders[$row['id']]['items'][] = [
        'name' => $row['name'],
        'quantity' => (int)$row['quantity'],
        'sell_rate' => (float)$row['sell_rate']
    ];
}

// Debug: Uncomment to diagnose
// echo '<pre>Session: ' . print_r($_SESSION, true) . '</pre>';
// echo '<pre>Orders: ' . print_r($orders, true) . '</pre>';
?>
<div class="container my-5">
    <h2>My Orders</h2>
    <?php if (empty($orders)): ?>
        <p>No orders placed yet. <a href="menu.php">Browse our menu</a>.</p>
    <?php else: ?>
        <?php foreach ($orders as $order_id => $order): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Order #<?php echo $order_id; ?> (<?php echo htmlspecialchars($order['status']); ?>)</h5>
                    <p class="card-text">Placed on: <?php echo htmlspecialchars($order['created_at']); ?></p>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order['items'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo format_currency($item['sell_rate']); ?></td>
                                    <td><?php echo format_currency($item['sell_rate'] * $item['quantity']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p><strong>Total: <?php echo format_currency($order['total']); ?></strong></p>
                    <?php if ($order['status'] === 'pending'): ?>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                            <button type="submit" name="cancel_order" class="btn btn-danger">Cancel Order</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
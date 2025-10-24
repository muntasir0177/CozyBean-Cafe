<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once '../includes/db.php';
include_once '../includes/auth.php';
include_once '../includes/header.php';
include_once '../includes/currency.php';

// Restrict to admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo '<div class="alert alert-danger">Access denied! Admins only.</div>';
    header('Location: ' . SITE_URL . '/login.php');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    if (!in_array($status, ['pending', 'completed', 'cancelled'])) {
        echo '<div class="alert alert-danger">Invalid status!</div>';
    } else {
        $query = "UPDATE orders SET status = '$status' WHERE id = '$order_id'";
        if (mysqli_query($conn, $query)) {
            if (mysqli_affected_rows($conn) > 0) {
                echo '<div class="alert alert-success">Order status updated to ' . htmlspecialchars($status) . '!</div>';
            } else {
                echo '<div class="alert alert-warning">Order not found or no changes made.</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Database error: ' . mysqli_error($conn) . '</div>';
        }
    }
}

// Fetch all orders
$result = mysqli_query($conn, "
    SELECT o.id, o.total, o.status, o.created_at, u.username, p.name, oi.quantity, oi.sell_rate
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
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
    $orders[$row['id']]['username'] = $row['username'];
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
    <h2>Manage Orders</h2>
    <?php if (empty($orders)): ?>
        <p>No orders placed yet.</p>
    <?php else: ?>
        <?php foreach ($orders as $order_id => $order): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Order #<?php echo $order_id; ?> (<?php echo htmlspecialchars($order['status']); ?>)</h5>
                    <p class="card-text">User: <?php echo htmlspecialchars($order['username']); ?></p>
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
                    <form method="POST" onsubmit="return confirm('Update status for Order #<?php echo $order_id; ?>?');">
                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                        <div class="mb-3">
                            <label for="status<?php echo $order_id; ?>" class="form-label">Status</label>
                            <select name="status" id="status<?php echo $order_id; ?>" class="form-select" required>
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
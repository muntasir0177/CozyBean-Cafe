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

// Fetch daily sales
$daily_sales_result = mysqli_query($conn, "
    SELECT DATE(o.created_at) as date, SUM(oi.quantity * oi.sell_rate) as total_sales
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.status = 'completed'
    GROUP BY DATE(o.created_at)
    ORDER BY date DESC
");

if (!$daily_sales_result) {
    echo '<div class="alert alert-danger">Error fetching daily sales: ' . mysqli_error($conn) . '</div>';
    exit;
}
$daily_sales = mysqli_fetch_all($daily_sales_result, MYSQLI_ASSOC);

// Fetch recent orders (last 10)
$orders_result = mysqli_query($conn, "
    SELECT o.id, o.total, o.status, o.created_at, u.username
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 10
");

if (!$orders_result) {
    echo '<div class="alert alert-danger">Error fetching orders: ' . mysqli_error($conn) . '</div>';
    exit;
}
$orders = mysqli_fetch_all($orders_result, MYSQLI_ASSOC);

// Fetch profit summaries
$today_profit_result = mysqli_query($conn, "
    SELECT SUM(oi.quantity * (oi.sell_rate - p.purchase_rate)) as profit
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.status = 'completed' AND DATE(o.created_at) = CURDATE()
");
$today_profit = mysqli_fetch_assoc($today_profit_result)['profit'] ?? 0;

$month_profit_result = mysqli_query($conn, "
    SELECT SUM(oi.quantity * (oi.sell_rate - p.purchase_rate)) as profit
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.status = 'completed' AND YEAR(o.created_at) = YEAR(CURDATE()) AND MONTH(o.created_at) = MONTH(CURDATE())
");
$month_profit = mysqli_fetch_assoc($month_profit_result)['profit'] ?? 0;

$year_profit_result = mysqli_query($conn, "
    SELECT SUM(oi.quantity * (oi.sell_rate - p.purchase_rate)) as profit
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.status = 'completed' AND YEAR(o.created_at) = YEAR(CURDATE())
");
$year_profit = mysqli_fetch_assoc($year_profit_result)['profit'] ?? 0;

// Debug: Uncomment to diagnose
// echo '<pre>Session: ' . print_r($_SESSION, true) . '</pre>';
// echo '<pre>Orders: ' . print_r($orders, true) . '</pre>';
// echo '<pre>Daily Sales: ' . print_r($daily_sales, true) . '</pre>';
?>
<div class="container my-5">
    <h2>Admin Dashboard</h2>
    
    <h4>Daily Sales</h4>
    <?php if (empty($daily_sales)): ?>
        <p>No sales recorded.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Total Sales</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daily_sales as $sale): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sale['date']); ?></td>
                        <td><?php echo format_currency($sale['total_sales']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <h4 class="mt-4">Profit Summary</h4>
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Today's Profit</h5>
                    <p class="card-text"><?php echo format_currency($today_profit); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">This Month's Profit</h5>
                    <p class="card-text"><?php echo format_currency($month_profit); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">This Year's Profit</h5>
                    <p class="card-text"><?php echo format_currency($year_profit); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <h4 class="mt-4">Recent Orders</h4>
    <?php if (empty($orders)): ?>
        <p>No orders placed yet.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>User</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                        <td><?php echo format_currency($order['total']); ?></td>
                        <td><?php echo htmlspecialchars($order['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Update status for Order #<?php echo $order['id']; ?>?');">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" class="form-select d-inline w-auto" required>
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
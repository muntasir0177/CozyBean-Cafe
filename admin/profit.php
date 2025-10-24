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

// Fetch daily profits
$daily_result = mysqli_query($conn, "
    SELECT DATE(o.created_at) as date, SUM(oi.quantity * (oi.sell_rate - p.purchase_rate)) as profit
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.status = 'completed'
    GROUP BY DATE(o.created_at)
    ORDER BY date DESC
");

if (!$daily_result) {
    echo '<div class="alert alert-danger">Error fetching daily profits: ' . mysqli_error($conn) . '</div>';
    exit;
}
$daily_profits = mysqli_fetch_all($daily_result, MYSQLI_ASSOC);

// Fetch monthly profits
$monthly_result = mysqli_query($conn, "
    SELECT DATE_FORMAT(o.created_at, '%Y-%m') as month, SUM(oi.quantity * (oi.sell_rate - p.purchase_rate)) as profit
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.status = 'completed'
    GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
    ORDER BY month DESC
");

if (!$monthly_result) {
    echo '<div class="alert alert-danger">Error fetching monthly profits: ' . mysqli_error($conn) . '</div>';
    exit;
}
$monthly_profits = mysqli_fetch_all($monthly_result, MYSQLI_ASSOC);

// Fetch yearly profits
$yearly_result = mysqli_query($conn, "
    SELECT YEAR(o.created_at) as year, SUM(oi.quantity * (oi.sell_rate - p.purchase_rate)) as profit
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.status = 'completed'
    GROUP BY YEAR(o.created_at)
    ORDER BY year DESC
");

if (!$yearly_result) {
    echo '<div class="alert alert-danger">Error fetching yearly profits: ' . mysqli_error($conn) . '</div>';
    exit;
}
$yearly_profits = mysqli_fetch_all($yearly_result, MYSQLI_ASSOC);

// Debug: Uncomment to diagnose
// echo '<pre>Daily: ' . print_r($daily_profits, true) . '</pre>';
// echo '<pre>Monthly: ' . print_r($monthly_profits, true) . '</pre>';
// echo '<pre>Yearly: ' . print_r($yearly_profits, true) . '</pre>';
?>
<div class="container my-5">
    <h2>Profit List</h2>
    
    <h4>Daily Profits</h4>
    <?php if (empty($daily_profits)): ?>
        <p>No daily profits recorded.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Profit</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($daily_profits as $profit): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($profit['date']); ?></td>
                        <td><?php echo format_currency($profit['profit']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <h4>Monthly Profits</h4>
    <?php if (empty($monthly_profits)): ?>
        <p>No monthly profits recorded.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Profit</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monthly_profits as $profit): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($profit['month']); ?></td>
                        <td><?php echo format_currency($profit['profit']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <h4>Yearly Profits</h4>
    <?php if (empty($yearly_profits)): ?>
        <p>No yearly profits recorded.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Year</th>
                    <th>Profit</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($yearly_profits as $profit): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($profit['year']); ?></td>
                        <td><?php echo format_currency($profit['profit']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
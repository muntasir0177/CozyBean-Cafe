<?php
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

// Fetch canceled orders
$result = mysqli_query($conn, "
    SELECT o.id, o.total, o.created_at, u.username, p.name, oi.quantity, oi.sell_rate
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.status = 'cancelled'
    ORDER BY o.created_at DESC
");
$returns = [];
while ($row = mysqli_fetch_assoc($result)) {
    $returns[$row['id']]['total'] = $row['total'];
    $returns[$row['id']]['username'] = $row['username'];
    $returns[$row['id']]['created_at'] = $row['created_at'];
    $returns[$row['id']]['items'][] = [
        'name' => $row['name'],
        'quantity' => $row['quantity'],
        'sell_rate' => $row['sell_rate']
    ];
}
?>
<div class="container my-5">
    <h2>Return List</h2>
    <?php if (empty($returns)): ?>
        <p>No canceled orders.</p>
    <?php else: ?>
        <?php foreach ($returns as $order_id => $return): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Order #<?php echo $order_id; ?> (Cancelled)</h5>
                    <p class="card-text">User: <?php echo htmlspecialchars($return['username']); ?></p>
                    <p class="card-text">Placed on: <?php echo $return['created_at']; ?></p>
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
                            <?php foreach ($return['items'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo format_currency($item['sell_rate']); ?></td>
                                    <td><?php echo format_currency($item['sell_rate'] * $item['quantity']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p><strong>Total Refunded: <?php echo format_currency($return['total']); ?></strong></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
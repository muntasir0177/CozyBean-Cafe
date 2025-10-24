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

// Handle add product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $purchase_rate = (float)$_POST['purchase_rate'];
    $sell_rate = (float)$_POST['sell_rate'];
    
    // Handle file upload
    $image_filename = 'latte.jpg'; // Default image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/';
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Validate file extension
        if (in_array($file_extension, $allowed_extensions)) {
            // Generate unique filename
            $image_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $image_filename;
            
            // Move uploaded file
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                echo '<div class="alert alert-danger">Error uploading image file.</div>';
                $image_filename = 'latte.jpg'; // Fallback to default
            }
        } else {
            echo '<div class="alert alert-danger">Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.</div>';
        }
    }
    
    $image = mysqli_real_escape_string($conn, $image_filename);
    
    if ($purchase_rate >= $sell_rate) {
        echo '<div class="alert alert-danger">Sell rate must be greater than purchase rate!</div>';
    } elseif ($purchase_rate <= 0 || $sell_rate <= 0) {
        echo '<div class="alert alert-danger">Rates must be positive!</div>';
    } else {
        $query = "INSERT INTO products (name, description, purchase_rate, sell_rate, image, is_active) 
                  VALUES ('$name', '$description', '$purchase_rate', '$sell_rate', '$image', 1)";
        if (mysqli_query($conn, $query)) {
            echo '<div class="alert alert-success">Product added successfully!</div>';
        } else {
            echo '<div class="alert alert-danger">Error adding product: ' . mysqli_error($conn) . '</div>';
        }
    }
}

// Handle edit product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $id = (int)$_POST['id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $purchase_rate = (float)$_POST['purchase_rate'];
    $sell_rate = (float)$_POST['sell_rate'];
    
    // Handle file upload for edit
    $image_filename = mysqli_real_escape_string($conn, $_POST['current_image']); // Default to current image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/';
        $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Validate file extension
        if (in_array($file_extension, $allowed_extensions)) {
            // Generate unique filename
            $image_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $image_filename;
            
            // Move uploaded file
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                echo '<div class="alert alert-danger">Error uploading image file.</div>';
                $image_filename = mysqli_real_escape_string($conn, $_POST['current_image']); // Keep current image
            }
        } else {
            echo '<div class="alert alert-danger">Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.</div>';
        }
    }
    
    $image = mysqli_real_escape_string($conn, $image_filename);
    
    if ($purchase_rate >= $sell_rate) {
        echo '<div class="alert alert-danger">Sell rate must be greater than purchase rate!</div>';
    } elseif ($purchase_rate <= 0 || $sell_rate <= 0) {
        echo '<div class="alert alert-danger">Rates must be positive!</div>';
    } else {
        $query = "UPDATE products SET 
                  name = '$name', 
                  description = '$description', 
                  purchase_rate = '$purchase_rate', 
                  sell_rate = '$sell_rate', 
                  image = '$image' 
                  WHERE id = '$id' AND is_active = 1";
        if (mysqli_query($conn, $query)) {
            if (mysqli_affected_rows($conn) > 0) {
                echo '<div class="alert alert-success">Product updated successfully!</div>';
            } else {
                echo '<div class="alert alert-warning">No product found or no changes made.</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Error updating product: ' . mysqli_error($conn) . '</div>';
        }
    }
}

// Handle delete product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $id = (int)$_POST['id'];
    
    // Check for active orders
    $check = mysqli_query($conn, "SELECT COUNT(*) as count FROM order_items WHERE product_id = '$id'");
    $count = mysqli_fetch_assoc($check)['count'];
    
    if ($count > 0) {
        echo '<div class="alert alert-danger">Cannot delete product; it is part of existing orders!</div>';
    } else {
        $query = "UPDATE products SET is_active = 0 WHERE id = '$id'";
        if (mysqli_query($conn, $query)) {
            if (mysqli_affected_rows($conn) > 0) {
                echo '<div class="alert alert-success">Product deleted successfully!</div>';
            } else {
                echo '<div class="alert alert-warning">No product found.</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Error deleting product: ' . mysqli_error($conn) . '</div>';
        }
    }
}

// Pagination setup
$products_per_page = 7; // Number of products per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1

// Get total number of products
$total_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM products WHERE is_active = 1");
$total_row = mysqli_fetch_assoc($total_result);
$total_products = $total_row['total'];
$total_pages = ceil($total_products / $products_per_page);

// Calculate offset for LIMIT clause
$offset = ($page - 1) * $products_per_page;

// Fetch products for current page
$result = mysqli_query($conn, "SELECT * FROM products WHERE is_active = 1 LIMIT $products_per_page OFFSET $offset");
if (!$result) {
    echo '<div class="alert alert-danger">Error fetching products: ' . mysqli_error($conn) . '</div>';
    exit;
}
$products = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<div class="container my-5">
    <h2>Manage Products</h2>
    
    <h4>Add New Product</h4>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="add_product" value="1">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label for="purchase_rate" class="form-label">Purchase Rate</label>
            <input type="number" name="purchase_rate" class="form-control" step="0.01" required>
        </div>
        <div class="mb-3">
            <label for="sell_rate" class="form-label">Sell Rate</label>
            <input type="number" name="sell_rate" class="form-control" step="0.01" required>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Product Image</label>
            <input type="file" name="image" class="form-control" accept="image/*">
            <div class="form-text">Allowed formats: JPG, JPEG, PNG, GIF. Leave empty to use default image.</div>
        </div>
        <button type="submit" class="btn btn-primary">Add Product</button>
    </form>
    
    <h4>Existing Products</h4>
    <?php if (empty($products)): ?>
        <p>No products available.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Purchase Rate</th>
                    <th>Sell Rate</th>
                    <th>Profit</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . (strlen($product['description']) > 50 ? '...' : ''); ?></td>
                        <td><?php echo format_currency($product['purchase_rate']); ?></td>
                        <td><?php echo format_currency($product['sell_rate']); ?></td>
                        <td><?php echo format_currency($product['sell_rate'] - $product['purchase_rate']); ?></td>
                        <td>
                            <img src="<?php echo '../assets/images/' . htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" width="50" onerror="this.src='../assets/images/latte.jpg';">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $product['id']; ?>">Edit</button>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete <?php echo htmlspecialchars($product['name']); ?>?');">
                                <input type="hidden" name="delete_product" value="1">
                                <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <!-- Edit Modal -->
                    <div class="modal fade" id="editModal<?php echo $product['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $product['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editModalLabel<?php echo $product['id']; ?>">Edit <?php echo htmlspecialchars($product['name']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="edit_product" value="1">
                                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image']); ?>">
                                        <div class="mb-3">
                                            <label for="name<?php echo $product['id']; ?>" class="form-label">Name</label>
                                            <input type="text" name="name" id="name<?php echo $product['id']; ?>" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="description<?php echo $product['id']; ?>" class="form-label">Description</label>
                                            <textarea name="description" id="description<?php echo $product['id']; ?>" class="form-control"><?php echo htmlspecialchars($product['description']); ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="purchase_rate<?php echo $product['id']; ?>" class="form-label">Purchase Rate</label>
                                            <input type="number" name="purchase_rate" id="purchase_rate<?php echo $product['id']; ?>" class="form-control" step="0.01" value="<?php echo $product['purchase_rate']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="sell_rate<?php echo $product['id']; ?>" class="form-label">Sell Rate</label>
                                            <input type="number" name="sell_rate" id="sell_rate<?php echo $product['id']; ?>" class="form-control" step="0.01" value="<?php echo $product['sell_rate']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="image<?php echo $product['id']; ?>" class="form-label">Product Image</label>
                                            <input type="file" name="image" id="image<?php echo $product['id']; ?>" class="form-control" accept="image/*">
                                            <div class="form-text">Current image: <?php echo htmlspecialchars($product['image']); ?></div>
                                            <div class="form-text">Upload a new image to replace the current one, or leave empty to keep the current image.</div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Product pagination">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="text-center">
                <p>Page <?php echo $page; ?> of <?php echo $total_pages; ?> (Total <?php echo $total_products; ?> products)</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php include '../includes/footer.php'; ?>
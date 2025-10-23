<?php
include 'includes/db.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    $result = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
    if (!$result) {
        echo '<div class="alert alert-danger">Database error: ' . mysqli_error($conn) . '</div>';
    } else {
        $user = mysqli_fetch_assoc($result);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            echo '<div class="alert alert-success">Login successful! Redirecting...</div>';
            if ($user['role'] === 'admin') {
                header('Location: ' . SITE_URL . '/admin/dashboard.php');
            } else {
                header('Location: ' . SITE_URL . '/index.php');
            }
            exit;
        } else {
            echo '<div class="alert alert-danger">Invalid username or password!</div>';
        }
    }
}
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center">Login</h2>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                        <p class="mt-3 text-center">Don't have an account? <a href="register.php">Register</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
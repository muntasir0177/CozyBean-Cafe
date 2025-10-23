<?php
include 'includes/db.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $query = "INSERT INTO users (username, password, role, email) VALUES ('$username', '$password', 'customer', '$email')";
    if (mysqli_query($conn, $query)) {
        echo '<div class="alert alert-success">Registration successful! Please <a href="login.php">login</a>.</div>';
    } else {
        echo '<div class="alert alert-danger">Error: ' . mysqli_error($conn) . '</div>';
    }
}
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center">Register</h2>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                        <p class="mt-3 text-center">Already have an account? <a href="login.php">Login</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
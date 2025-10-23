<?php
// More robust session handling for cPanel
include 'includes/header.php';

// Destroy all session data
$_SESSION = array();

// Delete session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to home page
echo '<div class="alert alert-success">Logged out successfully!</div>';
header('Location: ' . SITE_URL . '/index.php');
exit;
?>
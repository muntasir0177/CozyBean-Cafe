<?php
// Simplified SITE_URL configuration for XAMPP default setup
if (!defined('SITE_URL')) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    // For XAMPP default setup, no path prefix needed
    define('SITE_URL', $protocol . '://' . $host);
}
if (!defined('IMAGE_PATH')) {
    define('IMAGE_PATH', SITE_URL . '/coffeeshop/assets/images/');
}
?>
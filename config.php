<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hostel_management');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_URL', 'http://localhost/Hostel/');

// Razorpay Keys
define('RAZORPAY_KEY_ID',     'rzp_test_SWHcqK4BhgJt6Z');
define('RAZORPAY_KEY_SECRET', 'GW1POJdvPrRAFQ5t8Qa7VEOT');

// Razorpay SDK - no Composer needed
require_once __DIR__ . '/razorpay/Razorpay.php';
?>

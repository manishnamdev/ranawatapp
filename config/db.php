<?php
date_default_timezone_set("Asia/Kolkata");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if running on localhost
if (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false || strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false) {
    // Local database credentials
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "rankawat";
} else {
    // Production database credentials
    $host = "localhost"; // Usually localhost for shared hosting
    $user = "u609302395_rankaapp";
    $pass = "H;XlGc8D0je3";
    $dbname = "u609302395_rankaapp";
}

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed");
}
$conn->set_charset("utf8mb4");
$conn->query("SET time_zone = '+05:30'");

require_once __DIR__ . '/../init_db.php';
initialize_database($conn);

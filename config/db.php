<?php
date_default_timezone_set("Asia/Kolkata");

// Check if running on localhost
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
    // Local database credentials
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "ranka";
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

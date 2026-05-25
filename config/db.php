<?php
date_default_timezone_set("Asia/Kolkata");

$conn = new mysqli("localhost", "root", "", "ranka");

if ($conn->connect_error) {
    die("Database Connection Failed");
}
$conn->set_charset("utf8mb4");
$conn->query("SET time_zone = '+05:30'");

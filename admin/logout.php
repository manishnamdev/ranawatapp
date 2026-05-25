<?php
session_start();
include "../config/db.php";

if (isset($_SESSION['admin_id'])) {

    $admin_id = $_SESSION['admin_id'];

    $conn->query("
        UPDATE admin_login_logs 
        SET logout_time = NOW()
        WHERE admin_id = $admin_id
        ORDER BY id DESC
        LIMIT 1
    ");
}

session_destroy();
header("Location: login.php");
exit;

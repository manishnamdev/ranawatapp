<?php
session_start();
include "../config/db.php";

$username = trim($_POST['username']);
$password = $_POST['password'];

$stmt = $conn->prepare("SELECT id, password, status FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['login_error'] = "गलत यूज़रनेम या पासवर्ड";
    header("Location: login.php");
    exit;
}

$admin = $result->fetch_assoc();

if ($admin['status'] != 'active') {
    $_SESSION['login_error'] = "आपका अकाउंट निष्क्रिय है";
    header("Location: login.php");
    exit;
}

if (!password_verify($password, $admin['password'])) {
    $_SESSION['login_error'] = "गलत यूज़रनेम या पासवर्ड";
    header("Location: login.php");
    exit;
}

/* ✅ LOGIN SUCCESS */
$_SESSION['admin_id'] = $admin['id'];

// login log
$ip = $_SERVER['REMOTE_ADDR'];
$user_agent = $_SERVER['HTTP_USER_AGENT'];

$log = $conn->prepare("
    INSERT INTO admin_login_logs (admin_id, login_time, ip_address, user_agent)
    VALUES (?, NOW(), ?, ?)
");
$log->bind_param("iss", $admin['id'], $ip, $user_agent);
$log->execute();

header("Location: dashboard.php");
exit;

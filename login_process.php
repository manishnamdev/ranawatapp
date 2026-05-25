<?php
session_start();
include "config/db.php";
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['mobile']) || empty($_POST['pin'])) {
    header("Location: login.php");
    exit;
}

$mobile = preg_replace('/\D/', '', $_POST['mobile']);
$mobile = trim($mobile);
$pin    = $_POST['pin'];

$stmt = $conn->prepare("
    SELECT id, pin, status, name 
    FROM members 
    WHERE mobile = ?
");
$stmt->bind_param("s", $mobile);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['login_error'] = "गलत मोबाइल नंबर या लॉगिन पिन";
    header("Location: login.php");
    exit;
}

$member = $result->fetch_assoc();

if (!password_verify($pin, $member['pin'])) {
    $_SESSION['login_error'] = "गलत मोबाइल नंबर या लॉगिन पिन";
    header("Location: login.php");
    exit;
}

// rejected case
if ($member['status'] == 'rejected') {
    $_SESSION['login_error'] = "आपकी प्रोफ़ाइल अस्वीकृत कर दी गई है। कृपया समिति से संपर्क करें।";
    header("Location: login.php");
    exit;
}

// success
$_SESSION['member_id']   = $member['id'];
$_SESSION['member_name'] = $member['name'];

if ($member['status'] == 'pending') {
    header("Location: pending.php");
} else {
    header("Location: dashboard.php");
}
exit;

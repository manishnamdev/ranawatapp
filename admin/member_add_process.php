<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$name   = $_POST['name'] ?? '';
$mobile = trim($_POST['mobile'] ?? '');
$pin    = $_POST['pin'] ?? '';
$nivasi = $_POST['nivasi'] ?? '';
$gotra  = $_POST['gotra'] ?? '';
$whatsapp_number = trim($_POST['whatsapp_number'] ?? '');

$is_verified = isset($_POST['is_verified']) ? (int)$_POST['is_verified'] : 0;
$is_canvote  = isset($_POST['is_canvote'])  ? (int)$_POST['is_canvote']  : 0;

// AUTO MOBILE if empty
if ($mobile === '') {
    $mobile = substr(time(), -10);
}

// check primary mobile unique (only the main mobile must be unique)
$check = $conn->prepare("SELECT id FROM members WHERE mobile=?");
$check->bind_param("s", $mobile);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    $_SESSION['flash_msg'] = "मोबाइल नंबर पहले से मौजूद है";
    $_SESSION['flash_type'] = "danger";
    header("Location: dashboard.php");
    exit;
}
// NOTE: whatsapp_number (alternative mobile) is intentionally NOT checked for
// uniqueness. A family member's number or an existing member's number is allowed
// as the alternative contact number.

$hashed_pin = password_hash($pin, PASSWORD_DEFAULT);

// ✅ CORRECT INSERT QUERY
$stmt = $conn->prepare("
    INSERT INTO members
    (name, mobile, whatsapp_number, pin, nivasi, gotra, status, is_verified, is_canvote)
    VALUES (?,?,?,?,?,?,'approved',?,?)
");

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// ✅ CORRECT bind_param (7 strings + 2 ints)
$stmt->bind_param(
    "ssssssii",
    $name,
    $mobile,
    $whatsapp_number,
    $hashed_pin,
    $nivasi,
    $gotra,
    $is_verified,
    $is_canvote
);

if ($stmt->execute()) {

    $_SESSION['flash_msg'] = "सदस्य सफलतापूर्वक जोड़ा गया";
    $_SESSION['flash_type'] = "success";
    header("Location: dashboard.php");
    exit;
} else {
    $_SESSION['flash_msg'] = "कोई अन्य समस्या है मनीष से बात करो। ";
    $_SESSION['flash_type'] = "danger";
    header("Location: dashboard.php");
    exit;

}

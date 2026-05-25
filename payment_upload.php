<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['member_id'];

if (!isset($_FILES['payment_screenshot'])) {
    header("Location: payment.php");
    exit;
}

$folder = "uploads/payments/";
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

$filename = time() . "_" . $_FILES['payment_screenshot']['name'];
$path = $folder . $filename;

move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], $path);

$stmt = $conn->prepare("
    UPDATE members 
    SET payment_screenshot=?, payment_status='uploaded'
    WHERE id=?
");
$stmt->bind_param("si", $path, $id);
$stmt->execute();

header("Location: pending.php");
exit;

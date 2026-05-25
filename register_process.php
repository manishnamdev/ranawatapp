<?php
session_start();
include "config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['mobile']) || empty($_POST['pin'])) {
    header("Location: register.php");
    exit;
}

$name = trim($_POST['name']);
$nivasi = trim($_POST['nivasi']);
$gotra = $_POST['gotra'];
$mobile = trim($_POST['mobile']);
$pin = $_POST['pin'];
$security_question = $_POST['security_question'];
$security_answer = trim($_POST['security_answer']);

// duplicate mobile check
$check = $conn->prepare("SELECT id FROM members WHERE mobile=?");
$check->bind_param("s", $mobile);
$check->execute();
$check->store_result();
$enct = base64_encode($mobile);
if ($check->num_rows > 0) {
    header("Location: info.php?type=exists&m=$enct");
    exit;
}

$hashed_pin = password_hash($pin, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
INSERT INTO members
(name,nivasi,gotra,mobile,pin,security_question,security_answer)
VALUES (?,?,?,?,?,?,?)
");

$stmt->bind_param(
    "sssssss",
    $name,$nivasi,$gotra,
    $mobile,$hashed_pin,
    $security_question,$security_answer
);

if ($stmt->execute()) {
    $_SESSION['member_id'] = $conn->insert_id;
   header("Location: payment.php");
} else {
    header("Location: info.php?type=error");
}
exit;

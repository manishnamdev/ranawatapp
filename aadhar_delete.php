<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$memberId = (int) $_SESSION['member_id'];
$side = $_GET['side'] ?? '';
$columnMap = [
    'front' => 'aadhar_front',
    'back' => 'aadhar_back',
];

if (!isset($columnMap[$side])) {
    $_SESSION['aadhar_flash'] = ['type' => 'danger', 'message' => 'Invalid Aadhaar side selected.'];
    header("Location: pending.php");
    exit;
}

$stmt = $conn->prepare("SELECT status, aadhar_front, aadhar_back FROM members WHERE id = ?");
$stmt->bind_param("i", $memberId);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();

if (!$member) {
    $_SESSION['aadhar_flash'] = ['type' => 'danger', 'message' => 'Member not found.'];
    header("Location: login.php");
    exit;
}

$column = $columnMap[$side];
$existingFile = $member[$column] ?? '';

if (empty($existingFile)) {
    $_SESSION['aadhar_flash'] = ['type' => 'warning', 'message' => 'No Aadhaar image found to delete.'];
    header("Location: member_documents.php");
    exit;
}

$update = $conn->prepare("UPDATE members SET {$column} = NULL WHERE id = ?");
$update->bind_param("i", $memberId);
$update->execute();

$path = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "aadhar" . DIRECTORY_SEPARATOR . basename($existingFile);
if (is_file($path)) {
    unlink($path);
}

$_SESSION['aadhar_flash'] = ['type' => 'success', 'message' => ucfirst($side) . ' Aadhaar image deleted.'];
header("Location: member_documents.php");
exit;

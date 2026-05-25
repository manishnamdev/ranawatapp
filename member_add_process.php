<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$id || !$action) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $conn->prepare("UPDATE members SET status=? WHERE id=?");
$stmt->bind_param("si", $action, $id);
$stmt->execute();

// approval log
$log = $conn->prepare("
INSERT INTO member_approval_logs (member_id, admin_id, action)
VALUES (?,?,?)
");
$log->bind_param("iis", $id, $_SESSION['admin_id'], $action);
$log->execute();

header("Location: dashboard.php");
exit;

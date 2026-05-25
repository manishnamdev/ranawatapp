<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id']);
$action = $_GET['action'] ?? '';

if ($action == 'enable') {

    $stmt = $conn->prepare("UPDATE members SET is_canvote = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $_SESSION['flash_msg'] = "सदस्य को मतदान के लिए सक्षम कर दिया गया है";
    $_SESSION['flash_type'] = "success";
}

header("Location: dashboard.php");
exit;
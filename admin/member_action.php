<?php
session_start();
include "../config/db.php";
require_once dirname(__DIR__) . "/includes/push_helpers.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$action = $_GET['action'] ?? null;

if (!$id || !$action) {
    header("Location: dashboard.php");
    exit;
}

$memberStmt = $conn->prepare("SELECT id, name FROM members WHERE id = ?");
$memberStmt->bind_param("i", $id);
$memberStmt->execute();
$member = $memberStmt->get_result()->fetch_assoc();

if (!$member) {
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

if ($action === 'approved') {
    $memberName = trim((string) ($member['name'] ?? ''));
    $title = 'प्रोफाइल स्वीकृत';
    $body = $memberName !== ''
        ? $memberName . ' जी, आपकी सदस्य प्रोफाइल स्वीकृत हो गई है। अधिक जानकारी के लिए डैशबोर्ड देखें।'
        : 'आपकी सदस्य प्रोफाइल स्वीकृत हो गई है। अधिक जानकारी के लिए डैशबोर्ड देखें।';

    sendPushNotificationToMembers($conn, [$id], $title, $body, getAppBasePath() . '/dashboard.php');
}

header("Location: dashboard.php");
exit;

<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$member_id = $_SESSION['member_id'];
$whatsapp_number = isset($_POST['whatsapp_number']) ? trim($_POST['whatsapp_number']) : '';

// Basic validation: must be numeric if not empty.
// NOTE: uniqueness is NOT enforced on whatsapp_number — a family member's
// number or an existing member's primary/alt mobile is allowed here.
if (!empty($whatsapp_number) && !is_numeric($whatsapp_number)) {
    $_SESSION['flash_msg'] = "व्हाट्सएप नंबर केवल अंकों में होना चाहिए";
    $_SESSION['flash_type'] = "danger";
    header("Location: dashboard.php");
    exit;
}

$stmt = $conn->prepare("UPDATE members SET whatsapp_number = ? WHERE id = ?");
$stmt->bind_param("si", $whatsapp_number, $member_id);

if ($stmt->execute()) {
    $_SESSION['flash_msg'] = "प्रोफ़ाइल सफलतापूर्वक अपडेट की गई";
    $_SESSION['flash_type'] = "success";
} else {
    $_SESSION['flash_msg'] = "अपडेट करने में विफल";
    $_SESSION['flash_type'] = "danger";
}

header("Location: dashboard.php");
exit;

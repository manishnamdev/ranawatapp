<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include __DIR__ . "/../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    die("Admin not logged in");
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid member id");
}

/* MEMBER FETCH */
$stmt = $conn->prepare("SELECT id, name, mobile, whatsapp_number FROM members WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Member not found");
}

$m = $result->fetch_assoc();
$name = $m['name'];
$mobile = $m['mobile'];
$whatsapp_number = $m['whatsapp_number'];

/* 🔐 RANDOM 4 DIGIT PIN GENERATE */
$plainPin = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

/* OPTIONAL: HASH PIN (Recommended for Security) */
$hashedPin = password_hash($plainPin, PASSWORD_DEFAULT);

/* 📌 UPDATE PIN IN DATABASE */
$update = $conn->prepare("UPDATE members SET pin = ? WHERE id = ?");
$update->bind_param("si", $hashedPin, $id);
$update->execute();

/* 📲 HINDI WHATSAPP MESSAGE */
$message = "सम्माननीय समाज बंधु $name,\n\n";
$message .= "आपके पोर्टल लॉगिन की जानकारी नीचे दी जा रही है —\n\n";
$message .= "लॉगिन लिंक:\n";
$message .= "https://www.rankawatsamajrani.com/\n\n";
$message .= "यूज़र आईडी (Login ID):\n$mobile\n\n";
$message .= "लॉगिन पिन (PIN):\n$plainPin\n\n";
$message .= "कृपया अपने लॉगिन पिन को गोपनीय रखें एवं किसी के साथ साझा न करें।\n\n";
$message .= "— एडमिन टीम";

/* WHATSAPP REDIRECT */
$targetNumber = !empty($whatsapp_number) ? $whatsapp_number : $mobile;
$waUrl = "https://wa.me/91$targetNumber?text=" . urlencode($message);
header("Location: $waUrl");
exit;
<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: family_add.php");
    exit;
}

$member_id = $_SESSION['member_id'];
$name = trim($_POST['name'] ?? '');
$relation = trim($_POST['relation'] ?? '');
$birth_year = !empty($_POST['birth_year']) ? (int) $_POST['birth_year'] : null;
$marital_status = trim($_POST['marital_status'] ?? '');
$gotra = trim($_POST['gotra'] ?? '');
$current_location = trim($_POST['current_location'] ?? '');
$education = trim($_POST['education'] ?? '');

if (empty($name) || empty($relation) || empty($gotra)) {
    $_SESSION['family_error'] = "नाम, रिश्ता और गोत्र अनिवार्य हैं।";
    header("Location: family_add.php");
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO family_members 
    (member_id, name, relation, birth_year, marital_status, gotra, current_location, education)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param("isssssss", 
    $member_id, 
    $name, 
    $relation, 
    $birth_year, 
    $marital_status, 
    $gotra, 
    $current_location, 
    $education
);

if ($stmt->execute()) {
    $_SESSION['flash_msg'] = "परिवार के सदस्य को सफलतापूर्वक जोड़ दिया गया है!";
    $_SESSION['flash_type'] = "success";
    header("Location: dashboard.php");
} else {
    $_SESSION['family_error'] = "तकनीकी त्रुटि। कृपया पुनः प्रयास करें।";
    header("Location: family_add.php");
}
exit;
?>

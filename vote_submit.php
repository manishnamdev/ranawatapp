<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$member_id = $_SESSION['member_id'];
$poll_id = isset($_POST['poll_id']) ? (int)$_POST['poll_id'] : 0;
$poll_option_id = isset($_POST['poll_option_id']) ? (int)$_POST['poll_option_id'] : 0;

if ($poll_id == 0 || $poll_option_id == 0) {
    die("Invalid vote data.");
}

// Check if already voted on this poll
$check = $conn->query("SELECT id FROM votes WHERE member_id=$member_id AND poll_id=$poll_id");
if ($check->num_rows > 0) {
    header("Location: dashboard.php");
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO votes (member_id, poll_id, poll_option_id)
    VALUES (?, ?, ?)
");
$stmt->bind_param("iii", $member_id, $poll_id, $poll_option_id);
$stmt->execute();

header("Location: dashboard.php");
exit;

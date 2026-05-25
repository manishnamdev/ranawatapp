<?php
session_start();
include "config/db.php";

$member_id = $_SESSION['member_id'];
$vote = $_POST['vote'];

$stmt = $conn->prepare("
    INSERT INTO votes (member_id, vote_option)
    VALUES (?,?)
");
$stmt->bind_param("is", $member_id, $vote);
$stmt->execute();

header("Location: vote_results.php");
exit;

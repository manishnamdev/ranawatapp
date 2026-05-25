<?php
session_start();
include "config/db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['member_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$endpoint = $input['endpoint'] ?? '';

if ($endpoint === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Missing endpoint']);
    exit;
}

$memberId = (int) $_SESSION['member_id'];
$stmt = $conn->prepare("DELETE FROM push_subscriptions WHERE member_id = ? AND endpoint = ?");
$stmt->bind_param("is", $memberId, $endpoint);
$stmt->execute();

echo json_encode(['ok' => true]);

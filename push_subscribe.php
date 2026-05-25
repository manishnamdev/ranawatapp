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

if (!is_array($input) || empty($input['endpoint']) || empty($input['keys']['p256dh']) || empty($input['keys']['auth'])) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Invalid subscription payload']);
    exit;
}

$memberId = (int) $_SESSION['member_id'];
$endpoint = $input['endpoint'];
$p256dh = $input['keys']['p256dh'];
$authToken = $input['keys']['auth'];
$contentEncoding = $input['contentEncoding'] ?? 'aesgcm';
$userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);

$stmt = $conn->prepare("
    INSERT INTO push_subscriptions (member_id, endpoint, p256dh, auth_token, content_encoding, user_agent)
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        p256dh = VALUES(p256dh),
        auth_token = VALUES(auth_token),
        content_encoding = VALUES(content_encoding),
        user_agent = VALUES(user_agent),
        updated_at = CURRENT_TIMESTAMP
");
$stmt->bind_param("isssss", $memberId, $endpoint, $p256dh, $authToken, $contentEncoding, $userAgent);
$stmt->execute();

echo json_encode(['ok' => true]);

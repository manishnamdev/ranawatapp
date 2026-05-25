<?php

function getPushConfig()
{
    static $pushConfig = null;

    if ($pushConfig === null) {
        $configPath = dirname(__DIR__) . "/config/push.php";
        $pushConfig = file_exists($configPath) ? require $configPath : [];
    }

    return $pushConfig;
}

function getAppBasePath()
{
    $scriptDir = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '')));
    return ($scriptDir === '/' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/');
}

function createWebPushClient()
{
    static $webPush = false;

    if ($webPush !== false) {
        return $webPush;
    }

    $autoloadPath = dirname(__DIR__) . "/vendor/autoload.php";
    $pushConfig = getPushConfig();

    if (!file_exists($autoloadPath) || empty($pushConfig['publicKey']) || empty($pushConfig['privateKey']) || empty($pushConfig['subject'])) {
        $webPush = null;
        return $webPush;
    }

    require_once $autoloadPath;

    $webPush = new \Minishlink\WebPush\WebPush([
        'VAPID' => [
            'subject' => $pushConfig['subject'],
            'publicKey' => $pushConfig['publicKey'],
            'privateKey' => $pushConfig['privateKey'],
        ],
    ]);
    $webPush->setReuseVAPIDHeaders(true);

    return $webPush;
}

function sendPushNotificationToMembers(mysqli $conn, array $memberIds, $title, $body, $url = null)
{
    $memberIds = array_values(array_unique(array_filter(array_map('intval', $memberIds))));
    if (!$memberIds) {
        return ['sent' => 0, 'failed' => 0, 'skipped' => true];
    }

    $webPush = createWebPushClient();
    if (!$webPush) {
        return ['sent' => 0, 'failed' => 0, 'skipped' => true];
    }

    if ($url === null || $url === '') {
        $url = getAppBasePath() . '/dashboard.php';
    }

    $appBase = getAppBasePath();
    $payload = json_encode([
        'title' => $title,
        'body' => $body,
        'url' => $url,
        'icon' => $appBase . '/assets/images/banner.jpeg',
        'badge' => $appBase . '/assets/images/banner.jpeg',
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $placeholders = implode(',', array_fill(0, count($memberIds), '?'));
    $types = str_repeat('i', count($memberIds));
    $query = "SELECT endpoint, p256dh, auth_token, content_encoding FROM push_subscriptions WHERE member_id IN ($placeholders)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$memberIds);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $subscription = \Minishlink\WebPush\Subscription::create([
            'endpoint' => $row['endpoint'],
            'publicKey' => $row['p256dh'],
            'authToken' => $row['auth_token'],
            'contentEncoding' => $row['content_encoding'] ?: 'aesgcm',
        ]);
        $webPush->queueNotification($subscription, $payload);
    }

    $sent = 0;
    $failed = 0;

    foreach ($webPush->flush() as $report) {
        if ($report->isSuccess()) {
            $sent++;
            continue;
        }

        $failed++;
        $endpoint = $report->getEndpoint();
        $statusCode = $report->getResponse() ? $report->getResponse()->getStatusCode() : 0;

        if (in_array($statusCode, [404, 410], true)) {
            $delete = $conn->prepare("DELETE FROM push_subscriptions WHERE endpoint = ?");
            $delete->bind_param("s", $endpoint);
            $delete->execute();
        }
    }

    return ['sent' => $sent, 'failed' => $failed, 'skipped' => false];
}

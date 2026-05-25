<?php
session_start();
include "../config/db.php";
require_once dirname(__DIR__) . "/includes/push_helpers.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
$appBase = getAppBasePath();

$flash = null;
$subscriptionCount = (int) $conn->query("SELECT COUNT(*) AS total FROM push_subscriptions")->fetch_assoc()['total'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $url = trim($_POST['target_url'] ?? '');

    if ($title === '' || $body === '') {
        $flash = ['type' => 'danger', 'message' => 'Title and message are required.'];
    } else {
        if ($url === '') {
            $url = $appBase . '/dashboard.php';
        }

        $memberIds = [];
        $result = $conn->query("SELECT DISTINCT member_id FROM push_subscriptions");
        while ($row = $result->fetch_assoc()) {
            $memberIds[] = (int) $row['member_id'];
        }

        $delivery = sendPushNotificationToMembers($conn, $memberIds, $title, $body, $url);
        $sent = (int) $delivery['sent'];
        $failed = (int) $delivery['failed'];

        $subscriptionCount = (int) $conn->query("SELECT COUNT(*) AS total FROM push_subscriptions")->fetch_assoc()['total'];
        $flash = ['type' => 'success', 'message' => "Broadcast sent. Success: {$sent}, Failed: {$failed}."];
    }
}
?>

<?php include "./includes/admin_header.php"; ?>

<div class="container mt-4">
    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']); ?>">
            <?= htmlspecialchars($flash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <h5 class="fw-bold mb-2">Browser Notifications</h5>
            <p class="mb-1">Subscribed browsers: <b><?= $subscriptionCount; ?></b></p>
            <p class="small text-muted mb-0">Notifications are delivered only to users who allowed notifications in their browser.</p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Notification Title</label>
                    <input type="text" name="title" class="form-control" maxlength="120" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Message</label>
                    <textarea name="body" class="form-control" rows="4" maxlength="240" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Open URL</label>
                    <input type="text" name="target_url" class="form-control" placeholder="<?= htmlspecialchars($appBase . '/dashboard.php'); ?>">
                </div>

                <button class="btn btn-primary w-100">Send Broadcast Notification</button>
            </form>
        </div>
    </div>
</div>

<?php include "./includes/admin_footer.php"; ?>

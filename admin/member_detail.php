<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$stmt = $conn->prepare("SELECT * FROM members WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
    <h6 class="fw-bold mb-3">Member Detail</h6>

    <p><b>Name:</b> <?= $member['name']; ?></p>
    <p><b>Nivasi:</b> <?= $member['nivasi']; ?></p>
    <p><b>Avtang:</b> <?= $member['avtang']; ?></p>
    <p><b>Gotra:</b> <?= $member['gotra']; ?></p>
    <p><b>Mobile:</b> <?= $member['mobile']; ?></p>
    <p><b>Status:</b> <?= $member['status']; ?></p>

    <a href="dashboard.php" class="btn btn-secondary btn-sm">Back</a>
</div>
<?php if (!empty($member['aadhar_front']) || !empty($member['aadhar_back'])): ?>
    <hr>
    <h6 class="fw-bold">Aadhaar Images</h6>

    <?php if (!empty($member['aadhar_front'])): ?>
        <p class="mb-2">
            <b>Front:</b>
            <a href="../uploads/aadhar/<?= rawurlencode($member['aadhar_front']); ?>" target="_blank">
                View Front Image
            </a>
        </p>
    <?php else: ?>
        <p class="text-danger small mb-2">Aadhaar front image not uploaded</p>
    <?php endif; ?>

    <?php if (!empty($member['aadhar_back'])): ?>
        <p class="mb-2">
            <b>Back:</b>
            <a href="../uploads/aadhar/<?= rawurlencode($member['aadhar_back']); ?>" target="_blank">
                View Back Image
            </a>
        </p>
    <?php else: ?>
        <p class="text-danger small mb-2">Aadhaar back image not uploaded</p>
    <?php endif; ?>
<?php else: ?>
    <p class="text-danger small">Aadhaar images not uploaded</p>
<?php endif; ?>
<?php if ($member['payment_screenshot']): ?>
    <hr>
    <h6 class="fw-bold">Payment Screenshot</h6>

    <a href="../<?= $member['payment_screenshot']; ?>" target="_blank">
        View Screenshot
    </a>

    <p class="small">
        Payment Status: <b><?= $member['payment_status']; ?></b>
    </p>
<?php else: ?>
    <p class="text-danger small">
        Payment screenshot not uploaded
    </p>
<?php endif; ?>

<?php include "../includes/footer.php"; ?>

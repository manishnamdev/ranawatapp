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

$family_stmt = $conn->prepare("SELECT * FROM family_members WHERE member_id = ? ORDER BY created_at ASC");
$family_stmt->bind_param("i", $id);
$family_stmt->execute();
$family_members = $family_stmt->get_result();
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4 mb-5">
    <h6 class="fw-bold mb-3">Member Detail</h6>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <p class="mb-1"><b>Name:</b> <?= htmlspecialchars($member['name']); ?></p>
            <p class="mb-1"><b>Nivasi:</b> <?= htmlspecialchars($member['nivasi']); ?></p>
            <p class="mb-1"><b>Gotra:</b> <?= htmlspecialchars($member['gotra']); ?></p>
            <p class="mb-1"><b>Mobile:</b> <?= htmlspecialchars($member['mobile']); ?></p>
            <p class="mb-1"><b>Status:</b> <?= htmlspecialchars($member['status']); ?></p>
        </div>
    </div>

    <!-- Family Members -->
    <h6 class="fw-bold mt-4 mb-3">Family Members</h6>
    <?php if ($family_members->num_rows > 0): ?>
        <div class="row g-2">
        <?php while ($fm = $family_members->fetch_assoc()): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border border-primary shadow-sm h-100" style="border-radius: 12px;">
                    <div class="card-body py-2">
                        <h6 class="fw-bold mb-1 text-primary"><?= htmlspecialchars($fm['name']); ?></h6>
                        <small class="text-muted d-block mb-2"><?= htmlspecialchars($fm['relation']); ?></small>
                        <p class="mb-0 small">
                            <b>Birth Year:</b> <?= htmlspecialchars($fm['birth_year']); ?><br>
                            <b>Gotra:</b> <?= htmlspecialchars($fm['gotra']); ?><br>
                            <b>Marital:</b> <?= htmlspecialchars($fm['marital_status']); ?><br>
                            <b>Education:</b> <?= htmlspecialchars($fm['education']); ?><br>
                            <b>Location:</b> <?= htmlspecialchars($fm['current_location']); ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-muted small">No family members added.</p>
    <?php endif; ?>

    <!-- Documents -->
    <h6 class="fw-bold mt-4 mb-3">Documents & Payment</h6>
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <?php if (!empty($member['aadhar_front']) || !empty($member['aadhar_back'])): ?>
                <?php if (!empty($member['aadhar_front'])): ?>
                    <p class="mb-2"><b>Aadhaar Front:</b> <a href="../uploads/aadhar/<?= rawurlencode($member['aadhar_front']); ?>" target="_blank">View Image</a></p>
                <?php else: ?>
                    <p class="text-danger small mb-2">Aadhaar front missing</p>
                <?php endif; ?>

                <?php if (!empty($member['aadhar_back'])): ?>
                    <p class="mb-2"><b>Aadhaar Back:</b> <a href="../uploads/aadhar/<?= rawurlencode($member['aadhar_back']); ?>" target="_blank">View Image</a></p>
                <?php else: ?>
                    <p class="text-danger small mb-2">Aadhaar back missing</p>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-danger small mb-3">Aadhaar images not uploaded</p>
            <?php endif; ?>
            
            <hr>

            <?php if ($member['payment_screenshot']): ?>
                <p class="mb-1"><b>Payment Screenshot:</b> <a href="../<?= htmlspecialchars($member['payment_screenshot']); ?>" target="_blank">View Screenshot</a></p>
                <p class="small mb-0">Status: <b><?= htmlspecialchars($member['payment_status']); ?></b></p>
            <?php else: ?>
                <p class="text-danger small mb-0">Payment screenshot not uploaded</p>
            <?php endif; ?>
        </div>
    </div>

    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
</div>

<?php include "../includes/footer.php"; ?>

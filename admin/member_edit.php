<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../config/db.php";
include "../includes/dropdowns.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;
$member = $conn->query("SELECT * FROM members WHERE id=$id")->fetch_assoc();

if (!$member) {
    die("Member not found");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $conn->prepare("
        UPDATE members SET
            name=?,
            mobile=?,
            nivasi=?,
            avtang=?,
            gotra=?,
            status=?,
            payment_status=?,
            whatsapp_number=?,
            is_verified=?,
            is_canvote=?
        WHERE id=?
    ");

    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $nivasi = $_POST['nivasi'];
    $avtang = $_POST['avtang'];
    $gotra = $_POST['gotra'];
    $status = $_POST['status'];
    $payment_status = $_POST['payment_status'];
    $whatsapp_number = $_POST['whatsapp_number'] ?? '';
    $is_verified = $_POST['is_verified'];
    $is_canvote = $_POST['is_canvote'];

    $stmt->bind_param(
        "ssssssssiii",
        $name,
        $mobile,
        $nivasi,
        $avtang,
        $gotra,
        $status,
        $payment_status,
        $whatsapp_number,
        $is_verified,
        $is_canvote,
        $id
    );

    $stmt->execute();
    header("Location: member_detail.php?id=".$id);
    exit;
}
?>

<?php include "includes/admin_header.php"; ?>

<div class="container mt-4">
    <h6 class="fw-bold mb-3">Edit Member</h6>

    <form method="post">

        <!-- BASIC INFO -->
        <div class="form-floating mb-3">
            <input class="form-control" name="name" value="<?= $member['name']; ?>" required>
            <label>नाम</label>
        </div>

        <div class="form-floating mb-3">
            <input class="form-control" name="mobile" value="<?= $member['mobile']; ?>" required>
            <label>मोबाइल नंबर</label>
        </div>

        <div class="form-floating mb-3">
            <input class="form-control" name="whatsapp_number" value="<?= $member['whatsapp_number']; ?>">
            <label>व्हाट्सएप नंबर (वैकल्पिक)</label>
        </div>

        <!-- NIVASI -->
        <div class="mb-3">
            <label class="form-label">निवासी</label>
            <select name="nivasi" class="form-select" required>
                <?php foreach ($NIVASI_LIST as $n): ?>
                    <option value="<?= $n; ?>" <?= ($member['nivasi']==$n)?'selected':''; ?>>
                        <?= $n; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- AVTANG -->
        <div class="mb-3">
            <label class="form-label">अवटंग</label>
            <select name="avtang" class="form-select" required>
                <?php foreach ($AVTANG_LIST as $a): ?>
                    <option value="<?= $a; ?>" <?= ($member['avtang']==$a)?'selected':''; ?>>
                        <?= $a; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- GOTRA -->
        <div class="mb-3">
            <label class="form-label">गोत्र</label>
            <select name="gotra" class="form-select" required>
                <?php foreach ($GOTRA_LIST as $g): ?>
                    <option value="<?= $g; ?>" <?= ($member['gotra']==$g)?'selected':''; ?>>
                        <?= $g; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <hr>

        <!-- MEMBERSHIP STATUS -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Membership Status</label>
            <select name="status" class="form-select">
                <option value="pending"  <?= ($member['status']=='pending')?'selected':''; ?>>Pending</option>
                <option value="approved" <?= ($member['status']=='approved')?'selected':''; ?>>Approved</option>
                <option value="rejected" <?= ($member['status']=='rejected')?'selected':''; ?>>Rejected</option>
            </select>
        </div>

        <!-- PAYMENT STATUS -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Payment Status</label>
            <select name="payment_status" class="form-select">
                <option value="pending"  <?= ($member['payment_status']=='pending')?'selected':''; ?>>Pending</option>
                <option value="uploaded" <?= ($member['payment_status']=='uploaded')?'selected':''; ?>>Uploaded</option>
                <option value="verified" <?= ($member['payment_status']=='verified')?'selected':''; ?>>Verified</option>
            </select>
        </div>

        <!-- PAYMENT SCREENSHOT -->
        <?php if ($member['payment_screenshot']): ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Payment Screenshot</label><br>
                <a href="../<?= $member['payment_screenshot']; ?>" target="_blank"
                   class="btn btn-outline-secondary btn-sm">
                   View Screenshot
                </a>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label fw-semibold">Aadhaar Front</label><br>
            <?php if (!empty($member['aadhar_front'])): ?>
                <a href="../uploads/aadhar/<?= rawurlencode($member['aadhar_front']); ?>" target="_blank"
                   class="btn btn-outline-secondary btn-sm">
                   View Front
                </a>
            <?php else: ?>
                <span class="text-muted small">Not uploaded</span>
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Aadhaar Back</label><br>
            <?php if (!empty($member['aadhar_back'])): ?>
                <a href="../uploads/aadhar/<?= rawurlencode($member['aadhar_back']); ?>" target="_blank"
                   class="btn btn-outline-secondary btn-sm">
                   View Back
                </a>
            <?php else: ?>
                <span class="text-muted small">Not uploaded</span>
            <?php endif; ?>
        </div>

        <!-- FLAGS -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Verified Member</label><br>
            <label>
                <input type="radio" name="is_verified" value="1"
                       <?= $member['is_verified']?'checked':''; ?>> Yes
            </label>
            &nbsp;&nbsp;
            <label>
                <input type="radio" name="is_verified" value="0"
                       <?= !$member['is_verified']?'checked':''; ?>> No
            </label>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Can Vote</label><br>
            <label>
                <input type="radio" name="is_canvote" value="1"
                       <?= $member['is_canvote']?'checked':''; ?>> Yes
            </label>
            &nbsp;&nbsp;
            <label>
                <input type="radio" name="is_canvote" value="0"
                       <?= !$member['is_canvote']?'checked':''; ?>> No
            </label>
        </div>

        <button class="btn btn-success w-100">
            Update Member
        </button>
    </form>
</div>

<?php include "includes/admin_footer.php"; ?>

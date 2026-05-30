<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../config/db.php";

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
            gotra=?,
            status=?,
            payment_status=?,
            whatsapp_number=?,
            haal_niwas=?,
            vyavsaya=?,
            is_verified=?,
            is_canvote=?
        WHERE id=?
    ");

    $name = $_POST['name'];
    $mobile = $_POST['mobile'];
    $nivasi = $_POST['nivasi'];
    $gotra = $_POST['gotra'];
    $status = $_POST['status'];
    $payment_status = $_POST['payment_status'];
    $whatsapp_number = $_POST['whatsapp_number'] ?? '';
    $haal_niwas = $_POST['haal_niwas'] ?? '';
    $vyavsaya = $_POST['vyavsaya'] ?? '';
    $is_verified = (int)$_POST['is_verified'];
    $is_canvote = (int)$_POST['is_canvote'];

    $stmt->bind_param(
        "sssssssssiii",
        $name,
        $mobile,
        $nivasi,
        $gotra,
        $status,
        $payment_status,
        $whatsapp_number,
        $haal_niwas,
        $vyavsaya,
        $is_verified,
        $is_canvote,
        $id
    );

    if (!$stmt->execute()) {
        die("Error updating: " . $stmt->error);
    }
    
    header("Location: member_detail.php?id=".$id);
    exit;
}

$gotrasQuery = $conn->query("SELECT name FROM gotras ORDER BY name ASC");
$niwasQuery = $conn->query("SELECT name FROM niwas ORDER BY name ASC");
?>

<?php include "includes/admin_header.php"; ?>

<div class="container mt-4">
    <h6 class="fw-bold mb-3">Edit Member</h6>

    <form method="post">

        <!-- BASIC INFO -->
        <div class="form-floating mb-3">
            <input class="form-control" name="name" value="<?= htmlspecialchars($member['name']); ?>" required>
            <label>नाम</label>
        </div>

        <div class="form-floating mb-3">
            <input class="form-control" name="mobile" value="<?= htmlspecialchars($member['mobile']); ?>" required>
            <label>मोबाइल नंबर</label>
        </div>

        <div class="form-floating mb-3">
            <input class="form-control" name="whatsapp_number" value="<?= htmlspecialchars($member['whatsapp_number'] ?? ''); ?>">
            <label>व्हाट्सएप नंबर (वैकल्पिक)</label>
        </div>

        <!-- NIVASI -->
        <div class="mb-3">
            <label class="form-label">मूल निवास (Mool Niwas)</label>
            <select name="nivasi" class="form-select" required>
                <?php while ($n = $niwasQuery->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($n['name']); ?>" <?= ($member['nivasi']==$n['name'])?'selected':''; ?>>
                        <?= htmlspecialchars($n['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- GOTRA -->
        <div class="mb-3">
            <label class="form-label">गोत्र (Gotra)</label>
            <select name="gotra" class="form-select" required>
                <?php while ($g = $gotrasQuery->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($g['name']); ?>" <?= ($member['gotra']==$g['name'])?'selected':''; ?>>
                        <?= htmlspecialchars($g['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-floating mb-3">
            <input class="form-control" name="haal_niwas" value="<?= htmlspecialchars($member['haal_niwas'] ?? ''); ?>" placeholder="हाल निवास">
            <label>हाल निवास (वैकल्पिक)</label>
        </div>

        <div class="form-floating mb-3">
            <input class="form-control" name="vyavsaya" value="<?= htmlspecialchars($member['vyavsaya'] ?? ''); ?>" placeholder="व्यवसाय / प्रतिष्ठान">
            <label>व्यवसाय / प्रतिष्ठान (वैकल्पिक)</label>
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

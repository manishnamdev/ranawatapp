<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$memberId = (int) $_SESSION['member_id'];
$stmt = $conn->prepare("
    SELECT name, status, aadhar_front, aadhar_back
    FROM members
    WHERE id = ?
");
$stmt->bind_param("i", $memberId);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();

if (!$member) {
    header("Location: logout.php");
    exit;
}

$aadharFlash = $_SESSION['aadhar_flash'] ?? null;
unset($_SESSION['aadhar_flash']);

$hasFront = !empty($member['aadhar_front']);
$hasBack = !empty($member['aadhar_back']);
?>

<?php include "includes/front_header.php"; ?>

<div class="container mt-4">
    <?php if ($aadharFlash): ?>
        <div class="alert alert-<?= htmlspecialchars($aadharFlash['type']); ?> text-center">
            <?= htmlspecialchars($aadharFlash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-3">
        <div class="card-body text-center">
            <h5 class="fw-bold mb-2">Aadhaar Document Upload</h5>
            <p class="text-muted small mb-0">
                Upload front and back Aadhaar images separately. You can preview, replace, or delete them anytime from here.
            </p>
        </div>
    </div>

    <div class="alert alert-<?= ($hasFront && $hasBack) ? 'success' : 'info'; ?> text-center small">
        <?= ($hasFront && $hasBack)
            ? 'Both Aadhaar images are available.'
            : 'Please upload both front and back Aadhaar images.'; ?>
    </div>

    <div class="row g-3">
        <?php
        $cards = [
            'front' => ['title' => 'Aadhaar Front', 'file' => $member['aadhar_front']],
            'back' => ['title' => 'Aadhaar Back', 'file' => $member['aadhar_back']],
        ];
        foreach ($cards as $side => $card):
            $fileName = $card['file'];
            $imageUrl = $fileName ? "uploads/aadhar/" . rawurlencode($fileName) : '';
        ?>
            <div class="col-12 col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="fw-bold text-center mb-3"><?= htmlspecialchars($card['title']); ?></h6>

                        <?php if ($fileName): ?>
                            <div class="text-center mb-3">
                                <img src="<?= htmlspecialchars($imageUrl); ?>"
                                     alt="<?= htmlspecialchars($card['title']); ?>"
                                     class="img-fluid rounded border"
                                     style="max-height:220px;">
                            </div>

                            <div class="d-grid">
                                <a href="aadhar_delete.php?side=<?= urlencode($side); ?>"
                                   class="btn btn-outline-danger btn-sm"
                                   onclick="return confirm('Delete this Aadhaar image?');">
                                    Delete Image
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted small mb-3">No image uploaded yet.</div>
                        <?php endif; ?>

                        <form action="aadhar_upload.php" method="post" enctype="multipart/form-data" class="mt-3">
                            <input type="hidden" name="side" value="<?= htmlspecialchars($side); ?>">
                            <label class="form-label fw-semibold"><?= htmlspecialchars($card['title']); ?> File</label>
                            <input type="file" name="aadhar_image" class="form-control mb-2" accept=".jpg,.jpeg,.png,.webp,image/*" required>
                            <button class="btn btn-primary w-100">
                                <?= $fileName ? 'Replace ' . htmlspecialchars($card['title']) : 'Browse and Upload ' . htmlspecialchars($card['title']); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
        <a href="<?= $member['status'] === 'approved' ? 'dashboard.php' : 'pending.php'; ?>" class="btn btn-outline-secondary">
            Back
        </a>
    </div>
</div>

<?php include "includes/front_footer.php"; ?>

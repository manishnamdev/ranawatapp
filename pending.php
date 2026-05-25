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
$isReadyForReview = $hasFront && $hasBack;
?>

<?php include "includes/header.php"; ?>

<div class="container mt-4">
    <?php if ($aadharFlash): ?>
        <div class="alert alert-<?= htmlspecialchars($aadharFlash['type']); ?> text-center">
            <?= htmlspecialchars($aadharFlash['message']); ?>
        </div>
    <?php endif; ?>

    <div class="alert alert-warning text-center">
        <h6 class="fw-bold">प्रोफ़ाइल स्वीकृति लंबित</h6>
        <p class="mb-0">
            आपका पंजीकरण सफलतापूर्वक हो गया है।<br>
            आपकी प्रोफ़ाइल वर्तमान में व्यवस्थापक की स्वीकृति के लिए लंबित है।<br>
            कृपया कुछ समय प्रतीक्षा करें।
        </p>
    </div>

    <div class="card shadow-sm mt-3">
        <div class="card-body">
            <h6 class="fw-bold text-center mb-3">Aadhaar Card Upload</h6>

            <p class="small text-muted text-center mb-3">
                Upload separate front and back Aadhaar images. You can preview, replace, or delete them before profile approval.
            </p>

            <div class="alert alert-<?= $isReadyForReview ? 'success' : 'info'; ?> text-center small">
                <?= $isReadyForReview
                    ? 'Both Aadhaar images are uploaded and ready for admin review.'
                    : 'Please upload both front and back Aadhaar images for review.'; ?>
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
                        <div class="border rounded p-3 h-100">
                            <div class="fw-semibold mb-2 text-center"><?= htmlspecialchars($card['title']); ?></div>

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
                                        Delete
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted small mb-3">
                                    No image uploaded yet.
                                </div>
                            <?php endif; ?>

                            <form action="aadhar_upload.php" method="post" enctype="multipart/form-data" class="mt-3">
                                <input type="hidden" name="side" value="<?= htmlspecialchars($side); ?>">
                                <label class="form-label fw-semibold"><?= htmlspecialchars($card['title']); ?> File</label>
                                <input type="file" name="aadhar_image" class="form-control mb-2" accept=".jpg,.jpeg,.png,.webp,image/*" required>
                                <button class="btn btn-primary w-100 btn-sm">
                                    <?= $fileName ? 'Replace Image' : 'Browse and Upload'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="text-center mt-3">
        <a href="logout.php" class="btn btn-danger btn-sm">
            लॉगआउट
        </a>
    </div>
</div>

<?php include "includes/footer.php"; ?>

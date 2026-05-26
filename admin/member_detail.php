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

if (!$member) {
    die("Member not found");
}

$family_stmt = $conn->prepare("SELECT * FROM family_members WHERE member_id = ? ORDER BY created_at ASC");
$family_stmt->bind_param("i", $id);
$family_stmt->execute();
$family_members = $family_stmt->get_result();
?>

<?php include "includes/admin_header.php"; ?>

<style>
.profile-banner {
    background: linear-gradient(135deg, #0f7ae5, #18b7de);
    color: white;
    border-radius: 20px;
    padding: 30px 20px;
    margin-bottom: -40px;
    position: relative;
    box-shadow: 0 10px 25px rgba(15, 122, 229, 0.3);
}
.profile-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    margin-top: 20px;
}
.detail-item {
    padding: 12px 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
.detail-item:last-child {
    border-bottom: none;
}
.detail-label {
    font-size: 13px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
}
.detail-value {
    font-size: 16px;
    font-weight: 600;
    color: #0f172a;
}
.family-card {
    background: #ffffff;
    border-radius: 16px;
    border: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s, box-shadow 0.2s;
}
.family-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}
.status-badge {
    padding: 6px 12px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
}
.status-approved { background: #dcfce7; color: #166534; }
.status-pending { background: #fef3c7; color: #92400e; }
.status-rejected { background: #fee2e2; color: #991b1b; }
.doc-img {
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    max-width: 100%;
    height: auto;
    transition: transform 0.2s;
}
.doc-img:hover {
    transform: scale(1.02);
}
</style>

<div class="container mt-4 mb-5">
    <!-- Banner -->
    <div class="profile-banner text-center">
        <h3 class="fw-bold mb-1"><?= htmlspecialchars($member['name']); ?></h3>
        <p class="mb-0 opacity-75">ID #<?= $member['id']; ?> | <?= htmlspecialchars($member['mobile']); ?></p>
    </div>

    <!-- Basic Info -->
    <div class="profile-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0 text-primary">Basic Information</h5>
            <span class="status-badge status-<?= strtolower($member['status']); ?>"><?= htmlspecialchars($member['status']); ?></span>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="detail-item">
                    <div class="detail-label">Nivasi</div>
                    <div class="detail-value"><?= htmlspecialchars($member['nivasi']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Gotra</div>
                    <div class="detail-value"><?= htmlspecialchars($member['gotra']); ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="detail-item">
                    <div class="detail-label">WhatsApp</div>
                    <div class="detail-value"><?= htmlspecialchars($member['whatsapp_number'] ?: 'N/A'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Voting Right</div>
                    <div class="detail-value"><?= $member['is_canvote'] ? '✅ Enabled' : '❌ Disabled'; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Family Members -->
    <h5 class="fw-bold mt-5 mb-3 text-secondary">Family Members</h5>
    <?php if ($family_members->num_rows > 0): ?>
        <div class="row g-3">
        <?php while ($fm = $family_members->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card family-card h-100 p-3">
                    <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($fm['name']); ?></h6>
                    <span class="badge bg-light text-secondary mb-3 d-inline-block"><?= htmlspecialchars($fm['relation']); ?></span>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Birth Year:</span>
                        <span class="fw-semibold small"><?= htmlspecialchars($fm['birth_year']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Gotra:</span>
                        <span class="fw-semibold small"><?= htmlspecialchars($fm['gotra']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Marital:</span>
                        <span class="fw-semibold small"><?= htmlspecialchars($fm['marital_status']); ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Education:</span>
                        <span class="fw-semibold small"><?= htmlspecialchars($fm['education']); ?></span>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-light text-center rounded-4 text-muted">
            No family members added yet.
        </div>
    <?php endif; ?>

    <!-- Documents -->
    <h5 class="fw-bold mt-5 mb-3 text-secondary">Documents & Payment</h5>
    <div class="profile-card mt-0">
        <div class="row g-4">
            <!-- Aadhaar -->
            <div class="col-md-6">
                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-id-card"></i> Aadhaar Cards</h6>
                <div class="row g-2">
                    <div class="col-6 text-center">
                        <?php if (!empty($member['aadhar_front'])): ?>
                            <a href="../uploads/aadhar/<?= rawurlencode($member['aadhar_front']); ?>" target="_blank">
                                <img src="../uploads/aadhar/<?= rawurlencode($member['aadhar_front']); ?>" class="doc-img" alt="Aadhaar Front">
                            </a>
                            <div class="small mt-1 text-muted">Front Side</div>
                        <?php else: ?>
                            <div class="p-3 bg-light rounded-3 text-muted small border">Front missing</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-6 text-center">
                        <?php if (!empty($member['aadhar_back'])): ?>
                            <a href="../uploads/aadhar/<?= rawurlencode($member['aadhar_back']); ?>" target="_blank">
                                <img src="../uploads/aadhar/<?= rawurlencode($member['aadhar_back']); ?>" class="doc-img" alt="Aadhaar Back">
                            </a>
                            <div class="small mt-1 text-muted">Back Side</div>
                        <?php else: ?>
                            <div class="p-3 bg-light rounded-3 text-muted small border">Back missing</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Payment -->
            <div class="col-md-6 border-start-md">
                <h6 class="fw-bold text-primary mb-3"><i class="fas fa-receipt"></i> Payment Proof</h6>
                <div class="text-center">
                    <?php if ($member['payment_screenshot']): ?>
                        <a href="../<?= htmlspecialchars($member['payment_screenshot']); ?>" target="_blank">
                            <img src="../<?= htmlspecialchars($member['payment_screenshot']); ?>" class="doc-img" style="max-height: 150px;" alt="Payment">
                        </a>
                        <div class="mt-2">
                            <span class="status-badge status-<?= strtolower($member['payment_status']); ?>"><?= htmlspecialchars($member['payment_status']); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="p-4 bg-light rounded-3 text-muted small border">
                            No payment screenshot uploaded
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="d-flex justify-content-between mt-4">
        <a href="dashboard.php" class="btn btn-outline-secondary px-4 rounded-pill fw-bold">← Back</a>
        <a href="member_edit.php?id=<?= $id; ?>" class="btn btn-primary px-4 rounded-pill fw-bold">Edit Member</a>
    </div>
</div>

<?php include "includes/admin_footer.php"; ?>

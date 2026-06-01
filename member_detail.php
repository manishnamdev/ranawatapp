<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    header("Location: member_search.php");
    exit;
}

// Fetch member details
$stmt = $conn->prepare("
    SELECT m.*, (SELECT COUNT(*) FROM family_members fm WHERE fm.member_id = m.id) as fam_count
    FROM members m 
    WHERE m.id = ? AND m.status = 'approved'
");
$stmt->bind_param("i", $id);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();

if (!$member) {
    header("Location: member_search.php");
    exit;
}

// Fetch family members
$famStmt = $conn->prepare("SELECT * FROM family_members WHERE member_id = ? ORDER BY birth_year ASC");
$famStmt->bind_param("i", $id);
$famStmt->execute();
$familyMembers = $famStmt->get_result();

include "includes/front_header.php";
?>

<div class="container mt-4 mb-5">
    <div class="card app-card mb-4" style="border: none; border-radius: 20px; box-shadow: 0 8px 24px rgba(15,23,42,0.08);">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0" style="color: #0f172a;">👤 सदस्य विवरण</h3>
                <a href="member_search.php" class="btn btn-outline-secondary btn-sm rounded-pill">← Back</a>
            </div>

            <div class="text-center mb-4">
                <?php if (!empty($member['profile_photo'])): ?>
                    <img src="uploads/profile_photos/<?= htmlspecialchars($member['profile_photo']); ?>" style="width:120px;height:120px;object-fit:cover;border-radius:50%; border: 3px solid #e2e8f0; margin-bottom: 15px;">
                <?php else: ?>
                    <div style="width:120px;height:120px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:50px; border: 3px solid #e2e8f0; margin: 0 auto 15px auto;">👤</div>
                <?php endif; ?>
                <h4 class="fw-bold text-primary mb-1"><?= htmlspecialchars($member['name']); ?></h4>
                <p class="text-muted mb-2"><?= htmlspecialchars($member['gotra']); ?> | <?= htmlspecialchars($member['nivasi']); ?></p>
                <div class="d-inline-flex align-items-center bg-light px-3 py-2 rounded-pill mb-2">
                    <span class="me-2">📞</span>
                    <a href="tel:<?= htmlspecialchars($member['mobile']); ?>" class="fw-bold text-dark text-decoration-none"><?= htmlspecialchars($member['mobile']); ?></a>
                </div>
                <?php if (!empty($member['whatsapp_number'])): ?>
                <br>
                <div class="d-inline-flex align-items-center bg-light px-3 py-2 rounded-pill">
                    <span class="me-2">💬</span>
                    <a href="https://wa.me/91<?= ltrim(htmlspecialchars($member['whatsapp_number']), '0'); ?>" target="_blank" class="fw-bold text-primary text-decoration-none">WhatsApp: <?= htmlspecialchars($member['whatsapp_number']); ?></a>
                </div>
                <?php endif; ?>
            </div>

            <hr class="mb-4">
            <h5 class="fw-bold mb-3">पारिवारिक सदस्य (<?= (int)$member['fam_count']; ?>)</h5>
            
            <?php if ($familyMembers->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>नाम</th>
                                <th>रिश्ता</th>
                                <th>जन्म वर्ष</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fam = $familyMembers->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($fam['name']); ?></td>
                                    <td><span class="badge bg-info text-dark rounded-pill px-3"><?= htmlspecialchars($fam['relation']); ?></span></td>
                                    <td><?= htmlspecialchars($fam['birth_year']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning text-center rounded-3 mb-4">
                    कोई पारिवारिक सदस्य नहीं जोड़ा गया है।
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include "includes/front_footer.php"; ?>

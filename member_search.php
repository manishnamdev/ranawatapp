<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$search_niwas = $_GET['search_niwas'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$searchResult = null;
$total_pages = 0;
$total_records = 0;

if ($search_niwas != '') {
    // Count total for pagination
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM members WHERE nivasi = ? AND status = 'approved'");
    $countStmt->bind_param("s", $search_niwas);
    $countStmt->execute();
    $total_records = $countStmt->get_result()->fetch_assoc()['total'] ?? 0;
    $total_pages = ceil($total_records / $limit);

    // Fetch paginated
    $stmt = $conn->prepare("
        SELECT m.id, m.name, m.profile_photo, 
               (SELECT COUNT(*) FROM family_members fm WHERE fm.member_id = m.id) as fam_count
        FROM members m 
        WHERE m.nivasi = ? AND m.status = 'approved'
        ORDER BY m.name ASC
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("sii", $search_niwas, $limit, $offset);
    $stmt->execute();
    $searchResult = $stmt->get_result();
}

$niwasList = $conn->query("SELECT name FROM niwas ORDER BY name ASC");
?>

<?php include "includes/front_header.php"; ?>

<div class="container mt-4 mb-5">
    <div class="card app-card" style="border: none; border-radius: 20px; box-shadow: 0 8px 24px rgba(15,23,42,0.08);">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold mb-0" style="color: #0f172a;">🔍 सदस्य खोजें</h3>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill">← Back</a>
            </div>

            <form method="GET" class="mb-4">
                <div class="input-group">
                    <select name="search_niwas" class="form-select border-primary" style="border-radius: 12px 0 0 12px;" required>
                        <option value="">मूल निवास चुनें</option>
                        <?php while ($n = $niwasList->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($n['name']); ?>" <?= ($search_niwas == $n['name']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($n['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" class="btn btn-primary px-4 fw-bold" style="border-radius: 0 12px 12px 0;">खोजें</button>
                </div>
            </form>

            <?php if ($searchResult !== null): ?>
                <?php if ($searchResult->num_rows > 0): ?>
                    <p class="text-muted small mb-3">Found <?= $total_records ?> members from <?= htmlspecialchars($search_niwas) ?></p>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>फोटो</th>
                                    <th>नाम</th>
                                    <th>कुल सदस्य</th>
                                    <th>एक्शन</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($res = $searchResult->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($res['profile_photo'])): ?>
                                                <img src="uploads/profile_photos/<?= htmlspecialchars($res['profile_photo']); ?>" style="width:45px;height:45px;object-fit:cover;border-radius:50%; border: 2px solid #e2e8f0;">
                                            <?php else: ?>
                                                <div style="width:45px;height:45px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;font-size:20px; border: 2px solid #e2e8f0;">👤</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold text-primary"><?= htmlspecialchars($res['name']); ?></td>
                                        <td><span class="badge bg-secondary rounded-pill px-3"><?= (int)$res['fam_count'] + 1; ?></span></td>
                                        <td><a href="member_detail.php?id=<?= $res['id']; ?>" class="btn btn-sm btn-primary rounded-pill" style="font-size: 12px; font-weight: bold;">View Details</a></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?search_niwas=<?= urlencode($search_niwas) ?>&page=<?= $page - 1 ?>">Previous</a>
                                </li>
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?search_niwas=<?= urlencode($search_niwas) ?>&page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?search_niwas=<?= urlencode($search_niwas) ?>&page=<?= $page + 1 ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="alert alert-warning text-center rounded-3 mb-4">
                        इस मूल निवास से अभी कोई सदस्य नहीं जुड़ा है।
                    </div>
                    <?php 
                    $shareText = rawurlencode("रांकावत समाज रानी ऐप से जुड़ें और समाज को मजबूत बनाएं। अभी रजिस्टर करें: https://www.rankawatsamajrani.com/");
                    ?>
                    <a href="https://api.whatsapp.com/send?text=<?= $shareText; ?>" target="_blank" class="btn w-100 fw-bold d-flex align-items-center justify-content-center gap-2" style="background:#25D366;color:#fff; border-radius: 12px; padding: 12px;">
                        <i style="font-size: 20px;">📲</i> WhatsApp पर ऐप शेयर करें
                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include "includes/front_footer.php"; ?>

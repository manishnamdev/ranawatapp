<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

/* STATS */
$totalApproved = $conn->query("SELECT COUNT(*) total FROM members WHERE status='approved'")->fetch_assoc()['total'];
$totalPending  = $conn->query("SELECT COUNT(*) total FROM members WHERE status='pending'")->fetch_assoc()['total'];
$totalRejected = $conn->query("SELECT COUNT(*) total FROM members WHERE status='rejected'")->fetch_assoc()['total'];

$searchMobile = $_GET['search_mobile'] ?? '';
$searchResult = null;

if ($searchMobile != '') {

    $stmt = $conn->prepare("
        SELECT * FROM members 
        WHERE mobile LIKE ? 
        OR name LIKE ?
        ORDER BY created_at DESC 
        LIMIT 100
    ");

    $like = "%$searchMobile%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $searchResult = $stmt->get_result();
}
?>

<?php include "./includes/admin_header.php"; ?>

<style>
.stat-card {
    border-radius: 18px;
    color: #fff;
}
.bg-approved { background: linear-gradient(135deg,#43e97b,#38f9d7); }
.bg-pending  { background: linear-gradient(135deg,#f7971e,#ffd200); color:#000; }
.bg-rejected { background: linear-gradient(135deg,#ff5858,#f857a6); }

.section-title {
    font-weight: 700;
    margin: 25px 0 10px;
}
.member-card {
    border-radius: 16px;
}
</style>

<div class="container mt-3">
<?php if (isset($_SESSION['flash_msg'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type']; ?> alert-dismissible fade show">
        <?= $_SESSION['flash_msg']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <?php
    unset($_SESSION['flash_msg']);
    unset($_SESSION['flash_type']);
    ?>
<?php endif; ?>

    <!-- 🔍 SEARCH + ADD -->
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="get">
                <div class="input-group">
                    <input type="text"
                           name="search_mobile"
                           class="form-control"
                           placeholder="नाम या मोबाइल नंबर से सदस्य खोजें"
                           value="<?= htmlspecialchars($searchMobile); ?>">
                    <button class="btn btn-primary px-3">Search</button>
                </div>
            </form>

            <div class="row mt-3 g-2">
                <div class="col-12">
                    <a href="member_add.php" class="btn btn-success w-100 py-2 fw-bold">
                        ➕ Add New Member
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 📊 STATS -->
    <div class="row text-center mb-3">
        <div class="col-4">
            <div class="card stat-card bg-approved shadow-sm">
                <div class="card-body">
                    <small>Approved</small>
                    <h4><?= $totalApproved; ?></h4>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card stat-card bg-pending shadow-sm">
                <div class="card-body">
                    <small>Pending</small>
                    <h4><?= $totalPending; ?></h4>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card stat-card bg-rejected shadow-sm">
                <div class="card-body">
                    <small>Rejected</small>
                    <h4><?= $totalRejected; ?></h4>
                </div>
            </div>
        </div>
    </div>
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row">
            <div class="col-4 d-grid">
                <a href="members.php?status=approved" class="btn btn-outline-success btn-sm">
                    ✅ Approved
                </a>
            </div>
            <div class="col-4 d-grid">
                <a href="members.php?status=pending" class="btn btn-outline-warning btn-sm">
                    ⏳ Pending
                </a>
            </div>
            <div class="col-4 d-grid">
                <a href="members.php?status=rejected" class="btn btn-outline-danger btn-sm">
                    ❌ Rejected
                </a>
            </div>
        </div>
    </div>
</div>

    <!-- ⚡ QUICK ACTIONS -->
    <div class="card shadow-sm mb-3" style="border-radius: 18px; border: none;">
        <div class="card-body">
            <div class="row g-3 text-center">
                <div class="col-3">
                    <a href="members_print.php" target="_blank" class="text-decoration-none text-dark d-flex flex-column align-items-center">
                        <div class="d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #fee2e2; border-radius: 16px; margin-bottom: 6px;">
                            <span style="font-size: 22px;">📥</span>
                        </div>
                        <span style="font-size: 11px; font-weight: 600;">PDF</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="voting_settings.php" class="text-decoration-none text-dark d-flex flex-column align-items-center">
                        <div class="d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #e0f2fe; border-radius: 16px; margin-bottom: 6px;">
                            <span style="font-size: 22px;">🗳️</span>
                        </div>
                        <span style="font-size: 11px; font-weight: 600;">Setting</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="vote_results.php" class="text-decoration-none text-dark d-flex flex-column align-items-center">
                        <div class="d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #dcfce7; border-radius: 16px; margin-bottom: 6px;">
                            <span style="font-size: 22px;">📊</span>
                        </div>
                        <span style="font-size: 11px; font-weight: 600;">Results</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="notifications.php" class="text-decoration-none text-dark d-flex flex-column align-items-center">
                        <div class="d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background: #f3f4f6; border-radius: 16px; margin-bottom: 6px;">
                            <span style="font-size: 22px;">🔔</span>
                        </div>
                        <span style="font-size: 11px; font-weight: 600;">Notify</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 🔎 SEARCH RESULT -->
    <?php if ($searchResult !== null): ?>
        <div class="section-title">Search Result</div>

        <?php if ($searchResult->num_rows == 0): ?>
            <div class="alert alert-warning">कोई सदस्य नहीं मिला</div>
        <?php endif; ?>

        <?php while ($m = $searchResult->fetch_assoc()): ?>
            <div class="card member-card shadow-sm mb-2">
                <div class="card-body">
                    <b><?= $m['name']; ?></b><br>
                    <small><?= $m['mobile']; ?> | <?= ucfirst($m['status']); ?></small>

                    <div class="mt-2 d-flex gap-2 flex-wrap">
                        <a href="member_edit.php?id=<?= $m['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="member_detail.php?id=<?= $m['id']; ?>" class="btn btn-secondary btn-sm">Detail</a>
                                        <a href="member_send_whatsapp.php?id=<?= $m['id']; ?>"
   class="btn btn-success btn-sm">
   📲 Send Login
</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <!-- ⏳ PENDING -->
    <div class="section-title">⏳ Pending Members</div>
    <?php
    $pending = $conn->query("SELECT * FROM members WHERE status='pending' ORDER BY created_at DESC LIMIT 5");
    if ($pending->num_rows == 0) echo '<div class="alert alert-info">No pending members</div>';
    while ($m = $pending->fetch_assoc()):
    ?>
        <div class="card member-card shadow-sm mb-2">
            <div class="card-body">
                <b><?= $m['name']; ?></b><br>
                <small><?= $m['mobile']; ?></small>

                <div class="mt-2 d-flex gap-2">
                    <a href="member_action.php?id=<?= $m['id']; ?>&action=approved" class="btn btn-success btn-sm">Approve</a>
                    <a href="member_action.php?id=<?= $m['id']; ?>&action=rejected" class="btn btn-danger btn-sm">Reject</a>
                    <a href="member_detail.php?id=<?= $m['id']; ?>" class="btn btn-secondary btn-sm">Detail</a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>

    <!-- 👥 APPROVED -->
    <div class="section-title">✅ Approved Members</div>
    <?php
    $approved = $conn->query("SELECT * FROM members WHERE status='approved' ORDER BY created_at DESC LIMIT 5");
    if ($approved->num_rows == 0) echo '<div class="alert alert-info">No approved members</div>';
    while ($m = $approved->fetch_assoc()):
    ?>
        <div class="card member-card shadow-sm mb-2">
            <div class="card-body">
                <b><?= $m['name']; ?></b><br>
                <small><?= $m['mobile']; ?></small>
                <div class="mt-2">
                    <a href="member_detail.php?id=<?= $m['id']; ?>" class="btn btn-secondary btn-sm">Detail</a>
                <a href="member_edit.php?id=<?= $m['id']; ?>" class="btn btn-warning btn-sm">
        ✏️ Edit
    </a>
                    <a href="member_send_whatsapp.php?id=<?= $m['id']; ?>"
   class="btn btn-success btn-sm">
   📲 Send Login
</a>
                </div>
                
            </div>
        </div>
    <?php endwhile; ?>
<!-- 🚫 VOTING DISABLED MEMBERS -->
<div class="section-title">🚫 Voting Disabled Members</div>

<?php
$disabledVote = $conn->query("
    SELECT * FROM members 
    WHERE is_canvote = 0 
    AND status = 'approved'
    ORDER BY created_at DESC 
    LIMIT 10
");

if ($disabledVote->num_rows == 0) {
    echo '<div class="alert alert-success">सभी Approved सदस्य वोटिंग के लिए सक्षम हैं</div>';
}

while ($m = $disabledVote->fetch_assoc()):
?>
    <div class="card member-card shadow-sm mb-2">
        <div class="card-body">
            <b><?= $m['name']; ?></b><br>
            <small><?= $m['mobile']; ?></small>

            <div class="mt-2 d-flex gap-2 flex-wrap">
                
   <a href="member_toggle_vote.php?id=<?= $m['id']; ?>&action=enable"
   class="btn btn-success btn-sm"
   onclick="return confirm('क्या आप इस सदस्य को मतदान के लिए सक्षम करना चाहते हैं?');">
   ✅ Enable Voting
</a>

                <a href="member_detail.php?id=<?= $m['id']; ?>"
                   class="btn btn-secondary btn-sm">
                   Detail
                </a>

                <a href="member_edit.php?id=<?= $m['id']; ?>"
                   class="btn btn-warning btn-sm">
                   ✏️ Edit
                </a>

            </div>
        </div>
    </div>
<?php endwhile; ?>
    <!-- ❌ REJECTED -->
    <div class="section-title">❌ Rejected Members</div>
    <?php
    $rejected = $conn->query("SELECT * FROM members WHERE status='rejected' ORDER BY created_at DESC LIMIT 5");
    if ($rejected->num_rows == 0) echo '<div class="alert alert-info">No rejected members</div>';
    while ($m = $rejected->fetch_assoc()):
    ?>
        <div class="card member-card shadow-sm mb-2">
            <div class="card-body">
                <b><?= $m['name']; ?></b><br>
                <small><?= $m['mobile']; ?></small>
                <div class="mt-2 d-flex gap-2">
                    <a href="member_action.php?id=<?= $m['id']; ?>&action=approved" class="btn btn-success btn-sm">Re-Approve</a>
                    <a href="member_detail.php?id=<?= $m['id']; ?>" class="btn btn-secondary btn-sm">Detail</a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>

    <!-- 🔐 RECENT ADMIN LOGINS -->
    <div class="section-title">🔐 Recent Admin Logins</div>
    <?php
    $adminLogs = $conn->query("
        SELECT a.username, l.login_time, l.ip_address
        FROM admin_login_logs l
        JOIN admins a ON a.id = l.admin_id
        ORDER BY l.id DESC
        LIMIT 5
    ");
    if ($adminLogs && $adminLogs->num_rows > 0):
        while ($log = $adminLogs->fetch_assoc()):
    ?>
        <div class="card member-card shadow-sm mb-2">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <b><?= htmlspecialchars($log['username']); ?></b><br>
                        <small class="text-muted"><?= $log['login_time']; ?></small>
                    </div>
                    <span class="badge bg-secondary"><?= htmlspecialchars($log['ip_address']); ?></span>
                </div>
            </div>
        </div>
    <?php endwhile; else: ?>
        <div class="alert alert-info">कोई लॉगिन लॉग नहीं मिला</div>
    <?php endif; ?>
    <div class="text-end mb-2">
        <a href="logs.php" class="small text-muted">सभी लॉग देखें →</a>
    </div>

    <!-- FOOT ACTIONS -->
    <div class="mt-4 d-grid gap-2">
        <a href="members.php" class="btn btn-outline-primary btn-sm">View All Members</a>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>

</div>

<?php include "./includes/admin_footer.php"; ?>

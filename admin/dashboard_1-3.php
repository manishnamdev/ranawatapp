<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// stats
$totalApproved = $conn->query("SELECT COUNT(*) total FROM members WHERE status='approved'")->fetch_assoc()['total'];
$totalPending  = $conn->query("SELECT COUNT(*) total FROM members WHERE status='pending'")->fetch_assoc()['total'];
$totalRejected = $conn->query("SELECT COUNT(*) total FROM members WHERE status='rejected'")->fetch_assoc()['total'];

$searchMobile = $_GET['search_mobile'] ?? '';
$searchResult = null;

if ($searchMobile != '') {
    $stmt = $conn->prepare("
        SELECT * FROM members 
        WHERE mobile LIKE ?
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $like = "%$searchMobile%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $searchResult = $stmt->get_result();
}

?>

<?php include "../includes/header.php"; ?>
<!-- MEMBER SEARCH -->
<div class="card shadow-sm mb-3">
    <div class="card-body">

        <form method="get" class="mb-0">
            <div class="input-group">
                <input type="text"
                       name="search_mobile"
                       class="form-control"
                       placeholder="मोबाइल नंबर से सदस्य खोजें"
                       value="<?= $_GET['search_mobile'] ?? ''; ?>">

                <button class="btn btn-primary">
                    🔍 Search
                </button>
            </div>
        </form>

    </div>
    <a href="member_add.php" class="btn btn-primary btn-sm">
    ➕ Add Member
</a>

</div>
<?php if ($searchResult !== null): ?>
    <h6 class="fw-bold mt-3">Search Result</h6>

    <?php if ($searchResult->num_rows == 0): ?>
        <div class="alert alert-warning">
            कोई सदस्य नहीं मिला
        </div>
    <?php endif; ?>

    <?php while ($m = $searchResult->fetch_assoc()): ?>
        <div class="card mb-2 shadow-sm">
            <div class="card-body">
                <b><?= $m['name']; ?></b><br>
                <small><?= $m['mobile']; ?> | <?= $m['status']; ?></small>

                <div class="mt-2 d-flex gap-2 flex-wrap">

                    <?php if ($m['status'] != 'approved'): ?>
                        <a href="member_action.php?id=<?= $m['id']; ?>&action=approved"
                           class="btn btn-success btn-sm">Approve</a>
                    <?php endif; ?>

                    <?php if ($m['status'] != 'rejected'): ?>
                        <a href="member_action.php?id=<?= $m['id']; ?>&action=rejected"
                           class="btn btn-danger btn-sm">Reject</a>
                    <?php endif; ?>

                    <a href="member_edit.php?id=<?= $m['id']; ?>"
                       class="btn btn-warning btn-sm">Edit</a>

                    <a href="member_detail.php?id=<?= $m['id']; ?>"
                       class="btn btn-secondary btn-sm">Detail</a>

                </div>
            </div>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

<div class="btn-group w-100 mb-3">
    <a href="dashboard.php" class="btn btn-outline-primary btn-sm">Dashboard</a>
    <a href="members.php?status=pending" class="btn btn-outline-warning btn-sm">Pending</a>
    <a href="members.php?status=approved" class="btn btn-outline-success btn-sm">Approved</a>
    <a href="members.php?status=rejected" class="btn btn-outline-danger btn-sm">Rejected</a>
</div>

<div class="container mt-4">

    <h5 class="fw-bold mb-3">Admin Dashboard</h5>

    <!-- STAT CARDS -->
<div class="row text-center mb-4">
    <div class="col-4">
        <div class="card border-success shadow-sm">
            <div class="card-body">
                <small class="text-success">Approved</small>
                <h5><?= $totalApproved; ?></h5>
            </div>
               </div><a href="members.php?status=approved" class="btn btn-sm btn-success">More</a>
        
    </div>

    <div class="col-4">
        <div class="card border-warning shadow-sm">
            <div class="card-body">
                <small class="text-warning">Pending</small>
                <h5><?= $totalPending; ?></h5>
            </div>
     </div><a href="members.php?status=pending" class="btn btn-sm btn-primary">More</a>
    </div>

    <div class="col-4">
        <div class="card border-danger shadow-sm">
            <div class="card-body">
                <small class="text-danger">Rejected</small>
                <h5><?= $totalRejected; ?></h5>
            </div>
                </div><a href="members.php?status=rejected" class="btn btn-sm btn-danger">More</a>
        </div>

</div>

<div class="card shadow-sm mb-3">
    <div class="card-body d-grid gap-2">
        <a href="voting_settings.php" class="btn btn-outline-primary">
            🗳️ Voting Settings
        </a>

        <a href="vote_results.php" class="btn btn-outline-success">
            📊 Vote Results
        </a>
    </div>
</div>



    <!-- PENDING MEMBERS -->
    <h6 class="fw-bold mb-2">Pending Members</h6>

    <?php
    $pending = $conn->query("SELECT * FROM members WHERE status='pending' ORDER BY created_at DESC LIMIT 5");
    if ($pending->num_rows == 0) {
        echo '<div class="alert alert-info">No pending members</div>';
    }
    ?>

    <?php while ($m = $pending->fetch_assoc()) { ?>
        <div class="card mb-2 shadow-sm">
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
    <?php } ?>
    <!-- APPROVED MEMBERS -->
    <h6 class="fw-bold mt-4 mb-2">Approved Members</h6>

    <?php
    $approved = $conn->query("SELECT * FROM members WHERE status='approved' ORDER BY created_at DESC LIMIT 5");
    if ($approved->num_rows == 0) {
        echo '<div class="alert alert-info">No approved members</div>';
    }
    ?>

    <?php while ($m = $approved->fetch_assoc()) { ?>
        <div class="card mb-2 shadow-sm">
            <div class="card-body">
                <b><?= $m['name']; ?></b><br>
                <small><?= $m['mobile']; ?></small>

                <div class="mt-2 d-flex gap-2">
                    <a href="member_detail.php?id=<?= $m['id']; ?>" class="btn btn-secondary btn-sm">Detail</a>
                </div>
            </div>
        </div>
    <?php } ?>
    
    <h6 class="fw-bold mt-4 mb-2">Rejected Members</h6>

<?php
$rejected = $conn->query("SELECT * FROM members WHERE status='rejected' ORDER BY created_at DESC LIMIT 5");

if ($rejected->num_rows == 0) {
    echo '<div class="alert alert-info">No rejected members</div>';
}
?>

<?php while ($m = $rejected->fetch_assoc()) { ?>
    <div class="card mb-2 shadow-sm">
        <div class="card-body">
            <b><?= $m['name']; ?></b><br>
            <small><?= $m['mobile']; ?></small>

            <div class="mt-2 d-flex gap-2">
                <a href="member_action.php?id=<?= $m['id']; ?>&action=approved"
                   class="btn btn-success btn-sm">
                   Re-Approve
                </a>

                <a href="member_detail.php?id=<?= $m['id']; ?>"
                   class="btn btn-secondary btn-sm">
                   Detail
                </a>
            </div>
        </div>
    </div>
<?php } ?>


    <div class="mt-4">
        <a href="members.php" class="btn btn-primary btn-sm">View All Members</a>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>

</div>

<?php include "../includes/footer.php"; ?>

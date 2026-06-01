<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$status = $_GET['status'] ?? 'pending';
$page   = $_GET['page'] ?? 1;

$limit = 100;                    // per page records
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';

$where = "status='$status'";
if ($search != '') {
    // Cross-status search by name or mobile
    $where = "(name LIKE '%$search%' OR mobile LIKE '%$search%')";
}

$totalQuery = $conn->query(
    "SELECT COUNT(*) total FROM members WHERE $where"
);



$totalRecords = $totalQuery->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);

$members = $conn->query(
    "SELECT * FROM members 
     WHERE $where 
     ORDER BY created_at DESC 
     LIMIT $limit OFFSET $offset"
);


$startRecord = ($totalRecords > 0) ? $offset + 1 : 0;
$endRecord   = min($offset + $limit, $totalRecords);

?>

<?php include "./includes/admin_header.php"; ?>

<div class="container mt-3">
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0">Search Members</h6>
            <div class="d-flex gap-2">
                <a href="members_print.php" target="_blank" class="btn btn-outline-danger btn-sm fw-bold">
                    📥 Download PDF
                </a>
                <a href="member_add.php" class="btn btn-primary btn-sm">
                    ➕ Add New Member
                </a>
            </div>
        </div>
        <form method="get">
            <input type="hidden" name="status" value="<?= $status; ?>">
            <div class="input-group">
                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="नाम या मोबाइल नंबर से खोजें"
                       value="<?= htmlspecialchars($search); ?>">
                <button class="btn btn-primary px-4">Search</button>
            </div>
            <?php if ($search): ?>
                <div class="mt-2 text-center">
                    <a href="members.php?status=<?= $status; ?>" class="small text-decoration-none text-danger">
                        ✖ Clear Search
                    </a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</div>

    <h5 class="fw-bold mb-3">
        <?= ucfirst($status); ?> Members
    </h5>
<div class="d-flex justify-content-between align-items-center mt-4 mb-2">
    <small class="text-muted">
        Showing <?= $startRecord; ?> to <?= $endRecord; ?> 
        of <?= $totalRecords; ?> Members
    </small>
</div>
    <?php if ($members->num_rows == 0): ?>
        <div class="alert alert-info">No members found</div>
    <?php endif; ?>

    <?php while ($m = $members->fetch_assoc()): ?>
        <div class="card mb-2 shadow-sm">
            <div class="card-body">
                <b><?= $m['name']; ?></b><br>
                <small class="fw-bold"><?= $m['mobile']; ?></small> | 
                <span class="badge bg-<?= ($m['status']=='approved'?'success':($m['status']=='rejected'?'danger':'warning')); ?> text-dark tiny-badge">
                    <?= ucfirst($m['status']); ?>
                </span>
                <br>
                <?php 
$createdDate = date("d M Y, h:i A", strtotime($m['created_at']));
?>

<small class="text-muted">
    🗓️ Registered On: <?= $createdDate; ?>
</small>

                <div class="mt-2 d-flex gap-2">
                    <?php if ($status != 'approved'): ?>
                        <a href="member_action.php?id=<?= $m['id']; ?>&action=approved"
                           class="btn btn-primary btn-sm">Approve</a>
                    <?php endif; ?>

                    <?php if ($status != 'rejected'): ?>
                        <a href="member_action.php?id=<?= $m['id']; ?>&action=rejected"
                           class="btn btn-danger btn-sm">Reject</a>
                    <?php endif; ?>

                    <a href="member_detail.php?id=<?= $m['id']; ?>"
                       class="btn btn-secondary btn-sm">Detail</a>
					   
					   <a href="member_edit.php?id=<?= $m['id']; ?>"
   class="btn btn-warning btn-sm">
   Edit
</a>

                </div>
            </div>
        </div>
    <?php endwhile; ?>


<div class="d-flex justify-content-between align-items-center mt-4 mb-2">
    <small class="text-muted">
        Showing <?= $startRecord; ?> to <?= $endRecord; ?> 
        of <?= $totalRecords; ?> Members
    </small>
</div>
    <!-- PAGINATION -->
<?php if ($totalPages > 1): ?>
<nav class="mt-3">
    <ul class="pagination justify-content-center">

        <!-- Previous Button -->
        <?php if ($page > 1): ?>
        <li class="page-item">
            <a class="page-link"
               href="?status=<?= $status; ?>&page=<?= $page-1; ?>&search=<?= $search; ?>">
               &laquo; Previous
            </a>
        </li>
        <?php endif; ?>

        <!-- Next Button -->
        <?php if ($page < $totalPages): ?>
        <li class="page-item">
            <a class="page-link"
               href="?status=<?= $status; ?>&page=<?= $page+1; ?>&search=<?= $search; ?>">
               Next &raquo;
            </a>
        </li>
        <?php endif; ?>

    </ul>
</nav>
<?php endif; ?>

</div>

<?php include "./includes/admin_footer.php"; ?>

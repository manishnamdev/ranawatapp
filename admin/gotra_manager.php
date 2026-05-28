<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Add Gotra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT IGNORE INTO gotras (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
    }
    header("Location: gotra_manager.php?success=add");
    exit;
}

// Edit Gotra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    if ($name !== '' && $id > 0) {
        $stmt = $conn->prepare("UPDATE gotras SET name=? WHERE id=?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
    }
    header("Location: gotra_manager.php?success=edit");
    exit;
}

// Delete Gotra
if (isset($_GET['delete_id'])) {
    $del_id = (int) $_GET['delete_id'];
    $conn->query("DELETE FROM gotras WHERE id=$del_id");
    header("Location: gotra_manager.php?success=delete");
    exit;
}

// Fetch all
$result = $conn->query("SELECT * FROM gotras ORDER BY name ASC");
?>

<?php include "includes/admin_header.php"; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">गोत्र प्रबंधक (Gotra Manager)</h5>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Action completed successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Add New -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="post" class="d-flex gap-2">
                <input type="hidden" name="action" value="add">
                <input type="text" name="name" class="form-control" placeholder="नया गोत्र जोड़ें (Add new Gotra)" required>
                <button type="submit" class="btn btn-primary px-4">Add</button>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>गोत्र का नाम (Name)</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="ps-3"><?= $row['id'] ?></td>
                        <td>
                            <form method="post" class="d-flex gap-2" id="form-edit-<?= $row['id'] ?>">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <input type="text" name="name" class="form-control form-control-sm" value="<?= htmlspecialchars($row['name']) ?>" required>
                            </form>
                        </td>
                        <td class="text-end pe-3">
                            <button type="submit" form="form-edit-<?= $row['id'] ?>" class="btn btn-sm btn-success">Save</button>
                            <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('क्या आप वाकई डिलीट करना चाहते हैं? (Are you sure you want to delete?)')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($result->num_rows == 0): ?>
                    <tr><td colspan="3" class="text-center py-4 text-muted">No entries found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "includes/admin_footer.php"; ?>

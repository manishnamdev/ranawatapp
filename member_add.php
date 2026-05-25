<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'];
$member = $conn->query("SELECT * FROM members WHERE id=$id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name   = $_POST['name'];
    $mobile = $_POST['mobile'];
    $nivasi = $_POST['nivasi'];

    $stmt = $conn->prepare(
        "UPDATE members SET name=?, mobile=?, nivasi=? WHERE id=?"
    );
    $stmt->bind_param("sssi", $name, $mobile, $nivasi, $id);
    $stmt->execute();

    header("Location: members.php?status=".$member['status']);
    exit;
}
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
    <h6 class="fw-bold mb-3">Edit Member</h6>

    <form method="post">
        <div class="form-floating mb-3">
            <input class="form-control" name="name"
                   value="<?= $member['name']; ?>" required>
            <label>नाम</label>
        </div>

        <div class="form-floating mb-3">
            <input class="form-control" name="mobile"
                   value="<?= $member['mobile']; ?>" required>
            <label>मोबाइल</label>
        </div>

        <div class="form-floating mb-3">
            <input class="form-control" name="nivasi"
                   value="<?= $member['nivasi']; ?>" required>
            <label>निवासी</label>
        </div>

        <button class="btn btn-success w-100">Update</button>
    </form>
</div>

<?php include "../includes/footer.php"; ?>

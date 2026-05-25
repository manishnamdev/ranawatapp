<?php
session_start();
include "../config/db.php";
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

?>

<?php include "includes/admin_header.php"; ?>

<div class="container mt-4">
    <h6 class="fw-bold mb-3">Voting Settings</h6>

    <form method="post">
        <label>Start Date & Time</label>
        <input type="datetime-local" name="start" class="form-control mb-2" required>

        <label>End Date & Time</label>
        <input type="datetime-local" name="end" class="form-control mb-3" required>

        <button class="btn btn-success w-100">Save & Activate</button>
    </form>
</div>

<?php
if ($_POST) {
    $stmt = $conn->prepare("
        UPDATE voting_settings 
        SET start_datetime=?, end_datetime=?, is_active=1
        WHERE id=1
    ");
    $stmt->bind_param("ss", $_POST['start'], $_POST['end']);
    $stmt->execute();
}
?>

<?php include "includes/admin_footer.php"; ?>

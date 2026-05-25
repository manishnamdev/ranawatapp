<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-4">
    <h5 class="fw-bold mb-3">Admin Login Logs</h5>

    <table class="table table-bordered table-sm">
        <thead class="table-light">
            <tr>
                <th>Admin</th>
                <th>Login Time</th>
                <th>Logout Time</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $logs = $conn->query("
            SELECT a.name, l.login_time, l.logout_time, l.ip_address
            FROM admin_login_logs l
            JOIN admins a ON a.id = l.admin_id
            ORDER BY l.id DESC
        ");

        while ($row = $logs->fetch_assoc()) {
            echo "<tr>
                <td>{$row['name']}</td>
                <td>{$row['login_time']}</td>
                <td>".($row['logout_time'] ?? 'Active')."</td>
                <td>{$row['ip_address']}</td>
            </tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<?php include "../includes/footer.php"; ?>

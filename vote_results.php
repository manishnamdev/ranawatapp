<?php
session_start();
include "config/db.php";

date_default_timezone_set("Asia/Kolkata");

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$member_id = $_SESSION['member_id'];

// get vote
$res = $conn->query("
    SELECT vote_option, created_at
    FROM votes
    WHERE member_id = $member_id
");

if ($res->num_rows == 0) {
    header("Location: dashboard.php");
    exit;
}

$vote = $res->fetch_assoc();

$voteLabel = [
    'yes' => 'हाँ',
    'no'  => 'नहीं'
];
?>

<?php include "includes/front_header.php"; ?>

<style>
.rounded-xl { border-radius: 18px; }
</style>

<div class="container mt-4">

    <!-- VOTE SUCCESS CARD -->
    <div class="card shadow-sm rounded-xl mb-3"
         style="background: linear-gradient(135deg, #43e97b, #38f9d7); color:#fff;">
        <div class="card-body text-center">

            <div style="font-size:42px; margin-bottom:10px;">
                ✅
            </div>

            <h6 class="fw-bold mb-2">
                आपका वोट सफलतापूर्वक दर्ज हो चुका है
            </h6>

            <p class="mb-2" style="font-size:16px;">
                आपने
                <span style="
                    background:#ffffff;
                    color:#28a745;
                    padding:4px 12px;
                    border-radius:20px;
                    font-weight:600;
                ">
                    <?= $voteLabel[$vote['vote_option']] ?>
                </span>
                को वोट दिया है
            </p>

            <small>
                मतदान समय :
                <?= date("d M Y, h:i A", strtotime($vote['created_at'])) ?>
            </small>

        </div>
    </div>

    <!-- DASHBOARD BUTTON -->
    <div class="text-center">
        <a href="dashboard.php" class="btn btn-outline-primary btn-sm">
            ⬅ डैशबोर्ड पर जाएँ
        </a>
    </div>

</div>

<?php include "includes/front_footer.php"; ?>

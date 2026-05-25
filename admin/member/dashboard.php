<?php
session_start();
include "config/db.php";

date_default_timezone_set("Asia/Kolkata");
$conn->query("SET time_zone = '+05:30'");

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['member_id'];

$member = $conn->query("SELECT * FROM members WHERE id=$id")->fetch_assoc();

if ($member['status'] != 'approved') {
    header("Location: pending.php");
    exit;
}

// voting
$setting = $conn->query("SELECT * FROM voting_settings WHERE id=1")->fetch_assoc();
$now = date("Y-m-d H:i:s");

$voted = $conn->query("SELECT * FROM votes WHERE member_id=$id");
$hasVoted = $voted->num_rows > 0;
$voteData = $hasVoted ? $voted->fetch_assoc() : null;

$voteLabel = [
    'yes' => 'हाँ',
    'no'  => 'नहीं'
];
?>

<?php include "includes/front_header.php"; ?>
<style>
.card-gradient-blue {
    background: linear-gradient(135deg, #4facfe, #00c6ff);
    color: #fff;
}
.card-gradient-green {
    background: linear-gradient(135deg, #43e97b, #38f9d7);
    color: #fff;
}
.card-gradient-purple {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: #fff;
}
.card-gradient-orange {
    background: linear-gradient(135deg, #f7971e, #ffd200);
    color: #000;
}

.rounded-xl {
    border-radius: 22px;
}

.icon-circle {
    width: 78px;
    height: 78px;
    border-radius: 50%;
    background: rgba(255,255,255,0.22);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    margin: auto;
    box-shadow: inset 0 0 0 2px rgba(255,255,255,0.25);
}

.profile-name {
    font-size: 20px;
    font-weight: 700;
    letter-spacing: 0.3px;
}

.profile-sub {
    font-size: 13px;
    opacity: 0.9;
}

.info-line {
    font-size: 16px;
    padding: 4px 0;
}

.vote-title {
    font-size: 17px;
    letter-spacing: 0.4px;
}

.vote-success {
    background: #ffffff;
    color: #333;
    border-radius: 18px;
    padding: 14px;
    font-size: 15px;
}
/* Dark text for better readability */
.card-gradient-blue,
.card-gradient-green {
    color: #1f2937;   /* dark charcoal */
}

.card-gradient-blue .profile-name,
.card-gradient-blue .profile-sub,
.card-gradient-green .info-line {
    color: #1f2937;
}

/* Icons thode soft rakhen */
.card-gradient-blue .icon-circle {
    background: rgba(255,255,255,0.35);
}

</style>


<div class="container mt-4">

    <!-- PROFILE CARD -->
<div class="card card-gradient-blue rounded-xl shadow-sm mb-3">
    <div class="card-body text-center">
        <div class="icon-circle mb-3">👤</div>
<div class="profile-name"><?= $member['name']; ?></div>
<div class="profile-sub">सदस्य प्रोफ़ाइल</div>

    </div>
</div>


    <!-- INFO CARDS -->
<div class="card card-gradient-green rounded-xl shadow-sm mb-3">
    <div class="card-body">
        <div class="info-line"><b>📍 निवासी :</b> <?= $member['nivasi']; ?></div>
        <div class="info-line"><b>🧬 अवटंग :</b> <?= $member['avtang']; ?></div>
        <div class="info-line"><b>🔱 गोत्र :</b> <?= $member['gotra']; ?></div>
    </div>
</div>


 <div class="card card-gradient-purple rounded-xl shadow-sm mb-3">
    <div class="card-body text-center">

        <div class="vote-title fw-bold mb-3">🗳️ मतदान स्थिति</div>

        <?php
        if ($member['is_canvote'] != 1) {
            echo '<div class="card card-gradient-orange rounded-xl p-2">
                    आपको मतदान का अधिकार नहीं है।
                  </div>';
        }
        elseif ($setting['is_active'] != 1 ||
                $now < $setting['start_datetime'] ||
                $now > $setting['end_datetime']) {

            echo '<div class="card card-gradient-orange rounded-xl p-2">
                    फिलहाल मतदान उपलब्ध नहीं है।
                  </div>';
        }
        elseif ($hasVoted) {

            echo '<div class="vote-success">
                    <b>✅ आपका वोट दर्ज हो चुका है</b><br>
                    आपने 
                    <span class="fw-bold text-success">'
                    .$voteLabel[$voteData['vote_option']] .
                    '</span> 
                    को वोट दिया है
                  </div>';
        }
        else {
            echo '<a href="vote.php" class="btn btn-light fw-bold w-100 py-2">
                    🗳️ वोट करें
                  </a>';
        }
        ?>

    </div>
</div>


    <!-- LOGOUT -->
    <div class="text-center">
        <a href="logout.php" class="btn btn-outline-danger btn-sm">
            लॉगआउट
        </a>
    </div>

</div>

<?php include "includes/front_footer.php"; ?>

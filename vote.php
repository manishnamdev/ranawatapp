<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$member_id = $_SESSION['member_id'];

$member = $conn->query("
    SELECT is_canvote FROM members WHERE id=$member_id
")->fetch_assoc();

if ($member['is_canvote'] != 1) {
     header("Location: vote_results.php");
    exit;
}

// already voted?
$check = $conn->query("
    SELECT * FROM votes WHERE member_id=$member_id
");
if ($check->num_rows > 0) {
    header("Location: vote_results.php");
    exit;
}

// voting time
$setting = $conn->query("SELECT * FROM voting_settings WHERE id=1")->fetch_assoc();
$now = strtotime(date("Y-m-d H:i:s"));
$end = strtotime($setting['end_datetime']);

if ($now < strtotime($setting['start_datetime']) || $now > $end) {
    die("वोटिंग समय समाप्त हो चुका है");
}

$remaining = $end - $now;
?>

<?php include "includes/front_header.php"; ?>

<div class="container mt-4">
    <h6 class="fw-bold text-center">ऋषिकुल चुनाव में जिस तरह से अध्यक्ष मनोनीत करने का षड्यंत्र किया गया क्या वो गलत है और उस पर पुनर्विचार होना चाहिए ?</h6>

    <p class="text-center text-danger">
        शेष समय: <span id="timer"></span>
    </p>

   <form method="post" action="vote_submit.php" onsubmit="return confirmVote();">

    <div class="vote-option">
        <label>
            <input type="radio" name="vote" value="yes" required>
            <span>🔘 हाँ</span>
        </label>
    </div>

    <div class="vote-option">
        <label>
            <input type="radio" name="vote" value="no">
            <span>🔘 नहीं</span>
        </label>
    </div>

    <button class="btn btn-success btn-lg w-100 mt-3">
        वोट सबमिट करें
    </button>
</form>

</div>

<script>
let seconds = <?= $remaining ?>;
setInterval(() => {
    if (seconds <= 0) location.reload();
    let m = Math.floor(seconds / 60);
    let s = seconds % 60;
    document.getElementById("timer").innerHTML = m+"m "+s+"s";
    seconds--;
}, 1000);
</script>
<style>
    <style>
.vote-option {
    border: 2px solid #ddd;
    border-radius: 14px;
    padding: 15px;
    margin-bottom: 20px;
    font-size: 18px;
    cursor: pointer;
}

.vote-option label {
    display: flex;
    align-items: center;
    gap: 15px;
    cursor: pointer;
}

.vote-option input[type="radio"] {
    width: 26px;
    height: 26px;
    accent-color: #28a745;
}

.vote-option:hover {
    background: #f8f9fa;
}
</style>

 <script>
function confirmVote() {
    return confirm(
        "⚠️ आपका वोट बदला नहीं जा सकता।\n\nक्या आप अपना वोट कन्फर्म करना चाहते हैं?"
    );
}
</script>

<?php include "includes/front_footer.php"; ?>

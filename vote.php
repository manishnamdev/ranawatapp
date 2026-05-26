<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$member_id = $_SESSION['member_id'];
$poll_id = isset($_GET['poll_id']) ? (int) $_GET['poll_id'] : 0;

$member = $conn->query("
    SELECT is_canvote FROM members WHERE id=$member_id
")->fetch_assoc();

if ($member['is_canvote'] != 1) {
     header("Location: dashboard.php");
    exit;
}

// Get the specific poll
if ($poll_id == 0) {
    $poll = $conn->query("SELECT * FROM polls WHERE is_active=1 ORDER BY id DESC LIMIT 1")->fetch_assoc();
    if ($poll) $poll_id = $poll['id'];
} else {
    $poll = $conn->query("SELECT * FROM polls WHERE id=$poll_id")->fetch_assoc();
}

if (!$poll) {
    die("कोई सक्रिय मतदान नहीं है (No active poll found)");
}

// already voted on this poll?
$check = $conn->query("
    SELECT * FROM votes WHERE member_id=$member_id AND poll_id=$poll_id
");
if ($check->num_rows > 0) {
    header("Location: dashboard.php");
    exit;
}

// voting time
$now = strtotime(date("Y-m-d H:i:s"));
$end = strtotime($poll['end_datetime']);

if ($now < strtotime($poll['start_datetime']) || $now > $end) {
    die("वोटिंग समय समाप्त हो चुका है");
}

$remaining = $end - $now;

// Fetch options
$options = $conn->query("SELECT * FROM poll_options WHERE poll_id=$poll_id");
?>

<?php include "includes/front_header.php"; ?>

<div class="container mt-4">
    <h6 class="fw-bold text-center mb-4"><?= nl2br(htmlspecialchars($poll['question'])) ?></h6>

    <p class="text-center text-danger">
        शेष समय: <span id="timer"></span>
    </p>

   <form method="post" action="vote_submit.php" onsubmit="return confirmVote();">
    <input type="hidden" name="poll_id" value="<?= $poll_id ?>">

    <?php while ($opt = $options->fetch_assoc()): ?>
    <div class="vote-option">
        <label>
            <input type="radio" name="poll_option_id" value="<?= $opt['id'] ?>" required>
            <span>🔘 <?= htmlspecialchars($opt['option_text']) ?></span>
        </label>
    </div>
    <?php endwhile; ?>

    <button type="submit" class="btn btn-success btn-lg w-100 mt-3">
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
.vote-option {
    border: 2px solid #ddd;
    border-radius: 14px;
    padding: 15px;
    margin-bottom: 20px;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.2s;
}

.vote-option label {
    display: flex;
    align-items: center;
    gap: 15px;
    cursor: pointer;
    margin: 0;
    width: 100%;
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

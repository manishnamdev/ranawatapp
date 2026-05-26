<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get Selected Poll or Default to latest active/created
$poll_id = isset($_GET['poll_id']) ? (int) $_GET['poll_id'] : 0;

$polls_query = $conn->query("SELECT id, question, is_active FROM polls ORDER BY id DESC");
$polls = [];
while($p = $polls_query->fetch_assoc()) {
    $polls[] = $p;
    if ($poll_id == 0 && $p['is_active'] == 1) {
        $poll_id = $p['id'];
    }
}
// fallback if no active poll
if ($poll_id == 0 && count($polls) > 0) {
    $poll_id = $polls[0]['id'];
}

$poll_info = null;
if ($poll_id > 0) {
    $poll_info = $conn->query("SELECT * FROM polls WHERE id=$poll_id")->fetch_assoc();
}

$poll_options = [];
if ($poll_id > 0) {
    $opt_q = $conn->query("SELECT * FROM poll_options WHERE poll_id=$poll_id");
    while($o = $opt_q->fetch_assoc()) {
        $poll_options[$o['id']] = $o['option_text'];
    }
}

// Fetch Votes for this poll
$grouped = [];
$total_votes = 0;

if ($poll_id > 0) {
    // If we support legacy votes (poll_id is NULL), we might want a legacy view, but for dynamic we strictly use poll_id.
    // If poll_id is old and we didn't migrate old votes to have a poll_id, they won't show here unless we specify.
    $all_votes = $conn->query("
        SELECT v.poll_option_id, v.vote_option, m.name, m.nivasi
        FROM votes v
        JOIN members m ON v.member_id = m.id
        WHERE v.poll_id = $poll_id OR (v.poll_id IS NULL AND $poll_id = 0)
        ORDER BY m.name
    ");

    while ($row = $all_votes->fetch_assoc()) {
        $opt_key = $row['poll_option_id'] ? $row['poll_option_id'] : $row['vote_option'];
        $grouped[$opt_key][] = $row;
        $total_votes++;
    }
}

$colors = ['success', 'primary', 'danger', 'warning', 'info', 'secondary'];
?>

<?php include "includes/admin_header.php"; ?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">मतदान परिणाम (Results)</h5>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">⬅ Dashboard</a>
    </div>

    <!-- Poll Selector -->
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <form method="get" class="d-flex align-items-center gap-2">
                <select name="poll_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach($polls as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $p['id'] == $poll_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars(mb_strimwidth($p['question'], 0, 50, '...')) ?>
                            <?= $p['is_active'] ? '(Active)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <?php if ($poll_info): ?>
    
        <div class="alert alert-light border shadow-sm mb-4">
            <h6 class="fw-bold mb-1">Question:</h6>
            <p class="mb-0 text-dark"><?= nl2br(htmlspecialchars($poll_info['question'])) ?></p>
        </div>

        <!-- Summary Section -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body text-center py-4">
                        <h1 class="display-4 fw-bold mb-0"><?= $total_votes ?></h1>
                        <p class="mb-0 opacity-75">कुल मतदान (Total Votes Cast)</p>
                    </div>
                </div>
            </div>
            
            <?php 
            $i = 0;
            foreach ($poll_options as $opt_id => $opt_text): 
                $count = isset($grouped[$opt_id]) ? count($grouped[$opt_id]) : 0;
                $percent = $total_votes > 0 ? round(($count / $total_votes) * 100, 1) : 0;
                $color = $colors[$i % count($colors)];
                $i++;
            ?>
                <div class="col-6">
                    <div class="card border-0 shadow-sm border-start border-4 border-<?= $color ?>">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="text-muted small mb-1 fw-bold"><?= htmlspecialchars($opt_text) ?></p>
                                    <h4 class="fw-bold mb-0 text-<?= $color ?>"><?= $count ?></h4>
                                </div>
                                <div class="text-center">
                                    <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?> rounded-pill px-3">
                                        <?= $percent ?>%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <hr class="my-4 opacity-10">

        <h6 class="fw-bold mb-3">सदस्य विवरण (Member Details)</h6>
        
        <!-- Detailed Drill-down -->
        <div class="accordion shadow-sm" id="voteAccordion">
            <?php 
            $i = 0;
            foreach ($poll_options as $opt_id => $opt_text): 
                $members = $grouped[$opt_id] ?? [];
                $count = count($members);
                $color = $colors[$i % count($colors)];
                $i++;
            ?>
                <div class="accordion-item border-0 mb-2 overflow-hidden rounded-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $opt_id ?>">
                            <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                <span class="fw-bold text-<?= $color ?>">
                                    <?= htmlspecialchars($opt_text) ?>
                                </span>
                                <span class="badge bg-<?= $color ?> rounded-pill">
                                    <?= $count ?> सदस्य
                                </span>
                            </div>
                        </button>
                    </h2>
                    <div id="collapse-<?= $opt_id ?>" class="accordion-collapse collapse" data-bs-parent="#voteAccordion">
                        <div class="accordion-body p-0">
                            <?php if ($count > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle" style="font-size: 14px;">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3 py-2">नाम (Name)</th>
                                                <th>निवासी (Nivasi)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($members as $m): ?>
                                                <tr>
                                                    <td class="ps-3 py-2 fw-medium"><?= htmlspecialchars($m['name']); ?></td>
                                                    <td><?= htmlspecialchars($m['nivasi']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="p-4 text-center text-muted italic">
                                    No votes for this option.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <div class="alert alert-warning text-center">No polls available.</div>
    <?php endif; ?>

</div>

<style>
    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        box-shadow: none;
    }
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }
    .accordion-button::after {
        background-size: 1rem;
    }
    .bg-success-subtle { background-color: #d1e7dd !important; }
    .bg-primary-subtle { background-color: #cfe2ff !important; }
    .bg-danger-subtle { background-color: #f8d7da !important; }
    .bg-warning-subtle { background-color: #fff3cd !important; }
    .bg-info-subtle { background-color: #cff4fc !important; }
    .bg-secondary-subtle { background-color: #e2e3e5 !important; }
</style>

<?php include "includes/admin_footer.php"; ?>


<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$all_votes = $conn->query("
    SELECT v.vote_option, m.name, m.nivasi, m.avtang
    FROM votes v
    JOIN members m ON v.member_id = m.id
    ORDER BY v.vote_option, m.name
");

$grouped = [];
$total_votes = 0;
while ($row = $all_votes->fetch_assoc()) {
    $grouped[$row['vote_option']][] = $row;
    $total_votes++;
}

// Hindi labels mapping
$labels = [
    'yes' => ['label' => 'हाँ (Yes)', 'color' => 'success', 'icon' => 'check-circle'],
    'no'  => ['label' => 'नहीं (No)', 'color' => 'danger', 'icon' => 'x-circle']
];
?>

<?php include "includes/admin_header.php"; ?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">मतदान परिणाम (Vote Results)</h5>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
            ⬅ Dashboard
        </a>
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
        
        <?php foreach ($labels as $option => $info): 
            $count = isset($grouped[$option]) ? count($grouped[$option]) : 0;
            $percent = $total_votes > 0 ? round(($count / $total_votes) * 100, 1) : 0;
        ?>
            <div class="col-6">
                <div class="card border-0 shadow-sm border-start border-4 border-<?= $info['color'] ?>">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted small mb-1"><?= $info['label'] ?></p>
                                <h4 class="fw-bold mb-0 text-<?= $info['color'] ?>"><?= $count ?></h4>
                            </div>
                            <div class="text-center">
                                <span class="badge bg-<?= $info['color'] ?>-subtle text-<?= $info['color'] ?> rounded-pill px-3">
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
    <p class="text-muted small mb-3">ऑप्शन पर क्लिक करें उन सदस्यों की सूची देखने के लिए जिन्होंने उसे चुना है। (Click an option to view member list)</p>

    <!-- Detailed Drill-down -->
    <div class="accordion shadow-sm" id="voteAccordion">
        <?php foreach ($labels as $option => $info): 
            $members = $grouped[$option] ?? [];
            $count = count($members);
        ?>
            <div class="accordion-item border-0 mb-2 overflow-hidden rounded-3">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $option ?>">
                        <div class="d-flex justify-content-between align-items-center w-100 me-3">
                            <span class="fw-bold text-<?= $info['color'] ?>">
                                <?= $info['label'] ?>
                            </span>
                            <span class="badge bg-<?= $info['color'] ?> rounded-pill">
                                <?= $count ?> सदस्य
                            </span>
                        </div>
                    </button>
                </h2>
                <div id="collapse-<?= $option ?>" class="accordion-collapse collapse" data-bs-parent="#voteAccordion">
                    <div class="accordion-body p-0">
                        <?php if ($count > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle" style="font-size: 14px;">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3 py-2">नाम (Name)</th>
                                            <th>निवासी (Nivasi)</th>
                                            <th class="pe-3">अवतंग (Avtang)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($members as $m): ?>
                                            <tr>
                                                <td class="ps-3 py-2 fw-medium"><?= htmlspecialchars($m['name']); ?></td>
                                                <td><?= htmlspecialchars($m['nivasi']); ?></td>
                                                <td class="pe-3"><?= htmlspecialchars($m['avtang']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="p-4 text-center text-muted italic">
                                इस विकल्प के लिए कोई वोट नहीं मिला। (No votes for this option)
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

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
    .bg-danger-subtle { background-color: #f8d7da !important; }
</style>

<?php include "includes/admin_footer.php"; ?>


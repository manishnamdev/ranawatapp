<?php
session_start();
include "../config/db.php";
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Form Submission for new poll
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'create_poll') {
    $question = $_POST['question'];
    $start = $_POST['start'];
    $end = $_POST['end'];
    $options = $_POST['options']; // Array of options

    // Deactivate all existing polls if we want only 1 active. (Optional, here we'll just set it active)
    $conn->query("UPDATE polls SET is_active=0");

    $stmt = $conn->prepare("INSERT INTO polls (question, start_datetime, end_datetime, is_active) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("sss", $question, $start, $end);
    $stmt->execute();
    $poll_id = $conn->insert_id;

    $opt_stmt = $conn->prepare("INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)");
    foreach ($options as $opt) {
        $opt = trim($opt);
        if ($opt !== '') {
            $opt_stmt->bind_param("is", $poll_id, $opt);
            $opt_stmt->execute();
        }
    }
    
    header("Location: voting_settings.php?success=1");
    exit;
}

// Handle toggle active
if (isset($_GET['activate_id'])) {
    $activate_id = (int) $_GET['activate_id'];
    $conn->query("UPDATE polls SET is_active=0");
    $conn->query("UPDATE polls SET is_active=1 WHERE id=$activate_id");
    header("Location: voting_settings.php");
    exit;
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $del_id = (int) $_GET['delete_id'];
    $conn->query("DELETE FROM polls WHERE id=$del_id"); // CASCADE will delete options
    header("Location: voting_settings.php");
    exit;
}
?>

<?php include "includes/admin_header.php"; ?>

<div class="container mt-4">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Poll created successfully!</div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white fw-bold">
            Create New Poll
        </div>
        <div class="card-body">
            <form method="post">
                <input type="hidden" name="action" value="create_poll">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Poll Question</label>
                    <textarea name="question" class="form-control" rows="2" required placeholder="Enter your question here..."></textarea>
                </div>

                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label fw-bold">Start Date & Time</label>
                        <input type="datetime-local" name="start" class="form-control" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label fw-bold">End Date & Time</label>
                        <input type="datetime-local" name="end" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Options</label>
                    <div id="options-container">
                        <div class="input-group mb-2">
                            <input type="text" name="options[]" class="form-control" placeholder="Option 1" required>
                        </div>
                        <div class="input-group mb-2">
                            <input type="text" name="options[]" class="form-control" placeholder="Option 2" required>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addOption()">+ Add Another Option</button>
                </div>

                <button type="submit" class="btn btn-success w-100 fw-bold">Create & Activate Poll</button>
            </form>
        </div>
    </div>

    <h5 class="fw-bold mb-3">Manage Polls</h5>
    
    <?php
    $polls = $conn->query("SELECT * FROM polls ORDER BY id DESC");
    while ($p = $polls->fetch_assoc()):
        $p_id = $p['id'];
        $optQuery = $conn->query("SELECT * FROM poll_options WHERE poll_id=$p_id");
    ?>
    <div class="card shadow-sm mb-3 <?= $p['is_active'] ? 'border-success border-2' : '' ?>">
        <div class="card-body">
            <h6 class="fw-bold"><?= htmlspecialchars($p['question']) ?></h6>
            <p class="mb-2" style="font-size: 12px;">
                <span class="text-muted">Start:</span> <?= date('d M Y h:i A', strtotime($p['start_datetime'])) ?> | 
                <span class="text-muted">End:</span> <?= date('d M Y h:i A', strtotime($p['end_datetime'])) ?>
            </p>
            <ul class="mb-3 ps-3" style="font-size: 13px;">
                <?php while ($opt = $optQuery->fetch_assoc()): ?>
                    <li><?= htmlspecialchars($opt['option_text']) ?></li>
                <?php endwhile; ?>
            </ul>
            
            <div class="d-flex justify-content-between align-items-center">
                <?php if ($p['is_active']): ?>
                    <span class="badge bg-success">Active Poll</span>
                <?php else: ?>
                    <a href="?activate_id=<?= $p_id ?>" class="btn btn-sm btn-outline-primary">Set Active</a>
                <?php endif; ?>
                
                <a href="?delete_id=<?= $p_id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this poll?')">Delete</a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>

</div>

<script>
let optCount = 2;
function addOption() {
    optCount++;
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `<input type="text" name="options[]" class="form-control" placeholder="Option ${optCount}" required>
                     <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">X</button>`;
    document.getElementById('options-container').appendChild(div);
}
</script>

<?php include "includes/admin_footer.php"; ?>

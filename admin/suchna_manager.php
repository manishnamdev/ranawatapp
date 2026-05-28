<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$upload_dir = "../uploads/suchnas/";

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $title = trim($_POST['title']);
    $short_desc = trim($_POST['short_description']);
    $datetime = $_POST['datetime'];
    
    // Thumbnail upload
    $thumb_name = '';
    if (isset($_FILES['thumb_image']) && $_FILES['thumb_image']['error'] == 0) {
        $ext = pathinfo($_FILES['thumb_image']['name'], PATHINFO_EXTENSION);
        $thumb_name = "thumb_" . time() . "_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['thumb_image']['tmp_name'], $upload_dir . $thumb_name);
    }

    $stmt = $conn->prepare("INSERT INTO suchnas (title, short_description, thumb_image, datetime) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $short_desc, $thumb_name, $datetime);
    $stmt->execute();
    $suchna_id = $conn->insert_id;

    // Multi-image upload (max 5)
    if (isset($_FILES['extra_images'])) {
        $img_count = count($_FILES['extra_images']['name']);
        // limit to 5
        $img_count = min($img_count, 5);
        $stmt_img = $conn->prepare("INSERT INTO suchna_images (suchna_id, image_path) VALUES (?, ?)");
        
        for ($i = 0; $i < $img_count; $i++) {
            if ($_FILES['extra_images']['error'][$i] == 0) {
                $ext = pathinfo($_FILES['extra_images']['name'][$i], PATHINFO_EXTENSION);
                $img_name = "extra_" . time() . "_" . $i . uniqid() . "." . $ext;
                move_uploaded_file($_FILES['extra_images']['tmp_name'][$i], $upload_dir . $img_name);
                
                $stmt_img->bind_param("is", $suchna_id, $img_name);
                $stmt_img->execute();
            }
        }
    }

    header("Location: suchna_manager.php?success=add");
    exit;
}

// Handle Delete
if (isset($_GET['delete_id'])) {
    $del_id = (int) $_GET['delete_id'];
    
    // Delete files first
    $suchna = $conn->query("SELECT thumb_image FROM suchnas WHERE id=$del_id")->fetch_assoc();
    if ($suchna && $suchna['thumb_image'] && file_exists($upload_dir . $suchna['thumb_image'])) {
        unlink($upload_dir . $suchna['thumb_image']);
    }
    $images = $conn->query("SELECT image_path FROM suchna_images WHERE suchna_id=$del_id");
    while($img = $images->fetch_assoc()) {
        if ($img['image_path'] && file_exists($upload_dir . $img['image_path'])) {
            unlink($upload_dir . $img['image_path']);
        }
    }
    
    $conn->query("DELETE FROM suchnas WHERE id=$del_id"); // CASCADE deletes related suchna_images
    header("Location: suchna_manager.php?success=delete");
    exit;
}

$result = $conn->query("SELECT * FROM suchnas ORDER BY id DESC");
?>

<?php include "includes/admin_header.php"; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">सूचना प्रबंधक (Notice Manager)</h5>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Action completed successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Add Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white fw-bold">नयी सूचना जोड़ें (Add Notice)</div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Title (शीर्षक)</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Short Description (संक्षिप्त विवरण)</label>
                    <textarea name="short_description" class="form-control" rows="3" required></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Date & Time (समय)</label>
                        <input type="datetime-local" name="datetime" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Thumbnail Image (मुख्य फोटो)</label>
                        <input type="file" name="thumb_image" class="form-control" accept="image/*" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Extra Images (अधिकतम 5 फोटो)</label>
                    <input type="file" name="extra_images[]" class="form-control" accept="image/*" multiple max="5">
                    <div class="form-text">You can select up to 5 additional images.</div>
                </div>
                
                <button type="submit" class="btn btn-success w-100 fw-bold">Publish Notice</button>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Thumb</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="ps-3">
                            <img src="../uploads/suchnas/<?= htmlspecialchars($row['thumb_image']) ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                        </td>
                        <td class="fw-bold"><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= date('d M Y h:i A', strtotime($row['datetime'])) ?></td>
                        <td class="text-end pe-3">
                            <a href="?delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('क्या आप वाकई डिलीट करना चाहते हैं?')">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($result->num_rows == 0): ?>
                    <tr><td colspan="4" class="text-center py-4 text-muted">No notices found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Limit file selection to 5
document.querySelector('input[name="extra_images[]"]').addEventListener('change', function() {
    if (this.files.length > 5) {
        alert("You can only select a maximum of 5 images.");
        this.value = ''; // clear selection
    }
});
</script>

<?php include "includes/admin_footer.php"; ?>

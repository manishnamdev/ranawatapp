<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("Location: suchna_manager.php");
    exit;
}

$upload_dir = "../uploads/suchnas/";

// Handle Post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $short_desc = trim($_POST['short_description']);
    $datetime = $_POST['datetime'];

    $suchna = $conn->query("SELECT thumb_image FROM suchnas WHERE id=$id")->fetch_assoc();
    $thumb_name = $suchna['thumb_image'];

    // Update Thumbnail if new uploaded
    if (isset($_FILES['thumb_image']) && $_FILES['thumb_image']['error'] == 0) {
        $ext = pathinfo($_FILES['thumb_image']['name'], PATHINFO_EXTENSION);
        $thumb_name = "thumb_" . time() . "_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['thumb_image']['tmp_name'], $upload_dir . $thumb_name);
        
        // delete old thumb
        if ($suchna['thumb_image'] && file_exists($upload_dir . $suchna['thumb_image'])) {
            unlink($upload_dir . $suchna['thumb_image']);
        }
    }

    $stmt = $conn->prepare("UPDATE suchnas SET title=?, short_description=?, thumb_image=?, datetime=? WHERE id=?");
    $stmt->bind_param("ssssi", $title, $short_desc, $thumb_name, $datetime, $id);
    $stmt->execute();

    // Multi-image upload
    if (isset($_FILES['extra_images'])) {
        // Count existing extra images
        $existing_count = $conn->query("SELECT COUNT(*) as c FROM suchna_images WHERE suchna_id=$id")->fetch_assoc()['c'];
        
        $img_count = count($_FILES['extra_images']['name']);
        
        // We allow max 5 total. So we only upload if existing_count < 5
        $allowed = 5 - $existing_count;
        $img_count = min($img_count, $allowed);
        
        if ($img_count > 0) {
            $stmt_img = $conn->prepare("INSERT INTO suchna_images (suchna_id, image_path) VALUES (?, ?)");
            for ($i = 0; $i < $img_count; $i++) {
                if ($_FILES['extra_images']['error'][$i] == 0) {
                    $ext = pathinfo($_FILES['extra_images']['name'][$i], PATHINFO_EXTENSION);
                    $img_name = "extra_" . time() . "_" . $i . uniqid() . "." . $ext;
                    move_uploaded_file($_FILES['extra_images']['tmp_name'][$i], $upload_dir . $img_name);
                    
                    $stmt_img->bind_param("is", $id, $img_name);
                    $stmt_img->execute();
                }
            }
        }
    }

    header("Location: suchna_manager.php?success=edit");
    exit;
}

// Delete Extra Image Request
if (isset($_GET['delete_img_id'])) {
    $del_img_id = (int)$_GET['delete_img_id'];
    $img_res = $conn->query("SELECT image_path FROM suchna_images WHERE id=$del_img_id AND suchna_id=$id")->fetch_assoc();
    if ($img_res) {
        if ($img_res['image_path'] && file_exists($upload_dir . $img_res['image_path'])) {
            unlink($upload_dir . $img_res['image_path']);
        }
        $conn->query("DELETE FROM suchna_images WHERE id=$del_img_id");
    }
    header("Location: suchna_edit.php?id=$id");
    exit;
}

$suchna = $conn->query("SELECT * FROM suchnas WHERE id=$id")->fetch_assoc();
if (!$suchna) {
    header("Location: suchna_manager.php");
    exit;
}

$extra_images = $conn->query("SELECT * FROM suchna_images WHERE suchna_id=$id");
?>

<?php include "includes/admin_header.php"; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold mb-0">सूचना एडिट करें (Edit Notice)</h5>
        <a href="suchna_manager.php" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Title (शीर्षक)</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($suchna['title']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Short Description (संक्षिप्त विवरण)</label>
                    <textarea name="short_description" class="form-control" rows="3" required><?= htmlspecialchars($suchna['short_description']) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Date & Time (समय)</label>
                        <?php 
                        // format datetime for input type="datetime-local"
                        $dt = date('Y-m-d\TH:i', strtotime($suchna['datetime']));
                        ?>
                        <input type="datetime-local" name="datetime" class="form-control" value="<?= $dt ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Update Thumbnail Image (मुख्य फोटो बदलें)</label>
                        <input type="file" name="thumb_image" id="thumb_image_input" class="form-control" accept="image/*">
                        <div class="mt-2">
                            <span>Current:</span><br>
                            <img id="thumb_preview" src="../uploads/suchnas/<?= htmlspecialchars($suchna['thumb_image']) ?>" style="max-height: 100px; border-radius: 8px;">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Existing Extra Images</label>
                    <div class="d-flex gap-2 flex-wrap mb-2">
                        <?php while($img = $extra_images->fetch_assoc()): ?>
                            <div class="position-relative">
                                <img src="../uploads/suchnas/<?= htmlspecialchars($img['image_path']) ?>" style="height: 80px; border-radius: 8px; object-fit: cover;">
                                <a href="?id=<?= $id ?>&delete_img_id=<?= $img['id'] ?>" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0" style="width:20px;height:20px;line-height:20px;border-radius:50%;" onclick="return confirm('Delete this image?');">&times;</a>
                            </div>
                        <?php endwhile; ?>
                        <?php if($extra_images->num_rows == 0): ?>
                            <div class="text-muted small">No extra images.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Add Extra Images (Total up to 5)</label>
                    <input type="file" name="extra_images[]" id="extra_images_input" class="form-control" accept="image/*" multiple>
                    <div class="form-text">You currently have <?= $extra_images->num_rows ?> extra images. You can add up to <?= 5 - $extra_images->num_rows ?> more.</div>
                    <div id="extra_preview_container" class="mt-2 d-flex gap-2 flex-wrap"></div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 fw-bold">Update Notice</button>
            </form>
        </div>
    </div>
</div>

<script>
const existingCount = <?= $extra_images->num_rows ?>;
const allowedCount = 5 - existingCount;

const extraInput = document.getElementById('extra_images_input');
const extraPreview = document.getElementById('extra_preview_container');

extraInput.addEventListener('change', function() {
    extraPreview.innerHTML = '';
    if (this.files.length > allowedCount) {
        alert("You can only select a maximum of " + allowedCount + " more images.");
        this.value = ''; 
        return;
    }
    
    Array.from(this.files).forEach(file => {
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxHeight = '80px';
                img.style.borderRadius = '8px';
                img.style.objectFit = 'cover';
                extraPreview.appendChild(img);
            }
            reader.readAsDataURL(file);
        }
    });
});

const thumbInput = document.getElementById('thumb_image_input');
const thumbPreview = document.getElementById('thumb_preview');
const originalThumb = thumbPreview.src;

thumbInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            thumbPreview.src = e.target.result;
        }
        reader.readAsDataURL(file);
    } else {
        thumbPreview.src = originalThumb;
    }
});
</script>

<?php include "includes/admin_footer.php"; ?>

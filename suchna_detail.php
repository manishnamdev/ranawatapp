<?php
session_start();
include "config/db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("SELECT * FROM suchnas WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$suchna = $stmt->get_result()->fetch_assoc();

if (!$suchna) {
    header("Location: suchnas.php");
    exit;
}

$images_res = $conn->query("SELECT image_path FROM suchna_images WHERE suchna_id=$id");

include "includes/front_header.php";
?>

<style>
    body {
        background: #f4f6fb;
    }
    .detail-card {
        background: #fff;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        margin-bottom: 20px;
    }
    .main-image {
        width: 100%;
        height: auto;
        border-radius: 12px;
        margin-bottom: 16px;
    }
    .notice-title {
        font-size: 20px;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 8px;
    }
    .notice-meta {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .notice-desc {
        font-size: 15px;
        line-height: 1.6;
        color: #374151;
        white-space: pre-wrap;
    }
    .gallery {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
        margin-top: 20px;
    }
    .gallery img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
    }
</style>

<div class="container mt-3">
    <div class="d-flex align-items-center mb-3">
        <a href="javascript:history.back()" class="text-dark text-decoration-none" style="font-size: 24px;">&larr;</a>
    </div>

    <div class="detail-card">
        <img src="uploads/suchnas/<?= htmlspecialchars($suchna['thumb_image']) ?>" class="main-image" alt="Thumbnail">
        
        <h1 class="notice-title"><?= htmlspecialchars($suchna['title']) ?></h1>
        <div class="notice-meta">
            📅 <?= date('d M Y h:i A', strtotime($suchna['datetime'])) ?>
        </div>

        <div class="notice-desc"><?= nl2br(htmlspecialchars($suchna['short_description'])) ?></div>

        <?php if ($images_res->num_rows > 0): ?>
            <h5 class="mt-4 fw-bold">अधिक तस्वीरें (More Photos)</h5>
            <div class="gallery">
                <?php while ($img = $images_res->fetch_assoc()): ?>
                    <a href="uploads/suchnas/<?= htmlspecialchars($img['image_path']) ?>" target="_blank">
                        <img src="uploads/suchnas/<?= htmlspecialchars($img['image_path']) ?>" alt="Gallery Image">
                    </a>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "includes/front_footer.php"; ?>

<?php
session_start();
include "config/db.php";

$limit = 10; // Items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$total_query = $conn->query("SELECT COUNT(*) as count FROM suchnas");
$total_row = $total_query->fetch_assoc();
$total = $total_row['count'];
$pages = ceil($total / $limit);

$suchnas = $conn->query("SELECT * FROM suchnas ORDER BY id DESC LIMIT $limit OFFSET $offset");

include "includes/front_header.php";
?>

<style>
    .page-title {
        font-size: 20px;
        font-weight: 700;
        margin-top: 10px;
        margin-bottom: 20px;
        text-align: center;
        color: #1f2937;
    }

    .suchna-card {
        background: #fff;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 12px;
        display: flex;
        gap: 12px;
        align-items: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        text-decoration: none;
        color: inherit;
        border: 1px solid #eee;
        transition: transform 0.2s;
    }

    .suchna-card:active {
        transform: scale(0.98);
    }

    .suchna-thumb {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        object-fit: cover;
        flex-shrink: 0;
    }

    .suchna-content h4 {
        margin: 0 0 4px 0;
        font-size: 15px;
        font-weight: 700;
        color: #1f2937;
    }

    .suchna-content p {
        margin: 0;
        font-size: 13px;
        color: #6b7280;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 20px;
    }
    .pagination a {
        padding: 8px 12px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        color: #1f2937;
        text-decoration: none;
        font-weight: 600;
    }
    .pagination a.active {
        background: #3b82f6;
        color: #fff;
        border-color: #3b82f6;
    }
</style>

<div class="container mt-3">
    <div class="d-flex align-items-center mb-3">
        <a href="javascript:history.back()" class="text-dark text-decoration-none" style="font-size: 24px;">&larr;</a>
        <h2 class="page-title w-100 m-0">सभी सूचनाएं (All Notices)</h2>
    </div>

    <?php if ($suchnas && $suchnas->num_rows > 0): ?>
        <?php while ($s = $suchnas->fetch_assoc()): ?>
            <?php
                $words = explode(" ", strip_tags($s['short_description']));
                $short_desc = implode(" ", array_slice($words, 0, 20)) . (count($words) > 20 ? "..." : "");
            ?>
            <a href="suchna_detail.php?id=<?= $s['id'] ?>" class="suchna-card">
                <img src="uploads/suchnas/<?= htmlspecialchars($s['thumb_image']) ?>" alt="Notice Thumb" class="suchna-thumb">
                <div class="suchna-content">
                    <h4><?= htmlspecialchars($s['title']) ?></h4>
                    <p><?= htmlspecialchars($short_desc) ?></p>
                    <span style="font-size: 12px; color: #3b82f6; font-weight: 600; display: inline-block; margin-top: 4px;">View More &rarr;</span>
                </div>
            </a>
        <?php endwhile; ?>

        <?php if ($pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-info text-center mt-4">
            अभी कोई सूचना उपलब्ध नहीं है।
        </div>
    <?php endif; ?>
</div>

<?php include "includes/front_footer.php"; ?>

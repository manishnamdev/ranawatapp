<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['member_id'];
$member = $conn->query("SELECT * FROM members WHERE id=$id")->fetch_assoc();

if (!$member || $member['status'] != 'approved') {
    die("ID Card is only available for approved members.");
}

// Generate a barcode style string or format ID
$formatted_id = sprintf("RS-%04d", $member['id']);
?>
<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <title>ID Card - <?= htmlspecialchars($member['name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Devanagari:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Serif Devanagari', sans-serif;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .id-card-wrapper {
            background: #fff;
            width: 320px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            position: relative;
        }

        .id-card-header {
            background: linear-gradient(135deg, #d32f2f, #f57c00);
            color: #fff;
            text-align: center;
            padding: 16px 12px 60px;
        }

        .id-card-header img {
            width: 40px;
            border-radius: 50%;
            margin-bottom: 6px;
        }

        .id-card-header h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
        }

        .id-card-header p {
            margin: 2px 0 0;
            font-size: 10px;
            opacity: 0.9;
        }

        .id-card-body {
            background: #fff;
            border-top-left-radius: 24px;
            border-top-right-radius: 24px;
            margin-top: -30px;
            padding: 0 20px 20px;
            text-align: center;
            position: relative;
        }

        .profile-pic {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid #fff;
            object-fit: cover;
            background: #eee;
            margin-top: -50px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .member-name {
            font-size: 20px;
            font-weight: 800;
            color: #1f2937;
            margin: 12px 0 4px;
        }

        .member-type {
            display: inline-block;
            background: #fef3c7;
            color: #92400e;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 16px;
            text-transform: uppercase;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            text-align: left;
            margin-bottom: 20px;
        }

        .info-item {
            background: #f8fafc;
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #f1f5f9;
        }

        .info-label {
            font-size: 10px;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .info-value {
            font-size: 13px;
            color: #0f172a;
            font-weight: 700;
        }

        .id-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px dashed #cbd5e1;
        }

        .barcode-section {
            text-align: left;
        }
        
        .barcode-text {
            font-family: monospace;
            font-size: 16px;
            font-weight: bold;
            color: #334155;
            letter-spacing: 1px;
        }

        .signature-section {
            text-align: right;
        }

        .signature-text {
            font-family: 'Brush Script MT', cursive, sans-serif;
            font-size: 20px;
            color: #1e3a8a;
            margin-bottom: 4px;
        }

        .signature-title {
            font-size: 10px;
            color: #64748b;
            font-weight: 700;
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
        }

        .action-btns {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
        }

        .btn {
            background: #1f2937;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            text-decoration: none;
        }

        @media print {
            body { background: #fff; }
            .action-btns { display: none; }
            .id-card-wrapper { box-shadow: none; border: 1px solid #ccc; }
        }
    </style>
</head>
<body>

    <div class="id-card-wrapper">
        <div class="id-card-header">
            <img src="assets/images/logo.jpg" alt="Logo">
            <h2>श्री रंकण भवन रांकावत समाज</h2>
            <p>रानी स्टेशन</p>
        </div>
        
        <div class="id-card-body">
            <?php if (!empty($member['profile_photo'])): ?>
                <img src="uploads/profile_photos/<?= htmlspecialchars($member['profile_photo']); ?>" class="profile-pic" alt="Profile">
            <?php else: ?>
                <div class="profile-pic" style="display:flex;align-items:center;justify-content:center;font-size:40px;">👤</div>
            <?php endif; ?>
            
            <h1 class="member-name"><?= htmlspecialchars($member['name']) ?></h1>
            <div class="member-type"><?= htmlspecialchars($member['membership_type'] ?? 'Yearly') ?> Member</div>

            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Member ID</div>
                    <div class="info-value"><?= $formatted_id ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Mobile</div>
                    <div class="info-value"><?= htmlspecialchars($member['mobile']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Niwas</div>
                    <div class="info-value"><?= htmlspecialchars($member['nivasi']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Gotra</div>
                    <div class="info-value"><?= htmlspecialchars($member['gotra']) ?></div>
                </div>
            </div>

            <div class="id-card-footer">
                <div class="barcode-section">
                    <div class="barcode-text"><?= $formatted_id ?></div>
                </div>
                <div class="signature-section">
                    <div class="signature-text">अध्यक्ष</div>
                    <div class="signature-title">अध्यक्ष (Adyaksh)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="action-btns">
        <a href="dashboard.php" class="btn" style="background: #475569;">&larr; Back</a>
        <button class="btn" onclick="window.print()">🖨️ Print Card</button>
    </div>

</body>
</html>

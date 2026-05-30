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
            flex-direction: column;
        }

        .id-card-wrapper {
            /* 528x878 is ratio 1:1.662. For width 350px, height is 582px */
            width: 350px;
            height: 582px;
            background-image: url('assets/images/id_card_bg.png');
            background-size: 100% 100%;
            background-repeat: no-repeat;
            position: relative;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            border-radius: 4px; /* subtle border radius if needed */
            overflow: hidden;
            background-color: white;
        }

        .profile-pic {
            position: absolute;
            top: 24.5%; /* Adjusted to center in the white circle */
            left: 50%;
            transform: translateX(-50%);
            width: 160px;
            height: 160px;
            border-radius: 50%;
            object-fit: cover;
            /* background: transparent; */
        }

        .member-id-text {
            position: absolute;
            top: 55.2%;
            left: 65%;
            transform: translateX(-50%);
            font-size: 14px;
            font-weight: 800;
            color: #1e3a8a; /* match blue text */
        }

        .info-container {
            position: absolute;
            top: 66%; /* Below "सदस्यता कार्ड" */
            left: 0;
            width: 100%;
            padding: 0 30px;
            box-sizing: border-box;
            text-align: center;
        }

        .info-name {
            font-size: 20px;
            font-weight: 800;
            color: #1e3a8a; /* Blue text for name */
            margin-bottom: 8px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 80px 1fr;
            gap: 4px 10px;
            text-align: left;
            font-size: 13px;
            color: #334155;
            font-weight: 600;
        }

        .info-label {
            color: #1e3a8a;
            font-weight: 700;
        }

        .action-btns {
            margin-top: 20px;
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
            body { background: #fff; padding: 0; }
            .action-btns { display: none; }
            .id-card-wrapper { box-shadow: none; }
        }
    </style>
</head>
<body>

    <div class="id-card-wrapper" id="idCard">
        <?php if (!empty($member['profile_photo'])): ?>
            <img src="uploads/profile_photos/<?= htmlspecialchars($member['profile_photo']); ?>" class="profile-pic" alt="Profile">
        <?php else: ?>
            <div class="profile-pic" style="background:#eee; display:flex;align-items:center;justify-content:center;font-size:50px;">👤</div>
        <?php endif; ?>

        <!-- Placed next to 'सदस्यता क्रमांक :' -->
        <div class="member-id-text"><?= $formatted_id ?></div>

        <div class="info-container">
            <div class="info-name"><?= htmlspecialchars($member['name']) ?></div>
            
            <div class="info-grid">
                <div class="info-label">मोबाईल:</div>
                <div><?= htmlspecialchars($member['mobile']) ?></div>

                <div class="info-label">गोत्र:</div>
                <div><?= htmlspecialchars($member['gotra']) ?></div>

                <div class="info-label">निवास:</div>
                <div><?= htmlspecialchars($member['nivasi']) ?></div>

                <div class="info-label">सदस्यता:</div>
                <div><?= htmlspecialchars($member['membership_type'] ?? 'Yearly') ?> Member</div>
            </div>
        </div>

    </div>

    <div class="action-btns">
        <a href="dashboard.php" class="btn" style="background: #475569;">&larr; Back</a>
        <button class="btn" onclick="window.print()">🖨️ Print Card</button>
    </div>

</body>
</html>

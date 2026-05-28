<?php
session_start();
include "config/db.php";

date_default_timezone_set("Asia/Kolkata");
$conn->query("SET time_zone = '+05:30'");

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['member_id'];

$member = $conn->query("SELECT * FROM members WHERE id=$id")->fetch_assoc();

if ($member['status'] != 'approved') {
    header("Location: pending.php");
    exit;
}

$pushSubscriptionCount = 0;
$pushCheck = $conn->prepare("SELECT COUNT(*) AS total FROM push_subscriptions WHERE member_id = ?");
if ($pushCheck) {
    $pushCheck->bind_param("i", $id);
    $pushCheck->execute();
    $pushResult = $pushCheck->get_result()->fetch_assoc();
    $pushSubscriptionCount = (int) ($pushResult['total'] ?? 0);
}

$poll = $conn->query("SELECT * FROM polls WHERE is_active=1 ORDER BY id DESC LIMIT 1")->fetch_assoc();
$now = date("Y-m-d H:i:s");

$hasVoted = false;
$voteData = null;
if ($poll) {
    $poll_id = $poll['id'];
    $voted = $conn->query("
        SELECT v.*, po.option_text 
        FROM votes v 
        LEFT JOIN poll_options po ON v.poll_option_id = po.id 
        WHERE v.member_id=$id AND v.poll_id=$poll_id
    ");
    $hasVoted = $voted->num_rows > 0;
    $voteData = $hasVoted ? $voted->fetch_assoc() : null;
}

$family_members = $conn->query("SELECT * FROM family_members WHERE member_id=$id ORDER BY created_at ASC");

// Handle Search by Niwas
$search_niwas = $_GET['search_niwas'] ?? '';
$searchResult = null;
if ($search_niwas != '') {
    $stmt = $conn->prepare("
        SELECT m.id, m.name, m.profile_photo, 
               (SELECT COUNT(*) FROM family_members fm WHERE fm.member_id = m.id) as fam_count
        FROM members m 
        WHERE m.nivasi = ? AND m.status = 'approved'
        ORDER BY m.name ASC
    ");
    $stmt->bind_param("s", $search_niwas);
    $stmt->execute();
    $searchResult = $stmt->get_result();
}

// Fetch Niwas list for dropdown
$niwasList = $conn->query("SELECT name FROM niwas ORDER BY name ASC");
?>

<?php include "includes/front_header.php"; ?>
<style>
.dashboard-app {
    display: grid;
    gap: 16px;
}

.app-card {
    border: none;
    border-radius: 26px;
    overflow: hidden;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.10);
}

.member-profile-card {
    background:
        radial-gradient(circle at top right, rgba(255,255,255,0.20), transparent 30%),
        linear-gradient(135deg, #0f7ae5, #18b7de);
    color: #fff;
    padding: 20px 18px 18px;
}

.profile-topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.profile-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 12px;
    border-radius: 999px;
    background: rgba(255,255,255,0.18);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.4px;
}

.profile-heading {
    text-align: center;
    margin-bottom: 14px;
}

.profile-name {
    margin: 0;
    font-size: 27px;
    font-weight: 800;
    line-height: 1.1;
    text-transform: capitalize;
}

.profile-subtitle {
    margin: 5px 0 0;
    font-size: 13px;
    opacity: 0.92;
}

.profile-avatar-container {
    position: relative;
    width: 100px;
    height: 100px;
    margin: 0 auto 15px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    border: 3px solid rgba(255,255,255,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.profile-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-avatar-icon {
    font-size: 40px;
    opacity: 0.8;
}

.profile-upload-btn {
    display: inline-block;
    background: rgba(255,255,255,0.25);
    border: 1px solid rgba(255,255,255,0.4);
    color: #fff;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    margin-top: -5px;
    margin-bottom: 15px;
    transition: background 0.2s;
}

.profile-upload-btn:hover {
    background: rgba(255,255,255,0.4);
}

.profile-table {
    background: rgba(255,255,255,0.16);
    border-radius: 18px;
    overflow: hidden;
}

.profile-row {
    display: grid;
    grid-template-columns: 112px 1fr;
    gap: 12px;
    padding: 11px 14px;
    border-bottom: 1px solid rgba(255,255,255,0.16);
}

.profile-row:last-child {
    border-bottom: none;
}

.profile-key {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.45px;
    opacity: 0.84;
}

.profile-value {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.3;
}

.section-card {
    background: #ffffff;
}

.section-card .card-body {
    padding: 18px;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}

.section-icon {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
    color: #fff;
}

.section-icon-payment { background: linear-gradient(135deg, #0f7ae5, #18b7de); }
.section-icon-aadhar { background: linear-gradient(135deg, #16a34a, #22c55e); }
.section-icon-vote { background: linear-gradient(135deg, #6d5dfc, #8b5cf6); }

.section-title {
    margin: 0;
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
}

.section-desc {
    margin: 2px 0 0;
    font-size: 13px;
    color: #64748b;
}

.status-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 14px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
}

.status-success {
    background: #dcfce7;
    color: #166534;
}

.status-warning {
    background: #fef3c7;
    color: #92400e;
}

.status-muted {
    background: #e5e7eb;
    color: #374151;
}

.payment-preview,
.aadhar-preview {
    display: flex;
    justify-content: center;
    margin-bottom: 14px;
}

.payment-preview img,
.aadhar-preview img {
    width: 100%;
    max-width: 160px;
    border-radius: 18px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.10);
}

.aadhar-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.aadhar-box {
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    padding: 14px;
    background: linear-gradient(180deg, #ffffff, #f8fafc);
}

.aadhar-label {
    font-size: 14px;
    font-weight: 800;
    color: #0f172a;
    text-align: center;
    margin-bottom: 10px;
}

.empty-state {
    text-align: center;
    color: #64748b;
    font-size: 13px;
    padding: 10px 0 6px;
}

.vote-panel {
    border-radius: 20px;
    padding: 14px;
    font-size: 15px;
    text-align: center;
}

.vote-panel-muted {
    background: linear-gradient(135deg, #fff7ed, #fde68a);
    color: #7c2d12;
}

.vote-panel-success {
    background: linear-gradient(135deg, #dcfce7, #86efac);
    color: #14532d;
}

.app-btn {
    border: none;
    border-radius: 16px;
    padding: 12px 14px;
    font-size: 15px;
    font-weight: 700;
}

.app-btn-primary {
    background: linear-gradient(135deg, #0f7ae5, #18b7de);
    color: #fff;
}

.app-btn-danger-soft {
    background: #fff1f2;
    color: #b91c1c;
    border: 1px solid #fecdd3;
}

.logout-wrap {
    padding-bottom: 6px;
    text-align: center;
}

.info-ticker {
    position: relative;
    overflow: hidden;
    border-radius: 18px;
    background: linear-gradient(135deg, #fff7ed, #fde68a);
    border: 1px solid #fcd34d;
    box-shadow: 0 8px 18px rgba(245, 158, 11, 0.14);
}

.info-ticker-track {
    display: inline-block;
    white-space: nowrap;
    padding: 11px 0;
    color: #92400e;
    font-size: 14px;
    font-weight: 700;
    animation: ticker-move 18s linear infinite;
}

.info-ticker-track span {
    display: inline-block;
    padding-left: 100%;
    padding-right: 32px;
}

@keyframes ticker-move {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-100%);
    }
}

@media (max-width: 576px) {
    .profile-name {
        font-size: 23px;
    }

    .profile-row {
        grid-template-columns: 88px 1fr;
        gap: 10px;
    }

    .aadhar-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="container mt-4">
    <div class="dashboard-app">
        <?php if ($pushSubscriptionCount === 0): ?>
            <div class="info-ticker">
                <div class="info-ticker-track">
                    <span>आवश्यक सूचनाएं और नोटिफिकेशन प्राप्त करने के लिए, नीचे दिए गए Notification button को क्लिक करें।</span>
                </div>
            </div>
        <?php endif; ?>

        <section class="app-card member-profile-card">
            <div class="profile-topbar">
                <span class="profile-badge">Approved Member</span>
                <span class="profile-badge">ID #<?= (int) $member['id']; ?></span>
            </div>

            <div class="profile-heading text-center">
                <div class="profile-avatar-container">
                    <?php if (!empty($member['profile_photo'])): ?>
                        <img src="uploads/profile_photos/<?= htmlspecialchars($member['profile_photo']); ?>" class="profile-avatar-img" alt="Profile Photo">
                    <?php else: ?>
                        <div class="profile-avatar-icon">👤</div>
                    <?php endif; ?>
                </div>
                
                <form action="profile_photo_upload.php" method="POST" enctype="multipart/form-data" id="profilePhotoForm">
                    <label for="profilePhotoInput" class="profile-upload-btn">
                        🖼️ Update Photo
                    </label>
                    <div style="font-size: 11px; opacity: 0.85; margin-top: -10px; margin-bottom: 12px; font-weight: 500;">
                        Max Size: 500 KB
                    </div>
                    <input type="file" id="profilePhotoInput" name="profile_photo" accept="image/*" style="display:none;" onchange="document.getElementById('profilePhotoForm').submit();">
                </form>

                <h1 class="profile-name"><?= htmlspecialchars($member['name']); ?></h1>
                <p class="profile-subtitle">सदस्य प्रोफ़ाइल</p>
            </div>

            <div class="profile-table">
                <div class="profile-row">
                    <div class="profile-key">निवासी</div>
                    <div class="profile-value"><?= htmlspecialchars($member['nivasi']); ?></div>
                </div>

                <div class="profile-row">
                    <div class="profile-key">गोत्र</div>
                    <div class="profile-value"><?= htmlspecialchars($member['gotra']); ?></div>
                </div>

                <?php if (!empty($member['haal_niwas'])): ?>
                <div class="profile-row">
                    <div class="profile-key">हाल निवास</div>
                    <div class="profile-value"><?= htmlspecialchars($member['haal_niwas']); ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($member['mool_niwas'])): ?>
                <div class="profile-row">
                    <div class="profile-key">मूल निवास</div>
                    <div class="profile-value"><?= htmlspecialchars($member['mool_niwas']); ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($member['vyavsaya'])): ?>
                <div class="profile-row">
                    <div class="profile-key">व्यवसाय / प्रतिष्ठान</div>
                    <div class="profile-value"><?= htmlspecialchars($member['vyavsaya']); ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="mt-3">
                 <a href="id_card.php" class="app-btn w-100 d-block text-center text-decoration-none" style="background: #ffffff; color: #0f7ae5; font-weight: 800; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                     🪪 मेरा ID कार्ड (View ID Card)
                 </a>
            </div>
        </section>

        <section class="card app-card section-card">
            <div class="card-body">
                <div class="section-header">
                    <span class="section-icon section-icon-payment">💳</span>
                    <div>
                        <h2 class="section-title">भुगतान स्थिति</h2>
                        <p class="section-desc">सदस्यता शुल्क का रिकॉर्ड और स्क्रीनशॉट</p>
                    </div>
                </div>

                <?php if (empty($member['payment_screenshot'])): ?>
                    <form action="upload_payment.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <input type="file" name="payment_image" class="form-control" accept="image/*" required>
                        </div>

                        <button type="submit" class="app-btn app-btn-primary w-100">
                            भुगतान स्क्रीनशॉट अपलोड करें
                        </button>
                    </form>
                <?php else: ?>
                    <div class="payment-preview">
                        <img src="uploads/payments/<?= htmlspecialchars($member['payment_screenshot']); ?>" alt="Payment Screenshot">
                    </div>

                    <div class="text-center">
                        <?php if ($member['payment_status'] == 'verified'): ?>
                            <span class="status-chip status-success">Verified</span>
                        <?php elseif ($member['payment_status'] == 'uploaded'): ?>
                            <span class="status-chip status-warning">Under Review</span>
                        <?php else: ?>
                            <span class="status-chip status-muted">Pending</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="card app-card section-card">
            <div class="card-body">
                <div class="section-header">
                    <span class="section-icon section-icon-aadhar">🪪</span>
                    <div>
                        <h2 class="section-title">Aadhaar Documents</h2>
                        <p class="section-desc">Front aur Back image upload ya replace करें</p>
                    </div>
                </div>

                <div class="aadhar-grid">
                    <?php
                    $aadharCards = [
                        'front' => ['title' => 'Front Side', 'file' => $member['aadhar_front'] ?? ''],
                        'back' => ['title' => 'Back Side', 'file' => $member['aadhar_back'] ?? ''],
                    ];
                    foreach ($aadharCards as $side => $card):
                        $label = $card['title'];
                        $fileName = $card['file'];
                        $imageUrl = $fileName ? "uploads/aadhar/" . rawurlencode($fileName) : '';
                    ?>
                        <div class="aadhar-box">
                            <div class="aadhar-label"><?= htmlspecialchars($label); ?></div>

                            <?php if ($fileName): ?>
                                <div class="aadhar-preview">
                                    <img src="<?= htmlspecialchars($imageUrl); ?>" alt="Aadhaar <?= htmlspecialchars($label); ?>">
                                </div>

                                <div class="d-grid mb-3">
                                    <a href="aadhar_delete.php?side=<?= urlencode($side); ?>"
                                       class="btn app-btn app-btn-danger-soft"
                                       onclick="return confirm('Delete this Aadhaar image?');">
                                        Delete Image
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">Image uploaded नहीं है</div>
                            <?php endif; ?>

                            <form action="aadhar_upload.php" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="side" value="<?= htmlspecialchars($side); ?>">
                                <div class="mb-2">
                                    <input type="file" name="aadhar_image" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/*" required>
                                </div>
                                <button class="app-btn app-btn-primary w-100">
                                    <?= $fileName ? 'Replace Image' : 'Browse and Upload'; ?>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="card app-card section-card">
            <div class="card-body">
                <div class="section-header">
                    <span class="section-icon section-icon-vote">📱</span>
                    <div>
                        <h2 class="section-title">प्रोफ़ाइल सेटिंग्स</h2>
                        <p class="section-desc">अपना व्हाट्सएप नंबर अपडेट करें</p>
                    </div>
                </div>

                <form action="update_profile_process.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">व्हाट्सएप नंबर (पासवर्ड प्राप्त करने हेतु)</label>
                        <input type="text" name="whatsapp_number" class="form-control" 
                               value="<?= htmlspecialchars($member['whatsapp_number'] ?? ''); ?>" 
                               placeholder="91XXXXXXXXXX">
                    </div>

                    <button type="submit" class="app-btn app-btn-primary w-100">
                        अपडेट करें
                    </button>
                </form>
            </div>
        </section>

        <section class="card app-card section-card">
            <div class="card-body">
                <div class="section-header">
                    <span class="section-icon section-icon-vote">👨‍👩‍👧‍👦</span>
                    <div>
                        <h2 class="section-title">परिवार के सदस्य</h2>
                        <p class="section-desc">अपने परिवार के सदस्यों का विवरण जोड़ें</p>
                    </div>
                </div>

                <?php if ($family_members->num_rows > 0): ?>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm small align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>नाम</th>
                                    <th>रिश्ता</th>
                                    <th>जन्म वर्ष</th>
                                    <th>गोत्र</th>
                                    <th>शिक्षा</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($fm = $family_members->fetch_assoc()): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($fm['name']); ?></td>
                                        <td><?= htmlspecialchars($fm['relation']); ?></td>
                                        <td><?= htmlspecialchars($fm['birth_year']); ?></td>
                                        <td><?= htmlspecialchars($fm['gotra']); ?></td>
                                        <td><?= htmlspecialchars($fm['education']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info small text-center mb-3">आपने अभी तक परिवार के किसी सदस्य को नहीं जोड़ा है।</div>
                <?php endif; ?>

                <a href="family_add.php" class="app-btn app-btn-primary w-100 d-block text-center text-decoration-none">
                    ➕ परिवार का सदस्य जोड़ें
                </a>
            </div>
        </section>

        <!-- MEMBER SEARCH -->
        <section class="card app-card section-card">
            <div class="card-body">
                <div class="section-header">
                    <span class="section-icon" style="background: linear-gradient(135deg, #10b981, #34d399);">🔍</span>
                    <div>
                        <h2 class="section-title">सदस्य खोजें</h2>
                        <p class="section-desc">मूल निवास के आधार पर समाज के सदस्यों को खोजें</p>
                    </div>
                </div>

                <form method="GET" class="mb-4">
                    <div class="input-group">
                        <select name="search_niwas" class="form-select" required>
                            <option value="">मूल निवास चुनें</option>
                            <?php while ($n = $niwasList->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($n['name']); ?>" <?= ($search_niwas == $n['name']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($n['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <button type="submit" class="btn btn-primary px-3">खोजें</button>
                    </div>
                </form>

                <?php if ($searchResult !== null): ?>
                    <?php if ($searchResult->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm small align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>फोटो</th>
                                        <th>नाम</th>
                                        <th>कुल सदस्य</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($res = $searchResult->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($res['profile_photo'])): ?>
                                                    <img src="uploads/profile_photos/<?= htmlspecialchars($res['profile_photo']); ?>" style="width:40px;height:40px;object-fit:cover;border-radius:50%;">
                                                <?php else: ?>
                                                    <div style="width:40px;height:40px;border-radius:50%;background:#eee;display:flex;align-items:center;justify-content:center;font-size:18px;">👤</div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="fw-bold"><?= htmlspecialchars($res['name']); ?></td>
                                            <td><?= (int)$res['fam_count'] + 1; ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning small text-center mb-3">
                            इस मूल निवास से अभी कोई सदस्य नहीं जुड़ा है।
                        </div>
                        <?php 
                        $shareText = rawurlencode("रांकावत समाज रानी ऐप से जुड़ें और समाज को मजबूत बनाएं। अभी रजिस्टर करें: https://www.rankawatsamajrani.com/");
                        ?>
                        <a href="https://api.whatsapp.com/send?text=<?= $shareText; ?>" target="_blank" class="app-btn w-100 d-block text-center text-decoration-none" style="background:#25D366;color:#fff;">
                            📲 WhatsApp पर ऐप शेयर करें
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="card app-card section-card">
            <div class="card-body">
                <div class="section-header">
                    <span class="section-icon section-icon-vote">🗳️</span>
                    <div>
                        <h2 class="section-title">मतदान स्थिति</h2>
                        <p class="section-desc">वोटिंग अधिकार और current result status</p>
                    </div>
                </div>

                <?php
                if ($member['is_canvote'] != 1) {
                    echo '<div class="vote-panel vote-panel-muted">
                            आपको मतदान का अधिकार नहीं है।
                          </div>';
                } elseif (!$poll ||
                    $now < $poll['start_datetime'] ||
                    $now > $poll['end_datetime']) {

                    echo '<div class="vote-panel vote-panel-muted">
                            फिलहाल मतदान उपलब्ध नहीं है।
                          </div>';
                } elseif ($hasVoted) {
                    echo '<div class="vote-panel vote-panel-success">
                            <b>आपका वोट दर्ज हो चुका है</b><br>
                            आपने <span class="fw-bold">'.htmlspecialchars($voteData['option_text']).'</span> को वोट दिया है
                          </div>';
                } else {
                    echo '<a href="vote.php?poll_id='.$poll['id'].'" class="btn app-btn app-btn-primary w-100">
                            वोट करें
                          </a>';
                }
                ?>
            </div>
        </section>

        <section class="card app-card section-card">
            <div class="card-body">
                <div class="section-header">
                    <span class="section-icon" style="background: linear-gradient(135deg, #f6d365, #fda085);">📜</span>
                    <div>
                        <h2 class="section-title">इतिहास</h2>
                        <p class="section-desc">समाज का इतिहास और अन्य जानकारी</p>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="history.php" class="btn app-btn" style="background: linear-gradient(135deg, #f6d365, #fda085); color: #7b4a03; font-weight: 700;">
                        इतिहास देखें
                    </a>
                    <a href="suchnas.php" class="btn app-btn" style="background: linear-gradient(135deg, #84fab0, #8fd3f4); color: #0f5132; font-weight: 700;">
                        📢 सभी सूचनाएं (Notices)
                    </a>
                </div>
            </div>
        </section>

        <div class="logout-wrap">
            <a href="logout.php" class="btn btn-outline-danger btn-sm">
                लॉगआउट
            </a>
        </div>

        <div class="text-center mt-3 mb-4" style="font-size: 13px; color: #6b7280;">
            © श्री रंकण भवन रांकावत समाज संस्था<br>
            Developed by <a href="https://arbudaedutech.in" target="_blank" style="color: #6b7280; text-decoration: none; font-weight: bold;">Arbuda Edutech</a>
        </div>
    </div>
</div>

<?php include "includes/front_footer.php"; ?>

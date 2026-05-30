<?php
session_start();
include "config/db.php";
include "includes/dropdowns.php";

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['member_id'];
$member = $conn->query("SELECT * FROM members WHERE id=$id")->fetch_assoc();

if (!$member || $member['status'] != 'approved') {
    die("Only approved members can edit profile.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $whatsapp_number = trim($_POST['whatsapp_number'] ?? '');
    $nivasi = trim($_POST['nivasi'] ?? '');
    $gotra = trim($_POST['gotra'] ?? '');
    $haal_niwas = trim($_POST['haal_niwas'] ?? '');
    $vyavsaya = trim($_POST['vyavsaya'] ?? '');
    $new_pin = $_POST['new_pin'] ?? '';

    // Validate inputs
    if (empty($name) || empty($nivasi) || empty($gotra)) {
        $error = "नाम, गोत्र और निवासी (मूल निवास) अनिवार्य हैं।";
    } elseif (!empty($whatsapp_number) && !is_numeric($whatsapp_number)) {
        $error = "व्हाट्सएप नंबर केवल अंकों में होना चाहिए।";
    } else {
        // Update Query
        if (!empty($new_pin)) {
            $hashed_pin = password_hash($new_pin, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE members SET name=?, whatsapp_number=?, nivasi=?, gotra=?, haal_niwas=?, vyavsaya=?, pin=? WHERE id=?");
            $stmt->bind_param("sssssssi", $name, $whatsapp_number, $nivasi, $gotra, $haal_niwas, $vyavsaya, $hashed_pin, $id);
        } else {
            $stmt = $conn->prepare("UPDATE members SET name=?, whatsapp_number=?, nivasi=?, gotra=?, haal_niwas=?, vyavsaya=? WHERE id=?");
            $stmt->bind_param("ssssssi", $name, $whatsapp_number, $nivasi, $gotra, $haal_niwas, $vyavsaya, $id);
        }

        if ($stmt->execute()) {
            $_SESSION['flash_msg'] = "प्रोफ़ाइल सफलतापूर्वक अपडेट की गई।";
            $_SESSION['flash_type'] = "success";
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "जानकारी अपडेट करने में त्रुटि आई: " . $conn->error;
        }
    }
}
?>

<?php include "includes/front_header.php"; ?>

<div class="container mt-4 mb-5">
    <div class="card shadow-sm mb-4" style="border-radius: 18px;">
        <div class="card-body">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0 text-primary">प्रोफ़ाइल अपडेट करें</h5>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">⬅ वापस</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger text-center"><?= htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="profile_edit.php" method="POST">
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">मोबाइल नंबर (Primary Mobile)</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($member['mobile']) ?>" readonly style="background-color: #f1f5f9;">
                    <div class="form-text">रजिस्टर्ड मोबाइल नंबर बदला नहीं जा सकता।</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">पूरा नाम (Full Name) <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($member['name']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">व्हाट्सएप नंबर (WhatsApp)</label>
                    <input type="text" name="whatsapp_number" class="form-control" value="<?= htmlspecialchars($member['whatsapp_number']) ?>" placeholder="91XXXXXXXXXX">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">मूल निवास (Mool Niwas) <span class="text-danger">*</span></label>
                    <select name="nivasi" class="form-select" required>
                        <option value="">चुनें...</option>
                        <?php foreach ($NIVASI_LIST as $n): ?>
                            <option value="<?= $n; ?>" <?= ($member['nivasi'] == $n) ? 'selected' : '' ?>><?= $n; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">गोत्र (Gotra) <span class="text-danger">*</span></label>
                    <select name="gotra" class="form-select" required>
                        <option value="">चुनें...</option>
                        <?php foreach ($GOTRA_LIST as $g): ?>
                            <option value="<?= $g; ?>" <?= ($member['gotra'] == $g) ? 'selected' : '' ?>><?= $g; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">हाल निवास (Current Address)</label>
                    <input type="text" name="haal_niwas" class="form-control" value="<?= htmlspecialchars($member['haal_niwas']) ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">व्यवसाय / प्रतिष्ठान (Occupation)</label>
                    <input type="text" name="vyavsaya" class="form-control" value="<?= htmlspecialchars($member['vyavsaya']) ?>">
                </div>

                <hr class="my-4">
                
                <div class="mb-4">
                    <label class="form-label fw-semibold text-danger">नया पिन (New PIN)</label>
                    <input type="password" name="new_pin" class="form-control" placeholder="अगर बदलना चाहते हैं तभी दर्ज करें" minlength="4">
                    <div class="form-text">PIN बदलने के लिए नया 4 अंकों का पिन दर्ज करें, अन्यथा इसे खाली छोड़ दें।</div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="border-radius: 12px; background: linear-gradient(135deg, #0ea5e9, #2563eb); border: none;">
                    प्रोफ़ाइल अपडेट करें (Update Profile)
                </button>
            </form>
        </div>
    </div>
</div>

<?php include "includes/front_footer.php"; ?>

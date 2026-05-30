<?php
session_start();
include "config/db.php";
include "includes/dropdowns.php";

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$member_id = $_SESSION['member_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the family member ensuring it belongs to the logged in member
$stmt = $conn->prepare("SELECT * FROM family_members WHERE id=? AND member_id=?");
$stmt->bind_param("ii", $id, $member_id);
$stmt->execute();
$fam = $stmt->get_result()->fetch_assoc();

if (!$fam) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $relation = trim($_POST['relation'] ?? '');
    $birth_year = !empty($_POST['birth_year']) ? (int)$_POST['birth_year'] : null;
    $marital_status = trim($_POST['marital_status'] ?? '');
    $gotra = trim($_POST['gotra'] ?? '');
    $current_location = trim($_POST['current_location'] ?? '');
    $education = trim($_POST['education'] ?? '');

    if ($name === '' || $relation === '' || $gotra === '') {
        $error = "नाम, रिश्ता, और गोत्र अनिवार्य हैं।";
    } else {
        $upd = $conn->prepare("UPDATE family_members SET name=?, relation=?, birth_year=?, marital_status=?, gotra=?, current_location=?, education=? WHERE id=? AND member_id=?");
        $upd->bind_param("ssissssii", $name, $relation, $birth_year, $marital_status, $gotra, $current_location, $education, $id, $member_id);
        
        if ($upd->execute()) {
            $_SESSION['flash_msg'] = "परिवार के सदस्य की जानकारी अपडेट हो गई।";
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

<div class="container mt-4">
    <div class="card shadow-sm mb-4" style="border-radius: 18px;">
        <div class="card-body">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0 text-primary">परिवार के सदस्य को एडिट करें</h5>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                    ⬅ वापस
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger text-center">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="family_edit.php?id=<?= $id ?>" method="POST">
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">पूरा नाम (Full Name) <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($fam['name']) ?>" required>
                </div>
                
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label fw-semibold">रिश्ता (Relation) <span class="text-danger">*</span></label>
                        <select name="relation" class="form-select" required>
                            <option value="">चुनें...</option>
                            <?php 
                            $relations = ['पति (Husband)','पत्नी (Wife)','पुत्र (Son)','पुत्री (Daughter)','पिता (Father)','माता (Mother)','भाई (Brother)','बहन (Sister)','अन्य (Other)'];
                            foreach($relations as $rel): 
                            ?>
                                <option value="<?= $rel ?>" <?= ($fam['relation'] == $rel) ? 'selected' : '' ?>><?= $rel ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 mb-3">
                        <label class="form-label fw-semibold">जन्म वर्ष (Birth Year)</label>
                        <input type="number" name="birth_year" class="form-control" value="<?= htmlspecialchars($fam['birth_year']) ?>" min="1900" max="<?= date('Y'); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">वैवाहिक स्थिति (Marital Status)</label>
                    <select name="marital_status" class="form-select">
                        <option value="">चुनें...</option>
                        <?php 
                        $m_statuses = ['अविवाहित (Unmarried)','विवाहित (Married)','विधवा/विधुर (Widowed)','तलाकशुदा (Divorced)'];
                        foreach($m_statuses as $ms): 
                        ?>
                            <option value="<?= $ms ?>" <?= ($fam['marital_status'] == $ms) ? 'selected' : '' ?>><?= $ms ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">गोत्र (Gotra) <span class="text-danger">*</span></label>
                    <select name="gotra" class="form-select" required>
                        <option value="">चुनें...</option>
                        <?php foreach ($GOTRA_LIST as $g): ?>
                            <option value="<?= $g; ?>" <?= ($fam['gotra'] == $g) ? 'selected' : '' ?>><?= $g; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">वर्तमान स्थान (Current Location)</label>
                    <input type="text" name="current_location" class="form-control" value="<?= htmlspecialchars($fam['current_location']) ?>">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">शिक्षा (Education)</label>
                    <input type="text" name="education" class="form-control" value="<?= htmlspecialchars($fam['education']) ?>">
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="border-radius: 12px;">
                    अपडेट करें (Update)
                </button>
            </form>
        </div>
    </div>
</div>

<?php include "includes/front_footer.php"; ?>

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

if ($member['status'] != 'approved') {
    header("Location: pending.php");
    exit;
}

$error = $_SESSION['family_error'] ?? '';
unset($_SESSION['family_error']);
?>

<?php include "includes/front_header.php"; ?>

<div class="container mt-4">
    <div class="card shadow-sm mb-4" style="border-radius: 18px;">
        <div class="card-body">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0 text-primary">परिवार का सदस्य जोड़ें</h5>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">
                    ⬅ वापस
                </a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger text-center">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="family_add_process.php" method="POST">
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">पूरा नाम (Full Name) <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required placeholder="सदस्य का नाम दर्ज करें">
                </div>
                
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label fw-semibold">रिश्ता (Relation) <span class="text-danger">*</span></label>
                        <select name="relation" class="form-select" required>
                            <option value="">चुनें...</option>
                            <option value="पति (Husband)">पति (Husband)</option>
                            <option value="पत्नी (Wife)">पत्नी (Wife)</option>
                            <option value="पुत्र (Son)">पुत्र (Son)</option>
                            <option value="पुत्री (Daughter)">पुत्री (Daughter)</option>
                            <option value="पिता (Father)">पिता (Father)</option>
                            <option value="माता (Mother)">माता (Mother)</option>
                            <option value="भाई (Brother)">भाई (Brother)</option>
                            <option value="बहन (Sister)">बहन (Sister)</option>
                            <option value="अन्य (Other)">अन्य (Other)</option>
                        </select>
                    </div>

                    <div class="col-6 mb-3">
                        <label class="form-label fw-semibold">जन्म वर्ष (Birth Year)</label>
                        <input type="number" name="birth_year" class="form-control" placeholder="उदा: 1990" min="1900" max="<?= date('Y'); ?>">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">वैवाहिक स्थिति (Marital Status)</label>
                    <select name="marital_status" class="form-select">
                        <option value="">चुनें...</option>
                        <option value="अविवाहित (Unmarried)">अविवाहित (Unmarried)</option>
                        <option value="विवाहित (Married)">विवाहित (Married)</option>
                        <option value="विधवा/विधुर (Widowed)">विधवा/विधुर (Widowed)</option>
                        <option value="तलाकशुदा (Divorced)">तलाकशुदा (Divorced)</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">गोत्र (Gotra) <span class="text-danger">*</span></label>
                    <select name="gotra" class="form-select" required>
                        <option value="">चुनें...</option>
                        <?php foreach ($GOTRA_LIST as $g): ?>
                            <option value="<?= $g; ?>"><?= $g; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">वर्तमान स्थान (Current Location)</label>
                    <input type="text" name="current_location" class="form-control" placeholder="शहर या गाँव का नाम">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">शिक्षा (Education)</label>
                    <input type="text" name="education" class="form-control" placeholder="उदा: B.A., 10th Pass, B.Tech">
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="border-radius: 12px;">
                    सदस्य जोड़ें (Save Member)
                </button>
            </form>
        </div>
    </div>
</div>

<?php include "includes/front_footer.php"; ?>

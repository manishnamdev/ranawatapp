<?php
session_start();
include "../config/db.php";
include "../includes/dropdowns.php"; // GLOBAL DROPDOWNS

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>

<?php include "includes/admin_header.php"; ?>

<div class="container mt-4">
    <h6 class="fw-bold mb-3">Add New Member</h6>

    <form method="post" action="member_add_process.php">

        <!-- NAME -->
        <div class="form-floating mb-3">
            <input class="form-control" name="name" required>
            <label>नाम</label>
        </div>

        <!-- MOBILE -->
        <div class="form-floating mb-3">
            <input class="form-control" name="mobile"
                   placeholder="खाली छोड़ने पर auto-generate होगा">
            <label>मोबाइल नंबर</label>
        </div>

        <!-- WHATSAPP NUMBER (ALTERNATE) -->
        <div class="form-floating mb-3">
            <input class="form-control" name="whatsapp_number"
                   placeholder="मोबाइल न होने पर यहां पासवर्ड भेजा जाएगा">
            <label>व्हाट्सएप नंबर (वैकल्पिक)</label>
        </div>

        <!-- PIN -->
        <div class="form-floating mb-3">
            <input class="form-control" name="pin" required>
            <label>लॉगिन पिन</label>
        </div>

        <!-- NIVASI -->
        <div class="mb-3">
            <label class="form-label">निवासी</label>
            <select name="nivasi" class="form-select" required>
                <option value="">निवासी चुनें</option>
                <?php foreach ($NIVASI_LIST as $n): ?>
                    <option value="<?= $n; ?>"><?= $n; ?></option>
                <?php endforeach; ?>
            </select>
        </div>



        <!-- GOTRA -->
        <div class="mb-3">
            <label class="form-label">गोत्र</label>
            <select name="gotra" class="form-select" required>
                <option value="">गोत्र चुनें</option>
                <?php foreach ($GOTRA_LIST as $g): ?>
                    <option value="<?= $g; ?>"><?= $g; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-floating mb-3">
            <input class="form-control" name="haal_niwas" placeholder="हाल निवास">
            <label>हाल निवास (वैकल्पिक)</label>
        </div>

        <div class="form-floating mb-3">
            <input class="form-control" name="vyavsaya" placeholder="व्यवसाय / प्रतिष्ठान">
            <label>व्यवसाय / प्रतिष्ठान (वैकल्पिक)</label>
        </div>

        <!-- IS VERIFIED -->
        <div class="mb-3">
            <label class="form-label fw-semibold">Verified Member</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio"
                       name="is_verified" value="1" checked>
                <label class="form-check-label">Yes</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio"
                       name="is_verified" value="0">
                <label class="form-check-label">No</label>
            </div>
        </div>

        <!-- CAN VOTE -->
        <div class="mb-4">
            <label class="form-label fw-semibold">Can Vote</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio"
                       name="is_canvote" value="1">
                <label class="form-check-label">Yes</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio"
                       name="is_canvote" value="0" checked>
                <label class="form-check-label">No</label>
            </div>
        </div>

        <button class="btn btn-success w-100">
            Save Member
        </button>
    </form>
</div>

<?php include "includes/admin_footer.php"; ?>

<?php
session_start();
include "config/db.php";
//
if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['member_id'];
?>

<?php include "includes/front_header.php"; ?>

<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-body text-center">

            <h6 class="fw-bold mb-3">सदस्यता शुल्क जमा करें</h6>

            <p class="small">
                कृपया नीचे दिए गए QR कोड के माध्यम से सदस्यता राशि जमा करें।  
                भुगतान के बाद स्क्रीनशॉट अपलोड करें।
            </p>

            <!-- QR CODE IMAGE -->
            <img src="assets/images/payment_qr.jpeg"
                 alt="Payment QR"
                 class="img-fluid mb-3"
                 style="max-width:220px;">

            <form method="post"
                  action="payment_upload.php"
                  enctype="multipart/form-data">

                <div class="mb-3 text-start">
                    <label class="form-label">भुगतान का स्क्रीनशॉट</label>
                    <input type="file"
                           name="payment_screenshot"
                           class="form-control"
                           accept="image/*"
                           required>
                </div>

                <button class="btn btn-primary w-100">
                    स्क्रीनशॉट अपलोड करें
                </button>
            </form>

        </div>
    </div>
</div>

<?php include "includes/front_footer.php"; ?>

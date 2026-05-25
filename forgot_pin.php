<?php
session_start();
$message = $_SESSION['fp_message'] ?? '';
unset($_SESSION['fp_message']);
?>

<?php include "includes/front_header.php"; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-5">

            <div class="card shadow-sm mt-4">
                <div class="card-body">

                    <?php if ($message): ?>
                        <div class="alert alert-info text-center mb-3">
                            <?= $message; ?>
                        </div>
                    <?php endif; ?>

                    <h5 class="text-center fw-bold mb-4">
                        🔑 लॉगिन पिन भूल गए?
                    </h5>

                    <form method="post" action="forgot_pin_process.php">

                        <div class="form-floating mb-4">
                            <input type="text"
                                   name="mobile"
                                   class="form-control"
                                   placeholder="मोबाइल नंबर"
                                   required>
                            <label>अपना रजिस्टर मोबाइल नंबर डालें</label>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-warning btn-lg">
                                रिक्वेस्ट भेजें
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<?php include "includes/front_footer.php"; ?>
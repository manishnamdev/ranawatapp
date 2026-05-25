<?php
session_start();
$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>

<?php include "includes/front_header.php"; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-5">

            <div class="card shadow-sm mt-4">
                <div class="card-body">

                    <?php if ($error): ?>
                        <div class="alert alert-danger text-center mb-3">
                            <?= $error; ?>
                        </div>
                    <?php endif; ?>

                    <h5 class="text-center fw-bold mb-4">
                        सदस्य लॉगिन
                    </h5>

                    <form method="post" action="login_process.php">

                        <div class="form-floating mb-3">
                            <input type="text"
                                   name="mobile"
                                   class="form-control"
                                   placeholder="मोबाइल नंबर"
                                   required>
                            <label>मोबाइल नंबर</label>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="password"
                                   name="pin"
                                   class="form-control"
                                   placeholder="लॉगिन पिन"
                                   required>
                            <label>लॉगिन पिन</label>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-primary btn-lg">
                                लॉगिन करें
                            </button>
                            
                        </div>
<div class="text-center mt-3">
    <a href="forgot_pin.php" class="text-decoration-none">
        🔑 लॉगिन पिन भूल गए?
    </a>
</div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<?php include "includes/front_footer.php"; ?>

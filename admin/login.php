<?php
session_start();
$error = $_SESSION['login_error'] ?? "";
unset($_SESSION['login_error']);
?>

<?php include "../includes/header.php"; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-5">

            <div class="card shadow-sm mt-5">
                <div class="card-body">

                    <?php if ($error): ?>
                        <div class="alert alert-danger text-center mb-3">
                            <?= $error; ?>
                        </div>
                    <?php endif; ?>

                    <h5 class="text-center mb-4 fw-bold">
                        Admin Login
                    </h5>

                    <form method="post" action="login_process.php">

                        <div class="form-floating mb-3">
                            <input type="text" name="username" class="form-control" placeholder="Username" required>
                            <label>Username</label>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="password" name="password" class="form-control" placeholder="Password" required>
                            <label>Password</label>
                        </div>

                        <div class="d-grid">
                            <button class="btn btn-dark btn-lg">
                                Login
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>


<?php include "../includes/footer.php"; ?>

<?php
session_start();

if (!isset($_SESSION['rules_agreed'])) {
    header("Location: rules.php");
    exit;
}
include "includes/dropdowns.php";
include "includes/front_header.php"; ?>
<style>
.app-card {
    border-radius: 22px;
    border: none;
    background: #ffffff;
}

.app-card::before {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 22px;
    padding: 2px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-mask:
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
}

.form-label.small {
    font-size: 13px;
    font-weight: 600;
    color: #374151;
}

.form-control,
.form-select {
    border-radius: 14px;
    font-size: 15px;
    padding: 12px 14px;
}

.form-floating > label {
    font-size: 13px;
    color: #6b7280;
}

.section-title {
    font-size: 14px;
    font-weight: 700;
    color: #4b5563;
    margin-bottom: 6px;
}

.btn-primary {
    border-radius: 16px;
    font-size: 17px;
    font-weight: 600;
    padding: 12px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    border: none;
}

.btn-primary:hover {
    opacity: 0.95;
}
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6">

            <div class="card app-card shadow-sm mt-4">
                <div class="card-body">

             <h5 class="text-center mb-4 fw-bold">
    📝 सदस्य पंजीकरण
</h5>
                    <form method="post" action="register_process.php">

                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="name" placeholder="नाम" required>
                            <label>सदस्य का पूरा नाम</label>
                        </div>

                        <div class=" mb-3">
                        
                           <label class="form-label small">निवासी</label>

                                   <select name="nivasi" class="form-select" required>
    <option value="">निवासी चुनें</option>
    <?php foreach ($NIVASI_LIST as $t): ?>
        <option value="<?= $t; ?>"><?= $t; ?></option>
    <?php endforeach; ?>
</select>
    
                        </div>

  <div class="mb-3">
    <label class="form-label small">अवटंग</label>
        <select name="avtang" class="form-select" required>
    <option value="">अवटंग चुनें</option>
    <?php foreach ($AVTANG_LIST as $a): ?>
        <option value="<?= $a; ?>"><?= $a; ?></option>
    <?php endforeach; ?>
</select>
</div>


<div class="mb-3">
    <label class="form-label small">गोत्र</label>
            <select name="gotra" class="form-select" required>
    <option value=""> गोत्र चुनें</option>
    <?php foreach ($GOTRA_LIST as $g): ?>
        <option value="<?= $g; ?>"><?= $g; ?></option>
    <?php endforeach; ?>
</select>
</div>


                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" name="mobile" maxlength="10" placeholder="मोबाइल" required>
                            <label>मोबाइल नंबर</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" name="pin" placeholder="पिन" required>
                            <label>लॉगिन पिन</label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">सुरक्षा प्रश्न</label>
                            <select class="form-select" name="security_question" required>
                                <option value="">चयन करें</option>
                                <option>आपका जन्म स्थान क्या है?</option>
                                <option>आपके पिता का नाम क्या है?</option>
                            </select>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="text" class="form-control" name="security_answer" placeholder="उत्तर" required>
                            <label>उत्तर</label>
                        </div>

     <div class="d-grid mt-3">
    <button class="btn btn-primary btn-lg">
        पंजीकरण करें
    </button>
</div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<?php include "includes/front_footer.php"; ?>

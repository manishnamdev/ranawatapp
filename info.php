<?php
$type = $_GET['type'] ?? 'success';

$message = "";
$alert = "success";

if ($type == "success") {
    $message = "
    आपका पंजीकरण सफलतापूर्वक हो गया है।<br>
    आपकी प्रोफ़ाइल व्यवस्थापक की स्वीकृति के लिए भेज दी गई है।<br>
    कृपया लॉगिन करके स्थिति देखें।
    ";
    $alert = "success";
    unset($_SESSION['rules_agreed']);
}
elseif ($type == "exists") {
    $member_mobile = base64_decode($_GET['m']);

    $message = "
    यह मोबाइल <b>".$member_mobile."</b> नंबर पहले से पंजीकृत है।<br>
    कृपया लॉगिन करें।
    ";
    $alert = "warning";
}
else {
    $message = "
    कुछ तकनीकी समस्या आ गई है।<br>
    कृपया पुनः प्रयास करें।
    ";
    $alert = "danger";
}
?>

<?php include "includes/header.php"; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6">

            <div class="card shadow-sm">
                <div class="card-body text-center">

                    <div class="alert alert-<?= $alert; ?> mb-3">
                        <p class="mb-0"><?= $message; ?></p>
                    </div>
<?php if($type == "exists"): ?>

<?php
$admin_number = "919602711591"; // + remove karke country code ke sath
$whatsapp_message = urlencode(
    "नमस्ते Admin,\n\n" .
    "सदस्य मोबाइल नंबर: ".$member_mobile." \n" .
    "इस सदस्य को Login PIN की आवश्यकता है।\n" .
    "कृपया 4-6 घंटे में रजिस्टर्ड नंबर पर PIN भेज दें।\n\n" .
    "धन्यवाद।"
);

$whatsapp_link = "https://wa.me/".$admin_number."?text=".$whatsapp_message;
?>

<a href="<?= $whatsapp_link ?>" target="_blank" 
   class="btn btn-primary btn-lg w-100 mb-2">
   Admin को WhatsApp करें (Login PIN हेतु)
</a>

<?php endif; ?>
                    <a href="login.php" class="btn btn-primary btn-lg w-100">
                        लॉगिन पेज पर जाएँ
                    </a>

                </div>
            </div>

        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>

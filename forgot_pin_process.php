<?php
session_start();
include "config/db.php";

$mobile = trim($_POST['mobile']);

$result = $conn->query("SELECT * FROM members WHERE mobile='$mobile' LIMIT 1");

if ($result->num_rows > 0) {

    $admin_whatsapp = "919602711591"; // admin number without +
    
    $text = "नमस्ते Admin,%0A"
          . "एक सदस्य ने लॉगिन पिन रीसेट रिक्वेस्ट भेजी है.%0A"
          . "मोबाइल नंबर: $mobile%0A%0A"
          . "वेबसाइट: https://www.rankawatsamajrani.com/";

    $_SESSION['fp_message'] = "
        नंबर मिल गया ✅<br><br>
        <a href='https://wa.me/$admin_whatsapp?text=$text' 
           target='_blank'
           class='btn btn-primary w-100 mb-2'>
           WhatsApp से Admin को भेजें
        </a>

        <a href='sms:$admin_whatsapp?body=$text' 
           class='btn btn-primary w-100'>
           SMS भेजें
        </a>
    ";

} else {
    $_SESSION['fp_message'] = "यह मोबाइल नंबर सिस्टम में नहीं मिला ❌";
}

header("Location: forgot_pin.php");
exit;
<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // Only allow deletion if member is not 'approved' (so either pending or rejected)
    // Wait, the user said "allow delete member button or rejected and pending member", so we enforce this.
    $check = $conn->query("SELECT status FROM members WHERE id=$id")->fetch_assoc();
    
    if ($check && in_array($check['status'], ['pending', 'rejected'])) {
        // We delete the member. Since we have ON DELETE CASCADE for family members, etc., it should be safe.
        $stmt = $conn->prepare("DELETE FROM members WHERE id=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['flash_msg'] = "सदस्य को सफलतापूर्वक हटा दिया गया है।";
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_msg'] = "सदस्य को हटाने में त्रुटि: " . $conn->error;
            $_SESSION['flash_type'] = "danger";
        }
    } else {
        $_SESSION['flash_msg'] = "केवल Pending या Rejected सदस्यों को हटाया जा सकता है।";
        $_SESSION['flash_type'] = "warning";
    }
}

header("Location: dashboard.php");
exit;

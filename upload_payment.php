<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['member_id'];

if (isset($_FILES['payment_image']) && $_FILES['payment_image']['error'] == 0) {

    $file = $_FILES['payment_image'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ext, $allowed)) {

        $newName = "payment_" . $id . "_" . time() . "." . $ext;

        $uploadPath = __DIR__ . "/uploads/payments/" . $newName;

        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {

            $conn->query("UPDATE members 
                          SET payment_screenshot='$newName',
                              payment_status='uploaded'
                          WHERE id=$id");

        }
    }
}

header("Location: dashboard.php");
exit;
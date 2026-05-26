<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['member_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
    $file = $_FILES['profile_photo'];
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $max_size = 500 * 1024; // 500 KB limit
        if ($file['size'] > $max_size) {
            $_SESSION['flash_msg'] = "File size must be less than 500 KB.";
            $_SESSION['flash_type'] = "danger";
            header("Location: dashboard.php");
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowed_mimes)) {
            $_SESSION['flash_msg'] = "Invalid file type. Only JPG, PNG, and WebP are allowed.";
            $_SESSION['flash_type'] = "danger";
            header("Location: dashboard.php");
            exit;
        }

        $upload_dir = __DIR__ . '/uploads/profile_photos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $new_filename = 'profile_' . $id . '_' . time() . '.' . $ext;
        $dest = $upload_dir . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            // Remove old photo if exists
            $stmt = $conn->prepare("SELECT profile_photo FROM members WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $old_photo = $stmt->get_result()->fetch_assoc()['profile_photo'] ?? '';
            
            if (!empty($old_photo) && file_exists($upload_dir . $old_photo)) {
                unlink($upload_dir . $old_photo);
            }

            // Update DB
            $upd = $conn->prepare("UPDATE members SET profile_photo = ? WHERE id = ?");
            $upd->bind_param("si", $new_filename, $id);
            if ($upd->execute()) {
                $_SESSION['flash_msg'] = "Profile photo updated successfully!";
                $_SESSION['flash_type'] = "success";
            } else {
                $_SESSION['flash_msg'] = "Database error while updating photo.";
                $_SESSION['flash_type'] = "danger";
            }
        } else {
            $_SESSION['flash_msg'] = "Failed to move uploaded file.";
            $_SESSION['flash_type'] = "danger";
        }
    } else {
        $_SESSION['flash_msg'] = "Error uploading file. Try again.";
        $_SESSION['flash_type'] = "danger";
    }
}

header("Location: dashboard.php");
exit;

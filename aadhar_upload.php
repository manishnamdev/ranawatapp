<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit;
}

$memberId = (int) $_SESSION['member_id'];
$side = $_POST['side'] ?? '';
$columnMap = [
    'front' => 'aadhar_front',
    'back' => 'aadhar_back',
];

if (!isset($columnMap[$side])) {
    $_SESSION['aadhar_flash'] = ['type' => 'danger', 'message' => 'Invalid Aadhaar side selected.'];
    header("Location: pending.php");
    exit;
}

$stmt = $conn->prepare("SELECT status, aadhar_front, aadhar_back FROM members WHERE id = ?");
$stmt->bind_param("i", $memberId);
$stmt->execute();
$member = $stmt->get_result()->fetch_assoc();

if (!$member) {
    $_SESSION['aadhar_flash'] = ['type' => 'danger', 'message' => 'Member not found.'];
    header("Location: login.php");
    exit;
}

if (!isset($_FILES['aadhar_image'])) {
    $_SESSION['aadhar_flash'] = ['type' => 'danger', 'message' => 'No image received. The file might be too large (exceeds server limits).'];
    header("Location: member_documents.php");
    exit;
}

if ($_FILES['aadhar_image']['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.',
    ];
    $errorCode = $_FILES['aadhar_image']['error'];
    $message = isset($uploadErrors[$errorCode]) ? $uploadErrors[$errorCode] : 'Unknown upload error.';
    $_SESSION['aadhar_flash'] = ['type' => 'danger', 'message' => $message];
    header("Location: member_documents.php");
    exit;
}

$file = $_FILES['aadhar_image'];
$maxSize = 5 * 1024 * 1024;
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($ext, $allowedExt, true)) {
    $_SESSION['aadhar_flash'] = ['type' => 'danger', 'message' => 'Only JPG, JPEG, PNG, and WEBP files are allowed.'];
    header("Location: member_documents.php");
    exit;
}

if ($file['size'] > $maxSize) {
    $_SESSION['aadhar_flash'] = ['type' => 'danger', 'message' => 'Image size must be under 5 MB.'];
    header("Location: member_documents.php");
    exit;
}

$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR . "aadhar";
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        $_SESSION['aadhar_flash'] = ['type' => 'danger', 'message' => 'Failed to create upload directory. Please check permissions.'];
        header("Location: member_documents.php");
        exit;
    }
}

$newName = "aadhar_" . $side . "_" . $memberId . "_" . time() . "." . $ext;
$targetPath = $uploadDir . DIRECTORY_SEPARATOR . $newName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    $_SESSION['aadhar_flash'] = ['type' => 'danger', 'message' => 'Upload failed to save file. Please check folder permissions.'];
    header("Location: member_documents.php");
    exit;
}

$column = $columnMap[$side];
$existingFile = $member[$column] ?? '';

$update = $conn->prepare("UPDATE members SET {$column} = ? WHERE id = ?");
$update->bind_param("si", $newName, $memberId);
$update->execute();

if (!empty($existingFile)) {
    $oldPath = $uploadDir . DIRECTORY_SEPARATOR . basename($existingFile);
    if (is_file($oldPath)) {
        unlink($oldPath);
    }
}

$_SESSION['aadhar_flash'] = ['type' => 'success', 'message' => ucfirst($side) . ' Aadhaar image uploaded successfully.'];
header("Location: member_documents.php");
exit;

<?php
include "config/db.php";

$username = 'madan';
$password = 'laxmi1966';
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE admins SET username=?, password=? WHERE id=1");
$stmt->bind_param("ss", $username, $hash);

if ($stmt->execute()) {
    echo "Admin updated successfully with username: $username\n";
} else {
    echo "Failed to update admin.\n";
}

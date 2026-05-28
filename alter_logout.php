<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Force local DB for CLI (disabled)
// $_SERVER['HTTP_HOST'] = 'localhost:8000';
require_once __DIR__ . '/config/db.php';

echo "<pre>";
$q = "ALTER TABLE admin_login_logs ADD COLUMN IF NOT EXISTS logout_time DATETIME NULL";

if ($conn->query($q)) {
    echo "Success: $q\n";
} else {
    echo "Error on ($q): " . $conn->error . "\n";
}

echo "\nMigration Complete.</pre>";
?>

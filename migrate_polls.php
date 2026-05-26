<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$_SERVER['HTTP_HOST'] = 'localhost:8000';
include __DIR__ . "/config/db.php";

echo "<pre>";

$queries = [
    "CREATE TABLE IF NOT EXISTS polls (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question TEXT NOT NULL,
        is_active TINYINT(1) DEFAULT 0,
        start_datetime DATETIME,
        end_datetime DATETIME,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS poll_options (
        id INT AUTO_INCREMENT PRIMARY KEY,
        poll_id INT NOT NULL,
        option_text VARCHAR(255) NOT NULL,
        FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
    )",
    
    "ALTER TABLE votes ADD COLUMN IF NOT EXISTS poll_id INT NULL",
    "ALTER TABLE votes ADD COLUMN IF NOT EXISTS poll_option_id INT NULL"
];

foreach ($queries as $query) {
    if ($conn->query($query)) {
        echo "Success: $query\n";
    } else {
        echo "Error on: $query\n";
        echo $conn->error . "\n";
    }
}

echo "Migration Complete.</pre>";
?>

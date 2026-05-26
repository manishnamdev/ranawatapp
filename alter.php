<?php
require_once __DIR__ . '/config/db.php';
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
    "ALTER TABLE votes ADD COLUMN IF NOT EXISTS poll_option_id INT NULL",
    "ALTER TABLE votes MODIFY COLUMN vote_option VARCHAR(50) NULL"
];
foreach ($queries as $q) {
    if ($conn->query($q)) {
        echo "Executed: $q\n<br>";
    } else {
        echo "Error: " . $conn->error . "\n<br>";
    }
}
echo "Done.";
?>

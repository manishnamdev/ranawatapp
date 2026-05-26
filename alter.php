<?php
require_once __DIR__ . '/config/db.php';
$queries = [
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

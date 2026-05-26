<?php
include "config/db.php";

echo "<h2>Database Update Script</h2>";

// 1. Create push_subscriptions table
$sql = "CREATE TABLE IF NOT EXISTS push_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    endpoint VARCHAR(255) NOT NULL UNIQUE,
    p256dh VARCHAR(255) NOT NULL,
    auth_token VARCHAR(255) NOT NULL,
    content_encoding VARCHAR(50) DEFAULT 'aesgcm',
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
)";

if ($conn->query($sql)) {
    echo "<p>✅ <b>push_subscriptions</b> table is ready.</p>";
} else {
    echo "<p>❌ Error creating push_subscriptions: " . $conn->error . "</p>";
}

echo "<p>Database update completed successfully!</p>";
echo "<a href='dashboard.php'>Go to Dashboard</a>";

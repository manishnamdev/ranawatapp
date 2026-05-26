<?php
include "config/db.php";

echo "<h2>Database Update Script</h2>";

// 1. Create family_members table
$sql_family = "CREATE TABLE IF NOT EXISTS family_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    member_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    relation VARCHAR(50) NOT NULL,
    birth_year INT,
    marital_status VARCHAR(50),
    gotra VARCHAR(100),
    current_location VARCHAR(100),
    education VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
)";

if ($conn->query($sql_family)) {
    echo "<p>✅ <b>family_members</b> table is ready.</p>";
} else {
    echo "<p>❌ Error creating family_members: " . $conn->error . "</p>";
}

// 2. Create push_subscriptions table
$sql_push = "CREATE TABLE IF NOT EXISTS push_subscriptions (
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

if ($conn->query($sql_push)) {
    echo "<p>✅ <b>push_subscriptions</b> table is ready.</p>";
} else {
    echo "<p>❌ Error creating push_subscriptions: " . $conn->error . "</p>";
}

echo "<p>Database update completed successfully!</p>";
echo "<a href='dashboard.php'>Go to Dashboard</a>";

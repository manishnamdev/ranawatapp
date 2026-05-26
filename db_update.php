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

// 3. Update members table with missing columns
$missing_columns = [
    "nivasi" => "VARCHAR(100)",
    "gotra" => "VARCHAR(100)",
    "whatsapp_number" => "VARCHAR(20)",
    "security_question" => "VARCHAR(255)",
    "security_answer" => "VARCHAR(255)",
    "payment_screenshot" => "VARCHAR(255)",
    "payment_status" => "ENUM('pending', 'uploaded', 'verified') DEFAULT 'pending'",
    "aadhar_front" => "VARCHAR(255)",
    "aadhar_back" => "VARCHAR(255)",
    "profile_photo" => "VARCHAR(255) DEFAULT NULL",
    "is_verified" => "TINYINT(1) DEFAULT 0",
    "is_canvote" => "TINYINT(1) DEFAULT 0"
];

foreach ($missing_columns as $col => $type) {
    // Check if column exists
    $check_col = $conn->query("SHOW COLUMNS FROM members LIKE '$col'");
    if ($check_col->num_rows == 0) {
        if ($conn->query("ALTER TABLE members ADD COLUMN $col $type")) {
            echo "<p>✅ Added missing column <b>$col</b> to members table.</p>";
        } else {
            echo "<p>❌ Error adding column $col: " . $conn->error . "</p>";
        }
    }
}

echo "<p>Database update completed successfully!</p>";
echo "<a href='dashboard.php'>Go to Dashboard</a>";

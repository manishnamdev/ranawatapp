<?php
function initialize_database($conn) {
    // Check if tables exist, if so, skip initialization
    $result = $conn->query("SHOW TABLES LIKE 'admins'");
    if ($result->num_rows > 0) {
        return; // Already initialized
    }

    $queries = [
        "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL,
            password VARCHAR(255) NOT NULL,
            status ENUM('active', 'inactive') DEFAULT 'active'
        )",
        
        "CREATE TABLE IF NOT EXISTS members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            nivasi VARCHAR(100),
            gotra VARCHAR(100),
            mobile VARCHAR(20) NOT NULL,
            whatsapp_number VARCHAR(20),
            pin VARCHAR(255) NOT NULL,
            security_question VARCHAR(255),
            security_answer VARCHAR(255),
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            payment_screenshot VARCHAR(255),
            payment_status ENUM('pending', 'uploaded', 'verified') DEFAULT 'pending',
            aadhar_front VARCHAR(255),
            aadhar_back VARCHAR(255),
            profile_photo VARCHAR(255) DEFAULT NULL,
            is_verified TINYINT(1) DEFAULT 0,
            is_canvote TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS votes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            member_id INT NOT NULL,
            vote_option VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS voting_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            is_active TINYINT(1) DEFAULT 0,
            start_datetime DATETIME,
            end_datetime DATETIME
        )",
        
        "CREATE TABLE IF NOT EXISTS admin_login_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            login_time DATETIME,
            ip_address VARCHAR(50),
            user_agent TEXT
        )",
        
        "CREATE TABLE IF NOT EXISTS member_approval_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT NOT NULL,
            member_id INT NOT NULL,
            action VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS family_members (
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
        )"
    ];

    foreach ($queries as $sql) {
        if (!$conn->query($sql)) {
            error_log("DB Init Error: " . $conn->error);
        }
    }

    // Seed admin
    $hash = password_hash('JaiShriRam@123', PASSWORD_DEFAULT);
    $conn->query("INSERT IGNORE INTO admins (id, username, password, status) VALUES (1, 'superadmin', '$hash', 'active')");

    // Seed voting settings
    $conn->query("INSERT IGNORE INTO voting_settings (id, is_active) VALUES (1, 0)");
}
?>

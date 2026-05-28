<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Disable host check for CLI or allow dynamic
// $_SERVER['HTTP_HOST'] = 'localhost:8000';
require_once __DIR__ . '/config/db.php';

$GOTRA_LIST = [
    "गोयल", "रामीणा", "मनावत", "नानेचा", "माधावत", "रोटांगर", 
    "नागोरा", "गोरवाल", "भारद्वाज", "बोराणा", "टांक", "उदेशा", 
    "धनेरिया", "अज्ञात"
];

$NIVASI_LIST = [
    "पाली", "खौड़", "जवाली", "जैतपुरा", "ढोला", "माण्डल", "चांगवा", "चांचौड़ी", "नाडोल", "बिजोवा",
    "रानी स्टेशन", "फालना", "रानीगांव", "बिरामी", "साण्डेराव", "सिन्दरू", "सुमेरपुर", "खिमाड़ा", "कोसेलाव", "बाबागांव",
    "गुड़िया", "पावा", "पिचावा", "बिठुड़ा", "चाणौद", "शिवगंज", "सिरोही", "सादड़ी", "सुथारो का गुड़ा", "राजपुरा",
    "बाली", "मुण्डारा", "घाणेराव", "देसूरी", "चारभुजा", "अज्ञात"
];

// Create uploads directory if not exists
if (!file_exists(__DIR__ . '/uploads/suchnas')) {
    mkdir(__DIR__ . '/uploads/suchnas', 0777, true);
}

echo "<pre>";

$queries = [
    // Create Gotras
    "CREATE TABLE IF NOT EXISTS gotras (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE
    )",
    
    // Create Niwas
    "CREATE TABLE IF NOT EXISTS niwas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE
    )",
    
    // Create Suchnas
    "CREATE TABLE IF NOT EXISTS suchnas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        short_description TEXT NOT NULL,
        thumb_image VARCHAR(255) NOT NULL,
        datetime DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Create Suchna Images
    "CREATE TABLE IF NOT EXISTS suchna_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        suchna_id INT NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        FOREIGN KEY (suchna_id) REFERENCES suchnas(id) ON DELETE CASCADE
    )",

    // Add membership_type to members if missing
    "ALTER TABLE members ADD COLUMN IF NOT EXISTS membership_type ENUM('Yearly', 'Lifetime') DEFAULT 'Yearly'"
];

foreach ($queries as $q) {
    if ($conn->query($q)) {
        echo "Success: $q\n";
    } else {
        echo "Error on ($q): " . $conn->error . "\n";
    }
}

// Migrate Gotras
echo "\nMigrating Gotras...\n";
$stmt = $conn->prepare("INSERT IGNORE INTO gotras (name) VALUES (?)");
foreach ($GOTRA_LIST as $g) {
    $stmt->bind_param("s", $g);
    $stmt->execute();
}
echo "Gotras migrated.\n";

// Migrate Niwas
echo "\nMigrating Niwas...\n";
$stmt2 = $conn->prepare("INSERT IGNORE INTO niwas (name) VALUES (?)");
foreach ($NIVASI_LIST as $n) {
    $stmt2->bind_param("s", $n);
    $stmt2->execute();
}
echo "Niwas migrated.\n";

echo "\nMigration Complete.</pre>";
?>

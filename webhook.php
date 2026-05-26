<?php
// webhook.php

// 1. Basic security check (Optional but recommended)
// Only allow POST requests, or use a secret token
$secret_token = 'my_super_secret_token_123'; // Change this to a secure random string

// Check for the secret token in the URL (e.g., webhook.php?token=my_super_secret_token_123)
if (!isset($_GET['token']) || $_GET['token'] !== $secret_token) {
    http_response_code(403);
    die("Forbidden: Invalid token.");
}

// 2. Define the path to your repository
$repo_dir = __DIR__; // Assuming this file is in the root of your project

// 3. Command to execute
// Note: 2>&1 redirects stderr to stdout so we can capture any error messages
$command = "cd " . escapeshellarg($repo_dir) . " && git fetch origin && git reset --hard origin/main 2>&1";

// 4. Execute the command
exec($command, $output, $return_var);

// 5. Output the result
echo "<h2>Git Pull Result:</h2>";
echo "<strong>Command Executed:</strong> " . htmlspecialchars($command) . "<br><br>";

if ($return_var === 0) {
    echo "<span style='color: green; font-weight: bold;'>Success!</span><br>";
} else {
    echo "<span style='color: red; font-weight: bold;'>Error (Exit code: $return_var)</span><br>";
}

echo "<h3>Output:</h3>";
echo "<pre style='background: #f4f4f4; padding: 10px; border: 1px solid #ccc; border-radius: 5px;'>";
foreach ($output as $line) {
    echo htmlspecialchars($line) . "\n";
}
echo "</pre>";

// 6. Run Database Update Script
if ($return_var === 0) {
    echo "<h3>Database Update:</h3>";
    echo "<div style='background: #e6f7ff; padding: 10px; border: 1px solid #91d5ff; border-radius: 5px;'>";
    try {
        include "db_update.php";
    } catch (Exception $e) {
        echo "<span style='color: red;'>Error updating database: " . $e->getMessage() . "</span>";
    }
    echo "</div>";
}
?>

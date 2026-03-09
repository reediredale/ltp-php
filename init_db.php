<?php
// Initialize SQLite database for form submissions

$db_file = __DIR__ . '/data/submissions.db';
$data_dir = __DIR__ . '/data';

// Create data directory if it doesn't exist
if (!file_exists($data_dir)) {
    mkdir($data_dir, 0755, true);
    echo "Created data directory\n";
}

// Create database connection
try {
    $db = new PDO('sqlite:' . $db_file);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create submissions table
    $db->exec("
        CREATE TABLE IF NOT EXISTS submissions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            phone TEXT,
            message TEXT NOT NULL,
            submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            ip_address TEXT,
            user_agent TEXT
        )
    ");

    echo "Database initialized successfully!\n";
    echo "Location: $db_file\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

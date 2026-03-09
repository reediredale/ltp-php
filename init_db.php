<?php
// Initialize SQLite database for form submissions
header('Content-Type: text/html; charset=utf-8');

$db_file = __DIR__ . '/data/submissions.db';
$data_dir = __DIR__ . '/data';
$success = true;
$messages = [];

// Check if PDO SQLite is available
if (!extension_loaded('pdo_sqlite')) {
    $success = false;
    $messages[] = '❌ ERROR: PDO SQLite extension is not installed';
} else {
    $messages[] = '✅ PDO SQLite is available';
}

// Create data directory if it doesn't exist
if (!file_exists($data_dir)) {
    if (@mkdir($data_dir, 0777, true)) {
        $messages[] = '✅ Created data directory: ' . $data_dir;
        @chmod($data_dir, 0777); // Ensure it's writable
        $messages[] = '✅ Set directory permissions to 777 (fully writable)';
    } else {
        $success = false;
        $messages[] = '❌ ERROR: Failed to create data directory. Check permissions.';
        $messages[] = '   Try running: chmod 777 ' . dirname($data_dir);
    }
} else {
    $messages[] = '✅ Data directory exists';
    // Make sure it's writable
    @chmod($data_dir, 0777);
    $messages[] = '✅ Set directory permissions to 777';
}

// Check if directory is writable
if (file_exists($data_dir) && !is_writable($data_dir)) {
    $success = false;
    $messages[] = '❌ ERROR: Data directory is not writable';
    $messages[] = '   Try running: chmod 777 ' . $data_dir;
}

// Create database connection
if ($success) {
    try {
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $messages[] = '✅ Database connection successful';

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
        $messages[] = '✅ Submissions table created';

        // Verify table exists
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='submissions'");
        if ($stmt->fetch()) {
            $messages[] = '✅ Table verified successfully';
        }

        // Make database file writable
        @chmod($db_file, 0666);
        $messages[] = '✅ Set database file permissions to 666';

        // Check if we can write
        if (!is_writable($db_file)) {
            $success = false;
            $messages[] = '❌ WARNING: Database file is not writable';
            $messages[] = '   Try running: chmod 666 ' . $db_file;
        } else {
            $messages[] = '✅ Database file is writable';
        }

    } catch (PDOException $e) {
        $success = false;
        $messages[] = '❌ DATABASE ERROR: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Initialization</title>
    <style>
        body {
            font-family: monospace;
            background: <?php echo $success ? '#0fc53d' : '#dc3545'; ?>;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-width: 600px;
        }
        h1 {
            color: <?php echo $success ? '#0fc53d' : '#dc3545'; ?>;
            margin-bottom: 20px;
        }
        .message {
            padding: 10px;
            margin: 5px 0;
            border-left: 4px solid #ddd;
            background: #f9f9f9;
        }
        .success { border-left-color: #28a745; }
        .error { border-left-color: #dc3545; background: #ffe6e6; }
        .info {
            background: #e7f3ff;
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #0fc53d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
            font-weight: bold;
        }
        .button:hover { background: #0ca032; }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1><?php echo $success ? '✅ Success!' : '❌ Error'; ?></h1>

        <?php foreach ($messages as $msg): ?>
            <div class="message <?php echo strpos($msg, '✅') !== false ? 'success' : (strpos($msg, '❌') !== false ? 'error' : ''); ?>">
                <?php echo $msg; ?>
            </div>
        <?php endforeach; ?>

        <?php if ($success): ?>
            <div class="info">
                <strong>✅ Database initialized successfully!</strong><br><br>
                <strong>Location:</strong> <?php echo $db_file; ?><br>
                <strong>Size:</strong> <?php echo filesize($db_file); ?> bytes<br><br>
                <strong>Next steps:</strong><br>
                1. Delete this file (init_db.php) from your server<br>
                2. Test the form at <a href="/contact" style="color: #0fc53d;">/contact</a><br>
                3. View submissions at <a href="/phpliteadmin.php" style="color: #0fc53d;">phpliteadmin.php</a>
            </div>

            <a href="/diagnose.php" class="button">Run Diagnostics</a>
            <a href="/contact" class="button">Test Form</a>

        <?php else: ?>
            <div class="info" style="background: #ffe6e6; border-left: 4px solid #dc3545;">
                <strong>❌ Initialization failed!</strong><br><br>
                Check the errors above and fix them, then refresh this page.
            </div>

            <a href="?retry=1" class="button" style="background: #dc3545;">Retry</a>

        <?php endif; ?>
    </div>
</body>
</html>

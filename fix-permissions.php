<?php
// AUTOMATIC PERMISSION FIX
// This will fix the data directory and database file permissions

$data_dir = __DIR__ . '/data';
$db_file = $data_dir . '/submissions.db';

$results = [];
$success = true;

// Fix data directory permissions (777 = fully writable)
if (file_exists($data_dir)) {
    if (@chmod($data_dir, 0777)) {
        $results[] = '✅ Fixed data directory permissions (777 - fully writable)';
    } else {
        $results[] = '❌ Failed to fix data directory permissions';
        $success = false;
    }
} else {
    $results[] = '❌ Data directory does not exist';
    $success = false;
}

// Fix database file permissions (666 = read/write for all)
if (file_exists($db_file)) {
    if (@chmod($db_file, 0666)) {
        $results[] = '✅ Fixed database file permissions (666 - read/write for all)';
    } else {
        $results[] = '❌ Failed to fix database file permissions';
        $success = false;
    }
} else {
    $results[] = '❌ Database file does not exist';
    $success = false;
}

// Verify the fixes worked
if (file_exists($data_dir) && is_writable($data_dir)) {
    $results[] = '✅ VERIFIED: Data directory is now writable';
} else {
    $results[] = '❌ WARNING: Data directory still not writable';
    $success = false;
}

if (file_exists($db_file) && is_writable($db_file)) {
    $results[] = '✅ VERIFIED: Database file is now writable';
} else {
    $results[] = '❌ WARNING: Database file still not writable';
    $success = false;
}

// Try a test write to confirm
if ($success) {
    try {
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Try to create a test table and drop it
        $db->exec("CREATE TABLE IF NOT EXISTS _test (id INTEGER)");
        $db->exec("DROP TABLE IF EXISTS _test");

        $results[] = '✅ VERIFIED: Database is writable (write test passed)';
    } catch (PDOException $e) {
        $results[] = '❌ Database write test failed: ' . $e->getMessage();
        $success = false;
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Permissions</title>
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
            margin-right: 10px;
        }
        .button:hover { background: #0ca032; }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
            display: block;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="box">
        <h1><?php echo $success ? '✅ Fixed!' : '❌ Manual Fix Required'; ?></h1>

        <?php foreach ($results as $msg): ?>
            <div class="message <?php echo strpos($msg, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endforeach; ?>

        <?php if ($success): ?>
            <div class="info">
                <strong>✅ Permissions fixed successfully!</strong><br><br>
                Your form should now work correctly.<br><br>
                <strong>Next steps:</strong><br>
                1. Delete this file (fix-permissions.php)<br>
                2. Test the form at <a href="/contact" style="color: #0fc53d;">/contact</a><br>
                3. Check submissions at <a href="/phpliteadmin.php" style="color: #0fc53d;">phpliteadmin.php</a>
            </div>

            <a href="/diagnose.php" class="button">Re-run Diagnostics</a>
            <a href="/contact" class="button">Test Form</a>

        <?php else: ?>
            <div class="info" style="background: #ffe6e6; border-left: 4px solid #dc3545;">
                <strong>❌ Automatic fix failed!</strong><br><br>

                <strong>Manual fix required:</strong><br>
                SSH into your server and run these commands:<br>

                <code>cd <?php echo dirname(__DIR__); ?></code>
                <code>chmod 777 data</code>
                <code>chmod 666 data/submissions.db</code>

                Or if you have shell access via your hosting panel, run:<br>

                <code>chmod 777 data && chmod 666 data/submissions.db</code>

                <br>
                Then refresh this page or <a href="/diagnose.php">run diagnostics</a> again.
            </div>

            <a href="?retry=1" class="button" style="background: #dc3545;">Retry Fix</a>
            <a href="/diagnose.php" class="button">Run Diagnostics</a>

        <?php endif; ?>

        <div class="info" style="margin-top: 20px; background: #f9f9f9;">
            <strong>File Information:</strong><br>
            Data Directory: <?php echo $data_dir; ?><br>
            Database File: <?php echo $db_file; ?><br>
            Current User: <?php echo get_current_user(); ?><br>

            <?php if (file_exists($data_dir)): ?>
                Data Dir Perms: <?php echo substr(sprintf('%o', fileperms($data_dir)), -4); ?><br>
            <?php endif; ?>

            <?php if (file_exists($db_file)): ?>
                DB File Perms: <?php echo substr(sprintf('%o', fileperms($db_file)), -4); ?><br>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

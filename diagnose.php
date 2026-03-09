<?php
// DIAGNOSTIC PAGE - Shows exactly what's wrong
header('Content-Type: text/html; charset=utf-8');

$results = [];
$errors = [];

// Check 1: PHP Version
$results['PHP Version'] = phpversion();

// Check 2: PDO SQLite
$results['PDO SQLite'] = extension_loaded('pdo_sqlite') ? '✅ Installed' : '❌ NOT INSTALLED';

// Check 3: Data directory
$data_dir = __DIR__ . '/data';
if (is_dir($data_dir)) {
    $results['Data Directory'] = '✅ Exists';
    $results['Data Dir Writable'] = is_writable($data_dir) ? '✅ Writable' : '❌ NOT WRITABLE';
} else {
    $results['Data Directory'] = '❌ Does not exist';
    $errors[] = 'Data directory missing - Run init_db.php first';
}

// Check 4: Database file
$db_file = $data_dir . '/submissions.db';
if (file_exists($db_file)) {
    $results['Database File'] = '✅ Exists (' . filesize($db_file) . ' bytes)';
    $results['Database Writable'] = is_writable($db_file) ? '✅ Writable' : '❌ NOT WRITABLE';

    // Check 5: Database connection
    try {
        $db = new PDO('sqlite:' . $db_file);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $results['Database Connection'] = '✅ Connected';

        // Check 6: Table exists
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='submissions'");
        $table = $stmt->fetch();

        if ($table) {
            $results['Submissions Table'] = '✅ Exists';

            // Check 7: Count submissions
            $stmt = $db->query("SELECT COUNT(*) as count FROM submissions");
            $count = $stmt->fetch(PDO::FETCH_ASSOC);
            $results['Submissions Count'] = $count['count'] . ' submissions';

            // Check 8: Show recent submissions
            $stmt = $db->query("SELECT * FROM submissions ORDER BY submitted_at DESC LIMIT 3");
            $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } else {
            $results['Submissions Table'] = '❌ Does not exist';
            $errors[] = 'Table missing - Run init_db.php again';
        }

    } catch (PDOException $e) {
        $results['Database Connection'] = '❌ Failed: ' . $e->getMessage();
        $errors[] = $e->getMessage();
    }
} else {
    $results['Database File'] = '❌ Does not exist';
    $errors[] = 'Database file missing - Run init_db.php';
}

// Check 9: submit.php exists
$results['submit.php'] = file_exists(__DIR__ . '/submit.php') ? '✅ Exists' : '❌ Missing';

// Check 10: File permissions
$results['Current User'] = get_current_user();
$results['Script Owner'] = fileowner(__FILE__);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Form Diagnostic</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: monospace; background: #1e1e1e; color: #d4d4d4; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { color: #0fc53d; margin-bottom: 20px; }
        .section { background: #252526; padding: 20px; margin-bottom: 20px; border-radius: 8px; border-left: 4px solid #0fc53d; }
        .error-section { border-left-color: #f48771; }
        .success-section { border-left-color: #4ec9b0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        td { padding: 8px; border-bottom: 1px solid #333; }
        td:first-child { font-weight: bold; width: 200px; color: #4ec9b0; }
        .error { color: #f48771; }
        .success { color: #4ec9b0; }
        .warning { color: #f9c74f; }
        pre { background: #1e1e1e; padding: 15px; overflow-x: auto; margin-top: 10px; border-radius: 4px; }
        .test-form { background: #2d2d30; padding: 20px; border-radius: 8px; margin-top: 20px; }
        input, textarea { width: 100%; padding: 10px; margin: 5px 0; background: #3c3c3c; border: 1px solid #555; color: #d4d4d4; border-radius: 4px; font-family: inherit; }
        button { padding: 12px 30px; background: #0fc53d; color: white; border: none; border-radius: 50px; cursor: pointer; font-weight: bold; font-size: 14px; }
        button:hover { background: #0ca032; }
        .fix-button { padding: 10px 20px; background: #007acc; color: white; text-decoration: none; display: inline-block; border-radius: 5px; margin-top: 10px; }
        .fix-button:hover { background: #005a9e; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Form Submission Diagnostic</h1>

        <?php if (!empty($errors)): ?>
        <div class="section error-section">
            <h2 class="error">❌ ERRORS FOUND</h2>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li class="error"><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>

            <?php if (in_array('Data directory missing - Run init_db.php first', $errors) ||
                      in_array('Database file missing - Run init_db.php', $errors)): ?>
                <a href="/init_db.php" class="fix-button">Run init_db.php Now</a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="section success-section">
            <h2 class="success">✅ ALL CHECKS PASSED</h2>
            <p>Your form should be working correctly.</p>
        </div>
        <?php endif; ?>

        <div class="section">
            <h2>System Checks</h2>
            <table>
                <?php foreach ($results as $check => $result): ?>
                    <tr>
                        <td><?php echo $check; ?></td>
                        <td><?php echo $result; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <?php if (isset($recent) && !empty($recent)): ?>
        <div class="section">
            <h2>Recent Submissions (Last 3)</h2>
            <?php foreach ($recent as $sub): ?>
                <pre><?php
                    echo "ID: " . $sub['id'] . "\n";
                    echo "Name: " . htmlspecialchars($sub['name']) . "\n";
                    echo "Email: " . htmlspecialchars($sub['email']) . "\n";
                    echo "Phone: " . htmlspecialchars($sub['phone'] ?? 'N/A') . "\n";
                    echo "Time: " . $sub['submitted_at'] . "\n";
                    echo "Message: " . htmlspecialchars(substr($sub['message'], 0, 100)) . "...\n";
                ?></pre>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="section">
            <h2>Test Form Submission</h2>
            <p>Submit this form to test if everything is working:</p>

            <form method="POST" action="/submit.php" class="test-form">
                <input type="text" name="name" placeholder="Your Name" value="Test User" required>
                <input type="email" name="email" placeholder="Your Email" value="test@example.com" required>
                <input type="tel" name="phone" placeholder="Your Phone" value="1234567890">
                <textarea name="message" placeholder="Your Message" required>This is a test submission from the diagnostic page.</textarea>
                <button type="submit">Submit Test</button>
            </form>
        </div>

        <div class="section">
            <h2>File Paths</h2>
            <table>
                <tr>
                    <td>Script Directory</td>
                    <td><?php echo __DIR__; ?></td>
                </tr>
                <tr>
                    <td>Data Directory</td>
                    <td><?php echo $data_dir; ?></td>
                </tr>
                <tr>
                    <td>Database File</td>
                    <td><?php echo $db_file; ?></td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>What to Do</h2>
            <?php if (!empty($errors)): ?>
                <ol style="line-height: 1.8;">
                    <li>Check the errors above</li>
                    <li>If data directory is missing: <a href="/init_db.php" style="color: #0fc53d;">Run init_db.php</a></li>
                    <li>If permissions issue: Run <code>chmod 755 data && chmod 644 data/submissions.db</code></li>
                    <li>Refresh this page after fixing</li>
                </ol>
            <?php else: ?>
                <ol style="line-height: 1.8;">
                    <li>All checks passed - form should work</li>
                    <li>Test using the form above</li>
                    <li>Check submissions in <a href="/phpliteadmin.php" style="color: #0fc53d;">phpliteadmin.php</a></li>
                    <li>If test works, try the real form at <a href="/contact" style="color: #0fc53d;">/contact</a></li>
                </ol>
            <?php endif; ?>
        </div>

        <p style="text-align: center; margin-top: 20px; color: #666;">
            <a href="/contact" style="color: #0fc53d;">Go to Contact Form</a> |
            <a href="/phpliteadmin.php" style="color: #0fc53d;">View Database</a> |
            <a href="?refresh=1" style="color: #0fc53d;">Refresh</a>
        </p>
    </div>
</body>
</html>

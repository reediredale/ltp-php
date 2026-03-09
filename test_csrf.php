<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF Test</title>
    <style>
        body {
            font-family: monospace;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            background: #f5f5f5;
        }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
    </style>
</head>
<body>
    <h1>CSRF & Session Test</h1>

<?php
// Configure session settings before starting
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle test form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo '<div class="status">';
    echo '<h2>Form Submission Result:</h2>';

    echo '<p><strong>POST Data Received:</strong></p>';
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';

    echo '<p><strong>CSRF Token Check:</strong></p>';
    echo '<ul>';
    echo '<li>POST csrf_token present: ' . (isset($_POST['csrf_token']) ? '✓ YES' : '✗ NO') . '</li>';
    echo '<li>SESSION csrf_token present: ' . (isset($_SESSION['csrf_token']) ? '✓ YES' : '✗ NO') . '</li>';

    if (isset($_POST['csrf_token']) && isset($_SESSION['csrf_token'])) {
        $match = hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
        echo '<li>Tokens match: ' . ($match ? '✓ YES' : '✗ NO') . '</li>';

        if ($match) {
            echo '</ul>';
            echo '<p class="success"><strong>✓ CSRF VALIDATION PASSED!</strong></p>';
            echo '<p>Your form would have been processed successfully.</p>';
        } else {
            echo '</ul>';
            echo '<p class="error"><strong>✗ CSRF VALIDATION FAILED!</strong></p>';
            echo '<p>POST token: ' . htmlspecialchars(substr($_POST['csrf_token'], 0, 50)) . '...</p>';
            echo '<p>SESSION token: ' . htmlspecialchars(substr($_SESSION['csrf_token'], 0, 50)) . '...</p>';
        }
    } else {
        echo '</ul>';
        echo '<p class="error"><strong>✗ Missing Token!</strong></p>';
    }
    echo '</div>';
}
?>

    <div class="status">
        <h2>Current Session Info:</h2>
        <ul>
            <li><strong>Session ID:</strong> <?php echo session_id(); ?></li>
            <li><strong>Session Name:</strong> <?php echo session_name(); ?></li>
            <li><strong>CSRF Token:</strong> <?php echo htmlspecialchars(substr($_SESSION['csrf_token'], 0, 50)) . '...'; ?></li>
        </ul>
    </div>

    <div class="status">
        <h2>Test Form:</h2>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="text" name="test_field" placeholder="Type anything" required style="padding: 5px; width: 300px;">
            <button type="submit" style="padding: 5px 20px;">Submit Test</button>
        </form>
        <p><small>This form tests if CSRF validation works on your server.</small></p>
    </div>

    <div class="status">
        <h2>Cookie Info:</h2>
        <pre><?php print_r($_COOKIE); ?></pre>
    </div>

    <div class="status">
        <h2>PHP Session Settings:</h2>
        <ul>
            <li><strong>session.cookie_httponly:</strong> <?php echo ini_get('session.cookie_httponly'); ?></li>
            <li><strong>session.cookie_samesite:</strong> <?php echo ini_get('session.cookie_samesite'); ?></li>
            <li><strong>session.use_strict_mode:</strong> <?php echo ini_get('session.use_strict_mode'); ?></li>
            <li><strong>session.save_path:</strong> <?php echo ini_get('session.save_path'); ?></li>
        </ul>
    </div>

    <p><a href="?refresh=1">Refresh Page</a> | <a href="/contact">Go to Contact Form</a></p>
</body>
</html>

<?php
// VIEW FORM SUBMISSIONS
// Shows all submissions saved to JSON file

$password = 'leadstoprofit2024'; // Change this password!

// Simple password protection
session_start();
if (!isset($_SESSION['authenticated'])) {
    if (isset($_POST['password'])) {
        if ($_POST['password'] === $password) {
            $_SESSION['authenticated'] = true;
        } else {
            $error = 'Invalid password';
        }
    }

    if (!isset($_SESSION['authenticated'])) {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>View Submissions - Login</title>
            <style>
                body { font-family: Arial, sans-serif; background: #0fc53d; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
                .login-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); text-align: center; }
                h1 { color: #0fc53d; margin-bottom: 20px; }
                input { padding: 10px; width: 250px; border: 2px solid #ddd; border-radius: 5px; font-size: 16px; margin: 10px 0; }
                button { padding: 12px 40px; background: #0fc53d; color: white; border: none; border-radius: 50px; font-size: 16px; cursor: pointer; font-weight: bold; }
                button:hover { background: #0ca032; }
                .error { color: #dc3545; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class="login-box">
                <h1>🔒 Form Submissions</h1>
                <form method="POST">
                    <input type="password" name="password" placeholder="Enter password" autofocus><br>
                    <button type="submit">Login</button>
                    <?php if (isset($error)): ?>
                        <div class="error"><?php echo $error; ?></div>
                    <?php endif; ?>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// User is authenticated, show submissions
$jsonFile = __DIR__ . '/submissions.json';
$submissions = [];

if (file_exists($jsonFile)) {
    $submissions = json_decode(file_get_contents($jsonFile), true) ?? [];
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle mark as read
if (isset($_GET['mark_read'])) {
    $index = (int)$_GET['mark_read'];
    if (isset($submissions[$index])) {
        $submissions[$index]['read'] = true;
        file_put_contents($jsonFile, json_encode($submissions, JSON_PRETTY_PRINT));
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Sort by newest first
$submissions = array_reverse($submissions);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Form Submissions - Leads to Profit</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .header { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h1 { color: #0fc53d; }
        .stats { display: flex; gap: 20px; }
        .stat { text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; color: #0fc53d; }
        .stat-label { font-size: 12px; color: #666; text-transform: uppercase; }
        .logout { padding: 8px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; }
        .logout:hover { background: #c82333; }
        .submission { background: white; padding: 20px; margin-bottom: 15px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-left: 4px solid #0fc53d; }
        .submission.unread { border-left-color: #ff6b6b; background: #fffbf0; }
        .submission-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .submission-meta { display: flex; gap: 20px; flex-wrap: wrap; }
        .meta-item { display: flex; align-items: center; gap: 5px; color: #666; font-size: 14px; }
        .meta-item strong { color: #333; }
        .submission-message { background: #f9f9f9; padding: 15px; border-radius: 5px; margin-top: 15px; white-space: pre-wrap; line-height: 1.6; }
        .actions { display: flex; gap: 10px; }
        .btn { padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 12px; font-weight: bold; }
        .btn-primary { background: #0fc53d; color: white; }
        .btn:hover { opacity: 0.9; }
        .empty { text-align: center; padding: 60px 20px; color: #999; }
        .empty-icon { font-size: 48px; margin-bottom: 10px; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .badge-new { background: #ff6b6b; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>📬 Form Submissions</h1>
            <p style="color: #666; margin-top: 5px;">Leads to Profit Contact Form</p>
        </div>
        <div class="stats">
            <div class="stat">
                <div class="stat-number"><?php echo count($submissions); ?></div>
                <div class="stat-label">Total</div>
            </div>
            <div class="stat">
                <div class="stat-number"><?php echo count(array_filter($submissions, function($s) { return empty($s['read']); })); ?></div>
                <div class="stat-label">Unread</div>
            </div>
        </div>
        <a href="?logout=1" class="logout">Logout</a>
    </div>

    <?php if (empty($submissions)): ?>
        <div class="submission">
            <div class="empty">
                <div class="empty-icon">📭</div>
                <h2>No submissions yet</h2>
                <p>Form submissions will appear here.</p>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($submissions as $index => $sub): ?>
            <div class="submission <?php echo empty($sub['read']) ? 'unread' : ''; ?>">
                <div class="submission-header">
                    <div>
                        <h3 style="color: #333; margin-bottom: 10px;">
                            <?php echo htmlspecialchars($sub['name']); ?>
                            <?php if (empty($sub['read'])): ?>
                                <span class="badge badge-new">New</span>
                            <?php endif; ?>
                        </h3>
                        <div class="submission-meta">
                            <div class="meta-item">
                                <strong>📧</strong>
                                <a href="mailto:<?php echo htmlspecialchars($sub['email']); ?>" style="color: #0fc53d; text-decoration: none;">
                                    <?php echo htmlspecialchars($sub['email']); ?>
                                </a>
                            </div>
                            <?php if (!empty($sub['phone'])): ?>
                                <div class="meta-item">
                                    <strong>📞</strong>
                                    <a href="tel:<?php echo htmlspecialchars($sub['phone']); ?>" style="color: #0fc53d; text-decoration: none;">
                                        <?php echo htmlspecialchars($sub['phone']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div class="meta-item">
                                <strong>🕐</strong>
                                <?php echo htmlspecialchars($sub['timestamp']); ?>
                            </div>
                            <div class="meta-item">
                                <strong>🌐</strong>
                                <?php echo htmlspecialchars($sub['ip']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="actions">
                        <?php if (empty($sub['read'])): ?>
                            <a href="?mark_read=<?php echo $index; ?>" class="btn btn-primary">Mark Read</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="submission-message">
                    <?php echo htmlspecialchars($sub['message']); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <p style="text-align: center; color: #999; margin-top: 20px; font-size: 14px;">
        📁 Submissions saved to: <?php echo basename($jsonFile); ?> |
        <a href="/contact" style="color: #0fc53d;">View Contact Form</a>
    </p>
</body>
</html>

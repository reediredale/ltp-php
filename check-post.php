<?php
// Shows exactly what data is being received by POST
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>POST Data Checker</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .box { background: #252526; padding: 15px; margin: 15px 0; border-left: 3px solid #0fc53d; }
        pre { white-space: pre-wrap; word-wrap: break-word; }
        h1 { color: #0fc53d; }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
    </style>
</head>
<body>
    <h1>POST Data Diagnostic</h1>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="box">
            <h2 class="success">✓ POST Request Received</h2>
        </div>

        <div class="box">
            <h2>$_POST Contents:</h2>
            <pre><?php print_r($_POST); ?></pre>
        </div>

        <div class="box">
            <h2>$_SERVER Variables:</h2>
            <pre><?php
                echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
                echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
                echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
                echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n";
                echo "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? 'Not set') . "\n";
                echo "HTTP_USER_AGENT: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Not set') . "\n";
            ?></pre>
        </div>

        <div class="box">
            <h2>Raw POST Data:</h2>
            <pre><?php echo htmlspecialchars(file_get_contents('php://input')); ?></pre>
        </div>

    <?php else: ?>
        <div class="box">
            <h2>No POST data received</h2>
            <p>Submit a form to this page to see the POST data.</p>
        </div>
    <?php endif; ?>

    <div class="box">
        <h2>Test Form:</h2>
        <form method="POST" style="max-width: 400px;">
            <input type="text" name="test_name" placeholder="Name" style="width: 100%; padding: 8px; margin: 5px 0; background: #3c3c3c; border: 1px solid #555; color: #d4d4d4;"><br>
            <input type="email" name="test_email" placeholder="Email" style="width: 100%; padding: 8px; margin: 5px 0; background: #3c3c3c; border: 1px solid #555; color: #d4d4d4;"><br>
            <textarea name="test_message" placeholder="Message" style="width: 100%; padding: 8px; margin: 5px 0; background: #3c3c3c; border: 1px solid #555; color: #d4d4d4;"></textarea><br>
            <button type="submit" style="padding: 10px 20px; background: #0fc53d; color: white; border: none; cursor: pointer;">Submit Test</button>
        </form>
    </div>

    <p><a href="/contact" style="color: #4ec9b0;">← Back to Contact Form</a></p>
</body>
</html>

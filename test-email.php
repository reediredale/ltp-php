<?php
// Test if email is working on your server
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Test</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        .success { background: #0fc53d; color: white; padding: 15px; margin: 10px 0; }
        .error { background: #dc3545; color: white; padding: 15px; margin: 10px 0; }
        .info { background: #007acc; color: white; padding: 15px; margin: 10px 0; }
        pre { background: #252526; padding: 15px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Email Function Test</h1>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $testEmail = $_POST['email'] ?? 'reed@reediredale.com';

    echo '<div class="info">Testing email to: ' . htmlspecialchars($testEmail) . '</div>';

    $to = $testEmail;
    $subject = 'Test Email from Leads to Profit';
    $message = "This is a test email sent at " . date('Y-m-d H:i:s') . "\n\n";
    $message .= "If you receive this, the mail() function is working on your server.";
    $headers = "From: noreply@leadstoprofit.com.au\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    $result = @mail($to, $subject, $message, $headers);

    if ($result) {
        echo '<div class="success">✓ mail() function returned TRUE</div>';
        echo '<div class="info">Check your inbox at ' . htmlspecialchars($testEmail) . '</div>';
        echo '<p>If you don\'t receive it, check:</p>';
        echo '<ul>';
        echo '<li>Spam folder</li>';
        echo '<li>Server mail logs</li>';
        echo '<li>Mail server configuration</li>';
        echo '</ul>';
    } else {
        echo '<div class="error">✗ mail() function returned FALSE</div>';
        echo '<p>The mail() function failed. This means:</p>';
        echo '<ul>';
        echo '<li>Your server doesn\'t have mail configured</li>';
        echo '<li>You need to configure SMTP settings</li>';
        echo '<li>You might need to use a mail library like PHPMailer</li>';
        echo '</ul>';
    }

    echo '<h2>Server Info:</h2>';
    echo '<pre>';
    echo "PHP Version: " . phpversion() . "\n";
    echo "OS: " . PHP_OS . "\n";
    echo "SMTP: " . ini_get('SMTP') . "\n";
    echo "smtp_port: " . ini_get('smtp_port') . "\n";
    echo "sendmail_path: " . ini_get('sendmail_path') . "\n";
    echo '</pre>';
}
?>

    <h2>Send Test Email:</h2>
    <form method="POST">
        <label>Email address to test:</label><br>
        <input type="email" name="email" value="reed@reediredale.com" style="padding: 8px; width: 300px; margin: 10px 0;"><br>
        <button type="submit" style="padding: 10px 20px; background: #0fc53d; color: white; border: none; cursor: pointer;">Send Test Email</button>
    </form>

    <h2>PHP Mail Configuration:</h2>
    <pre><?php
        echo "SMTP: " . (ini_get('SMTP') ?: 'Not set') . "\n";
        echo "smtp_port: " . (ini_get('smtp_port') ?: 'Not set') . "\n";
        echo "sendmail_from: " . (ini_get('sendmail_from') ?: 'Not set') . "\n";
        echo "sendmail_path: " . (ini_get('sendmail_path') ?: 'Not set') . "\n";
    ?></pre>

    <p><a href="/contact" style="color: #4ec9b0;">← Back to Contact Form</a></p>
</body>
</html>

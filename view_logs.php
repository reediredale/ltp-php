<?php
// Simple log viewer for debugging
header('Content-Type: text/html; charset=utf-8');

// Try to find the PHP error log
$logFiles = [
    ini_get('error_log'),
    'C:\Users\reed\AppData\Roaming\Herd\logs\php_errors.log',
    'C:\Users\reed\Herd\logs\php.log',
    '/var/log/php_errors.log',
    '/var/log/apache2/error.log',
    'error_log',
    '../error_log',
];

$foundLog = null;
foreach ($logFiles as $logFile) {
    if ($logFile && file_exists($logFile)) {
        $foundLog = $logFile;
        break;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Error Log Viewer</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
        pre { background: #252526; padding: 15px; overflow-x: auto; border-left: 3px solid #0fc53d; }
        h1 { color: #0fc53d; }
        .info { background: #1a1a1a; padding: 10px; margin: 10px 0; border-left: 3px solid #007acc; }
    </style>
</head>
<body>
    <h1>PHP Error Log Viewer</h1>

    <div class="info">
        <p><strong>PHP Error Log Setting:</strong> <?php echo ini_get('error_log') ?: 'Not set'; ?></p>
        <p><strong>Display Errors:</strong> <?php echo ini_get('display_errors') ? 'On' : 'Off'; ?></p>
        <p><strong>Log Errors:</strong> <?php echo ini_get('log_errors') ? 'On' : 'Off'; ?></p>
    </div>

    <?php if ($foundLog): ?>
        <h2>Log File: <?php echo htmlspecialchars($foundLog); ?></h2>
        <p>Last modified: <?php echo date('Y-m-d H:i:s', filemtime($foundLog)); ?></p>
        <p>File size: <?php echo number_format(filesize($foundLog)); ?> bytes</p>

        <h3>Last 50 Lines:</h3>
        <pre><?php
            $lines = file($foundLog);
            $lastLines = array_slice($lines, -50);

            // Highlight form-related entries
            foreach ($lastLines as $line) {
                if (stripos($line, 'FORM') !== false || stripos($line, 'contact') !== false) {
                    echo '<span style="background: #3a3d41; color: #4ec9b0;">' . htmlspecialchars($line) . '</span>';
                } elseif (stripos($line, 'error') !== false || stripos($line, 'fail') !== false) {
                    echo '<span style="color: #f48771;">' . htmlspecialchars($line) . '</span>';
                } elseif (stripos($line, 'success') !== false) {
                    echo '<span style="color: #4ec9b0;">' . htmlspecialchars($line) . '</span>';
                } else {
                    echo htmlspecialchars($line);
                }
            }
        ?></pre>
    <?php else: ?>
        <div class="info" style="border-left-color: #f48771;">
            <h2>No log file found</h2>
            <p>Searched in:</p>
            <ul>
                <?php foreach ($logFiles as $file): ?>
                    <li><?php echo htmlspecialchars($file ?: 'null'); ?></li>
                <?php endforeach; ?>
            </ul>
            <p><strong>Check your PHP configuration to enable error logging.</strong></p>
        </div>
    <?php endif; ?>

    <p><a href="?refresh=1" style="color: #4ec9b0;">Refresh</a> | <a href="/contact" style="color: #4ec9b0;">Go to Contact Form</a></p>
</body>
</html>

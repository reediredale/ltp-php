<?php
session_start();

// Generate CSRF token if it doesn't exist
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

header('Content-Type: text/plain');
echo "=== SESSION DEBUG ===\n\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Session Save Path: " . session_save_path() . "\n\n";

echo "Session Data:\n";
print_r($_SESSION);
echo "\n";

echo "Session Cookie Params:\n";
print_r(session_get_cookie_params());
echo "\n";

echo "Headers Sent: " . (headers_sent() ? 'YES' : 'NO') . "\n\n";

echo "Cookie Data:\n";
print_r($_COOKIE);

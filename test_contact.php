<?php
// Test the contact form by simulating a real submission
session_start();

// Generate a token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

echo "Testing contact form...\n\n";
echo "Session ID: " . session_id() . "\n";
echo "CSRF Token: " . $_SESSION['csrf_token'] . "\n\n";

// Simulate form submission
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/contact';
$_POST['csrf_token'] = $_SESSION['csrf_token'];
$_POST['name'] = 'Test User';
$_POST['email'] = 'test@example.com';
$_POST['phone'] = '1234567890';
$_POST['message'] = 'This is a test message that is longer than 10 characters.';
$_POST['website'] = ''; // Honeypot should be empty
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

echo "POST Data:\n";
print_r($_POST);
echo "\n";

// Capture any redirects
ob_start();
include 'index.php';
$output = ob_get_clean();

// Check headers
$headers = headers_list();
echo "Headers sent:\n";
print_r($headers);
echo "\n\nOutput:\n";
echo $output;

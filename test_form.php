<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simulate form submission
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/contact';
$_POST['name'] = 'Test User';
$_POST['email'] = 'test@example.com';
$_POST['phone'] = '1234567890';
$_POST['message'] = 'This is a test message';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'TestAgent';

echo "Testing form submission...\n\n";

// Include the main file
ob_start();
include 'index.php';
$output = ob_get_clean();

echo "Response:\n";
echo $output;
echo "\n\nTest complete.\n";

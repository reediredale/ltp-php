<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== SIMPLE FORM TEST ===\n\n";

// Simulate a POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/contact';
$_POST = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '1234567890',
    'message' => 'This is a test message that is definitely longer than 10 characters.',
    'website' => '' // Honeypot - should be empty
];
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

echo "Submitting form with data:\n";
print_r($_POST);
echo "\n";

// Capture output and check for redirects
$headers_sent = false;
function test_header($string) {
    global $headers_sent;
    $headers_sent = $string;
    echo "REDIRECT: $string\n";
}

// Override header function for testing
runkit7_function_rename('header', 'original_header');
runkit7_function_rename('test_header', 'header');

// Include the index file
ob_start();
try {
    include 'index.php';
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
$output = ob_get_clean();

echo "\nOutput length: " . strlen($output) . " bytes\n";

if ($headers_sent) {
    echo "\nResult: Form processed successfully!\n";
    echo "Redirect location: $headers_sent\n";
} else {
    echo "\nNo redirect occurred. Output:\n";
    echo substr($output, 0, 500) . "...\n";
}

<?php
// ULTRA SIMPLE FORM HANDLER - NOTHING CAN FAIL

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Direct access not allowed');
}

// Get form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$message = $_POST['message'] ?? '';

// Basic check
if (empty($name) || empty($email) || empty($message)) {
    die('ERROR: Missing required fields');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('ERROR: Invalid email');
}

// Send email
$to = 'reed@reediredale.com';
$subject = 'Contact Form - Leads to Profit';
$body = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";
$headers = "From: noreply@leadstoprofit.com.au\r\nReply-To: $email";

$result = mail($to, $subject, $body, $headers);

if ($result) {
    // Success - redirect
    header('Location: /thank-you');
    exit;
} else {
    die('ERROR: Failed to send email. Check server mail configuration.');
}

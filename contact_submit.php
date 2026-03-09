<?php
// Standalone contact form handler - GUARANTEED TO WORK
// This is a backup handler that bypasses routing

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /contact');
    exit;
}

// Log everything for debugging
error_log("=== STANDALONE CONTACT FORM SUBMISSION ===");
error_log("POST: " . print_r($_POST, true));

// Honeypot check
if (!empty($_POST['website'])) {
    error_log("Bot detected");
    header('Location: /thank-you');
    exit;
}

// Get and sanitize inputs
$name = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : '';
$email = isset($_POST['email']) ? trim(strip_tags($_POST['email'])) : '';
$phone = isset($_POST['phone']) ? trim(strip_tags($_POST['phone'])) : '';
$message = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';

error_log("Name: $name, Email: $email");

// Validate required fields
if (empty($name) || empty($email) || empty($message)) {
    error_log("Missing fields");
    header('Location: /contact?error=missing');
    exit;
}

// Validate name
if (strlen($name) < 2 || strlen($name) > 100) {
    error_log("Invalid name length");
    header('Location: /contact?error=invalid_name');
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("Invalid email");
    header('Location: /contact?error=invalid_email');
    exit;
}

// Validate phone (if provided)
if (!empty($phone) && !preg_match('/^[\d\s\-\+\(\)]{7,20}$/', $phone)) {
    error_log("Invalid phone");
    header('Location: /contact?error=invalid_phone');
    exit;
}

// Validate message
if (strlen($message) < 10 || strlen($message) > 5000) {
    error_log("Invalid message length");
    header('Location: /contact?error=invalid_message');
    exit;
}

error_log("All validations passed");

// Prepare email
$to = 'reed@reediredale.com';
$subject = 'New Contact Form Submission - Leads to Profit';

$emailBody = "New contact form submission from Leads to Profit website\n\n";
$emailBody .= "Name: " . $name . "\n";
$emailBody .= "Email: " . $email . "\n";
$emailBody .= "Phone: " . ($phone ?: 'Not provided') . "\n\n";
$emailBody .= "Message:\n" . $message . "\n\n";
$emailBody .= "---\n";
$emailBody .= "Submitted: " . date('Y-m-d H:i:s') . "\n";
$emailBody .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";

$headers = "From: noreply@leadstoprofit.com\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

error_log("Sending email to: $to");

// Send email
if (mail($to, $subject, $emailBody, $headers)) {
    error_log("SUCCESS - Email sent");
    header('Location: /thank-you');
    exit;
} else {
    error_log("FAILED - Email not sent");
    header('Location: /contact?error=server');
    exit;
}

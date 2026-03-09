<?php
// CONTACT FORM HANDLER - Saves to SQLite Database

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Direct access not allowed');
}

// Get form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$message = $_POST['message'] ?? '';

// Basic validation
if (empty($name) || empty($email) || empty($message)) {
    header('Location: /contact?error=missing');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: /contact?error=invalid_email');
    exit;
}

// Get additional info
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

// Save to database
$db_file = __DIR__ . '/data/submissions.db';

try {
    $db = new PDO('sqlite:' . $db_file);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("
        INSERT INTO submissions (name, email, phone, message, ip_address, user_agent)
        VALUES (:name, :email, :phone, :message, :ip, :user_agent)
    ");

    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':message' => $message,
        ':ip' => $ip,
        ':user_agent' => $userAgent
    ]);

    error_log("Form submitted successfully: $name <$email>");

    // Try to send email (but don't fail if it doesn't work)
    $to = 'reed@reediredale.com';
    $subject = 'Contact Form - Leads to Profit';
    $body = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message\n\nIP: $ip\nTime: " . date('Y-m-d H:i:s');
    $headers = "From: noreply@leadstoprofit.com.au\r\nReply-To: $email";

    @mail($to, $subject, $body, $headers);

    // Redirect to success
    header('Location: /thank-you');
    exit;

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header('Location: /contact?error=server');
    exit;
}

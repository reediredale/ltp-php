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

// Check if database exists
if (!file_exists($db_file)) {
    error_log("FORM ERROR: Database file not found at: $db_file");
    error_log("FORM ERROR: Run init_db.php to create the database");
    header('Location: /contact?error=server');
    exit;
}

// Check if database is writable
if (!is_writable($db_file)) {
    error_log("FORM ERROR: Database file is not writable: $db_file");
    header('Location: /contact?error=server');
    exit;
}

try {
    $db = new PDO('sqlite:' . $db_file);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("
        INSERT INTO submissions (name, email, phone, message, ip_address, user_agent)
        VALUES (:name, :email, :phone, :message, :ip, :user_agent)
    ");

    $result = $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':message' => $message,
        ':ip' => $ip,
        ':user_agent' => $userAgent
    ]);

    if ($result) {
        $insertId = $db->lastInsertId();
        error_log("✅ Form submitted successfully: ID=$insertId, Name=$name, Email=$email");

        // Try to send email (but don't fail if it doesn't work)
        $to = 'reed@reediredale.com';
        $subject = 'Contact Form - Leads to Profit';
        $body = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message\n\nIP: $ip\nTime: " . date('Y-m-d H:i:s');
        $headers = "From: noreply@leadstoprofit.com.au\r\nReply-To: $email";

        if (@mail($to, $subject, $body, $headers)) {
            error_log("✅ Email sent successfully");
        } else {
            error_log("⚠️ Email failed to send (but form saved to database)");
        }

        // Redirect to success
        header('Location: /thank-you');
        exit;
    } else {
        error_log("❌ Database insert returned false");
        header('Location: /contact?error=server');
        exit;
    }

} catch (PDOException $e) {
    error_log("❌ DATABASE ERROR: " . $e->getMessage());
    error_log("Database file: $db_file");
    error_log("File exists: " . (file_exists($db_file) ? 'YES' : 'NO'));
    error_log("File writable: " . (is_writable($db_file) ? 'YES' : 'NO'));
    header('Location: /contact?error=server');
    exit;
}

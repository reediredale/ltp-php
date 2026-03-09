<?php
// Debug script to check form submissions
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Form Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; max-width: 1000px; margin: 0 auto; }
        .section { background: #f5f5f5; padding: 15px; margin: 15px 0; border-left: 4px solid #0fc53d; }
        .error { border-left-color: #dc3545; background: #ffe6e6; }
        .success { border-left-color: #28a745; background: #e6ffe6; }
        pre { background: white; padding: 10px; overflow-x: auto; }
        .button { background: #0fc53d; color: white; padding: 10px 20px; border: none; cursor: pointer; font-size: 16px; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0; font-family: inherit; }
        label { display: block; margin-top: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Form Submission Debug Tool</h1>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo '<div class="section success"><h2>✓ POST Request Received</h2></div>';

    echo '<div class="section">';
    echo '<h2>Raw POST Data:</h2>';
    echo '<pre>';
    print_r($_POST);
    echo '</pre>';
    echo '</div>';

    echo '<div class="section">';
    echo '<h2>Server Variables:</h2>';
    echo '<ul>';
    echo '<li><strong>REQUEST_METHOD:</strong> ' . $_SERVER['REQUEST_METHOD'] . '</li>';
    echo '<li><strong>REQUEST_URI:</strong> ' . $_SERVER['REQUEST_URI'] . '</li>';
    echo '<li><strong>REMOTE_ADDR:</strong> ' . ($_SERVER['REMOTE_ADDR'] ?? 'Not set') . '</li>';
    echo '</ul>';
    echo '</div>';

    // Simulate validation
    $name = trim(strip_tags($_POST['name'] ?? ''));
    $email = trim(strip_tags($_POST['email'] ?? ''));
    $phone = trim(strip_tags($_POST['phone'] ?? ''));
    $message = trim(strip_tags($_POST['message'] ?? ''));
    $website = $_POST['website'] ?? '';

    echo '<div class="section">';
    echo '<h2>Sanitized Values:</h2>';
    echo '<ul>';
    echo '<li><strong>Name:</strong> ' . htmlspecialchars($name) . ' (length: ' . strlen($name) . ')</li>';
    echo '<li><strong>Email:</strong> ' . htmlspecialchars($email) . '</li>';
    echo '<li><strong>Phone:</strong> ' . htmlspecialchars($phone) . '</li>';
    echo '<li><strong>Message:</strong> ' . htmlspecialchars(substr($message, 0, 100)) . '... (length: ' . strlen($message) . ')</li>';
    echo '<li><strong>Website (honeypot):</strong> ' . htmlspecialchars($website) . '</li>';
    echo '</ul>';
    echo '</div>';

    $errors = [];
    $validations = [];

    // Honeypot check
    if (!empty($website)) {
        $errors[] = "Honeypot filled (bot detected)";
        $validations[] = ['check' => 'Honeypot', 'status' => false, 'message' => 'Field was filled (bot)'];
    } else {
        $validations[] = ['check' => 'Honeypot', 'status' => true, 'message' => 'Empty (good)'];
    }

    // Required fields
    if (empty($name) || empty($email) || empty($message)) {
        $errors[] = "Missing required fields";
        $validations[] = ['check' => 'Required Fields', 'status' => false, 'message' => 'One or more required fields empty'];
    } else {
        $validations[] = ['check' => 'Required Fields', 'status' => true, 'message' => 'All present'];
    }

    // Name length
    if (strlen($name) < 2 || strlen($name) > 100) {
        $errors[] = "Name must be 2-100 characters";
        $validations[] = ['check' => 'Name Length', 'status' => false, 'message' => 'Must be 2-100 chars'];
    } else {
        $validations[] = ['check' => 'Name Length', 'status' => true, 'message' => 'Valid'];
    }

    // Email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
        $validations[] = ['check' => 'Email Format', 'status' => false, 'message' => 'Invalid format'];
    } else {
        $validations[] = ['check' => 'Email Format', 'status' => true, 'message' => 'Valid'];
    }

    // Phone (if provided)
    if (!empty($phone) && !preg_match('/^[\d\s\-\+\(\)]{7,20}$/', $phone)) {
        $errors[] = "Invalid phone format";
        $validations[] = ['check' => 'Phone Format', 'status' => false, 'message' => 'Invalid format'];
    } else {
        $validations[] = ['check' => 'Phone Format', 'status' => true, 'message' => empty($phone) ? 'Not provided' : 'Valid'];
    }

    // Message length
    if (strlen($message) < 10 || strlen($message) > 5000) {
        $errors[] = "Message must be 10-5000 characters";
        $validations[] = ['check' => 'Message Length', 'status' => false, 'message' => 'Must be 10-5000 chars'];
    } else {
        $validations[] = ['check' => 'Message Length', 'status' => true, 'message' => 'Valid'];
    }

    echo '<div class="section ' . (empty($errors) ? 'success' : 'error') . '">';
    echo '<h2>Validation Results:</h2>';
    echo '<ul>';
    foreach ($validations as $v) {
        $icon = $v['status'] ? '✓' : '✗';
        echo '<li>' . $icon . ' <strong>' . $v['check'] . ':</strong> ' . $v['message'] . '</li>';
    }
    echo '</ul>';

    if (empty($errors)) {
        echo '<p style="color: green; font-weight: bold;">✓ ALL VALIDATIONS PASSED</p>';
        echo '<p>This form would be processed successfully on your live site.</p>';
        echo '<p><strong>Next step:</strong> Email would be sent to reed@reediredale.com</p>';
    } else {
        echo '<p style="color: red; font-weight: bold;">✗ VALIDATION FAILED</p>';
        echo '<p>Errors found:</p>';
        echo '<ul>';
        foreach ($errors as $error) {
            echo '<li>' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul>';
    }
    echo '</div>';
} else {
    echo '<div class="section">';
    echo '<p>Submit the form below to test validation and see detailed debug info.</p>';
    echo '</div>';
}
?>

    <div class="section">
        <h2>Test Form:</h2>
        <form method="POST">
            <input type="hidden" name="website" value="">

            <label>Name *</label>
            <input type="text" name="name" value="John Doe" required>

            <label>Email *</label>
            <input type="email" name="email" value="test@example.com" required>

            <label>Phone</label>
            <input type="tel" name="phone" value="1234567890">

            <label>Message *</label>
            <textarea name="message" required>This is a test message that is longer than 10 characters.</textarea>

            <br><br>
            <button type="submit" class="button">Submit Test</button>
        </form>
    </div>

    <div class="section">
        <h3>Instructions:</h3>
        <ol>
            <li>Submit the form above to test locally</li>
            <li>Check if all validations pass</li>
            <li>Upload this file to production and test there too</li>
            <li>Check your PHP error logs for detailed logging</li>
        </ol>
        <p><strong>Log location:</strong> Check your server's error_log or php_errors.log file</p>
    </div>

    <p><a href="/contact">← Back to Contact Form</a></p>
</body>
</html>

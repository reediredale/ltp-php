<!DOCTYPE html>
<html>
<head>
    <title>Quick Form Test</title>
</head>
<body>
    <h1>Form Submission Test</h1>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>POST Data Received:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";

    echo "<h2>Validation Results:</h2>";
    echo "<ul>";

    $name = trim(strip_tags($_POST['name'] ?? ''));
    $email = trim(strip_tags($_POST['email'] ?? ''));
    $phone = trim(strip_tags($_POST['phone'] ?? ''));
    $message = trim(strip_tags($_POST['message'] ?? ''));

    echo "<li>Name: " . htmlspecialchars($name) . "</li>";
    echo "<li>Email: " . htmlspecialchars($email) . "</li>";
    echo "<li>Phone: " . htmlspecialchars($phone) . "</li>";
    echo "<li>Message: " . htmlspecialchars(substr($message, 0, 50)) . "...</li>";
    echo "</ul>";

    echo "<h2>Validation Checks:</h2>";
    echo "<ul>";
    echo "<li>All required fields filled: " . (!empty($name) && !empty($email) && !empty($message) ? "✓ YES" : "✗ NO") . "</li>";
    echo "<li>Name length (2-100): " . (strlen($name) >= 2 && strlen($name) <= 100 ? "✓ YES" : "✗ NO") . "</li>";
    echo "<li>Valid email: " . (filter_var($email, FILTER_VALIDATE_EMAIL) ? "✓ YES" : "✗ NO") . "</li>";
    echo "<li>Message length (10-5000): " . (strlen($message) >= 10 && strlen($message) <= 5000 ? "✓ YES" : "✗ NO") . "</li>";
    echo "<li>Honeypot empty: " . (empty($_POST['website']) ? "✓ YES" : "✗ NO") . "</li>";
    echo "</ul>";

    if (!empty($name) && !empty($email) && !empty($message) &&
        strlen($name) >= 2 && strlen($name) <= 100 &&
        filter_var($email, FILTER_VALIDATE_EMAIL) &&
        strlen($message) >= 10 && strlen($message) <= 5000 &&
        empty($_POST['website'])) {
        echo "<h2 style='color: green;'>✓ ALL VALIDATIONS PASSED!</h2>";
        echo "<p>This form would be processed successfully on your live site.</p>";
    } else {
        echo "<h2 style='color: red;'>✗ VALIDATION FAILED</h2>";
        echo "<p>Fix the issues above before submitting.</p>";
    }
}
?>

    <h2>Test Form:</h2>
    <form method="POST" style="max-width: 500px;">
        <div style="position: absolute; left: -5000px;">
            <input type="text" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div style="margin-bottom: 10px;">
            <label>Name *</label><br>
            <input type="text" name="name" style="width: 100%; padding: 5px;" required>
        </div>

        <div style="margin-bottom: 10px;">
            <label>Email *</label><br>
            <input type="email" name="email" style="width: 100%; padding: 5px;" required>
        </div>

        <div style="margin-bottom: 10px;">
            <label>Phone</label><br>
            <input type="tel" name="phone" style="width: 100%; padding: 5px;">
        </div>

        <div style="margin-bottom: 10px;">
            <label>Message *</label><br>
            <textarea name="message" style="width: 100%; padding: 5px; min-height: 100px;" required></textarea>
        </div>

        <button type="submit" style="padding: 10px 30px; background: #0fc53d; color: white; border: none; cursor: pointer;">
            Test Submit
        </button>
    </form>

    <p><a href="/contact">Go to actual contact form</a></p>
</body>
</html>

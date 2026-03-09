<?php
// Simple viewer for form submissions
// WARNING: Add authentication before using in production!

$db_file = __DIR__ . '/data/submissions.db';

if (!file_exists($db_file)) {
    die('Database not found. Run init_db.php first.');
}

try {
    $db = new PDO('sqlite:' . $db_file);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->query("SELECT * FROM submissions ORDER BY submitted_at DESC");
    $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Submissions</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            padding: 2rem;
            background: #f5f5f5;
        }
        h1 {
            margin-bottom: 2rem;
            color: #0fc53d;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 2rem;
        }
        .submissions {
            display: grid;
            gap: 1.5rem;
        }
        .submission {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .submission-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
        }
        .submission-header strong {
            color: #0fc53d;
            font-size: 1.1rem;
        }
        .submission-header .date {
            color: #666;
            font-size: 0.9rem;
        }
        .field {
            margin-bottom: 0.8rem;
        }
        .field label {
            font-weight: bold;
            color: #333;
            display: block;
            margin-bottom: 0.3rem;
        }
        .field .value {
            color: #666;
        }
        .message-field {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 5px;
            margin-top: 1rem;
        }
        .no-submissions {
            text-align: center;
            padding: 3rem;
            color: #999;
        }
        .count {
            color: #666;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <h1>Form Submissions</h1>

    <div class="warning">
        <strong>⚠️ WARNING:</strong> This page has no authentication. Add password protection before using in production!
    </div>

    <?php if (empty($submissions)): ?>
        <div class="no-submissions">
            No submissions yet.
        </div>
    <?php else: ?>
        <p class="count"><strong><?php echo count($submissions); ?></strong> submission(s)</p>

        <div class="submissions">
            <?php foreach ($submissions as $sub): ?>
                <div class="submission">
                    <div class="submission-header">
                        <strong><?php echo htmlspecialchars($sub['name']); ?></strong>
                        <span class="date"><?php echo date('M j, Y g:i A', strtotime($sub['submitted_at'])); ?></span>
                    </div>

                    <div class="field">
                        <label>Email:</label>
                        <div class="value">
                            <a href="mailto:<?php echo htmlspecialchars($sub['email']); ?>">
                                <?php echo htmlspecialchars($sub['email']); ?>
                            </a>
                        </div>
                    </div>

                    <?php if (!empty($sub['phone'])): ?>
                    <div class="field">
                        <label>Phone:</label>
                        <div class="value">
                            <a href="tel:<?php echo htmlspecialchars($sub['phone']); ?>">
                                <?php echo htmlspecialchars($sub['phone']); ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="field message-field">
                        <label>Message:</label>
                        <div class="value"><?php echo nl2br(htmlspecialchars($sub['message'])); ?></div>
                    </div>

                    <?php if (!empty($sub['ip_address'])): ?>
                    <div class="field" style="font-size: 0.85rem; color: #999; margin-top: 1rem;">
                        <label>IP:</label>
                        <div class="value"><?php echo htmlspecialchars($sub['ip_address']); ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</body>
</html>

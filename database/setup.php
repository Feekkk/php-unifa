<?php
/**
 * Database Setup Script
 * Run this file once to create the database and tables
 * Access via: http://localhost/unifa/database/setup.php
 */

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'unifa_db';

// Create connection without database
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = true;
$messages = [];

// Create database
$createDbSql = "CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($createDbSql)) {
    $messages[] = "✓ Database '$dbname' created or already exists";
} else {
    $success = false;
    $messages[] = "✗ Error creating database: " . $conn->error;
}

// Select database
if ($conn->select_db($dbname)) {
    $messages[] = "✓ Selected database '$dbname'";
} else {
    $success = false;
    $messages[] = "✗ Error selecting database: " . $conn->error;
}

// Read SQL file
$sqlFile = __DIR__ . '/schema.sql';
if (!file_exists($sqlFile)) {
    die("SQL file not found: $sqlFile");
}

$sql = file_get_contents($sqlFile);

// Remove CREATE DATABASE and USE statements from SQL file content
$sql = preg_replace('/CREATE DATABASE[^;]+;/i', '', $sql);
$sql = preg_replace('/USE[^;]+;/i', '', $sql);

// Split SQL statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $statement) {
    // Skip empty statements and comments
    if (empty($statement) || strpos(trim($statement), '--') === 0) {
        continue;
    }
    
    // Remove comments from statement
    $statement = preg_replace('/--.*$/m', '', $statement);
    $statement = trim($statement);
    
    if (empty($statement)) {
        continue;
    }
    
    if ($conn->query($statement)) {
        $stmtPreview = substr($statement, 0, 60);
        $messages[] = "✓ Executed: $stmtPreview...";
    } else {
        $success = false;
        $messages[] = "✗ Error: " . $conn->error;
        $messages[] = "Statement: " . substr($statement, 0, 100);
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - UniFa</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0a3d62;
            margin-bottom: 20px;
        }
        .message {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
            font-family: monospace;
            font-size: 0.9em;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #0a3d62;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .btn:hover {
            background: #0b4873;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Database Setup</h1>
        <?php if ($success): ?>
            <div class="message success">
                <strong>✓ Database setup completed successfully!</strong>
            </div>
        <?php else: ?>
            <div class="message error">
                <strong>✗ Database setup encountered errors.</strong>
            </div>
        <?php endif; ?>
        
        <h2>Execution Log:</h2>
        <?php foreach ($messages as $message): ?>
            <div class="message <?php echo strpos($message, '✗') !== false ? 'error' : 'success'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endforeach; ?>
        
        <a href="../index.php" class="btn">Go to Home</a>
        <a href="../pages/register.php" class="btn">Go to Registration</a>
    </div>
</body>
</html>

<?php
/**
 * Migration script to add course and semester columns to users table
 * Run this file once to update existing database schema
 * Access via: http://localhost/unifa/database/migrate_add_course_semester.php
 */

require_once '../config.php';

$conn = getDBConnection();
$success = true;
$messages = [];

// Check if table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
if ($tableCheck->num_rows === 0) {
    $messages[] = "✗ Users table does not exist. Please run setup.php first.";
    $success = false;
} else {
    $messages[] = "✓ Users table exists";
    
    // Get existing columns
    $existingColumns = [];
    $columnsResult = $conn->query("SHOW COLUMNS FROM users");
    if ($columnsResult) {
        while ($row = $columnsResult->fetch_assoc()) {
            $existingColumns[] = $row['Field'];
        }
    }
    $messages[] = "ℹ Current columns: " . implode(', ', $existingColumns);
    
    // Add course column if it doesn't exist
    if (!in_array('course', $existingColumns)) {
        // Determine position - after phone, before bank_name
        if (in_array('phone', $existingColumns)) {
            $sql = "ALTER TABLE users ADD COLUMN course VARCHAR(100) AFTER phone";
        } elseif (in_array('student_id', $existingColumns)) {
            $sql = "ALTER TABLE users ADD COLUMN course VARCHAR(100) AFTER student_id";
        } else {
            $sql = "ALTER TABLE users ADD COLUMN course VARCHAR(100)";
        }
        
        if ($conn->query($sql)) {
            $messages[] = "✓ Added course column";
            $existingColumns[] = 'course';
        } else {
            $messages[] = "✗ Error adding course column: " . $conn->error;
            $success = false;
        }
    } else {
        $messages[] = "✓ Course column already exists";
    }
    
    // Add semester column if it doesn't exist
    if (!in_array('semester', $existingColumns)) {
        // Determine position - after course if it exists, otherwise after phone
        if (in_array('course', $existingColumns)) {
            $sql = "ALTER TABLE users ADD COLUMN semester VARCHAR(50) AFTER course";
        } elseif (in_array('phone', $existingColumns)) {
            $sql = "ALTER TABLE users ADD COLUMN semester VARCHAR(50) AFTER phone";
        } elseif (in_array('student_id', $existingColumns)) {
            $sql = "ALTER TABLE users ADD COLUMN semester VARCHAR(50) AFTER student_id";
        } else {
            $sql = "ALTER TABLE users ADD COLUMN semester VARCHAR(50)";
        }
        
        if ($conn->query($sql)) {
            $messages[] = "✓ Added semester column";
            $existingColumns[] = 'semester';
        } else {
            $messages[] = "✗ Error adding semester column: " . $conn->error;
            $success = false;
        }
    } else {
        $messages[] = "✓ Semester column already exists";
    }
    
    // Verify final state
    $existingColumns = [];
    $columnsResult = $conn->query("SHOW COLUMNS FROM users");
    if ($columnsResult) {
        while ($row = $columnsResult->fetch_assoc()) {
            $existingColumns[] = $row['Field'];
        }
    }
    $messages[] = "ℹ Final columns: " . implode(', ', $existingColumns);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration - Add Course/Semester - UniFa</title>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
            padding: 40px 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #0a3d62;
            margin-bottom: 24px;
        }
        .message {
            padding: 12px;
            margin-bottom: 8px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
        }
        .message.success {
            background-color: #d1fae5;
            color: #065f46;
        }
        .message.error {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .message.info {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin-top: 24px;
            margin-right: 12px;
            background: #0a3d62;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #0c4a6e;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Database Migration - Add Course/Semester</h1>
        <?php if ($success): ?>
            <div class="message success">
                <strong>✓ Migration completed successfully!</strong>
            </div>
        <?php else: ?>
            <div class="message error">
                <strong>✗ Migration encountered some errors.</strong>
            </div>
        <?php endif; ?>
        
        <h2>Migration Log:</h2>
        <?php foreach ($messages as $message): ?>
            <div class="message <?php 
                echo strpos($message, '✗') !== false ? 'error' : 
                    (strpos($message, 'ℹ') !== false ? 'info' : 'success'); 
            ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endforeach; ?>
        
        <a href="../index.php" class="btn">Go to Home</a>
        <a href="setup.php" class="btn">Run Full Setup</a>
    </div>
</body>
</html>


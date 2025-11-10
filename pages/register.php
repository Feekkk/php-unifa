<?php
require_once '../config.php';

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['fullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $studentId = trim($_POST['studentId'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $bankName = trim($_POST['bankName'] ?? '');
    $bankNumber = trim($_POST['bankNumber'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    
    // Validation
    if (empty($fullName) || empty($email) || empty($studentId) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $conn = getDBConnection();
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR student_id = ?");
        $stmt->bind_param("ss", $email, $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email or Student ID already exists.';
            $stmt->close();
        } else {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, student_id, phone, course, semester, bank_name, bank_number, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'student')");
            $stmt->bind_param("sssssssss", $fullName, $email, $studentId, $phone, $course, $semester, $bankName, $bankNumber, $hashedPassword);
            
            if ($stmt->execute()) {
                // Get the newly created user
                $userId = $conn->insert_id;
                
                // Set session
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $fullName;
                $_SESSION['user_email'] = $email;
                $_SESSION['student_id'] = $studentId;
                $_SESSION['user_role'] = 'student';
                
                // Set success message
                $_SESSION['message'] = 'Account created successfully! Welcome to RCMP UniFa, ' . htmlspecialchars($fullName) . '!';
                $_SESSION['message_type'] = 'success';
                
                // Redirect to student dashboard
                header('Location: student/StudentDashboard.php');
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $stmt->close();
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Register - RCMP UniFa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css" />
</head>
<body>
    <?php 
    include 'component/MessageDialog.php';
    renderMessageDialogScript();
    if ($error) {
        showErrorMessage($error, true, null, 5000);
    }
    ?>
    <main class="auth-page">
        <div class="auth-card-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <a class="brand-mini" href="../index.php">
                    <img src="../public/unikl-rcmp.png" alt="UniKL RCMP logo" />
                </a>
                <h1>Create your account</h1>
                <p class="small muted">Register to apply for financial aid programs</p>
            </div>
            <div class="auth-body">
                <form method="post" action="">
                    <div class="form-grid">
                        <div class="form-row">
                            <label for="fullName">Full Name <span style="color: #dc2626;">*</span></label>
                            <input class="input" type="text" id="fullName" name="fullName" placeholder="Your full name" required />
                        </div>
                        <div class="form-row">
                            <label for="email">Email <span style="color: #dc2626;">*</span></label>
                            <input class="input" type="email" id="email" name="email" placeholder="name@unikl.edu.my" required />
                        </div>
                        <div class="form-row">
                            <label for="studentId">Student ID <span style="color: #dc2626;">*</span></label>
                            <input class="input" type="text" id="studentId" name="studentId" placeholder="e.g. B12345" required />
                        </div>
                        <div class="form-row">
                            <label for="phone">Phone</label>
                            <input class="input" type="tel" id="phone" name="phone" placeholder="e.g. 012-3456789" />
                        </div>
                        <div class="form-row">
                            <label for="course">Course</label>
                            <input class="input" type="text" id="course" name="course" placeholder="e.g. Bachelor of Computer Science" />
                        </div>
                        <div class="form-row">
                            <label for="semester">Semester</label>
                            <input class="input" type="text" id="semester" name="semester" placeholder="e.g. Semester 1, Semester 2, Year 1" />
                        </div>
                        <div class="form-row">
                            <label for="bankName">Bank Name</label>
                            <input class="input" type="text" id="bankName" name="bankName" placeholder="e.g. Maybank, CIMB, Public Bank" />
                        </div>
                        <div class="form-row">
                            <label for="bankNumber">Bank Account Number</label>
                            <input class="input" type="text" id="bankNumber" name="bankNumber" placeholder="e.g. 1234567890" />
                        </div>
                        <div class="form-row">
                            <label for="password">Password <span style="color: #dc2626;">*</span></label>
                            <input class="input" type="password" id="password" name="password" placeholder="Create a password" required />
                        </div>
                        <div class="form-row">
                            <label for="confirm">Confirm Password <span style="color: #dc2626;">*</span></label>
                            <input class="input" type="password" id="confirm" name="confirm" placeholder="Confirm password" required />
                        </div>
                    </div>
                    <div class="form-row form-row-full" style="margin-top:32px;">
                        <button type="submit" class="btn btn-primary" style="width:100%; padding: 14px; font-size: 1rem;">Create Account</button>
                    </div>
                </form>
                <div class="divider"></div>
                <div style="text-align: center;">
                    <p class="small" style="margin: 0 0 8px;">Already have an account? <a class="link" href="login.php">Login</a></p>
                    <p class="small" style="margin: 0;"><a class="link" href="../index.php">← Back to Home</a></p>
                </div>
            </div>
        </div>
        </div>
    </main>
    <?php include 'component/footer.php'; renderFooter('../'); ?>
    <script src="../js/main.js"></script>
</body>
</html>



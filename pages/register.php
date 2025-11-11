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
    <style>
        .auth-page .auth-card-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 32px;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 24px;
        }
        .auth-card {
            max-width: 100%;
        }
        .video-card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            position: relative;
            height: 100%;
            min-height: 600px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            border: 1px solid #eef2f7;
        }
        .video-card-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 24px;
            width: 100%;
            height: 100%;
        }
        .video-card-text {
            text-align: center;
            z-index: 4;
            position: relative;
        }
        .video-card-text h2 {
            margin: 0 0 12px;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1.2;
        }
        .video-card-text p {
            margin: 0 0 16px;
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.6;
        }
        .video-card-text p:last-child {
            margin-bottom: 0;
        }
        .video-card-features {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 8px;
            text-align: left;
        }
        .video-card-features .feature-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text);
            font-size: 0.95rem;
        }
        .video-card-features .feature-item::before {
            content: '✓';
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.875rem;
            flex-shrink: 0;
        }
        .video-wrapper {
            position: relative;
            width: 100%;
            height: 100%;
            border-radius: var(--radius-sm);
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            z-index: 2;
        }
        .video-wrapper video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .video-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.02);
            z-index: 3;
            pointer-events: none;
            border-radius: var(--radius-sm);
        }
        @media (max-width: 1024px) {
            .auth-page .auth-card-wrapper {
                grid-template-columns: 1fr;
            }
            .video-card {
                display: none;
            }
        }
    </style>
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
        <!-- Video Card -->
        <div class="video-card">
            <div class="video-card-content">
                <div class="video-card-text">
                    <h2>RCMP UniFa</h2>
                    <p>Join thousands of students who have successfully applied for financial aid through our streamlined platform.</p>
                    <div class="video-card-features">
                        <div class="feature-item">
                            <span>Simple registration process</span>
                        </div>
                        <div class="feature-item">
                            <span>Multiple aid programs available</span>
                        </div>
                        <div class="feature-item">
                            <span>Real-time application tracking</span>
                        </div>
                        <div class="feature-item">
                            <span>Secure and confidential</span>
                        </div>
                    </div>
                </div>
                <div class="video-wrapper">
                    <video autoplay loop muted playsinline>
                        <source src="../public/logo-animation.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <div class="video-overlay"></div>
                </div>
            </div>
        </div>
        </div>
    </main>
    <?php include 'component/footer.php'; renderFooter('../'); ?>
    <script src="../js/main.js"></script>
</body>
</html>



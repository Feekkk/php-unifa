<?php
require_once '../../config.php';

// Require student role
requireRole('student');

// Get user data
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];
$studentId = $_SESSION['student_id'];

$error = '';
$success = '';

// Get current user data from database
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT full_name, email, phone, course, semester, bank_name, bank_number FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

$currentFullName = $userData['full_name'] ?? '';
$currentEmail = $userData['email'] ?? '';
$currentPhone = $userData['phone'] ?? '';
$currentCourse = $userData['course'] ?? '';
$currentSemester = $userData['semester'] ?? '';
$currentBankName = $userData['bank_name'] ?? '';
$currentBankNumber = $userData['bank_number'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $bankName = trim($_POST['bank_name'] ?? '');
    $bankNumber = trim($_POST['bank_number'] ?? '');
    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');
    
    // Validate required fields
    if (empty($fullName)) {
        $error = 'Full name is required.';
    } elseif (empty($email)) {
        $error = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email is already taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = 'This email is already registered to another account.';
        }
        $stmt->close();
    }
    
    // Validate password change if provided
    if (empty($error) && (!empty($newPassword) || !empty($confirmPassword))) {
        if (empty($currentPassword)) {
            $error = 'Please enter your current password to change it.';
        } elseif (empty($newPassword)) {
            $error = 'Please enter a new password.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'New password must be at least 6 characters long.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New password and confirmation password do not match.';
        } else {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            if (!password_verify($currentPassword, $user['password'])) {
                $error = 'Current password is incorrect.';
            }
        }
    }
    
    // Update user data if no errors
    if (empty($error)) {
        // Update password if provided, otherwise just update other fields
        if (!empty($newPassword)) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, course = ?, semester = ?, bank_name = ?, bank_number = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssssssssi", $fullName, $email, $phone, $course, $semester, $bankName, $bankNumber, $hashedPassword, $userId);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, course = ?, semester = ?, bank_name = ?, bank_number = ? WHERE id = ?");
            $stmt->bind_param("sssssssi", $fullName, $email, $phone, $course, $semester, $bankName, $bankNumber, $userId);
        }
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            
            // Update session data
            $_SESSION['user_name'] = $fullName;
            $_SESSION['user_email'] = $email;
            
            // Set success message and redirect
            $_SESSION['message'] = 'Profile updated successfully!';
            $_SESSION['message_type'] = 'success';
            header('Location: StudentDashboard.php');
            exit();
        } else {
            $error = 'Failed to update profile. Please try again.';
            $stmt->close();
        }
    }
    
    $conn->close();
    
    // Update current values for display if there was an error
    $currentFullName = $fullName;
    $currentEmail = $email;
    $currentPhone = $phone;
    $currentCourse = $course;
    $currentSemester = $semester;
    $currentBankName = $bankName;
    $currentBankNumber = $bankNumber;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Profile - RCMP UniFa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/styles.css" />
    <style>
        .profile-container {
            min-height: 100vh;
            background: var(--light);
            padding: 32px 0;
        }
        .profile-header {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            padding: 32px 0;
            margin-bottom: 32px;
        }
        .profile-header h1 {
            margin: 0 0 8px;
            font-size: 2rem;
            font-weight: 700;
        }
        .profile-header p {
            margin: 0;
            opacity: 0.9;
        }
        .profile-content {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 24px;
        }
        .profile-section {
            background: var(--card);
            padding: 32px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }
        .profile-section h2 {
            margin: 0 0 24px;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            border-bottom: 2px solid var(--primary);
            padding-bottom: 12px;
        }
        .form-group {
            margin-bottom: 24px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text);
        }
        .form-group label .required {
            color: #dc2626;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #fff;
            color: var(--text);
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease;
            font-family: inherit;
        }
        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(10,61,98,0.12);
        }
        .form-group .help-text {
            font-size: 0.875rem;
            color: var(--muted);
            margin-top: 6px;
        }
        .form-group input[readonly] {
            background-color: #f3f4f6;
            cursor: not-allowed;
            border-color: #d1d5db;
        }
        .form-group input[readonly]:focus {
            border-color: #d1d5db;
            box-shadow: none;
        }
        .form-actions {
            display: flex;
            gap: 16px;
            justify-content: flex-end;
            margin-top: 32px;
        }
        .nav-bar {
            background: var(--card);
            padding: 16px 0;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 0;
        }
        .nav-bar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }
        .nav-links {
            display: flex;
            gap: 24px;
            align-items: center;
        }
        .nav-links a {
            color: var(--muted);
            font-weight: 500;
            transition: color 0.2s ease;
        }
        .nav-links a:hover {
            color: var(--primary);
        }
        .logout-btn {
            padding: 8px 16px;
            background: #fee;
            color: #c33;
            border: 1px solid #fcc;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .logout-btn:hover {
            background: #fdd;
        }
        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 24px;
        }
        .alert-error {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        .password-section {
            border-top: 2px solid #e5e7eb;
            padding-top: 24px;
            margin-top: 24px;
        }
        .password-section h3 {
            margin: 0 0 16px;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text);
        }
        .password-note {
            font-size: 0.875rem;
            color: var(--muted);
            font-style: italic;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <?php 
    include '../component/MessageDialog.php';
    renderMessageDialogScript();
    if ($error) {
        showErrorMessage($error, true, null, 5000);
    }
    ?>
    <!-- Navigation Bar -->
    <div class="nav-bar">
        <div class="nav-bar-content">
            <a href="../../index.php" class="brand">
                <img src="../../public/unikl-rcmp.png" alt="UniKL RCMP logo" class="logo" style="height: 40px;" />
            </a>
            <div class="nav-links">
                <a href="StudentDashboard.php">Dashboard</a>
                <a href="ApplicationForm.php">New Application</a>
                <a href="EditProfile.php">Profile</a>
                <form method="post" action="../logout.php" style="display: inline;">
                    <button type="submit" class="logout-btn" name="logout">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-content">
            <h1>Edit Profile</h1>
            <p>Update your personal information and account settings</p>
        </div>
    </div>

    <!-- Profile Form -->
    <div class="profile-container">
        <div class="profile-content">
            <form method="post" action="" id="profileForm">
                <!-- Personal Information Section -->
                <div class="profile-section">
                    <h2>Personal Information</h2>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($currentFullName); ?>" required />
                        <div class="help-text">Your full name as it appears on your student records</div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($currentEmail); ?>" required />
                        <div class="help-text">Your email address for account notifications</div>
                    </div>

                    <div class="form-group">
                        <label for="student_id">Student ID</label>
                        <input type="text" id="student_id" name="student_id" value="<?php echo htmlspecialchars($studentId); ?>" readonly />
                        <div class="help-text">Student ID cannot be changed</div>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($currentPhone); ?>" placeholder="e.g. 0123456789" />
                        <div class="help-text">Your contact phone number (optional)</div>
                    </div>

                    <div class="form-group">
                        <label for="course">Course</label>
                        <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($currentCourse); ?>" placeholder="e.g. Bachelor of Computer Science" />
                        <div class="help-text">Your course of study (optional)</div>
                    </div>

                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <input type="text" id="semester" name="semester" value="<?php echo htmlspecialchars($currentSemester); ?>" placeholder="e.g. Semester 1, Semester 2, Year 1" />
                        <div class="help-text">Your current semester (optional)</div>
                    </div>
                </div>

                <!-- Bank Information Section -->
                <div class="profile-section">
                    <h2>Bank Information</h2>
                    
                    <div class="form-group">
                        <label for="bank_name">Bank Name</label>
                        <input type="text" id="bank_name" name="bank_name" value="<?php echo htmlspecialchars($currentBankName); ?>" placeholder="e.g. Maybank, CIMB, Public Bank" />
                        <div class="help-text">Your bank name for financial aid transfers</div>
                    </div>

                    <div class="form-group">
                        <label for="bank_number">Bank Account Number</label>
                        <input type="text" id="bank_number" name="bank_number" value="<?php echo htmlspecialchars($currentBankNumber); ?>" placeholder="e.g. 1234567890" />
                        <div class="help-text">Your bank account number for financial aid transfers</div>
                    </div>
                </div>

                <!-- Password Change Section -->
                <div class="profile-section">
                    <h2>Security</h2>
                    <div class="password-section">
                        <h3>Change Password</h3>
                        <p class="password-note">Leave password fields empty if you don't want to change your password</p>
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" autocomplete="current-password" />
                            <div class="help-text">Enter your current password to change it</div>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" autocomplete="new-password" minlength="6" />
                            <div class="help-text">Must be at least 6 characters long</div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" minlength="6" />
                            <div class="help-text">Re-enter your new password to confirm</div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="StudentDashboard.php" class="btn btn-light">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../component/footer.php'; renderFooter('../../'); ?>
    <script src="../../js/main.js"></script>
    <script>
        // Password validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('profileForm');
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            const currentPassword = document.getElementById('current_password');
            
            // Validate password match on form submit
            form.addEventListener('submit', function(e) {
                const newPassValue = newPassword.value.trim();
                const confirmPassValue = confirmPassword.value.trim();
                const currentPassValue = currentPassword.value.trim();
                
                // If any password field is filled, all must be filled
                if (newPassValue || confirmPassValue || currentPassValue) {
                    if (!currentPassValue) {
                        e.preventDefault();
                        currentPassword.setCustomValidity('Please enter your current password to change it.');
                        currentPassword.reportValidity();
                        return false;
                    }
                    
                    if (!newPassValue) {
                        e.preventDefault();
                        newPassword.setCustomValidity('Please enter a new password.');
                        newPassword.reportValidity();
                        return false;
                    }
                    
                    if (newPassValue.length < 6) {
                        e.preventDefault();
                        newPassword.setCustomValidity('Password must be at least 6 characters long.');
                        newPassword.reportValidity();
                        return false;
                    }
                    
                    if (newPassValue !== confirmPassValue) {
                        e.preventDefault();
                        confirmPassword.setCustomValidity('Passwords do not match.');
                        confirmPassword.reportValidity();
                        return false;
                    }
                }
            });
            
            // Clear custom validity on input
            newPassword.addEventListener('input', function() {
                this.setCustomValidity('');
                if (this.value.trim() && this.value.trim().length < 6) {
                    this.setCustomValidity('Password must be at least 6 characters long.');
                }
            });
            
            confirmPassword.addEventListener('input', function() {
                this.setCustomValidity('');
                if (this.value.trim() && this.value.trim() !== newPassword.value.trim()) {
                    this.setCustomValidity('Passwords do not match.');
                }
            });
            
            currentPassword.addEventListener('input', function() {
                this.setCustomValidity('');
            });
        });
    </script>
</body>
</html>


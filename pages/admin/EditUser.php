<?php
require_once '../../config.php';

// Require admin role
requireRole('admin');

// Get admin data
$adminId = $_SESSION['user_id'];
$adminName = $_SESSION['user_name'];
$adminEmail = $_SESSION['user_email'];

// Get user ID from query parameter
$userId = intval($_GET['id'] ?? 0);

if ($userId <= 0) {
    $_SESSION['message'] = 'Invalid user ID.';
    $_SESSION['message_type'] = 'error';
    header('Location: ManageUser.php');
    exit();
}

$error = '';
$success = '';

// Get current user data
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, full_name, email, student_id, phone, course, semester, bank_name, bank_number, role FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    $conn->close();
    $_SESSION['message'] = 'User not found.';
    $_SESSION['message_type'] = 'error';
    header('Location: ManageUser.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $studentId = trim($_POST['student_id'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $bankName = trim($_POST['bank_name'] ?? '');
    $bankNumber = trim($_POST['bank_number'] ?? '');
    $role = $_POST['role'] ?? 'student';
    $newPassword = trim($_POST['new_password'] ?? '');
    
    // Validation
    if (empty($fullName)) {
        $error = 'Full name is required.';
    } elseif (empty($email)) {
        $error = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        // Check if email is already taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $error = 'Email is already taken by another user.';
        }
        $stmt->close();
        
        // Check if student_id is already taken by another user (if provided)
        if (empty($error) && !empty($studentId)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE student_id = ? AND id != ?");
            $stmt->bind_param("si", $studentId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $error = 'Student ID is already taken by another user.';
            }
            $stmt->close();
        }
    }
    
    // Update user if no errors
    if (empty($error)) {
        if (!empty($newPassword)) {
            // Update with password
            if (strlen($newPassword) < 6) {
                $error = 'Password must be at least 6 characters long.';
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, student_id = ?, phone = ?, course = ?, semester = ?, bank_name = ?, bank_number = ?, role = ?, password = ? WHERE id = ?");
                $stmt->bind_param("ssssssssssi", $fullName, $email, $studentId, $phone, $course, $semester, $bankName, $bankNumber, $role, $hashedPassword, $userId);
            }
        } else {
            // Update without password
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, student_id = ?, phone = ?, course = ?, semester = ?, bank_name = ?, bank_number = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssssssssi", $fullName, $email, $studentId, $phone, $course, $semester, $bankName, $bankNumber, $role, $userId);
        }
        
        if (empty($error)) {
            if ($stmt->execute()) {
                $_SESSION['message'] = 'User updated successfully!';
                $_SESSION['message_type'] = 'success';
                header('Location: ManageUser.php');
                exit();
            } else {
                $error = 'Error updating user: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}

$conn->close();

// Check for session messages
$sessionMessage = $_SESSION['message'] ?? '';
$sessionMessageType = $_SESSION['message_type'] ?? '';
if ($sessionMessage) {
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit User - Admin - RCMP UniFa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/styles.css" />
    <style>
        .profile-container {
            min-height: 100vh;
            background: var(--light);
            padding: 24px 0;
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
            max-width: 800px;
            margin: 0 auto;
            padding: 0 24px;
        }
        .profile-card {
            background: var(--card);
            padding: 40px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }
        .profile-card h2 {
            margin: 0 0 32px;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            padding-bottom: 16px;
            border-bottom: 2px solid var(--light);
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }
        .form-row {
            margin-bottom: 0;
        }
        .form-row.full-width {
            grid-column: 1 / -1;
        }
        .form-row label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text);
            font-size: 0.9rem;
        }
        .form-row .input,
        .form-row select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: var(--radius-sm);
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        .form-row .input:focus,
        .form-row select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(10, 61, 98, 0.1);
        }
        .form-help {
            font-size: 0.875rem;
            color: var(--muted);
            margin-top: 4px;
        }
        .password-section {
            margin-top: 32px;
            padding-top: 32px;
            border-top: 2px solid var(--light);
        }
        .password-section h3 {
            margin: 0 0 24px;
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text);
        }
        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 2px solid var(--light);
        }
        .nav-bar {
            background: var(--card);
            padding: 16px 0;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 24px;
        }
        .nav-bar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
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
        }
        .logout-btn:hover {
            background: #fdd;
        }
        @media (max-width: 640px) {
            .profile-card {
                padding: 24px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .form-row.full-width {
                grid-column: 1;
            }
            .form-actions {
                flex-direction: column;
            }
            .form-actions .btn {
                width: 100%;
            }
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
    if ($sessionMessage) {
        showMessageDialog($sessionMessageType ?: 'success', $sessionMessage, true, null, 4000);
    }
    ?>
    <!-- Navigation Bar -->
    <div class="nav-bar">
        <div class="nav-bar-content">
            <a href="../../index.php" class="brand">
                <img src="../../public/unikl-rcmp.png" alt="UniKL RCMP logo" class="logo" style="height: 40px;" />
            </a>
            <div class="nav-links">
                <a href="AdminDashboard.php">Dashboard</a>
                <a href="ViewApplication.php">Applications</a>
                <a href="ManageUser.php">Users</a>
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
            <h1>Edit User</h1>
            <p>Update user information</p>
        </div>
    </div>

    <!-- Profile Content -->
    <div class="profile-container">
        <div class="profile-content">
            <div class="profile-card">
                <h2>User Information</h2>
                <form method="post" action="" id="userForm">
                    <div class="form-grid">
                        <div class="form-row">
                            <label for="full_name">Full Name <span style="color: #dc2626;">*</span></label>
                            <input class="input" type="text" id="full_name" name="full_name" placeholder="Full name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required />
                        </div>
                        <div class="form-row">
                            <label for="email">Email <span style="color: #dc2626;">*</span></label>
                            <input class="input" type="email" id="email" name="email" placeholder="email@example.com" value="<?php echo htmlspecialchars($user['email']); ?>" required />
                        </div>
                        <div class="form-row">
                            <label for="student_id">Student ID</label>
                            <input class="input" type="text" id="student_id" name="student_id" placeholder="Student ID" value="<?php echo htmlspecialchars($user['student_id'] ?? ''); ?>" />
                        </div>
                        <div class="form-row">
                            <label for="phone">Phone</label>
                            <input class="input" type="text" id="phone" name="phone" placeholder="Phone number" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" />
                        </div>
                        <div class="form-row">
                            <label for="course">Course</label>
                            <input class="input" type="text" id="course" name="course" placeholder="Course" value="<?php echo htmlspecialchars($user['course'] ?? ''); ?>" />
                        </div>
                        <div class="form-row">
                            <label for="semester">Semester</label>
                            <input class="input" type="text" id="semester" name="semester" placeholder="Semester" value="<?php echo htmlspecialchars($user['semester'] ?? ''); ?>" />
                        </div>
                        <div class="form-row">
                            <label for="bank_name">Bank Name</label>
                            <input class="input" type="text" id="bank_name" name="bank_name" placeholder="Bank name" value="<?php echo htmlspecialchars($user['bank_name'] ?? ''); ?>" />
                        </div>
                        <div class="form-row">
                            <label for="bank_number">Bank Account Number</label>
                            <input class="input" type="text" id="bank_number" name="bank_number" placeholder="Bank account number" value="<?php echo htmlspecialchars($user['bank_number'] ?? ''); ?>" />
                        </div>
                        <div class="form-row">
                            <label for="role">Role <span style="color: #dc2626;">*</span></label>
                            <select id="role" name="role" class="input" required>
                                <option value="student" <?php echo $user['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="committee" <?php echo $user['role'] === 'committee' ? 'selected' : ''; ?>>Committee</option>
                            </select>
                        </div>
                    </div>

                    <!-- Password Change Section -->
                    <div class="password-section">
                        <h3>Change Password</h3>
                        <p class="form-help" style="margin-bottom: 24px;">Leave blank if you don't want to change the password</p>
                        <div class="form-grid">
                            <div class="form-row full-width">
                                <label for="new_password">New Password</label>
                                <input class="input" type="password" id="new_password" name="new_password" placeholder="Enter new password (min. 6 characters)" minlength="6" />
                                <p class="form-help">Password must be at least 6 characters long</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="ManageUser.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../component/footer.php'; renderFooter('../../'); ?>
    <script src="../../js/main.js"></script>
    <script>
        // Client-side password validation
        const userForm = document.getElementById('userForm');
        const newPasswordInput = document.getElementById('new_password');

        userForm.addEventListener('submit', function(e) {
            const newPassword = newPasswordInput.value;

            if (newPassword && newPassword.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long.');
                newPasswordInput.focus();
                return false;
            }
        });
    </script>
</body>
</html>


<?php
require_once '../../config.php';

// Require admin role
requireRole('admin');

// Get admin data
$adminId = $_SESSION['user_id'];
$adminName = $_SESSION['user_name'];
$adminEmail = $_SESSION['user_email'];

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name)) {
        $error = 'Name is required.';
    } elseif (empty($email)) {
        $error = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } else {
        $conn = getDBConnection();
        
        // Check if email is already taken by another admin
        $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $adminId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email is already taken by another admin.';
            $stmt->close();
        } else {
            $stmt->close();
            
            // Check if password change is requested
            $passwordChange = !empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword);
            
            if ($passwordChange) {
                // Validate password fields
                if (empty($currentPassword)) {
                    $error = 'Current password is required to change password.';
                } elseif (empty($newPassword)) {
                    $error = 'New password is required.';
                } elseif (strlen($newPassword) < 6) {
                    $error = 'New password must be at least 6 characters long.';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'New password and confirm password do not match.';
                } else {
                    // Verify current password
                    $stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
                    $stmt->bind_param("i", $adminId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $admin = $result->fetch_assoc();
                    $stmt->close();
                    
                    if (!password_verify($currentPassword, $admin['password'])) {
                        $error = 'Current password is incorrect.';
                    } else {
                        // Update admin with new password
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("UPDATE admin SET name = ?, email = ?, password = ? WHERE id = ?");
                        $stmt->bind_param("sssi", $name, $email, $hashedPassword, $adminId);
                        
                        if ($stmt->execute()) {
                            // Update session variables
                            $_SESSION['user_name'] = $name;
                            $_SESSION['user_email'] = $email;
                            $_SESSION['message'] = 'Profile updated successfully!';
                            $_SESSION['message_type'] = 'success';
                            header('Location: AdminDashboard.php');
                            exit();
                        } else {
                            $error = 'Error updating profile: ' . $conn->error;
                        }
                        $stmt->close();
                    }
                }
            } else {
                // Update admin without password change
                $stmt = $conn->prepare("UPDATE admin SET name = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $email, $adminId);
                
                if ($stmt->execute()) {
                    // Update session variables
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['message'] = 'Profile updated successfully!';
                    $_SESSION['message_type'] = 'success';
                    header('Location: AdminDashboard.php');
                    exit();
                } else {
                    $error = 'Error updating profile: ' . $conn->error;
                }
                $stmt->close();
            }
        }
        
        $conn->close();
    }
}

// Fetch current admin data
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT name, email FROM admin WHERE id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();
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
    <title>Edit Profile - Admin - RCMP UniFa</title>
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
            grid-template-columns: 1fr;
            gap: 24px;
        }
        .form-row {
            margin-bottom: 0;
        }
        .form-row label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text);
            font-size: 0.9rem;
        }
        .form-row .input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e5e7eb;
            border-radius: var(--radius-sm);
            font-size: 1rem;
            transition: all 0.2s ease;
        }
        .form-row .input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(10, 61, 98, 0.1);
        }
        .form-row .input[readonly] {
            background: var(--light);
            color: var(--muted);
            cursor: not-allowed;
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
        }
        .logout-btn:hover {
            background: #fdd;
        }
        @media (max-width: 640px) {
            .profile-card {
                padding: 24px;
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
            <p>Update your admin profile information</p>
        </div>
    </div>

    <!-- Profile Content -->
    <div class="profile-container">
        <div class="profile-content">
            <div class="profile-card">
                <h2>Profile Information</h2>
                <form method="post" action="" id="profileForm">
                    <div class="form-grid">
                        <div class="form-row">
                            <label for="name">Name <span style="color: #dc2626;">*</span></label>
                            <input class="input" type="text" id="name" name="name" placeholder="Your full name" value="<?php echo htmlspecialchars($admin['name']); ?>" required />
                        </div>
                        <div class="form-row">
                            <label for="email">Email <span style="color: #dc2626;">*</span></label>
                            <input class="input" type="email" id="email" name="email" placeholder="admin@unikl.com" value="<?php echo htmlspecialchars($admin['email']); ?>" required />
                        </div>
                    </div>

                    <!-- Password Change Section -->
                    <div class="password-section">
                        <h3>Change Password</h3>
                        <p class="form-help" style="margin-bottom: 24px;">Leave blank if you don't want to change your password</p>
                        <div class="form-grid">
                            <div class="form-row">
                                <label for="current_password">Current Password</label>
                                <input class="input" type="password" id="current_password" name="current_password" placeholder="Enter current password" />
                            </div>
                            <div class="form-row">
                                <label for="new_password">New Password</label>
                                <input class="input" type="password" id="new_password" name="new_password" placeholder="Enter new password (min. 6 characters)" minlength="6" />
                                <p class="form-help">Password must be at least 6 characters long</p>
                            </div>
                            <div class="form-row">
                                <label for="confirm_password">Confirm New Password</label>
                                <input class="input" type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" minlength="6" />
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="AdminDashboard.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../component/footer.php'; renderFooter('../../'); ?>
    <script src="../../js/main.js"></script>
    <script>
        // Client-side password validation
        const profileForm = document.getElementById('profileForm');
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const currentPasswordInput = document.getElementById('current_password');

        profileForm.addEventListener('submit', function(e) {
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const currentPassword = currentPasswordInput.value;

            // Check if any password field is filled
            const passwordChangeRequested = currentPassword || newPassword || confirmPassword;

            if (passwordChangeRequested) {
                // All password fields must be filled
                if (!currentPassword) {
                    e.preventDefault();
                    alert('Please enter your current password to change your password.');
                    currentPasswordInput.focus();
                    return false;
                }

                if (!newPassword) {
                    e.preventDefault();
                    alert('Please enter a new password.');
                    newPasswordInput.focus();
                    return false;
                }

                if (newPassword.length < 6) {
                    e.preventDefault();
                    alert('New password must be at least 6 characters long.');
                    newPasswordInput.focus();
                    return false;
                }

                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('New password and confirm password do not match.');
                    confirmPasswordInput.focus();
                    return false;
                }
            }
        });

        // Real-time password match validation
        confirmPasswordInput.addEventListener('input', function() {
            if (confirmPasswordInput.value && newPasswordInput.value) {
                if (confirmPasswordInput.value !== newPasswordInput.value) {
                    confirmPasswordInput.setCustomValidity('Passwords do not match');
                } else {
                    confirmPasswordInput.setCustomValidity('');
                }
            }
        });

        newPasswordInput.addEventListener('input', function() {
            if (confirmPasswordInput.value && newPasswordInput.value) {
                if (confirmPasswordInput.value !== newPasswordInput.value) {
                    confirmPasswordInput.setCustomValidity('Passwords do not match');
                } else {
                    confirmPasswordInput.setCustomValidity('');
                }
            }
        });
    </script>
</body>
</html>


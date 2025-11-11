<?php
require_once '../config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = $_SESSION['user_role'];
    switch ($role) {
        case 'admin':
            header('Location: admin/AdminDashboard.php');
            break;
        case 'committee':
            header('Location: committee/CommitteeDashboard.php');
            break;
        case 'student':
        default:
            header('Location: student/StudentDashboard.php');
            break;
    }
    exit();
}

$error = '';
$userType = $_POST['userType'] ?? 'student'; // Default to student
$staffRole = $_POST['staffRole'] ?? 'admin'; // Default to admin for staff

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginId = trim($_POST['loginId'] ?? '');
    $password = $_POST['password'] ?? '';
    $userType = $_POST['userType'] ?? 'student';
    $staffRole = $_POST['staffRole'] ?? 'admin'; // admin or committee
    $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';
    
    // Validation
    $loginFieldLabel = 'Student ID/Email';
    if ($userType === 'staff') {
        $loginFieldLabel = 'Email';
    }
    
    // Validate staff role is selected when staff is chosen
    if ($userType === 'staff' && empty($staffRole)) {
        $staffRole = 'admin'; // Default to admin
    }
    
    if (empty($loginId) || empty($password)) {
        $error = 'Please enter your ' . $loginFieldLabel . ' and password.';
    } else {
        $conn = getDBConnection();
        
        if ($userType === 'staff' && $staffRole === 'admin') {
            // Check admin table for admin login
            $stmt = $conn->prepare("SELECT id, name, email, password FROM admin WHERE email = ?");
            $stmt->bind_param("s", $loginId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $admin['password'])) {
                    // Set session variables for admin
                    $_SESSION['user_id'] = $admin['id'];
                    $_SESSION['user_name'] = $admin['name'];
                    $_SESSION['user_email'] = $admin['email'];
                    $_SESSION['user_role'] = 'admin';
                    
                    // Set success message
                    $_SESSION['message'] = 'Welcome back, ' . htmlspecialchars($admin['name']) . '! You have been successfully logged in.';
                    $_SESSION['message_type'] = 'success';
                    
                    // Handle "Remember Me" - set cookie for 30 days
                    if ($remember) {
                        $cookieValue = base64_encode($admin['id'] . ':admin:' . hash('sha256', $admin['password']));
                        setcookie('remember_token', $cookieValue, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                    }
                    
                    // Redirect to admin dashboard
                    header('Location: admin/AdminDashboard.php');
                    exit();
                } else {
                    $error = 'Invalid email or password.';
                }
            } else {
                $error = 'Invalid email or password.';
            }
            $stmt->close();
        } elseif ($userType === 'staff' && $staffRole === 'committee') {
            // Check committee table for committee login
            $stmt = $conn->prepare("SELECT id, name, email, password FROM committee WHERE email = ?");
            $stmt->bind_param("s", $loginId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $committee = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $committee['password'])) {
                    // Set session variables for committee
                    $_SESSION['user_id'] = $committee['id'];
                    $_SESSION['user_name'] = $committee['name'];
                    $_SESSION['user_email'] = $committee['email'];
                    $_SESSION['user_role'] = 'committee';
                    
                    // Set success message
                    $_SESSION['message'] = 'Welcome back, ' . htmlspecialchars($committee['name']) . '! You have been successfully logged in.';
                    $_SESSION['message_type'] = 'success';
                    
                    // Handle "Remember Me" - set cookie for 30 days
                    if ($remember) {
                        $cookieValue = base64_encode($committee['id'] . ':committee:' . hash('sha256', $committee['password']));
                        setcookie('remember_token', $cookieValue, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                    }
                    
                    // Redirect to committee dashboard
                    header('Location: committee/CommitteeDashboard.php');
                    exit();
                } else {
                    $error = 'Invalid email or password.';
                }
            } else {
                $error = 'Invalid email or password.';
            }
            $stmt->close();
        } else {
            // Check users table for student login
            $stmt = $conn->prepare("SELECT id, full_name, email, student_id, password, role FROM users WHERE email = ? OR student_id = ?");
            $stmt->bind_param("ss", $loginId, $loginId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['student_id'] = $user['student_id'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Set success message
                    $_SESSION['message'] = 'Welcome back, ' . htmlspecialchars($user['full_name']) . '! You have been successfully logged in.';
                    $_SESSION['message_type'] = 'success';
                    
                    // Handle "Remember Me" - set cookie for 30 days
                    if ($remember) {
                        $cookieValue = base64_encode($user['id'] . ':user:' . hash('sha256', $user['password']));
                        setcookie('remember_token', $cookieValue, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                    }
                    
                    // Redirect based on role
                    switch ($user['role']) {
                        case 'admin':
                            header('Location: admin/AdminDashboard.php');
                            break;
                        case 'committee':
                            header('Location: committee/CommitteeDashboard.php');
                            break;
                        case 'student':
                        default:
                            header('Location: student/StudentDashboard.php');
                            break;
                    }
                    exit();
                } else {
                    $error = 'Invalid Student ID/Email or password.';
                }
            } else {
                $error = 'Invalid Student ID/Email or password.';
            }
            $stmt->close();
        }
        
        $conn->close();
    }
}

// Check for session messages (from logout, etc.)
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
    <title>Login - RCMP UniFa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css" />
    <style>
        .user-type-selector {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            background: var(--light);
            padding: 4px;
            border-radius: var(--radius-sm);
        }
        #staffRoleSelector {
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .user-type-option {
            flex: 1;
            position: relative;
        }
        .user-type-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }
        .user-type-option label {
            display: block;
            padding: 12px 16px;
            text-align: center;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
            color: var(--muted);
            background: transparent;
            border: 2px solid transparent;
        }
        .user-type-option input[type="radio"]:checked + label {
            background: var(--card);
            color: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .user-type-option label:hover {
            color: var(--primary);
        }
        .login-field-label {
            transition: all 0.2s ease;
        }
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
    if ($sessionMessage) {
        showMessageDialog($sessionMessageType ?: 'info', $sessionMessage, true, null, 4000);
    }
    ?>
    <main class="auth-page">
        <div class="auth-card-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <a class="brand-mini" href="../index.php">
                    <img src="../public/unikl-rcmp.png" alt="UniKL RCMP logo" />
                </a>
                <h1>Welcome back</h1>
                <p class="small muted">Sign in to manage your financial aid applications</p>
            </div>
            <div class="auth-body">
                <form method="post" action="" id="loginForm">
                    <!-- User Type Selector -->
                    <div class="user-type-selector">
                        <div class="user-type-option">
                            <input type="radio" id="userTypeStudent" name="userType" value="student" <?php echo $userType === 'student' ? 'checked' : ''; ?> />
                            <label for="userTypeStudent">Student</label>
                        </div>
                        <div class="user-type-option">
                            <input type="radio" id="userTypeStaff" name="userType" value="staff" <?php echo $userType === 'staff' ? 'checked' : ''; ?> />
                            <label for="userTypeStaff">Staff</label>
                        </div>
                    </div>
                    
                    <!-- Staff Role Selector (only shown when Staff is selected) -->
                    <div class="form-row" id="staffRoleSelector" style="display: <?php echo $userType === 'staff' ? 'block' : 'none'; ?>;">
                        <label for="staffRole">Role</label>
                        <select class="input" id="staffRole" name="staffRole">
                            <option value="admin" <?php echo ($userType === 'staff' && $staffRole === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="committee" <?php echo ($userType === 'staff' && $staffRole === 'committee') ? 'selected' : ''; ?>>Committee</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <label for="loginId" class="login-field-label" id="loginLabel">Student ID / Email</label>
                        <input class="input" type="text" id="loginId" name="loginId" placeholder="e.g. B12345 or name@unikl.edu.my" value="<?php echo isset($_POST['loginId']) ? htmlspecialchars($_POST['loginId']) : ''; ?>" required />
                    </div>
                    <div class="form-row">
                        <label for="password">Password</label>
                        <input class="input" type="password" id="password" name="password" placeholder="Your password" required />
                    </div>
                    <div class="form-actions">
                        <label class="small muted"><input type="checkbox" name="remember" /> Remember me</label>
                        <a href="#" class="small link">Forgot password?</a>
                    </div>
                    <div class="form-row" style="margin-top:14px;">
                        <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
                    </div>
                </form>
                <div class="divider"></div>
                <p class="text-center small" id="registerLink">New to UniFa? <a class="link" href="register.php">Create an account</a></p>
                <p class="text-center small"><a class="link" href="../index.php">← Back to Home</a></p>
            </div>
        </div>
        <!-- Video Card -->
        <div class="video-card">
            <div class="video-card-content">
                <div class="video-card-text">
                    <h2>RCMP UniFa</h2>
                    <p>Your trusted platform for managing financial aid applications at UniKL RCMP.</p>
                    <div class="video-card-features">
                        <div class="feature-item">
                            <span>Easy application submission</span>
                        </div>
                        <div class="feature-item">
                            <span>Track application status</span>
                        </div>
                        <div class="feature-item">
                            <span>Secure document management</span>
                        </div>
                        <div class="feature-item">
                            <span>Quick approval process</span>
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
    <script>
        // Update form based on user type selection
        const userTypeRadios = document.querySelectorAll('input[name="userType"]');
        const staffRoleSelect = document.getElementById('staffRole');
        const staffRoleSelector = document.getElementById('staffRoleSelector');
        const loginLabel = document.getElementById('loginLabel');
        const loginInput = document.getElementById('loginId');
        const registerLink = document.getElementById('registerLink');
        
        function updateFormForUserType() {
            const selectedType = document.querySelector('input[name="userType"]:checked').value;
            
            // Show/hide staff role selector
            if (selectedType === 'staff') {
                staffRoleSelector.style.display = 'block';
                staffRoleSelect.required = true;
                updateFormForStaffRole();
            } else {
                staffRoleSelector.style.display = 'none';
                staffRoleSelect.required = false;
                loginLabel.textContent = 'Student ID / Email';
                loginInput.placeholder = 'e.g. B12345 or name@unikl.edu.my';
                registerLink.style.display = 'block';
            }
        }
        
        function updateFormForStaffRole() {
            const role = staffRoleSelect.value;
            loginLabel.textContent = 'Email';
            
            if (role === 'admin') {
                loginInput.placeholder = 'e.g. admin@unikl.com';
            } else if (role === 'committee') {
                loginInput.placeholder = 'e.g. committee1@unikl.com';
            }
            
            registerLink.style.display = 'none';
        }
        
        userTypeRadios.forEach(radio => {
            radio.addEventListener('change', updateFormForUserType);
        });
        
        staffRoleSelect.addEventListener('change', updateFormForStaffRole);
        
        // Initialize on page load
        updateFormForUserType();
    </script>
    <?php include 'component/footer.php'; renderFooter('../'); ?>
    <script src="../js/main.js"></script>
</body>
</html>



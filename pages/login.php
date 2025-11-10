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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginId = trim($_POST['studentId'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';
    
    // Validation
    if (empty($loginId) || empty($password)) {
        $error = 'Please enter your Student ID/Email and password.';
    } else {
        $conn = getDBConnection();
        
        // Check if user exists by email or student_id
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
                
                // Handle "Remember Me" - set cookie for 30 days
                if ($remember) {
                    $cookieValue = base64_encode($user['id'] . ':' . hash('sha256', $user['password']));
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
        $conn->close();
    }
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
</head>
<body>
    <main class="auth-page">
        <div class="auth-card">
            <div class="auth-header">
                <a class="brand-mini" href="../index.php">
                    <img src="../public/unikl-rcmp.png" alt="UniKL RCMP logo" />
                </a>
                <h1>Welcome back</h1>
                <p class="small muted">Sign in to manage your financial aid applications</p>
            </div>
            <div class="auth-body">
                <?php if ($error): ?>
                    <div style="background-color: #fee; color: #c33; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #fcc;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                <form method="post" action="">
                    <div class="form-row">
                        <label for="studentId">Student ID / Email</label>
                        <input class="input" type="text" id="studentId" name="studentId" placeholder="e.g. B12345 or name@unikl.edu.my" value="<?php echo isset($_POST['studentId']) ? htmlspecialchars($_POST['studentId']) : ''; ?>" required />
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
                <p class="text-center small">New to UniFa? <a class="link" href="register.php">Create an account</a></p>
                <p class="text-center small"><a class="link" href="../index.php">← Back to Home</a></p>
            </div>
        </div>
    </main>
    <script src="../js/main.js"></script>
</body>
</html>



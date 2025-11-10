<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'unifa_db');

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-login from remember me cookie if session is not set
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $cookieValue = $_COOKIE['remember_token'];
    $decoded = base64_decode($cookieValue);
    $parts = explode(':', $decoded);
    
    if (count($parts) === 2) {
        $userId = $parts[0];
        $passwordHash = $parts[1];
        
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, full_name, email, student_id, password, role FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verify the password hash matches
            if (hash('sha256', $user['password']) === $passwordHash) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['user_role'] = $user['role'];
            } else {
                // Invalid cookie, delete it
                setcookie('remember_token', '', time() - 3600, '/');
            }
        } else {
            // User not found, delete cookie
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        $stmt->close();
        $conn->close();
    }
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Helper function to redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        // Get the directory of the current script
        $currentDir = dirname($_SERVER['SCRIPT_NAME']);
        // Calculate path to login.php relative to current script
        if (strpos($currentDir, '/pages/student') !== false || 
            strpos($currentDir, '/pages/admin') !== false || 
            strpos($currentDir, '/pages/committee') !== false) {
            // We're in a subdirectory, go up one level to pages
            header('Location: ../login.php');
        } else {
            // We're in pages directory or root
            header('Location: pages/login.php');
        }
        exit();
    }
}

// Helper function to check user role
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Helper function to require specific role
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        // Get the directory of the current script
        $currentDir = dirname($_SERVER['SCRIPT_NAME']);
        // Calculate path to index.php relative to current script
        if (strpos($currentDir, '/pages/student') !== false || 
            strpos($currentDir, '/pages/admin') !== false || 
            strpos($currentDir, '/pages/committee') !== false) {
            // We're in a subdirectory, go up to root
            header('Location: ../../index.php');
        } elseif (strpos($currentDir, '/pages') !== false) {
            // We're in pages directory
            header('Location: ../index.php');
        } else {
            header('Location: index.php');
        }
        exit();
    }
}
?>

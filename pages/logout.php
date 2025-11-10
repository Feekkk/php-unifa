<?php
require_once '../config.php';

// Clear remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destroy session
session_unset();
session_destroy();

// Redirect to home page with logout parameter
header('Location: ../index.php?logged_out=1');
exit();
?>

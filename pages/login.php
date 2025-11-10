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
                <form method="post" action="#">
                    <div class="form-row">
                        <label for="studentId">Student ID / Email</label>
                        <input class="input" type="text" id="studentId" name="studentId" placeholder="e.g. B12345 or name@unikl.edu.my" required />
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
    <!-- Note: Wire up actual authentication later (server-side). -->
</body>
<!-- c:\laragon\www\unifa\pages\login.php -->
</html>



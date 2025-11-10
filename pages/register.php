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
    <main class="auth-page">
        <div class="auth-card">
            <div class="auth-header">
                <a class="brand-mini" href="../index.php">
                    <img src="../public/unikl-rcmp.png" alt="UniKL RCMP logo" />
                </a>
                <h1>Create your account</h1>
                <p class="small muted">Register to apply for financial aid programs</p>
            </div>
            <div class="auth-body">
                <form method="post" action="#">
                    <div class="form-row">
                        <label for="fullName">Full Name</label>
                        <input class="input" type="text" id="fullName" name="fullName" placeholder="Your full name" required />
                    </div>
                    <div class="form-row">
                        <label for="email">Email</label>
                        <input class="input" type="email" id="email" name="email" placeholder="name@unikl.edu.my" required />
                    </div>
                    <div class="form-row">
                        <label for="studentId">Student ID</label>
                        <input class="input" type="text" id="studentId" name="studentId" placeholder="e.g. B12345" required />
                    </div>
                    <div class="form-row">
                        <label for="phone">Phone</label>
                        <input class="input" type="tel" id="phone" name="phone" placeholder="e.g. 012-3456789" />
                    </div>
                    <div class="form-row">
                        <label for="password">Password</label>
                        <input class="input" type="password" id="password" name="password" placeholder="Create a password" required />
                    </div>
                    <div class="form-row">
                        <label for="confirm">Confirm Password</label>
                        <input class="input" type="password" id="confirm" name="confirm" placeholder="Confirm password" required />
                    </div>
                    <div class="form-row" style="margin-top:14px;">
                        <button type="submit" class="btn btn-primary" style="width:100%;">Create Account</button>
                    </div>
                </form>
                <div class="divider"></div>
                <p class="text-center small">Already have an account? <a class="link" href="login.php">Login</a></p>
                <p class="text-center small"><a class="link" href="../index.php">← Back to Home</a></p>
            </div>
        </div>
    </main>
    <script src="../js/main.js"></script>
</body>
</html>



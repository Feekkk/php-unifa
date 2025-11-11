<?php
require_once '../../config.php';

// Require admin role
requireRole('admin');

// Get admin data
$adminId = $_SESSION['user_id'];
$adminName = $_SESSION['user_name'];
$adminEmail = $_SESSION['user_email'];

// Get application statistics
$conn = getDBConnection();

// Total applications
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM applications");
$stmt->execute();
$result = $stmt->get_result();
$totalApps = $result->fetch_assoc()['total'];
$stmt->close();

// Pending applications
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM applications WHERE status = 'pending'");
$stmt->execute();
$result = $stmt->get_result();
$pendingCount = $result->fetch_assoc()['total'];
$stmt->close();

// Under review applications
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM applications WHERE status = 'under_review'");
$stmt->execute();
$result = $stmt->get_result();
$underReviewCount = $result->fetch_assoc()['total'];
$stmt->close();

// Approved applications
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM applications WHERE status = 'approved'");
$stmt->execute();
$result = $stmt->get_result();
$approvedCount = $result->fetch_assoc()['total'];
$stmt->close();

// Rejected applications
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM applications WHERE status = 'rejected'");
$stmt->execute();
$result = $stmt->get_result();
$rejectedCount = $result->fetch_assoc()['total'];
$stmt->close();

// Total students
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'student'");
$stmt->execute();
$result = $stmt->get_result();
$totalStudents = $result->fetch_assoc()['total'];
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
    <title>Admin Dashboard - RCMP UniFa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/styles.css" />
    <style>
        .dashboard-container {
            min-height: 100vh;
            background: var(--light);
            padding: 24px 0;
        }
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            padding: 32px 0;
            margin-bottom: 32px;
        }
        .dashboard-header h1 {
            margin: 0 0 8px;
            font-size: 2rem;
            font-weight: 700;
        }
        .dashboard-header p {
            margin: 0;
            opacity: 0.9;
        }
        .dashboard-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: var(--card);
            padding: 20px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 100px;
            transition: all 0.2s ease;
        }
        .stat-card:hover {
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        .stat-card h3 {
            margin: 0 0 8px;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0;
            line-height: 1.2;
        }
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        .dashboard-section {
            background: var(--card);
            padding: 32px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }
        .dashboard-section h2 {
            margin: 0 0 24px;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
        }
        .admin-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        .info-item {
            padding: 16px;
            background: var(--light);
            border-radius: var(--radius-sm);
        }
        .info-label {
            font-size: 0.875rem;
            color: var(--muted);
            margin-bottom: 4px;
        }
        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text);
        }
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
            margin-top: 24px;
        }
        .action-card {
            padding: 24px;
            background: var(--light);
            border-radius: var(--radius-sm);
            border: 2px solid #e5e7eb;
            transition: all 0.2s ease;
        }
        .action-card:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        .action-card h3 {
            margin: 0 0 8px;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text);
        }
        .action-card p {
            margin: 0 0 16px;
            color: var(--muted);
            font-size: 0.875rem;
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
    </style>
</head>
<body>
    <?php 
    include '../component/MessageDialog.php';
    renderMessageDialogScript();
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
                <a href="EditProfile.php">Profile</a>
                <form method="post" action="../logout.php" style="display: inline;">
                    <button type="submit" class="logout-btn" name="logout">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="dashboard-content">
            <h1>Welcome back, <?php echo htmlspecialchars($adminName); ?>!</h1>
            <p>Manage financial aid applications and system administration</p>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="dashboard-container">
        <div class="dashboard-content">
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Applications</h3>
                    <p class="stat-value"><?php echo $totalApps; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Pending</h3>
                    <p class="stat-value"><?php echo $pendingCount; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Under Review</h3>
                    <p class="stat-value"><?php echo $underReviewCount; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Approved</h3>
                    <p class="stat-value"><?php echo $approvedCount; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Rejected</h3>
                    <p class="stat-value"><?php echo $rejectedCount; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Students</h3>
                    <p class="stat-value"><?php echo $totalStudents; ?></p>
                </div>
            </div>

            <!-- Admin Profile Section -->
            <div class="dashboard-section">
                <h2>Admin Profile</h2>
                <div class="admin-info">
                    <div class="info-item">
                        <div class="info-label">Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($adminName); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($adminEmail); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Role</div>
                        <div class="info-value">Administrator</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Section -->
            <div class="dashboard-section">
                <h2>Quick Actions</h2>
                <div class="actions-grid">
                    <div class="action-card">
                        <h3>View Applications</h3>
                        <p>Review and manage all applications</p>
                        <a href="ViewApplication.php" class="btn btn-primary">View All</a>
                    </div>
                    <div class="action-card">
                        <h3>Manage Students</h3>
                        <p>View and manage student accounts</p>
                        <a href="#" class="btn btn-outline">Manage</a>
                    </div>
                    <div class="action-card">
                        <h3>Edit Profile</h3>
                        <p>Update your admin profile information</p>
                        <a href="EditProfile.php" class="btn btn-outline">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../component/footer.php'; renderFooter('../../'); ?>
    <script src="../../js/main.js"></script>
</body>
</html>


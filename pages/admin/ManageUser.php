<?php
require_once '../../config.php';

// Require admin role
requireRole('admin');

// Get admin data
$adminId = $_SESSION['user_id'];
$adminName = $_SESSION['user_name'];
$adminEmail = $_SESSION['user_email'];

// Get filter parameters
$roleFilter = $_GET['role'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Build query
$conn = getDBConnection();

// Base query to get all users
$query = "SELECT 
    id,
    full_name,
    email,
    student_id,
    phone,
    course,
    semester,
    bank_name,
    bank_number,
    role,
    created_at,
    updated_at
FROM users
WHERE 1=1";

$params = [];
$types = '';

// Apply filters
if (!empty($roleFilter)) {
    $query .= " AND role = ?";
    $params[] = $roleFilter;
    $types .= 's';
}

if (!empty($searchQuery)) {
    $query .= " AND (full_name LIKE ? OR email LIKE ? OR student_id LIKE ?)";
    $searchParam = "%{$searchQuery}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();

// Get application counts for each user
$userAppCounts = [];
if (!empty($users)) {
    $userIds = array_column($users, 'id');
    if (!empty($userIds)) {
        $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
        $stmt = $conn->prepare("SELECT user_id, COUNT(*) as app_count FROM applications WHERE user_id IN ($placeholders) GROUP BY user_id");
        $stmt->bind_param(str_repeat('i', count($userIds)), ...$userIds);
        $stmt->execute();
        $appResult = $stmt->get_result();
        while ($row = $appResult->fetch_assoc()) {
            $userAppCounts[$row['user_id']] = $row['app_count'];
        }
        $stmt->close();
    }
}

$conn->close();

// Helper function to get role badge class
function getRoleBadgeClass($role) {
    switch ($role) {
        case 'admin':
            return 'role-admin';
        case 'committee':
            return 'role-committee';
        default:
            return 'role-student';
    }
}

// Helper function to get role label
function getRoleLabel($role) {
    switch ($role) {
        case 'admin':
            return 'Admin';
        case 'committee':
            return 'Committee';
        default:
            return 'Student';
    }
}

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
    <title>Manage Users - Admin - RCMP UniFa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/styles.css" />
    <style>
        .users-container {
            min-height: 100vh;
            background: var(--light);
            padding: 24px 0;
        }
        .users-header {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            padding: 32px 0;
            margin-bottom: 32px;
        }
        .users-header h1 {
            margin: 0 0 8px;
            font-size: 2rem;
            font-weight: 700;
        }
        .users-header p {
            margin: 0;
            opacity: 0.9;
        }
        .users-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
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
        .filters-section {
            background: var(--card);
            padding: 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
        }
        .filters-section h3 {
            margin: 0 0 16px;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text);
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        .filter-group label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--muted);
            margin-bottom: 8px;
        }
        .filter-group select,
        .filter-group input {
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(10, 61, 98, 0.1);
        }
        .filter-actions {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-item {
            padding: 20px;
            background: var(--card);
            border-radius: var(--radius-sm);
            border: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 100px;
            transition: all 0.2s ease;
        }
        .stat-item:hover {
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        .stat-item .label {
            font-size: 0.75rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .stat-item .value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1.2;
        }
        .users-table {
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .table-header {
            padding: 20px 24px;
            border-bottom: 2px solid var(--light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .table-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
        }
        .table-wrapper {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead {
            background: var(--light);
        }
        th {
            padding: 16px 24px;
            text-align: left;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }
        td {
            padding: 16px 24px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.9rem;
            color: var(--text);
        }
        tbody tr {
            transition: background 0.2s ease;
        }
        tbody tr:hover {
            background: var(--light);
        }
        tbody tr:last-child td {
            border-bottom: none;
        }
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .role-student {
            background: #dbeafe;
            color: #1e40af;
        }
        .role-admin {
            background: #fee2e2;
            color: #991b1b;
        }
        .role-committee {
            background: #fef3c7;
            color: #92400e;
        }
        .user-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .user-name {
            font-weight: 600;
            color: var(--text);
        }
        .user-email {
            font-size: 0.875rem;
            color: var(--muted);
        }
        .user-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 0.875rem;
        }
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .meta-label {
            color: var(--muted);
            font-weight: 500;
        }
        .meta-value {
            color: var(--text);
        }
        .app-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            padding: 4px 8px;
            background: var(--primary);
            color: #fff;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .empty-state {
            text-align: center;
            padding: 64px 24px;
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 16px;
            opacity: 0.3;
        }
        .empty-state h3 {
            margin: 0 0 8px;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text);
        }
        .empty-state p {
            margin: 0;
            color: var(--muted);
        }
        @media (max-width: 768px) {
            .filters-grid {
                grid-template-columns: 1fr;
            }
            .table-wrapper {
                overflow-x: scroll;
            }
            table {
                min-width: 800px;
            }
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
                <a href="ManageUser.php">Users</a>
                <a href="EditProfile.php">Profile</a>
                <form method="post" action="../logout.php" style="display: inline;">
                    <button type="submit" class="logout-btn" name="logout">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Users Header -->
    <div class="users-header">
        <div class="users-content">
            <h1>Manage Users</h1>
            <p>View and manage all system users</p>
        </div>
    </div>

    <!-- Users Content -->
    <div class="users-container">
        <div class="users-content">
            <!-- Stats Bar -->
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="label">Total Users</div>
                    <div class="value"><?php echo count($users); ?></div>
                </div>
                <div class="stat-item">
                    <div class="label">Students</div>
                    <div class="value"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'student')); ?></div>
                </div>
                <div class="stat-item">
                    <div class="label">Admins</div>
                    <div class="value"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'admin')); ?></div>
                </div>
                <div class="stat-item">
                    <div class="label">Committee</div>
                    <div class="value"><?php echo count(array_filter($users, fn($u) => $u['role'] === 'committee')); ?></div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="filters-section">
                <h3>Filters</h3>
                <form method="get" action="" id="filterForm">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="role">Role</label>
                            <select id="role" name="role">
                                <option value="">All Roles</option>
                                <option value="student" <?php echo $roleFilter === 'student' ? 'selected' : ''; ?>>Student</option>
                                <option value="admin" <?php echo $roleFilter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="committee" <?php echo $roleFilter === 'committee' ? 'selected' : ''; ?>>Committee</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="search">Search</label>
                            <input type="text" id="search" name="search" placeholder="Name, email, or student ID" value="<?php echo htmlspecialchars($searchQuery); ?>" />
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="ManageUser.php" class="btn btn-outline">Clear</a>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="users-table">
                <div class="table-header">
                    <h3>All Users</h3>
                </div>
                <div class="table-wrapper">
                    <?php if (empty($users)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">👥</div>
                            <h3>No Users Found</h3>
                            <p>There are no users matching your filters.</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Student ID</th>
                                    <th>Role</th>
                                    <th>Course</th>
                                    <th>Semester</th>
                                    <th>Phone</th>
                                    <th>Applications</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): 
                                    $appCount = $userAppCounts[$user['id']] ?? 0;
                                ?>
                                    <tr>
                                        <td>
                                            <div class="user-info">
                                                <div class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                                <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($user['student_id'])): ?>
                                                <?php echo htmlspecialchars($user['student_id']); ?>
                                            <?php else: ?>
                                                <span style="color: var(--muted);">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="role-badge <?php echo getRoleBadgeClass($user['role']); ?>">
                                                <?php echo getRoleLabel($user['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($user['course'])): ?>
                                                <?php echo htmlspecialchars($user['course']); ?>
                                            <?php else: ?>
                                                <span style="color: var(--muted);">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($user['semester'])): ?>
                                                <?php echo htmlspecialchars($user['semester']); ?>
                                            <?php else: ?>
                                                <span style="color: var(--muted);">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($user['phone'])): ?>
                                                <?php echo htmlspecialchars($user['phone']); ?>
                                            <?php else: ?>
                                                <span style="color: var(--muted);">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($appCount > 0): ?>
                                                <span class="app-count"><?php echo $appCount; ?></span>
                                            <?php else: ?>
                                                <span style="color: var(--muted);">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../component/footer.php'; renderFooter('../../'); ?>
    <script src="../../js/main.js"></script>
</body>
</html>


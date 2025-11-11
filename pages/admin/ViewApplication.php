<?php
require_once '../../config.php';

// Require admin role
requireRole('admin');

// Get admin data
$adminId = $_SESSION['user_id'];
$adminName = $_SESSION['user_name'];
$adminEmail = $_SESSION['user_email'];

// Get filter parameters
$statusFilter = $_GET['status'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Build query
$conn = getDBConnection();

// Base query to get all applications with student info
$query = "SELECT 
    a.*,
    u.full_name as student_name,
    u.email as student_email,
    u.student_id,
    u.phone as student_phone,
    u.course as student_course,
    u.semester as student_semester
FROM applications a
INNER JOIN users u ON a.user_id = u.id
WHERE 1=1";

$params = [];
$types = '';

// Apply filters
if (!empty($statusFilter)) {
    $query .= " AND a.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if (!empty($categoryFilter)) {
    $query .= " AND a.category = ?";
    $params[] = $categoryFilter;
    $types .= 's';
}

if (!empty($searchQuery)) {
    $query .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.student_id LIKE ? OR a.id LIKE ?)";
    $searchParam = "%{$searchQuery}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'ssss';
}

$query .= " ORDER BY a.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$applications = [];
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}
$stmt->close();

// Get documents for all applications
$documentsByApp = [];
if (!empty($applications)) {
    $applicationIds = array_column($applications, 'id');
    if (!empty($applicationIds)) {
        $placeholders = str_repeat('?,', count($applicationIds) - 1) . '?';
        $stmt = $conn->prepare("SELECT * FROM application_documents WHERE application_id IN ($placeholders) ORDER BY uploaded_at ASC");
        $stmt->bind_param(str_repeat('i', count($applicationIds)), ...$applicationIds);
        $stmt->execute();
        $docResult = $stmt->get_result();
        while ($doc = $docResult->fetch_assoc()) {
            if (!isset($documentsByApp[$doc['application_id']])) {
                $documentsByApp[$doc['application_id']] = [];
            }
            $documentsByApp[$doc['application_id']][] = $doc;
        }
        $stmt->close();
    }
}

// Get unique categories for filter
$stmt = $conn->prepare("SELECT DISTINCT category FROM applications ORDER BY category");
$stmt->execute();
$categoryResult = $stmt->get_result();
$categories = [];
while ($row = $categoryResult->fetch_assoc()) {
    $categories[] = $row['category'];
}
$stmt->close();

$conn->close();

// Helper function to format file size
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

// Helper function to get status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'approved':
            return 'status-approved';
        case 'rejected':
            return 'status-rejected';
        case 'under_review':
            return 'status-review';
        default:
            return 'status-pending';
    }
}

// Helper function to get status label
function getStatusLabel($status) {
    switch ($status) {
        case 'approved':
            return 'Approved';
        case 'rejected':
            return 'Rejected';
        case 'under_review':
            return 'Under Review';
        default:
            return 'Pending';
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
    <title>View Applications - Admin - RCMP UniFa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/styles.css" />
    <style>
        .applications-container {
            min-height: 100vh;
            background: var(--light);
            padding: 24px 0;
        }
        .applications-header {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            padding: 32px 0;
            margin-bottom: 32px;
        }
        .applications-header h1 {
            margin: 0 0 8px;
            font-size: 2rem;
            font-weight: 700;
        }
        .applications-header p {
            margin: 0;
            opacity: 0.9;
        }
        .applications-content {
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
        .application-card {
            background: var(--card);
            padding: 32px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }
        .application-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .application-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--light);
        }
        .application-id {
            font-size: 0.875rem;
            color: var(--muted);
            margin-bottom: 4px;
        }
        .application-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            margin: 0;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .status-review {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }
        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }
        .application-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        .info-section {
            background: var(--light);
            padding: 20px;
            border-radius: var(--radius-sm);
        }
        .info-section h4 {
            margin: 0 0 16px;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.875rem;
        }
        .info-item {
            margin-bottom: 12px;
        }
        .info-item:last-child {
            margin-bottom: 0;
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
        .documents-section {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 2px solid var(--light);
        }
        .documents-section h4 {
            margin: 0 0 16px;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text);
        }
        .documents-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 16px;
        }
        .document-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            padding: 20px;
            background: var(--light);
            border-radius: var(--radius-sm);
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }
        .document-item:hover {
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .document-icon {
            width: 48px;
            height: 48px;
            min-width: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            color: #fff;
            border-radius: 10px;
            font-size: 1.5rem;
        }
        .document-info {
            flex: 1;
            min-width: 0;
        }
        .document-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
            word-break: break-word;
            line-height: 1.4;
        }
        .document-meta {
            font-size: 0.8rem;
            color: var(--muted);
            line-height: 1.4;
            word-break: break-word;
        }
        .document-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 100px;
        }
        .document-actions a {
            padding: 10px 16px;
            font-size: 0.875rem;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
            text-align: center;
            font-weight: 500;
            white-space: nowrap;
        }
        .btn-view {
            background: var(--primary);
            color: #fff;
        }
        .btn-view:hover {
            background: #0a3d62;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .btn-download {
            background: var(--card);
            color: var(--text);
            border: 1px solid #e5e7eb;
        }
        .btn-download:hover {
            background: #f3f4f6;
            border-color: var(--primary);
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
        @media (max-width: 768px) {
            .application-header {
                flex-direction: column;
                gap: 16px;
            }
            .application-grid {
                grid-template-columns: 1fr;
            }
            .filters-grid {
                grid-template-columns: 1fr;
            }
            .documents-list {
                grid-template-columns: 1fr;
            }
            .document-item {
                flex-direction: column;
                align-items: stretch;
            }
            .document-actions {
                flex-direction: row;
                width: 100%;
            }
            .document-actions a {
                flex: 1;
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
                <a href="EditProfile.php">Profile</a>
                <form method="post" action="../logout.php" style="display: inline;">
                    <button type="submit" class="logout-btn" name="logout">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Applications Header -->
    <div class="applications-header">
        <div class="applications-content">
            <h1>Student Applications</h1>
            <p>View and manage all financial aid applications</p>
        </div>
    </div>

    <!-- Applications Content -->
    <div class="applications-container">
        <div class="applications-content">
            <!-- Stats Bar -->
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="label">Total Applications</div>
                    <div class="value"><?php echo count($applications); ?></div>
                </div>
                <div class="stat-item">
                    <div class="label">Pending</div>
                    <div class="value"><?php echo count(array_filter($applications, fn($a) => $a['status'] === 'pending')); ?></div>
                </div>
                <div class="stat-item">
                    <div class="label">Under Review</div>
                    <div class="value"><?php echo count(array_filter($applications, fn($a) => $a['status'] === 'under_review')); ?></div>
                </div>
                <div class="stat-item">
                    <div class="label">Approved</div>
                    <div class="value"><?php echo count(array_filter($applications, fn($a) => $a['status'] === 'approved')); ?></div>
                </div>
                <div class="stat-item">
                    <div class="label">Rejected</div>
                    <div class="value"><?php echo count(array_filter($applications, fn($a) => $a['status'] === 'rejected')); ?></div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="filters-section">
                <h3>Filters</h3>
                <form method="get" action="" id="filterForm">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="under_review" <?php echo $statusFilter === 'under_review' ? 'selected' : ''; ?>>Under Review</option>
                                <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="category">Category</label>
                            <select id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $categoryFilter === $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="search">Search</label>
                            <input type="text" id="search" name="search" placeholder="Student name, email, ID, or App ID" value="<?php echo htmlspecialchars($searchQuery); ?>" />
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                        <a href="ViewApplication.php" class="btn btn-outline">Clear</a>
                    </div>
                </form>
            </div>

            <!-- Applications List -->
            <?php if (empty($applications)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <h3>No Applications Found</h3>
                    <p>There are no applications matching your filters.</p>
                </div>
            <?php else: ?>
                <?php foreach ($applications as $app): 
                    $applicationData = !empty($app['application_data']) ? json_decode($app['application_data'], true) : [];
                    $documents = $documentsByApp[$app['id']] ?? [];
                ?>
                    <div class="application-card">
                        <div class="application-header">
                            <div>
                                <div class="application-id">Application #<?php echo $app['id']; ?></div>
                                <h2 class="application-title"><?php echo htmlspecialchars($app['category']); ?>
                                    <?php if (!empty($app['subcategory'])): ?>
                                        <span style="font-weight: 400; font-size: 1.25rem; color: var(--muted);"> - <?php echo htmlspecialchars($app['subcategory']); ?></span>
                                    <?php endif; ?>
                                </h2>
                            </div>
                            <span class="status-badge <?php echo getStatusBadgeClass($app['status']); ?>">
                                <?php echo getStatusLabel($app['status']); ?>
                            </span>
                        </div>

                        <div class="application-grid">
                            <!-- Student Information -->
                            <div class="info-section">
                                <h4>Student Information</h4>
                                <div class="info-item">
                                    <div class="info-label">Name</div>
                                    <div class="info-value"><?php echo htmlspecialchars($app['student_name']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Student ID</div>
                                    <div class="info-value"><?php echo htmlspecialchars($app['student_id']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Email</div>
                                    <div class="info-value"><?php echo htmlspecialchars($app['student_email']); ?></div>
                                </div>
                                <?php if (!empty($app['student_phone'])): ?>
                                    <div class="info-item">
                                        <div class="info-label">Phone</div>
                                        <div class="info-value"><?php echo htmlspecialchars($app['student_phone']); ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($app['student_course'])): ?>
                                    <div class="info-item">
                                        <div class="info-label">Course</div>
                                        <div class="info-value"><?php echo htmlspecialchars($app['student_course']); ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($app['student_semester'])): ?>
                                    <div class="info-item">
                                        <div class="info-label">Semester</div>
                                        <div class="info-value"><?php echo htmlspecialchars($app['student_semester']); ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Application Details -->
                            <div class="info-section">
                                <h4>Application Details</h4>
                                <div class="info-item">
                                    <div class="info-label">Amount Applied</div>
                                    <div class="info-value">RM <?php echo number_format($app['amount_applied'], 2); ?></div>
                                </div>
                                <?php if (!empty($app['amount_approved'])): ?>
                                    <div class="info-item">
                                        <div class="info-label">Amount Approved</div>
                                        <div class="info-value">RM <?php echo number_format($app['amount_approved'], 2); ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($app['bank_name'])): ?>
                                    <div class="info-item">
                                        <div class="info-label">Bank Name</div>
                                        <div class="info-value"><?php echo htmlspecialchars($app['bank_name']); ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($app['bank_account_number'])): ?>
                                    <div class="info-item">
                                        <div class="info-label">Bank Account</div>
                                        <div class="info-value"><?php echo htmlspecialchars($app['bank_account_number']); ?></div>
                                    </div>
                                <?php endif; ?>
                                <div class="info-item">
                                    <div class="info-label">Submitted</div>
                                    <div class="info-value"><?php echo date('d M Y, h:i A', strtotime($app['created_at'])); ?></div>
                                </div>
                                <?php if (!empty($app['reviewed_at'])): ?>
                                    <div class="info-item">
                                        <div class="info-label">Reviewed</div>
                                        <div class="info-value"><?php echo date('d M Y, h:i A', strtotime($app['reviewed_at'])); ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Admin Notes & Committee Remarks -->
                        <?php if (!empty($app['admin_notes']) || !empty($app['committee_remarks'])): ?>
                            <div style="margin-top: 24px; padding-top: 24px; border-top: 2px solid var(--light);">
                                <?php if (!empty($app['admin_notes'])): ?>
                                    <div style="margin-bottom: 16px;">
                                        <h4 style="margin: 0 0 8px; font-size: 0.875rem; font-weight: 600; color: var(--muted); text-transform: uppercase;">Admin Notes</h4>
                                        <p style="margin: 0; padding: 12px; background: var(--light); border-radius: var(--radius-sm); color: var(--text);">
                                            <?php echo nl2br(htmlspecialchars($app['admin_notes'])); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($app['committee_remarks'])): ?>
                                    <div>
                                        <h4 style="margin: 0 0 8px; font-size: 0.875rem; font-weight: 600; color: var(--muted); text-transform: uppercase;">Committee Remarks</h4>
                                        <p style="margin: 0; padding: 12px; background: var(--light); border-radius: var(--radius-sm); color: var(--text);">
                                            <?php echo nl2br(htmlspecialchars($app['committee_remarks'])); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Documents Section -->
                        <?php if (!empty($documents)): ?>
                            <div class="documents-section">
                                <h4>Submitted Documents</h4>
                                <div class="documents-list">
                                    <?php foreach ($documents as $doc): 
                                        $filePath = '../../' . $doc['file_path'];
                                        $viewUrl = 'view_document.php?id=' . $doc['id'];
                                        $downloadUrl = 'view_document.php?id=' . $doc['id'] . '&download=1';
                                    ?>
                                        <div class="document-item">
                                            <div class="document-icon">📄</div>
                                            <div class="document-info">
                                                <div class="document-name"><?php echo htmlspecialchars($doc['document_type']); ?></div>
                                                <div class="document-meta">
                                                    <?php echo htmlspecialchars($doc['file_name']); ?> • 
                                                    <?php echo formatFileSize($doc['file_size']); ?>
                                                </div>
                                            </div>
                                            <div class="document-actions">
                                                <a href="<?php echo $viewUrl; ?>" class="btn-view" target="_blank">View</a>
                                                <a href="<?php echo $downloadUrl; ?>" class="btn-download">Download</a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../component/footer.php'; renderFooter('../../'); ?>
    <script src="../../js/main.js"></script>
</body>
</html>


<?php
require_once '../../config.php';

// Require student role
requireRole('student');

// Get user data
$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];
$studentId = $_SESSION['student_id'];

// Get all applications for this user
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM applications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Applications - RCMP UniFa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/styles.css" />
    <style>
        .applications-container {
            min-height: 100vh;
            background: var(--light);
            padding: 32px 0;
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
            max-width: 1200px;
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
            cursor: pointer;
        }
        .logout-btn:hover {
            background: #fdd;
        }
        .application-card {
            background: var(--card);
            border: 1px solid #e5e7eb;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 24px;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .application-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .application-header {
            padding: 24px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 16px;
        }
        .application-header-left {
            flex: 1;
        }
        .application-id {
            font-size: 0.875rem;
            color: var(--muted);
            margin-bottom: 8px;
        }
        .application-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
            margin: 0 0 4px;
        }
        .application-subtitle {
            font-size: 0.95rem;
            color: var(--muted);
            margin: 0;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
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
        .application-body {
            padding: 24px;
        }
        .application-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }
        .detail-item {
            padding: 16px;
            background: var(--light);
            border-radius: var(--radius-sm);
        }
        .detail-label {
            font-size: 0.875rem;
            color: var(--muted);
            margin-bottom: 6px;
            font-weight: 600;
        }
        .detail-value {
            font-size: 1rem;
            color: var(--text);
            font-weight: 500;
        }
        .application-data-section {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #f1f5f9;
        }
        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 16px;
        }
        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }
        .data-item {
            padding: 12px;
            background: var(--light);
            border-radius: 8px;
        }
        .data-label {
            font-size: 0.8rem;
            color: var(--muted);
            margin-bottom: 4px;
        }
        .data-value {
            font-size: 0.95rem;
            color: var(--text);
        }
        .documents-section {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #f1f5f9;
        }
        .documents-list {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .document-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: var(--light);
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .document-item:hover {
            background: #f8fafc;
            border-color: var(--primary);
        }
        .document-icon {
            font-size: 1.5rem;
        }
        .document-info {
            flex: 1;
        }
        .document-name {
            font-weight: 600;
            color: var(--text);
            font-size: 0.9rem;
        }
        .document-meta {
            font-size: 0.8rem;
            color: var(--muted);
        }
        .document-download {
            padding: 6px 14px;
            background: var(--primary);
            color: #fff;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .document-download:hover {
            background: var(--primary-600);
            transform: translateY(-1px);
        }
        .document-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .notes-section {
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #f1f5f9;
        }
        .notes-box {
            padding: 16px;
            background: var(--light);
            border-radius: 8px;
            border-left: 4px solid var(--primary);
        }
        .notes-label {
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
        }
        .notes-content {
            color: var(--text);
            line-height: 1.6;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--card);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 16px;
            opacity: 0.3;
        }
        .empty-state h2 {
            margin: 0 0 8px;
            color: var(--text);
        }
        .empty-state p {
            color: var(--muted);
            margin: 0 0 24px;
        }
        .collapsible {
            cursor: pointer;
            user-select: none;
        }
        .collapsible-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        .collapsible-content.open {
            max-height: 2000px;
        }
        .toggle-icon {
            display: inline-block;
            transition: transform 0.3s ease;
        }
        .toggle-icon.open {
            transform: rotate(180deg);
        }
    </style>
</head>
<body>
    <?php 
    include '../component/MessageDialog.php';
    renderMessageDialogScript();
    ?>
    <!-- Navigation Bar -->
    <div class="nav-bar">
        <div class="nav-bar-content">
            <a href="../../index.php" class="brand">
                <img src="../../public/unikl-rcmp.png" alt="UniKL RCMP logo" class="logo" style="height: 40px;" />
            </a>
            <div class="nav-links">
                <a href="StudentDashboard.php">Dashboard</a>
                <a href="ApplicationForm.php">New Application</a>
                <a href="ViewApplication.php">My Applications</a>
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
            <h1>My Applications</h1>
            <p>View and track all your financial aid applications</p>
        </div>
    </div>

    <!-- Applications Content -->
    <div class="applications-container">
        <div class="applications-content">
            <?php if (empty($applications)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <h2>No Applications Yet</h2>
                    <p>You haven't submitted any applications yet. Start by creating a new application.</p>
                    <a href="ApplicationForm.php" class="btn btn-primary">Create New Application</a>
                </div>
            <?php else: ?>
                <?php foreach ($applications as $app): 
                    $applicationData = !empty($app['application_data']) ? json_decode($app['application_data'], true) : [];
                    $documents = $documentsByApp[$app['id']] ?? [];
                ?>
                    <div class="application-card">
                        <div class="application-header">
                            <div class="application-header-left">
                                <div class="application-id">Application #<?php echo $app['id']; ?></div>
                                <h3 class="application-title"><?php echo htmlspecialchars($app['category']); ?></h3>
                                <?php if (!empty($app['subcategory'])): ?>
                                    <p class="application-subtitle"><?php echo htmlspecialchars($app['subcategory']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="status-badge <?php echo getStatusBadgeClass($app['status']); ?>">
                                    <?php echo getStatusLabel($app['status']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="application-body">
                            <!-- Basic Details -->
                            <div class="application-details">
                                <div class="detail-item">
                                    <div class="detail-label">Amount Applied</div>
                                    <div class="detail-value">RM <?php echo number_format($app['amount_applied'], 2); ?></div>
                                </div>
                                <?php if (!empty($app['amount_approved'])): ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Amount Approved</div>
                                        <div class="detail-value">RM <?php echo number_format($app['amount_approved'], 2); ?></div>
                                    </div>
                                <?php endif; ?>
                                <div class="detail-item">
                                    <div class="detail-label">Submitted Date</div>
                                    <div class="detail-value"><?php echo date('d M Y, h:i A', strtotime($app['created_at'])); ?></div>
                                </div>
                                <?php if (!empty($app['reviewed_at'])): ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Reviewed Date</div>
                                        <div class="detail-value"><?php echo date('d M Y, h:i A', strtotime($app['reviewed_at'])); ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($app['bank_name'])): ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Bank Name</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($app['bank_name']); ?></div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($app['bank_account_number'])): ?>
                                    <div class="detail-item">
                                        <div class="detail-label">Bank Account Number</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($app['bank_account_number']); ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Application Data (Additional Fields) -->
                            <?php if (!empty($applicationData)): ?>
                                <div class="application-data-section">
                                    <h4 class="section-title">Additional Information</h4>
                                    <div class="data-grid">
                                        <?php foreach ($applicationData as $key => $value): 
                                            if (empty($value) || $key === 'description') continue;
                                            $label = ucwords(str_replace('_', ' ', $key));
                                        ?>
                                            <div class="data-item">
                                                <div class="data-label"><?php echo htmlspecialchars($label); ?></div>
                                                <div class="data-value"><?php echo htmlspecialchars($value); ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                        <?php if (!empty($applicationData['description'])): ?>
                                            <div class="data-item" style="grid-column: 1 / -1;">
                                                <div class="data-label">Description</div>
                                                <div class="data-value"><?php echo nl2br(htmlspecialchars($applicationData['description'])); ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Documents -->
                            <?php if (!empty($documents)): ?>
                                <div class="documents-section">
                                    <h4 class="section-title">Submitted Documents (<?php echo count($documents); ?>)</h4>
                                    <div class="documents-list">
                                        <?php foreach ($documents as $doc): 
                                            $fileIcon = '📄';
                                            if (strpos($doc['mime_type'] ?? '', 'image') !== false) {
                                                $fileIcon = '🖼️';
                                            } elseif (strpos($doc['mime_type'] ?? '', 'pdf') !== false) {
                                                $fileIcon = '📕';
                                            }
                                        ?>
                                            <div class="document-item">
                                                <span class="document-icon"><?php echo $fileIcon; ?></span>
                                                <div class="document-info">
                                                    <div class="document-name"><?php echo htmlspecialchars($doc['file_name']); ?></div>
                                                    <div class="document-meta">
                                                        <?php echo htmlspecialchars($doc['document_type']); ?> • 
                                                        <?php echo formatFileSize($doc['file_size'] ?? 0); ?> • 
                                                        <?php echo date('d M Y', strtotime($doc['uploaded_at'])); ?>
                                                    </div>
                                                </div>
                                                <div class="document-actions">
                                                    <a href="view_document.php?id=<?php echo $doc['id']; ?>" target="_blank" class="document-download">View</a>
                                                    <a href="view_document.php?id=<?php echo $doc['id']; ?>&download=download" class="document-download" style="background: var(--muted);">Download</a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Admin Notes -->
                            <?php if (!empty($app['admin_notes'])): ?>
                                <div class="notes-section">
                                    <div class="notes-box">
                                        <div class="notes-label">Admin Notes</div>
                                        <div class="notes-content"><?php echo nl2br(htmlspecialchars($app['admin_notes'])); ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Committee Remarks -->
                            <?php if (!empty($app['committee_remarks'])): ?>
                                <div class="notes-section">
                                    <div class="notes-box">
                                        <div class="notes-label">Committee Remarks</div>
                                        <div class="notes-content"><?php echo nl2br(htmlspecialchars($app['committee_remarks'])); ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../component/footer.php'; renderFooter('../../'); ?>
    <script src="../../js/main.js"></script>
</body>
</html>


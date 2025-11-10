<?php
/**
 * Secure Document Viewer
 * This file ensures only the application owner can view their documents
 */

require_once '../../config.php';

// Require student role
requireRole('student');

$userId = $_SESSION['user_id'];
$documentId = $_GET['id'] ?? 0;

if (empty($documentId)) {
    die('Document ID is required');
}

$conn = getDBConnection();

// Get document and verify ownership
$stmt = $conn->prepare("
    SELECT ad.*, a.user_id 
    FROM application_documents ad
    INNER JOIN applications a ON ad.application_id = a.id
    WHERE ad.id = ? AND a.user_id = ?
");
$stmt->bind_param("ii", $documentId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    die('Document not found or access denied');
}

$document = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Get file path
$filePath = dirname(dirname(__DIR__)) . '/' . $document['file_path'];

// Check if file exists
if (!file_exists($filePath)) {
    die('File not found');
}

// Set headers for file download/view
$fileName = $document['file_name'];
$mimeType = $document['mime_type'] ?? 'application/octet-stream';
$fileSize = filesize($filePath);

// Determine if we should display inline or force download
$disposition = $_GET['download'] ?? 'inline';
if ($disposition === 'download') {
    header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
} else {
    header('Content-Disposition: inline; filename="' . basename($fileName) . '"');
}

header('Content-Type: ' . $mimeType);
header('Content-Length: ' . $fileSize);
header('Cache-Control: private, max-age=3600');
header('Pragma: private');

// Output file
readfile($filePath);
exit();


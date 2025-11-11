<?php
/**
 * Admin Document Viewer
 * Allows admins to view any student application documents
 */

require_once '../../config.php';

// Require admin role
requireRole('admin');

$documentId = $_GET['id'] ?? 0;

if (empty($documentId)) {
    die('Document ID is required');
}

$conn = getDBConnection();

// Get document (admins can view any document)
$stmt = $conn->prepare("
    SELECT ad.*
    FROM application_documents ad
    WHERE ad.id = ?
");
$stmt->bind_param("i", $documentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    die('Document not found');
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
if ($disposition === 'download' || $disposition === '1') {
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


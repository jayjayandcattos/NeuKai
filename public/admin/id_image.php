<?php
/**
 * Authenticated proxy for password-reset ID images.
 * Files live in storage/ (outside the web root) and are never served directly.
 */

require_once __DIR__ . '/../../app/bootstrap.php';
require_admin();

$ticketId = (int) ($_GET['ticket_id'] ?? 0);

if ($ticketId <= 0) {
    http_response_code(400);
    exit('Invalid request.');
}

$stmt = $conn->prepare('SELECT id_image_path FROM password_reset_tickets WHERE ticket_id = ?');
$stmt->bind_param('i', $ticketId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row || empty($row['id_image_path'])) {
    http_response_code(404);
    exit('Image not found.');
}

$filename = basename((string) $row['id_image_path']);
$path = STORAGE_PATH . '/id_verifications/' . $filename;

if (!is_file($path)) {
    http_response_code(404);
    exit('Image not found.');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($path) ?: '';

if (!isset(allowed_image_types()[$mime])) {
    http_response_code(415);
    exit('Unsupported file type.');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string) filesize($path));
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Cache-Control: private, no-store');

readfile($path);

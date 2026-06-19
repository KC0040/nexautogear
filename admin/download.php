<?php
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$type = $_GET['type'] ?? '';
$id   = (int)($_GET['id'] ?? 0);
if (!$id) exit('Invalid request.');

$uploadBase = __DIR__ . '/../uploads/';

if ($type === 'quote') {
    $row = db()->prepare('SELECT quote_path, order_number FROM b2b_orders WHERE id=?');
    $row->execute([$id]);
    $row = $row->fetch();
    $path = $row['quote_path'] ?? null;
    $name = 'Quote_' . ($row['order_number'] ?? $id) . '.pdf';
} elseif ($type === 'order_doc') {
    $row = db()->prepare('SELECT document_path, order_number FROM b2b_orders WHERE id=?');
    $row->execute([$id]);
    $row = $row->fetch();
    $path = $row['document_path'] ?? null;
    $name = 'PO_' . ($row['order_number'] ?? $id) . '.' . pathinfo($path ?? '', PATHINFO_EXTENSION);
} elseif ($type === 'app_doc') {
    $row = db()->prepare('SELECT document_path, company FROM b2b_applications WHERE id=?');
    $row->execute([$id]);
    $row = $row->fetch();
    $path = $row['document_path'] ?? null;
    $name = 'Doc_' . preg_replace('/[^a-z0-9]/i','_', $row['company'] ?? $id) . '.' . pathinfo($path ?? '', PATHINFO_EXTENSION);
} else {
    exit('Invalid type.');
}

if (!$path) exit('No file attached.');
$full = realpath($uploadBase . $path);

// Security: ensure path is inside uploads dir
if (!$full || strpos($full, realpath($uploadBase)) !== 0 || !is_file($full)) {
    http_response_code(404); exit('File not found.');
}

$mime = mime_content_type($full);
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $name . '"');
header('Content-Length: ' . filesize($full));
readfile($full);

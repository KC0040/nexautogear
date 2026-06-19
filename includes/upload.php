<?php
// 安全上傳 PDF/圖片，回傳儲存路徑或拋出例外
function upload_document(string $field, string $subfolder = 'general'): ?string {
    if (empty($_FILES[$field]['name'])) return null;

    $file    = $_FILES[$field];
    $maxSize = 8 * 1024 * 1024; // 8MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload error code: ' . $file['error']);
    }
    if ($file['size'] > $maxSize) {
        throw new RuntimeException('File too large (max 8MB).');
    }

    $mime = mime_content_type($file['tmp_name']);
    $allowed = ['application/pdf','image/jpeg','image/png','image/webp'];
    if (!in_array($mime, $allowed)) {
        throw new RuntimeException('Only PDF, JPG, PNG files are allowed.');
    }

    $ext    = ($mime === 'application/pdf') ? 'pdf' : pathinfo($file['name'], PATHINFO_EXTENSION);
    $name   = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
    $dir    = __DIR__ . '/../uploads/' . $subfolder . '/';

    if (!is_dir($dir)) mkdir($dir, 0755, true);

    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
        throw new RuntimeException('Failed to save file.');
    }

    return $subfolder . '/' . $name;
}

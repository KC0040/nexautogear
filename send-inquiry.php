<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://www.nexautogear.com');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$name    = trim(strip_tags($_POST['name'] ?? ''));
$company = trim(strip_tags($_POST['company'] ?? ''));
$email   = trim($_POST['email'] ?? '');
$product = trim(strip_tags($_POST['product'] ?? ''));
$qty     = trim(strip_tags($_POST['qty'] ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));

// 基本驗證
if (!$name || !$email || !$product) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

$to      = 'Sales@nexautogear.com';
$subject = "B2B Inquiry: $product — $company";
$body    = "New B2B Inquiry from NEXAutogear\n";
$body   .= str_repeat('=', 40) . "\n\n";
$body   .= "Name:    $name\n";
$body   .= "Company: $company\n";
$body   .= "Email:   $email\n";
$body   .= "Product: $product\n";
$body   .= "Qty:     $qty\n\n";
$body   .= "Message:\n$message\n";

$headers  = "From: noreply@nexautogear.com\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: NEXAutogear-Inquiry/1.0\r\n";

if (mail($to, $subject, $body, $headers)) {
    echo json_encode(['success' => true, 'message' => 'Inquiry sent successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to send email. Please contact Sales@nexautogear.com directly.']);
}

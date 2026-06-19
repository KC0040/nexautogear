<?php
require_once __DIR__ . '/db.php';

session_set_cookie_params(['httponly' => true, 'secure' => true, 'samesite' => 'Strict']);

function session_start_safe(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
}

function member_login(string $email, string $password): bool {
    session_start_safe();
    $stmt = db()->prepare('SELECT * FROM b2b_members WHERE email = ? AND status = "active"');
    $stmt->execute([$email]);
    $member = $stmt->fetch();
    if ($member && password_verify($password, $member['password_hash'])) {
        $_SESSION['member_id']   = $member['id'];
        $_SESSION['member_name'] = $member['contact_name'];
        $_SESSION['company']     = $member['company'];
        $_SESSION['tier']        = $member['account_tier'];
        db()->prepare('UPDATE b2b_members SET last_login = NOW() WHERE id = ?')->execute([$member['id']]);
        return true;
    }
    return false;
}

function admin_login(string $username, string $password): bool {
    session_start_safe();
    $stmt = db()->prepare('SELECT * FROM admin_users WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['username'];
        return true;
    }
    return false;
}

function require_member(): void {
    session_start_safe();
    if (empty($_SESSION['member_id'])) {
        header('Location: /members/login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function require_admin(): void {
    session_start_safe();
    if (empty($_SESSION['admin_id'])) {
        header('Location: /admin/login.php');
        exit;
    }
}

function logout(): void {
    session_start_safe();
    session_destroy();
    header('Location: /');
    exit;
}

<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/helpers.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']);
}

function current_user(): ?array
{
    return is_logged_in() ? $_SESSION['user'] : null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('/business_store/login.php');
    }
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

function login_user(string $email, string $password): array
{
    $pdo = db();

    $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash, role, status FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        return [false, "Invalid email or password."];
    }

    if ($user['status'] !== 'active') {
        return [false, "Your account is disabled. Contact admin."];
    }

    if (!password_verify($password, $user['password_hash'])) {
        return [false, "Invalid email or password."];
    }

    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role'],
    ];

    return [true, "OK"];
}

function role_home_path(array $user): string
{
    if ($user['role'] === 'retailer') {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT approval_status
            FROM retailers
            WHERE user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$user['id']]);
        $ret = $stmt->fetch();

        if (!$ret) {
            return '/business_store/retailer/pending.php';
        }
        if ($ret['approval_status'] !== 'approved') {
            return '/business_store/retailer/pending.php';
        }
        return '/business_store/retailer/index.php';
    }

    if ($user['role'] === 'admin') return '/business_store/admin/index.php';
    if ($user['role'] === 'staff') return '/business_store/staff/index.php';
    return '/business_store/customer/index.php';
}

function require_approved_retailer(): void
{
    require_login();
    $u = current_user();

    if (!$u || $u['role'] !== 'retailer') {
        redirect('/business_store/dashboard.php');
    }

    $pdo = db();
    $stmt = $pdo->prepare("SELECT approval_status FROM retailers WHERE user_id = ? LIMIT 1");
    $stmt->execute([$u['id']]);
    $ret = $stmt->fetch();

    if (!$ret || $ret['approval_status'] !== 'approved') {
        redirect('/business_store/retailer/pending.php');
    }
}


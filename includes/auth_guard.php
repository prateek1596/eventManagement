<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function start_eventspace_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string
{
    start_eventspace_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token = null): void
{
    start_eventspace_session();
    $submitted = $token ?? ($_POST['csrf_token'] ?? '');
    if (!is_string($submitted) || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $submitted)) {
        http_response_code(419);
        exit('Invalid security token. Refresh the page and try again.');
    }
}

function current_user(): ?array
{
    start_eventspace_session();
    if (empty($_SESSION['username'])) {
        return null;
    }
    return [
        'username' => (string) $_SESSION['username'],
        'role' => (string) ($_SESSION['role'] ?? 'Staff'),
    ];
}

function require_auth(string $loginPath = 'login.php'): void
{
    if (!current_user()) {
        redirect_to($loginPath);
    }
}

function require_role(array $roles, string $redirectPath = 'dashboard.php'): void
{
    $user = current_user();
    $allowed = array_map('strtolower', $roles);
    if (!$user || !in_array(strtolower($user['role']), $allowed, true)) {
        flash('danger', 'You do not have permission to access that page.');
        redirect_to($redirectPath);
    }
}

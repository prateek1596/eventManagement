<?php
function requireLogin(string $loginPath = 'login.php'): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if (!isset($_SESSION['username'])) {
        header('Location: ' . $loginPath);
        exit();
    }
}

function currentRole(): string
{
    return strtolower((string) ($_SESSION['role'] ?? 'staff'));
}

function requireRole(array $roles, string $redirectPath = 'dashboard.php'): void
{
    if (!in_array(currentRole(), array_map('strtolower', $roles), true)) {
        header('Location: ' . $redirectPath . '?error=forbidden');
        exit();
    }
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid security token. Refresh the page and try again.');
    }
}

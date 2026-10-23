<?php
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/flash.php';

function requireLogin(string $loginPath = 'login.php'): void
{
    start_eventspace_session();
    if (!current_user()) {
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
    return csrf_token();
}

function verifyCsrf(): void
{
    verify_csrf_token();
}

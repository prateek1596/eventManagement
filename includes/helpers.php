<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function eventspace_url(string $path = ''): string
{
    return EVENTSPACE_BASE_URL . '/' . ltrim($path, '/');
}

function redirect_to(string $path): void
{
    header('Location: ' . eventspace_url($path));
    exit;
}

function request_is_post(): bool
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function old(string $key, string $default = ''): string
{
    return e($_POST[$key] ?? $default);
}

function is_active_path(string $path): bool
{
    $current = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '');
    return $current === basename($path);
}

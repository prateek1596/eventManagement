<?php
declare(strict_types=1);

function flash(string $type, string $message): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION['flash'][$type][] = $message;
}

function consume_flash(): array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return is_array($messages) ? $messages : [];
}

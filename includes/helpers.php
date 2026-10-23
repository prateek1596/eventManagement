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

function store_event_image(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > EVENTSPACE_MAX_UPLOAD_BYTES) {
        throw new RuntimeException('The event photo must be a valid image no larger than 5 MB.');
    }

    $imageInfo = @getimagesize((string) $file['tmp_name']);
    $mime = $imageInfo['mime'] ?? '';
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if ($imageInfo === false || !isset($extensions[$mime])) {
        throw new RuntimeException('Event photos must be JPG, PNG, or WebP images.');
    }

    if (!is_dir(EVENTSPACE_UPLOAD_DIR) && !mkdir(EVENTSPACE_UPLOAD_DIR, 0755, true) && !is_dir(EVENTSPACE_UPLOAD_DIR)) {
        throw new RuntimeException('The event photo directory is unavailable.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file((string) $file['tmp_name'], EVENTSPACE_UPLOAD_DIR . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('The event photo could not be saved.');
    }
    return 'assets/img/uploads/' . $filename;
}

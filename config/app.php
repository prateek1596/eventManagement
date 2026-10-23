<?php
declare(strict_types=1);

if (!function_exists('eventspace_load_env')) {
    function eventspace_load_env(string $path): void
    {
        if (!is_readable($path)) {
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            [$name, $value] = array_pad(explode('=', $line, 2), 2, '');
            $name = trim($name);
            $value = trim($value);
            if ($name !== '' && getenv($name) === false) {
                putenv($name . '=' . trim($value, "\"'"));
            }
        }
    }
}

eventspace_load_env(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');

if (!defined('EVENTSPACE_ROOT')) {
    define('EVENTSPACE_ROOT', dirname(__DIR__));
}
if (!defined('EVENTSPACE_BASE_URL')) {
    define('EVENTSPACE_BASE_URL', rtrim((string) (getenv('APP_BASE_URL') ?: '/eventManagement'), '/'));
}
if (!defined('EVENTSPACE_ENV')) {
    define('EVENTSPACE_ENV', (string) (getenv('APP_ENV') ?: 'local'));
}
if (!defined('EVENTSPACE_DEBUG')) {
    define('EVENTSPACE_DEBUG', filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN));
}
if (!defined('EVENTSPACE_UPLOAD_DIR')) {
    define('EVENTSPACE_UPLOAD_DIR', EVENTSPACE_ROOT . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'uploads');
}
if (!defined('EVENTSPACE_MAX_UPLOAD_BYTES')) {
    define('EVENTSPACE_MAX_UPLOAD_BYTES', 5 * 1024 * 1024);
}
if (!defined('EVENTSPACE_AI_API_KEY')) {
    define('EVENTSPACE_AI_API_KEY', (string) (getenv('AI_API_KEY') ?: ''));
}
if (!defined('EVENTSPACE_AI_API_URL')) {
    define('EVENTSPACE_AI_API_URL', (string) (getenv('AI_API_URL') ?: ''));
}

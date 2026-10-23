<?php
declare(strict_types=1);

require_once __DIR__ . '/app.php';

function eventspace_database(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = (string) (getenv('DB_HOST') ?: '127.0.0.1');
    $port = (int) (getenv('DB_PORT') ?: 3307);
    $database = (string) (getenv('DB_NAME') ?: 'pateek');
    $username = (string) (getenv('DB_USER') ?: 'root');
    $password = (string) (getenv('DB_PASSWORD') ?: '');
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $database);

    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $exception) {
        error_log('EventSpace database connection failed: ' . $exception->getMessage());
        throw new RuntimeException('The database is temporarily unavailable.');
    }

    return $pdo;
}

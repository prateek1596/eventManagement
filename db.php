<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

$host = (string) (getenv('DB_HOST') ?: '127.0.0.1');
$user = (string) (getenv('DB_USER') ?: 'root');
$password = (string) (getenv('DB_PASSWORD') ?: '');
$database = (string) (getenv('DB_NAME') ?: 'pateek');
$port = (int) (getenv('DB_PORT') ?: 3306);

mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($host, $user, $password, $database, $port);
if ($conn->connect_errno) {
    error_log('EventSpace mysqli connection failed: ' . $conn->connect_error);
    http_response_code(503);
    exit('The database is temporarily unavailable.');
}
$conn->set_charset('utf8mb4');

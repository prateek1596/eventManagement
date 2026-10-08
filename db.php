<?php
$connectionError = 'Database connection is unavailable. Check the MySQL service and db.php settings.';
$host = "localhost";
$user = "root";
$password = "Prateek@1596";
$database = getenv('EVENT_DB_NAME') ?: "pateek";

$conn = @new mysqli($host, $user, $password, $database, 3306);
if ($conn->connect_error) {
    die($connectionError . ' Current database: ' . htmlspecialchars($database, ENT_QUOTES, 'UTF-8'));
}
$conn->set_charset('utf8mb4');
?>

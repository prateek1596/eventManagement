<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
start_eventspace_session();
require_auth('../login.php');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="eventspace-organisers.csv"');
$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Address', 'Rating', 'Event ID']);
$result = $conn->query('SELECT OrganiserID, Name, Address, Rating, EventID FROM Organiser ORDER BY Name ASC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [$row['OrganiserID'], $row['Name'], $row['Address'], $row['Rating'], $row['EventID']]);
    }
}
fclose($output);

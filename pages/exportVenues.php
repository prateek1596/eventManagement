<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
start_eventspace_session();
require_auth('../login.php');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="eventspace-venues.csv"');
$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Location', 'Cost', 'Facilities', 'Availability']);
$result = $conn->query('SELECT VenueID, Name, Location, Cost, Facilities, Availability FROM Venue ORDER BY Name ASC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [$row['VenueID'], $row['Name'], $row['Location'], $row['Cost'], $row['Facilities'], !empty($row['Availability']) ? 'Available' : 'Unavailable']);
    }
}
fclose($output);

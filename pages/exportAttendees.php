<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
start_eventspace_session();
require_auth('../login.php');

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="eventspace-attendees.csv"');
$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Name', 'Email', 'Phone', 'RSVP']);
$result = $conn->query('SELECT AttendeeID, Name, Email, Phone_No, RSVP FROM Attendee ORDER BY Name ASC');
if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [$row['AttendeeID'], $row['Name'], $row['Email'], $row['Phone_No'], !empty($row['RSVP']) ? 'Confirmed' : 'Pending']);
    }
}
fclose($output);

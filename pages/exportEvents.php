<?php
include '../db.php';
include '../auth.php';
session_start();
requireLogin('../login.php');

$search = trim($_GET['search'] ?? '');
$where = '';
if ($search !== '') {
    $safeSearch = $conn->real_escape_string($search);
    $where = " WHERE (`Name` LIKE '%$safeSearch%' OR `Venue` LIKE '%$safeSearch%' OR `Contact_info` LIKE '%$safeSearch%')";
}

$result = $conn->query("SELECT EventID, Name, Date_OF_Booking, Time, Venue, Contact_info, Status, Budget FROM Event$where ORDER BY Date_OF_Booking ASC, Time ASC");
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=eventspace-events.csv');
$output = fopen('php://output', 'w');
fputcsv($output, ['Event ID', 'Name', 'Booking Date', 'Time', 'Venue', 'Contact', 'Status', 'Budget']);
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
exit();

<?php
include '../db.php';
include '../auth.php';
session_start();
requireLogin('../login.php');

$month = $_GET['month'] ?? date('Y-m');
$monthDate = DateTime::createFromFormat('!Y-m', $month);
if (!$monthDate || $monthDate->format('Y-m') !== $month) {
    $monthDate = new DateTime('first day of this month');
}
$startDate = $monthDate->format('Y-m-01');
$endDate = (clone $monthDate)->modify('first day of next month')->format('Y-m-01');
$stmt = $conn->prepare('SELECT EventID, Name, Date_OF_Booking, Time, Venue, Contact_info, Budget FROM Event WHERE Date_OF_Booking >= ? AND Date_OF_Booking < ? ORDER BY Date_OF_Booking, Time');
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

function calendarText(string $value): string
{
    return str_replace(["\\", ";", ",", "\r", "\n"], ["\\\\", "\\;", "\\,", '', '\\n'], $value);
}

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=eventspace-' . $monthDate->format('Y-m') . '.ics');
echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//EventSpace//Events//EN\r\n";
while ($event = $result->fetch_assoc()) {
    $start = new DateTime($event['Date_OF_Booking'] . ' ' . ($event['Time'] ?: '09:00:00'));
    $end = (clone $start)->modify('+2 hours');
    echo "BEGIN:VEVENT\r\n";
    echo 'UID:event-' . (int) $event['EventID'] . '@eventspace' . "\r\n";
    echo 'DTSTAMP:' . gmdate('Ymd\THis\Z') . "\r\n";
    echo 'DTSTART:' . $start->format('Ymd\THis') . "\r\n";
    echo 'DTEND:' . $end->format('Ymd\THis') . "\r\n";
    echo 'SUMMARY:' . calendarText($event['Name']) . "\r\n";
    echo 'LOCATION:' . calendarText($event['Venue']) . "\r\n";
    echo 'DESCRIPTION:Contact ' . calendarText($event['Contact_info']) . ' | Budget ' . calendarText((string) $event['Budget']) . "\r\n";
    echo "END:VEVENT\r\n";
}
echo "END:VCALENDAR\r\n";

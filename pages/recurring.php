<?php
include '../db.php';
include '../auth.php';
session_start();
requireLogin('../login.php');
requireRole(['admin', 'organizer'], 'events.php');

$eventID = filter_input(INPUT_GET, 'event', FILTER_VALIDATE_INT);
if (!$eventID) {
    header('Location: events.php');
    exit();
}
$eventStmt = $conn->prepare('SELECT EventID, Name, Date_OF_Booking, Time, Recurrence_Rule, Recurrence_End_Date FROM Event WHERE EventID = ?');
$eventStmt->bind_param('i', $eventID);
$eventStmt->execute();
$event = $eventStmt->get_result()->fetch_assoc();
$eventStmt->close();
if (!$event) {
    header('Location: events.php');
    exit();
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $start = new DateTime($event['Date_OF_Booking']);
    $end = new DateTime($event['Recurrence_End_Date']);
    $step = ['Daily' => '+1 day', 'Weekly' => '+1 week', 'Monthly' => '+1 month'][$event['Recurrence_Rule']] ?? null;
    if (!$step || $end < $start) {
        $message = 'Set a valid recurrence rule and end date on the event first.';
    } else {
        $insert = $conn->prepare("INSERT IGNORE INTO event_occurrences (EventID, Occurrence_Date, Occurrence_Time) VALUES (?, ?, ?)");
        $created = 0;
        $start->modify($step);
        while ($start <= $end) {
            $date = $start->format('Y-m-d');
            $insert->bind_param('iss', $eventID, $date, $event['Time']);
            $insert->execute();
            $created += $insert->affected_rows;
            $start->modify($step);
        }
        $insert->close();
        $message = $created . ' future occurrence(s) generated.';
    }
}
$occurrences = $conn->prepare('SELECT Occurrence_Date, Occurrence_Time, Status FROM event_occurrences WHERE EventID = ? ORDER BY Occurrence_Date');
$occurrences->bind_param('i', $eventID);
$occurrences->execute();
$rows = $occurrences->get_result();
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Recurring event | EventSpace</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link href="../assets/css/legacy.css" rel="stylesheet"></head>
<body><main class="container mt-4"><div class="d-flex justify-content-between align-items-center mb-4"><div><p class="text-uppercase text-muted mb-1">Schedule generator</p><h1><?= htmlspecialchars($event['Name'], ENT_QUOTES, 'UTF-8') ?></h1></div><a href="events.php" class="btn btn-outline-secondary">Back to events</a></div><?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><div class="card p-4 mb-4"><p class="mb-1">Repeats <strong><?= htmlspecialchars($event['Recurrence_Rule'], ENT_QUOTES, 'UTF-8') ?></strong> until <strong><?= htmlspecialchars($event['Recurrence_End_Date'] ?: 'not set', ENT_QUOTES, 'UTF-8') ?></strong>.</p><form method="POST"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"><button class="btn btn-primary mt-3">Generate future occurrences</button></form></div><div class="card p-4"><h2 class="h5">Generated dates</h2><table class="table"><thead><tr><th>Date</th><th>Time</th><th>Status</th></tr></thead><tbody><?php while ($row = $rows->fetch_assoc()): ?><tr><td><?= htmlspecialchars($row['Occurrence_Date'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($row['Occurrence_Time'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($row['Status'], ENT_QUOTES, 'UTF-8') ?></td></tr><?php endwhile; ?></tbody></table></div></main></body></html>

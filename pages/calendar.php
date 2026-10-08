<?php
header('Location: events.php?view=calendar');
exit();
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
$previousMonth = (clone $monthDate)->modify('-1 month')->format('Y-m');
$nextMonth = (clone $monthDate)->modify('+1 month')->format('Y-m');

$stmt = $conn->prepare('SELECT EventID, Name, Date_OF_Booking, Time, Venue, Status, Budget FROM Event WHERE Date_OF_Booking >= ? AND Date_OF_Booking < ? ORDER BY Date_OF_Booking, Time');
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
$eventsByDate = [];
while ($event = $result->fetch_assoc()) {
    $eventsByDate[$event['Date_OF_Booking']][] = $event;
}
$stmt->close();
$daysInMonth = (int) $monthDate->format('t');
$firstWeekday = (int) $monthDate->format('w');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar | EventSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        .calendar-grid { display:grid; grid-template-columns:repeat(7, minmax(100px, 1fr)); border:1px solid var(--line); }
        .calendar-heading, .calendar-day { min-height:112px; padding:10px; border-right:1px solid var(--line); border-bottom:1px solid var(--line); }
        .calendar-heading { min-height:auto; background:#edf4f0; color:var(--teal-dark); font:700 .75rem 'Trebuchet MS', sans-serif; text-transform:uppercase; }
        .calendar-day:nth-child(7n) { border-right:0; }
        .calendar-day.is-today { background:#fff8ed; }
        .day-number { color:var(--muted); font:700 .82rem 'Trebuchet MS', sans-serif; }
        .calendar-event { display:block; margin-top:7px; padding:6px 7px; background:#dff2e8; border-left:3px solid var(--teal); border-radius:5px; color:var(--ink); font:700 .75rem 'Trebuchet MS', sans-serif; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        @media (max-width:700px) { .calendar-grid { grid-template-columns:repeat(7, minmax(76px, 1fr)); overflow-x:auto; } .calendar-heading, .calendar-day { min-height:90px; padding:6px; } .calendar-event { font-size:.68rem; } }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 py-2">
    <a class="navbar-brand" href="../dashboard.php">EventSpace</a>
    <div class="navbar-nav"><a class="nav-link" href="events.php">Events</a><a class="nav-link active" href="calendar.php">Calendar</a></div>
    <div class="ms-auto"><a class="btn btn-outline-light btn-sm" href="exportCalendar.php?month=<?= urlencode($monthDate->format('Y-m')) ?>">Export .ics</a></div>
</nav>
<div class="container mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div><p class="text-muted mb-1">Schedule view</p><h3 class="mb-0"><?= htmlspecialchars($monthDate->format('F Y'), ENT_QUOTES, 'UTF-8') ?></h3></div>
        <div class="btn-group"><a class="btn btn-secondary" href="?month=<?= $previousMonth ?>">Previous</a><a class="btn btn-secondary" href="?month=<?= date('Y-m') ?>">Today</a><a class="btn btn-secondary" href="?month=<?= $nextMonth ?>">Next</a></div>
    </div>
    <div class="calendar-grid">
        <?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday): ?><div class="calendar-heading"><?= $weekday ?></div><?php endforeach; ?>
        <?php for ($cell = 0; $cell < $firstWeekday; $cell++): ?><div class="calendar-day"></div><?php endfor; ?>
        <?php for ($day = 1; $day <= $daysInMonth; $day++): $date = $monthDate->format('Y-m-') . str_pad((string) $day, 2, '0', STR_PAD_LEFT); ?>
            <div class="calendar-day <?= $date === date('Y-m-d') ? 'is-today' : '' ?>">
                <span class="day-number"><?= $day ?></span>
                <?php foreach ($eventsByDate[$date] ?? [] as $event): ?>
                    <a class="calendar-event" href="events.php?search=<?= urlencode($event['Name']) ?>" title="<?= htmlspecialchars($event['Name'] . ' at ' . $event['Venue'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($event['Name'], ENT_QUOTES, 'UTF-8') ?></a>
                <?php endforeach; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>
</body>
</html>

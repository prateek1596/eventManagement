<?php
include '../db.php';
include '../auth.php';
session_start();
requireLogin('../login.php');
requireRole(['admin', 'organizer'], '../dashboard.php');

$stats = [];
$queries = [
    'Total events' => 'SELECT COUNT(*) total FROM Event',
    'Upcoming events' => 'SELECT COUNT(*) total FROM Event WHERE Date_OF_Booking >= CURDATE()',
    'Total RSVPs' => 'SELECT COUNT(*) total FROM event_rsvps',
    'Confirmed RSVPs' => "SELECT COUNT(*) total FROM event_rsvps WHERE RSVP_Status = 'Confirmed'",
];
foreach ($queries as $label => $sql) {
    $result = $conn->query($sql);
    $stats[$label] = $result ? (int) $result->fetch_assoc()['total'] : 0;
}
$popular = $conn->query("SELECT e.Name, COUNT(r.RSVP_ID) rsvps FROM Event e LEFT JOIN event_rsvps r ON r.EventID = e.EventID GROUP BY e.EventID, e.Name ORDER BY rsvps DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Analytics | EventSpace</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link href="style.css" rel="stylesheet"></head>
<body><main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4"><h1>Event analytics</h1><a href="../dashboard.php" class="btn btn-outline-secondary">Dashboard</a></div>
    <div class="row g-3 mb-4"><?php foreach ($stats as $label => $value): ?><div class="col-6 col-lg-3"><div class="card p-3 h-100"><small class="text-muted"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></small><strong class="display-6"><?= $value ?></strong></div></div><?php endforeach; ?></div>
    <div class="card p-4"><h2 class="h5">Most requested events</h2><table class="table"><thead><tr><th>Event</th><th>Registrations</th></tr></thead><tbody><?php while ($row = $popular->fetch_assoc()): ?><tr><td><?= htmlspecialchars($row['Name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= (int) $row['rsvps'] ?></td></tr><?php endwhile; ?></tbody></table></div>
</main></body></html>

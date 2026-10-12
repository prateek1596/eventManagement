<?php
include '../db.php';
include '../auth.php';
session_start();
requireLogin('../login.php');

$username = $_SESSION['username'];
$conn->query("INSERT INTO event_notifications (Username, EventID, Notification_Type, Message)
    SELECT '" . $conn->real_escape_string($username) . "', EventID, 'Reminder',
    CONCAT('Upcoming event: ', Name, ' on ', Date_OF_Booking)
    FROM Event WHERE Date_OF_Booking BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    AND NOT EXISTS (SELECT 1 FROM event_notifications n WHERE n.Username = '" . $conn->real_escape_string($username) . "' AND n.EventID = Event.EventID AND n.Notification_Type = 'Reminder')");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $stmt = $conn->prepare('UPDATE event_notifications SET Is_Read = 1 WHERE Username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
}
$stmt = $conn->prepare('SELECT Message, Created_At, Is_Read FROM event_notifications WHERE Username = ? ORDER BY Created_At DESC LIMIT 50');
$stmt->bind_param('s', $username);
$stmt->execute();
$notifications = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Notifications | EventSpace</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link href="style.css" rel="stylesheet"></head>
<body><main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4"><h1>Notifications</h1><a href="../dashboard.php" class="btn btn-outline-secondary">Dashboard</a></div>
    <form method="POST" class="mb-3"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"><button class="btn btn-sm btn-outline-primary">Mark all as read</button></form>
    <?php while ($notification = $notifications->fetch_assoc()): ?><div class="alert <?= $notification['Is_Read'] ? 'alert-light' : 'alert-info' ?>"><strong><?= htmlspecialchars($notification['Message'], ENT_QUOTES, 'UTF-8') ?></strong><br><small><?= htmlspecialchars($notification['Created_At'], ENT_QUOTES, 'UTF-8') ?></small></div><?php endwhile; ?>
</main></body></html>

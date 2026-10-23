<?php
declare(strict_types=1);
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';
start_eventspace_session();
requireLogin('../login.php');
$username = (string) $_SESSION['username'];
$conn->query("INSERT INTO event_notifications (Username, EventID, Notification_Type, Message) SELECT '" . $conn->real_escape_string($username) . "', EventID, 'Reminder', CONCAT('Upcoming event: ', Name, ' on ', Date_OF_Booking) FROM Event WHERE Date_OF_Booking BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND NOT EXISTS (SELECT 1 FROM event_notifications n WHERE n.Username = '" . $conn->real_escape_string($username) . "' AND n.EventID = Event.EventID AND n.Notification_Type = 'Reminder')");
if ($_SERVER['REQUEST_METHOD'] === 'POST') { verifyCsrf(); $stmt = $conn->prepare('UPDATE event_notifications SET Is_Read = 1 WHERE Username = ?'); $stmt->bind_param('s', $username); $stmt->execute(); }
$stmt = $conn->prepare('SELECT Message, Created_At, Is_Read FROM event_notifications WHERE Username = ? ORDER BY Created_At DESC LIMIT 50');
$stmt->bind_param('s', $username); $stmt->execute(); $notifications = $stmt->get_result();
$pageTitle = 'Notifications';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-heading-row mb-4"><div><p class="app-eyebrow">Your inbox</p><h1 class="mb-2">Notifications</h1><p class="app-muted mb-0">Reminders and updates from your event workspace.</p></div><form method="POST"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><button class="btn btn-light border"><i class="bi bi-check2-all me-1"></i>Mark all as read</button></form></section>
<section class="notification-list app-card">
<?php if ($notifications->num_rows === 0): ?><div class="app-empty"><i class="bi bi-inbox fs-2 d-block mb-2"></i>No notifications yet.</div><?php else: while ($notification = $notifications->fetch_assoc()): ?><article class="notification-row <?= !$notification['Is_Read'] ? 'is-unread' : '' ?>"><span class="notification-row-icon"><i class="bi bi-calendar-heart"></i></span><div><strong><?= e($notification['Message']) ?></strong><small><?= e($notification['Created_At']) ?></small></div></article><?php endwhile; endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

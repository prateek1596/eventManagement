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
$eventStmt = $conn->prepare('SELECT EventID, Name FROM Event WHERE EventID = ?');
$eventStmt->bind_param('i', $eventID);
$eventStmt->execute();
$event = $eventStmt->get_result()->fetch_assoc();
$eventStmt->close();
if (!$event) {
    header('Location: events.php');
    exit();
}
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $ticketName = trim($_POST['ticket_name'] ?? '');
    $price = $_POST['price'] ?? '';
    $capacity = $_POST['capacity'] ?? '';
    if ($ticketName === '' || !is_numeric($price) || (float) $price < 0 || ($capacity !== '' && (!ctype_digit($capacity) || (int) $capacity < 1))) {
        $error = 'Enter a ticket name, a non-negative price, and an optional positive capacity.';
    } else {
        $insert = $conn->prepare("INSERT INTO event_tickets (EventID, Ticket_Name, Price, Capacity) VALUES (?, ?, ?, NULLIF(?, ''))");
        $priceValue = (float) $price;
        $insert->bind_param('isds', $eventID, $ticketName, $priceValue, $capacity);
        if ($insert->execute()) {
            $message = 'Ticket type added.';
        } else {
            $error = 'Unable to add the ticket type.';
        }
        $insert->close();
    }
}
$tickets = $conn->prepare("SELECT t.Ticket_ID, t.Ticket_Name, t.Price, t.Capacity, COUNT(r.RSVP_ID) AS sold, COALESCE(SUM(CASE WHEN r.Payment_Status = 'Paid' THEN t.Price ELSE 0 END), 0) AS revenue FROM event_tickets t LEFT JOIN event_rsvps r ON r.Ticket_ID = t.Ticket_ID WHERE t.EventID = ? GROUP BY t.Ticket_ID ORDER BY t.Price, t.Ticket_Name");
$tickets->bind_param('i', $eventID);
$tickets->execute();
$ticketRows = $tickets->get_result();
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Tickets | EventSpace</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link href="../assets/css/legacy.css" rel="stylesheet"></head>
<body><main class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4"><div><p class="text-uppercase text-muted mb-1">Ticket desk</p><h1><?= htmlspecialchars($event['Name'], ENT_QUOTES, 'UTF-8') ?></h1></div><a href="events.php" class="btn btn-outline-secondary">Back to events</a></div>
    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <div class="row g-4"><div class="col-lg-4"><div class="card p-4"><h2 class="h5">Add ticket type</h2><form method="POST"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"><label class="form-label mt-3">Name</label><input class="form-control" name="ticket_name" placeholder="General admission" required><label class="form-label mt-3">Price</label><input class="form-control" name="price" type="number" min="0" step="0.01" value="0" required><label class="form-label mt-3">Capacity</label><input class="form-control" name="capacity" type="number" min="1" placeholder="Unlimited"><button class="btn btn-primary w-100 mt-4">Add ticket</button></form></div></div>
    <div class="col-lg-8"><div class="card p-4"><h2 class="h5">Sales overview</h2><div class="table-responsive"><table class="table"><thead><tr><th>Ticket</th><th>Price</th><th>Sold</th><th>Capacity</th><th>Paid revenue</th></tr></thead><tbody><?php if ($ticketRows->num_rows === 0): ?><tr><td colspan="5" class="text-muted">No ticket types yet.</td></tr><?php endif; ?><?php while ($ticket = $ticketRows->fetch_assoc()): ?><tr><td><?= htmlspecialchars($ticket['Ticket_Name'], ENT_QUOTES, 'UTF-8') ?></td><td><?= number_format((float) $ticket['Price'], 2) ?></td><td><?= (int) $ticket['sold'] ?></td><td><?= $ticket['Capacity'] === null ? 'Unlimited' : (int) $ticket['Capacity'] ?></td><td><?= number_format((float) $ticket['revenue'], 2) ?></td></tr><?php endwhile; ?></tbody></table></div></div></div></div>
</main></body></html>

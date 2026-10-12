<?php
include '../db.php';

$eventID = filter_input(INPUT_GET, 'event', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$event = null;
$message = '';
$error = '';
$tickets = [];

if ($eventID) {
    $eventStmt = $conn->prepare('SELECT EventID, Name, Date_OF_Booking, Time, Venue, Contact_info FROM Event WHERE EventID = ?');
    $eventStmt->bind_param('i', $eventID);
    $eventStmt->execute();
    $event = $eventStmt->get_result()->fetch_assoc();
    $ticketStmt = $conn->prepare('SELECT Ticket_ID, Ticket_Name, Price, Capacity, (SELECT COUNT(*) FROM event_rsvps r WHERE r.Ticket_ID = event_tickets.Ticket_ID) AS Sold FROM event_tickets WHERE EventID = ? ORDER BY Price, Ticket_Name');
    $ticketStmt->bind_param('i', $eventID);
    $ticketStmt->execute();
    $ticketResult = $ticketStmt->get_result();
    while ($ticket = $ticketResult->fetch_assoc()) {
        $tickets[] = $ticket;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $event) {
    $name = trim($_POST['guest_name'] ?? '');
    $email = trim($_POST['guest_email'] ?? '');
    $phone = trim($_POST['guest_phone'] ?? '');
    $ticketID = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter your name and a valid email address.';
    } elseif ($tickets && !$ticketID) {
        $error = 'Choose a ticket type.';
    } else {
        if ($ticketID) {
            $ticket = array_values(array_filter($tickets, static fn ($item) => (int) $item['Ticket_ID'] === $ticketID))[0] ?? null;
            if (!$ticket || ($ticket['Capacity'] !== null && (int) $ticket['Sold'] >= (int) $ticket['Capacity'])) {
                $error = 'That ticket is no longer available.';
            }
        }
    }
    if ($error === '') {
        $token = bin2hex(random_bytes(32));
        $insert = $conn->prepare("INSERT INTO event_rsvps (EventID, Guest_Name, Guest_Email, Guest_Phone, RSVP_Token, Ticket_ID) VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param('issssi', $eventID, $name, $email, $phone, $token, $ticketID);
        if ($insert->execute()) {
            $message = 'Your RSVP has been recorded. Save this check-in code: ' . $token;
        } else {
            $error = 'We could not save your RSVP. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>RSVP | EventSpace</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link href="style.css" rel="stylesheet"></head>
<body><main class="container mt-5" style="max-width:700px"><p class="text-uppercase text-muted mb-2">Event invitation</p><?php if (!$event): ?><div class="alert alert-danger">This event link is not valid.</div><?php else: ?><h1><?= htmlspecialchars($event['Name'], ENT_QUOTES, 'UTF-8') ?></h1><p class="lead"><?= htmlspecialchars($event['Date_OF_Booking'] . ' at ' . $event['Time'] . ' · ' . $event['Venue'], ENT_QUOTES, 'UTF-8') ?></p><?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php else: ?><?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><form method="POST"><input type="hidden" name="event_id" value="<?= (int) $eventID ?>"><div class="mb-3"><label class="form-label">Full name</label><input class="form-control" name="guest_name" required></div><div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="guest_email" required></div><div class="mb-3"><label class="form-label">Phone</label><input class="form-control" name="guest_phone"></div><?php if ($tickets): ?><div class="mb-3"><label class="form-label">Ticket type</label><select class="form-select" name="ticket_id" required><option value="">Choose a ticket</option><?php foreach ($tickets as $ticket): ?><?php $available = $ticket['Capacity'] === null || (int) $ticket['Sold'] < (int) $ticket['Capacity']; ?><option value="<?= (int) $ticket['Ticket_ID'] ?>" <?= $available ? '' : 'disabled' ?>><?= htmlspecialchars($ticket['Ticket_Name'], ENT_QUOTES, 'UTF-8') ?> — <?= number_format((float) $ticket['Price'], 2) ?><?= $available ? '' : ' (sold out)' ?></option><?php endforeach; ?></select></div><?php endif; ?><button class="btn btn-primary">Confirm attendance</button></form><?php endif; ?><?php endif; ?></main></body></html>

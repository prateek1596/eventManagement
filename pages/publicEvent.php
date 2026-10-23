<?php
include '../db.php';

$eventID = filter_input(INPUT_GET, 'event', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
$event = null;
$message = '';
$error = '';
$tickets = [];

if ($eventID) {
    $eventStmt = $conn->prepare('SELECT EventID, Name, Date_OF_Booking, Time, Venue, Contact_info, Capacity, Recurrence_Rule, Recurrence_End_Date FROM Event WHERE EventID = ?');
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
        $conn->begin_transaction();
        $capacityStmt = $conn->prepare('SELECT Capacity FROM Event WHERE EventID = ? FOR UPDATE');
        $capacityStmt->bind_param('i', $eventID);
        $capacityStmt->execute();
        $capacityRow = $capacityStmt->get_result()->fetch_assoc();
        $capacityStmt->close();
        $countStmt = $conn->prepare("SELECT COUNT(*) AS booked FROM event_rsvps WHERE EventID = ? AND RSVP_Status IN ('Pending', 'Confirmed')");
        $countStmt->bind_param('i', $eventID);
        $countStmt->execute();
        $booked = (int) $countStmt->get_result()->fetch_assoc()['booked'];
        $countStmt->close();
        $rsvpStatus = ($capacityRow['Capacity'] !== null && $booked >= (int) $capacityRow['Capacity']) ? 'Waitlisted' : 'Pending';
        if ($ticketID) {
            $insert = $conn->prepare("INSERT INTO event_rsvps (EventID, Guest_Name, Guest_Email, Guest_Phone, RSVP_Token, RSVP_Status, Payment_Status, Ticket_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $paymentStatus = ((float) $ticket['Price'] > 0) ? 'Pending' : 'Not Required';
            $insert->bind_param('issssssi', $eventID, $name, $email, $phone, $token, $rsvpStatus, $paymentStatus, $ticketID);
        } else {
            $insert = $conn->prepare("INSERT INTO event_rsvps (EventID, Guest_Name, Guest_Email, Guest_Phone, RSVP_Token, RSVP_Status) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->bind_param('isssss', $eventID, $name, $email, $phone, $token, $rsvpStatus);
        }
        if ($insert->execute()) {
            $conn->commit();
            $message = $rsvpStatus === 'Waitlisted'
                ? 'This event is currently full. You have been added to the waitlist. Save this check-in code: ' . $token
                : 'Your RSVP has been recorded. Save this check-in code: ' . $token;
        } else {
            $conn->rollback();
            $error = 'We could not save your RSVP. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RSVP | EventSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
<main class="container mt-5" style="max-width:900px">
    <?php if (!$event): ?>
        <div class="alert alert-danger">This event link is not valid.</div>
    <?php else: ?>
        <section class="event-gallery mb-4" aria-label="Event gallery">
            <img src="https://images.unsplash.com/photo-1505236858219-8359eb29e329?auto=format&fit=crop&w=1200&q=85" alt="<?= htmlspecialchars($event['Name'], ENT_QUOTES, 'UTF-8') ?> atmosphere" onerror="this.style.display='none'">
            <img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=800&q=80" alt="Guests enjoying an event" onerror="this.style.display='none'">
            <img src="https://images.unsplash.com/photo-1511795409834-ef04bbd61622?auto=format&fit=crop&w=800&q=80" alt="Event table and decor" onerror="this.style.display='none'">
        </section>
        <p class="text-uppercase text-muted mb-2" style="letter-spacing:.14em;font-size:.72rem;font-weight:800">Event invitation</p>
        <h1><?= htmlspecialchars($event['Name'], ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="lead"><?= htmlspecialchars($event['Date_OF_Booking'] . ' at ' . $event['Time'] . ' · ' . $event['Venue'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php if ($event['Recurrence_Rule'] !== 'None'): ?><p class="text-muted">Repeats <?= htmlspecialchars(strtolower($event['Recurrence_Rule']), ENT_QUOTES, 'UTF-8') ?> until <?= htmlspecialchars($event['Recurrence_End_Date'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
        <?php if ($event['Capacity'] !== null): ?><p class="text-muted">Capacity: <?= (int) $event['Capacity'] ?> guests. Full events accept waitlist requests.</p><?php endif; ?>
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="card text-center p-4"><p class="mb-2">Show this QR code at check-in</p><img class="mx-auto" style="width:180px;height:180px" alt="RSVP check-in QR code" src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode($token) ?>"><small class="text-muted mt-2"><?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?></small></div>
        <?php else: ?>
            <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <form method="POST" class="card p-4">
                <input type="hidden" name="event_id" value="<?= (int) $eventID ?>">
                <div class="mb-3"><label class="form-label">Full name</label><input class="form-control" name="guest_name" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input class="form-control" type="email" name="guest_email" required></div>
                <div class="mb-3"><label class="form-label">Phone</label><input class="form-control" name="guest_phone"></div>
                <?php if ($tickets): ?><div class="mb-3"><label class="form-label">Ticket type</label><select class="form-select" name="ticket_id" required><option value="">Choose a ticket</option><?php foreach ($tickets as $ticket): ?><?php $available = $ticket['Capacity'] === null || (int) $ticket['Sold'] < (int) $ticket['Capacity']; ?><option value="<?= (int) $ticket['Ticket_ID'] ?>" <?= $available ? '' : 'disabled' ?>><?= htmlspecialchars($ticket['Ticket_Name'], ENT_QUOTES, 'UTF-8') ?> — <?= number_format((float) $ticket['Price'], 2) ?><?= $available ? '' : ' (sold out)' ?></option><?php endforeach; ?></select></div><?php endif; ?>
                <button class="btn btn-primary">Confirm attendance</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</main>
<script src="../assets/theme.js"></script>
</body>
</html>

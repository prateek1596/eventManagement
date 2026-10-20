<?php
include '../db.php';
include '../auth.php';
session_start();
requireLogin('../login.php');
requireRole(['admin', 'organizer', 'staff'], '../dashboard.php');
$message = '';
$error = '';
$guest = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $token = trim($_POST['token'] ?? '');
    $lookup = $conn->prepare('SELECT r.Guest_Name, r.RSVP_Status, e.Name AS Event_Name FROM event_rsvps r JOIN Event e ON e.EventID = r.EventID WHERE r.RSVP_Token = ?');
    $lookup->bind_param('s', $token);
    $lookup->execute();
    $guest = $lookup->get_result()->fetch_assoc();
    $lookup->close();
    $stmt = $conn->prepare("UPDATE event_rsvps SET Checked_In_At = NOW() WHERE RSVP_Token = ? AND Checked_In_At IS NULL AND RSVP_Status IN ('Pending', 'Confirmed')");
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $message = $stmt->affected_rows ? 'Guest checked in successfully.' : 'Code not found, guest is waitlisted/declined, or already checked in.';
}
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Check-in | EventSpace</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link href="style.css" rel="stylesheet"></head><body><main class="container mt-5" style="max-width:600px"><h2>Guest check-in</h2><p class="text-muted">Scan a QR code or enter the guest code from their RSVP confirmation.</p><?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?><?php if ($guest): ?><br><strong><?= htmlspecialchars($guest['Guest_Name'], ENT_QUOTES, 'UTF-8') ?></strong> · <?= htmlspecialchars($guest['Event_Name'], ENT_QUOTES, 'UTF-8') ?><?php endif; ?></div><?php endif; ?><form method="POST"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"><input class="form-control mb-3" name="token" placeholder="Paste RSVP check-in code" required autofocus><button class="btn btn-primary">Check in guest</button></form></main></body></html>

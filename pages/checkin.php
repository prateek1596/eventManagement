<?php
include '../db.php';
include '../auth.php';
session_start();
requireLogin('../login.php');
requireRole(['admin', 'organizer', 'staff'], '../dashboard.php');
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $token = trim($_POST['token'] ?? '');
    $stmt = $conn->prepare('UPDATE event_rsvps SET Checked_In_At = NOW() WHERE RSVP_Token = ? AND Checked_In_At IS NULL');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $message = $stmt->affected_rows ? 'Guest checked in successfully.' : 'Code not found or guest is already checked in.';
}
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Check-in | EventSpace</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link href="style.css" rel="stylesheet"></head><body><main class="container mt-5" style="max-width:600px"><h2>Guest check-in</h2><p class="text-muted">Enter the guest code from their RSVP confirmation.</p><?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><form method="POST"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"><input class="form-control mb-3" name="token" placeholder="Paste RSVP check-in code" required><button class="btn btn-primary">Check in guest</button></form></main></body></html>

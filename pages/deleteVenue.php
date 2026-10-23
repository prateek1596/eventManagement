<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';
start_eventspace_session();
requireLogin('../login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: venue.php?error=method');
    exit();
}
verifyCsrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: venue.php?error=invalid');
    exit();
}
$stmt = $conn->prepare('DELETE FROM Venue WHERE VenueID = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();
header('Location: venue.php?success=1');
exit();

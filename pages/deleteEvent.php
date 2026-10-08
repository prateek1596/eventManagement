<?php
include '../db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$eventID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$eventID) {
    header('Location: events.php?delete_error=invalid');
    exit();
}

try {
    $conn->begin_transaction();

    $feedback = $conn->prepare('DELETE FROM feedback WHERE EventID = ?');
    $feedback->bind_param('i', $eventID);
    $feedback->execute();
    $feedback->close();

    $event = $conn->prepare('DELETE FROM Event WHERE EventID = ?');
    $event->bind_param('i', $eventID);
    $event->execute();
    $event->close();

    $conn->commit();
    header('Location: events.php?deleted=1');
} catch (mysqli_sql_exception $exception) {
    $conn->rollback();
    header('Location: events.php?delete_error=linked');
}
exit();

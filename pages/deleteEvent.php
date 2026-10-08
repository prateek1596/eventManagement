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

    $dependentTables = ['feedback', 'organiser', 'payment', 'ticket', 'venue'];
    foreach ($dependentTables as $table) {
        $dependent = $conn->prepare("DELETE FROM `$table` WHERE EventID = ?");
        $dependent->bind_param('i', $eventID);
        $dependent->execute();
        $dependent->close();
    }

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

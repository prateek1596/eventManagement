<?php
include '../db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $attendee_id = $_GET['id'];

    // Deleting the attendee
    $query = "DELETE FROM Attendee WHERE AttendeeID = $attendee_id";
    if ($conn->query($query)) {
        header("Location: attendee.php?success=true");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: attendee.php");
    exit();
}
?>

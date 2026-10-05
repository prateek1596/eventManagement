<?php
include '../db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("DELETE FROM Venue WHERE VenueID = '$id'");
    header("Location: venue.php?success=1");
    exit();
} else {
    echo "Error: No ID provided!";
}
?>

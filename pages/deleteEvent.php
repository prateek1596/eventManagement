<?php
include '../db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$eventID = $_GET['id'];

$conn->query("DELETE FROM Event WHERE `EventID`='$eventID'");

header("Location: events.php");
exit();

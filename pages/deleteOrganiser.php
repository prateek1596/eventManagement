<?php
include '../db.php';
session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $conn->query("DELETE FROM Organiser WHERE `OrganiserID` = $id");
    header("Location: organiser.php");  // Adjust the redirect URL if necessary
    exit();
} else {
    echo "Error: No ID provided!";
}
?>

<?php
include '../db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $ratings = $_POST['ratings'];
    $eventID = $_POST['event_id'];

    $sql = "INSERT INTO Organiser (`Name`, `Address`, `Rating`, `EventID`) 
            VALUES ('$name', '$address', '$ratings', '$eventID')";

    if ($conn->query($sql)) {
        header("Location: organiser.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Organiser</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet"> 
</head>
<body>
<div class="container mt-4">
    <h3>Add Organiser</h3>
    <form method="POST">
        <div class="row g-3">
            <div class="col-md-4">
                <label for="name">Name</label>
                <input type="text" name="name" class="form-control" required id="name">
            </div>
            <div class="col-md-4">
                <label for="address">Address</label>
                <input type="text" name="address" class="form-control" required id="address">
            </div>
            <div class="col-md-4">
                <label for="ratings">Ratings</label>
                <input type="number" step="0.1" name="ratings" class="form-control" required id="ratings">
            </div>
            <div class="col-md-4">
                <label for="event_id">Event ID</label>
                <input type="number" name="event_id" class="form-control" required id="event_id">
            </div>
        </div>
        <button class="btn btn-primary mt-3" type="submit">Submit</button>
        <a href="organiser.php" class="btn btn-secondary mt-3">Cancel</a>
    </form>
</div>
</body>
</html>

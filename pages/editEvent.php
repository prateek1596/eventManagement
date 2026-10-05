<?php
include '../db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$eventID = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $bookingDate = $_POST['booking_date'];
    $time = $_POST['time'];
    $venue = $_POST['venue'];
    $contactInfo = $_POST['contact_info'];
    $status = $_POST['status'];
    $budget = $_POST['budget'];

    $sql = "UPDATE Event SET 
                `Name`='$name',
                `Date_OF_Booking`='$bookingDate',
                `Time`='$time',
                `Venue`='$venue',
                `Contact_info`='$contactInfo',
                `Status`='$status',
                `Budget`='$budget'
            WHERE `EventID`='$eventID'";

    if ($conn->query($sql)) {
        header("Location: events.php?success=1");
        exit();}
    else {
        $error = "Error: " . $conn->error;
    }
}

$result = $conn->query("SELECT * FROM Event WHERE `EventID`='$eventID'");
$event = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h3>Edit Event</h3>
    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row g-3">
            <div class="col-md-4">
                <label>Customer Name</label>
                <input type="text" name="name" value="<?php echo $event['Name']; ?>" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Booking Date</label>
                <input type="date" name="booking_date" value="<?php echo $event['Date_OF_Booking']; ?>" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Time</label>
                <input type="text" name="time" value="<?php echo $event['Time']; ?>" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Venue</label>
                <input type="text" name="venue" value="<?php echo $event['Venue']; ?>" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Contact Info</label>
                <input type="nummber" name="contact_info" value="<?php echo $event['Contact_info']; ?>" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Status</label>
                <select name="status" class="form-select">
                <option value="0" <?php if ($event['Status'] == 0) echo 'selected'; ?>>1</option>
                <option value="1" <?php if ($event['Status'] == 1) echo 'selected'; ?>>0</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Budget</label>
                <input type="number" name="budget" value="<?php echo $event['Budget']; ?>" class="form-control" step="0.01" required>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-primary">Update</button>
            <a href="events.php" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>
</body>
</html>

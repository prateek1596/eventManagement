<?php
include '../db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: attendee.php");
    exit();
}

$attendee_id = $_GET['id'];
$result = $conn->query("SELECT * FROM Attendee WHERE AttendeeID = $attendee_id");
$row = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_no = $_POST['phone_no'];
    $rsvp = isset($_POST['rsvp']) ? 1 : 0;

    $query = "UPDATE Attendee SET Name='$name', Email='$email', Phone_No='$phone_no', RSVP='$rsvp' WHERE AttendeeID=$attendee_id";
    if ($conn->query($query)) {
        header("Location: attendee.php?success=true");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Attendee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h3>Edit Attendee</h3>

    <form method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= $row['Name'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= $row['Email'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="phone_no" class="form-label">Phone No</label>
            <input type="text" class="form-control" id="phone_no" name="phone_no" value="<?= $row['Phone_No'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="rsvp" class="form-label">RSVP</label>
            <select class="form-select" id="rsvp" name="rsvp" required>
                <option value="1" <?= $row['RSVP'] == 1 ? 'selected' : '' ?>>Yes</option>
                <option value="0" <?= $row['RSVP'] == 0 ? 'selected' : '' ?>>No</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Update Attendee</button>
    </form>
</div>
</body>
</html>

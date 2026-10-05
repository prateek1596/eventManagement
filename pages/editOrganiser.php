<?php
include '../db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Get organiser ID from URL
$organiserID = $_GET['id'] ?? null;

// Fetch organiser details
if ($organiserID) {
    $result = $conn->query("SELECT * FROM Organiser WHERE OrganiserID = '$organiserID'");
    $organiser = $result->fetch_assoc();
} else {
    echo "Error: No organiser ID provided!";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $rating = $_POST['rating'];
    $eventID = $_POST['event_id'];

    $sql = "UPDATE Organiser SET 
                `Name`='$name',
                `Address`='$address',
                `Rating`='$rating',
                `EventID`='$eventID'
            WHERE `OrganiserID`='$organiserID'";

    if ($conn->query($sql)) {
        header("Location: organiser.php?success=1");
        exit();
    } else {
        $error = "Error updating organiser: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Organiser</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h3>Edit Organiser</h3>

    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
        <div class="row g-3">
            <div class="col-md-6">
                <label>Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($organiser['Name']); ?>" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label>Address</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($organiser['Address']); ?>" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label>Rating</label>
                <input type="number" name="rating" value="<?php echo htmlspecialchars($organiser['Rating']); ?>" class="form-control" step="0.1" required>
            </div>

            <div class="col-md-6">
                <label>Event ID</label>
                <input type="number" name="event_id" value="<?php echo htmlspecialchars($organiser['EventID']); ?>" class="form-control" required>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="organiser.php" class="btn btn-secondary">Back</a>
        </div>
    </form>
</div>
</body>
</html>

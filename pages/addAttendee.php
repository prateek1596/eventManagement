<?php
include '../db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

// Check if form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input values to prevent SQL injection
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone_no = mysqli_real_escape_string($conn, $_POST['phone_no']);
    $rsvp = isset($_POST['rsvp']) ? 1 : 0;

    // Prepare the SQL query with placeholders for user input
    $query = "INSERT INTO Attendee (Name, Email, Phone_No, RSVP) VALUES (?, ?, ?, ?)";

    // Prepare the statement
    if ($stmt = $conn->prepare($query)) {
        // Bind the parameters to the statement
        $stmt->bind_param("sssi", $name, $email, $phone_no, $rsvp);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to attendee list page with success message
            header("Location: attendee.php?success=true");
            exit();
        } else {
            // Handle error if the query execution fails
            echo "Error: " . $stmt->error;
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        // Handle error if the preparation of the query fails
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Attendee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h3>Add Attendee</h3>

    <form method="POST">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="phone_no" class="form-label">Phone No</label>
            <input type="text" class="form-control" id="phone_no" name="phone_no" required>
        </div>
        <div class="mb-3">
            <label for="rsvp" class="form-label">RSVP</label>
            <select class="form-select" id="rsvp" name="rsvp" required>
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Add Attendee</button>
    </form>
</div>
</body>
</html>

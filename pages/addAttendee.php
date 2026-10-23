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

<?php
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Add Attendee';
require __DIR__ . '/../includes/header.php';
?>
<section class="form-page-heading"><div><p class="app-eyebrow">People management</p><h1>Add an attendee</h1><p class="app-muted mb-0">Capture guest details and RSVP status.</p></div><a href="attendee.php" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i>Back to attendees</a></section>
<section class="app-card form-studio form-studio-narrow">
    <div class="form-studio-heading"><span class="form-studio-icon"><i class="bi bi-person-plus"></i></span><div><h2 class="h4 mb-1">Attendee details</h2><p class="app-muted mb-0">These details help your team coordinate the guest list.</p></div></div>

    <form method="POST" class="eventspace-form">
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
        <div class="form-actions"><a href="attendee.php" class="btn btn-light border">Cancel</a><button type="submit" class="btn app-btn-primary"><i class="bi bi-check2 me-1"></i>Add attendee</button></div>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

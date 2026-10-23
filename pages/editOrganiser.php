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

<?php
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Edit Organiser';
require __DIR__ . '/../includes/header.php';
?>
<section class="form-page-heading"><div><p class="app-eyebrow">People management</p><h1>Edit organiser</h1><p class="app-muted mb-0">Keep the organiser profile accurate and useful.</p></div><a href="organiser.php" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i>Back to organisers</a></section>
<section class="app-card form-studio form-studio-narrow">
    <div class="form-studio-heading"><span class="form-studio-icon"><i class="bi bi-person-gear"></i></span><div><h2 class="h4 mb-1">Organiser details</h2><p class="app-muted mb-0">Update the information shown to your event team.</p></div></div>

    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST" class="eventspace-form">
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

        <div class="form-actions">
            <a href="organiser.php" class="btn btn-light border">Cancel</a>
            <button type="submit" class="btn app-btn-primary"><i class="bi bi-check2 me-1"></i>Save changes</button>
        </div>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

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

<?php
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Add Organiser';
require __DIR__ . '/../includes/header.php';
?>
<section class="form-page-heading"><div><p class="app-eyebrow">People management</p><h1>Add an organiser</h1><p class="app-muted mb-0">Create a clear profile for the person leading an event.</p></div><a href="organiser.php" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i>Back to organisers</a></section>
<section class="app-card form-studio form-studio-narrow">
    <div class="form-studio-heading"><span class="form-studio-icon"><i class="bi bi-person-badge"></i></span><div><h2 class="h4 mb-1">Organiser details</h2><p class="app-muted mb-0">Add the organiser’s contact and event information.</p></div></div>
    <form method="POST" class="eventspace-form">
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
        <div class="form-actions"><a href="organiser.php" class="btn btn-light border">Cancel</a><button class="btn app-btn-primary" type="submit"><i class="bi bi-check2 me-1"></i>Add organiser</button></div>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

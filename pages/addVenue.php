<?php
include '../db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $location = $_POST['location'];
    $cost = $_POST['cost'];
    $facilities = $_POST['facilities'];
    $availability = $_POST['availability'];
    $eventID = $_POST['event_id'];

    $sql = "INSERT INTO Venue (Name, Location, Cost, Facilities, Availability, EventID)
            VALUES ('$name', '$location', '$cost', '$facilities', '$availability', '$eventID')";

    if ($conn->query($sql)) {
        header("Location: venue.php?success=1");
        exit();
    } else {
        $error = "Error adding venue: " . $conn->error;
    }
}
?>

<?php
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Add Venue';
require __DIR__ . '/../includes/header.php';
?>
<section class="form-page-heading"><div><p class="app-eyebrow">Venue management</p><h1>Add a venue</h1><p class="app-muted mb-0">Create a polished home for your next experience.</p></div><a href="venue.php" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i>Back to venues</a></section>
<section class="app-card form-studio">
    <div class="form-studio-heading"><span class="form-studio-icon"><i class="bi bi-building"></i></span><div><h2 class="h4 mb-1">Venue details</h2><p class="app-muted mb-0">Add the essentials your team needs to make bookings.</p></div></div>

    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST" class="eventspace-form">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="venue-name">Name</label>
                <input id="venue-name" type="text" name="name" class="form-control" placeholder="e.g. The Garden House" required>
            </div>
            <div class="col-md-6">
                <label for="venue-location">Location</label>
                <input id="venue-location" type="text" name="location" class="form-control" placeholder="City or full address" required>
            </div>
            <div class="col-md-4">
                <label for="venue-cost">Cost</label>
                <input id="venue-cost" type="number" step="0.01" name="cost" class="form-control" placeholder="0.00" required>
            </div>
            <div class="col-md-8">
                <label for="venue-facilities">Facilities</label>
                <input id="venue-facilities" type="text" name="facilities" class="form-control" placeholder="Parking, catering, sound system" required>
            </div>
            <div class="col-md-4">
                <label for="venue-availability">Availability</label>
                <select name="availability" class="form-select" required>
                    <option value="1">Available</option>
                    <option value="0">Not Available</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="venue-event">Event ID <span class="app-muted">(optional)</span></label>
                <input id="venue-event" type="number" name="event_id" class="form-control" placeholder="Link to an event">
            </div>
        </div>

        <div class="form-actions">
            <a href="venue.php" class="btn btn-light border">Cancel</a>
            <button type="submit" class="btn app-btn-primary"><i class="bi bi-check2 me-1"></i>Add venue</button>
        </div>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

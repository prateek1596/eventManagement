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

<?php
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Edit Attendee';
require __DIR__ . '/../includes/header.php';
?>
<section class="form-page-heading"><div><p class="app-eyebrow">People management</p><h1>Edit attendee</h1><p class="app-muted mb-0">Keep this guest’s contact and RSVP information current.</p></div><a href="attendee.php" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i>Back to attendees</a></section>
<section class="app-card form-studio form-studio-narrow">
    <div class="form-studio-heading"><span class="form-studio-icon"><i class="bi bi-person-check"></i></span><div><h2 class="h4 mb-1">Attendee details</h2><p class="app-muted mb-0">Update the information used by your event team.</p></div></div>

    <form method="POST" class="eventspace-form">
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
        <div class="form-actions"><a href="attendee.php" class="btn btn-light border">Cancel</a><button type="submit" class="btn app-btn-primary"><i class="bi bi-check2 me-1"></i>Save changes</button></div>
    </form>
 </section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

<?php
include '../db.php';
include '../auth.php';
session_start();
requireLogin('../login.php');
requireRole(['admin', 'organizer'], 'events.php');

$eventID = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$eventID) {
    header('Location: events.php?error=invalid_event');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrf();
    $name = trim($_POST['name'] ?? '');
    $bookingDate = $_POST['booking_date'] ?? '';
    $time = $_POST['time'] ?? '';
    $venue = trim($_POST['venue'] ?? '');
    $contactInfo = trim($_POST['contact_info'] ?? '');
    $status = $_POST['status'] ?? '';
    $budget = $_POST['budget'] ?? '';
    $error = null;

    if ($name === '' || $venue === '' || $contactInfo === '' || !in_array($status, ['0', '1'], true)) {
        $error = 'Complete all event fields with valid values.';
    } elseif (!is_numeric($budget) || $budget < 0 || $budget > 9999999999999.99) {
        $error = 'Budget must be a number between 0 and 9,999,999,999,999.99.';
    }

    if ($error === null) {
        $stmt = $conn->prepare('UPDATE Event SET `Name` = ?, `Date_OF_Booking` = ?, `Time` = ?, `Venue` = ?, `Contact_info` = ?, `Status` = ?, `Budget` = ? WHERE `EventID` = ?');
        $statusValue = (int) $status;
        $budgetValue = (float) $budget;
        $stmt->bind_param('sssssidi', $name, $bookingDate, $time, $venue, $contactInfo, $statusValue, $budgetValue, $eventID);
    }

    if ($error === null && $stmt->execute()) {
        $stmt->close();
        header("Location: events.php?success=1");
        exit();
    } elseif ($error === null) {
        $error = 'Unable to update the event.';
        $stmt->close();
    }
}

$stmt = $conn->prepare('SELECT * FROM Event WHERE `EventID` = ?');
$stmt->bind_param('i', $eventID);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$event) {
    header('Location: events.php?error=not_found');
    exit();
}
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
    <?php if (isset($success)) echo "<div class='alert alert-success'>" . htmlspecialchars($success, ENT_QUOTES, 'UTF-8') . "</div>"; ?>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</div>"; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label>Customer Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($event['Name'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Booking Date</label>
                <input type="date" name="booking_date" value="<?php echo htmlspecialchars($event['Date_OF_Booking'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Time</label>
                <input type="text" name="time" value="<?php echo htmlspecialchars($event['Time'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Venue</label>
                <input type="text" name="venue" value="<?php echo htmlspecialchars($event['Venue'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Contact Info</label>
                <input type="text" name="contact_info" value="<?php echo htmlspecialchars($event['Contact_info'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Status</label>
                <select name="status" class="form-select">
                <option value="1" <?php if ((int) $event['Status'] === 1) echo 'selected'; ?>>1</option>
                <option value="0" <?php if ((int) $event['Status'] === 0) echo 'selected'; ?>>0</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Budget</label>
                <input type="number" name="budget" value="<?php echo htmlspecialchars($event['Budget'], ENT_QUOTES, 'UTF-8'); ?>" class="form-control" step="0.01" min="0" max="9999999999999.99" required>
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

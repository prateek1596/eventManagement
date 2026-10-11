<?php
include ('../db.php');
include ('../auth.php');
session_start();
requireLogin('../login.php');
requireRole(['admin', 'organizer'], 'events.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verifyCsrf();
    $eventID = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
    $name = trim($_POST['name'] ?? '');
    $bookingDate = $_POST['booking_date'] ?? '';
    $time = $_POST['time'] ?? '';
    $venue = trim($_POST['venue'] ?? '');
    $contactInfo = trim($_POST['contact_info'] ?? '');
    $status = $_POST['status'] ?? '';
    $budget = $_POST['budget'] ?? '';

    if (!$eventID || $name === '' || $venue === '' || $contactInfo === '' || !in_array($status, ['0', '1'], true)) {
        $error = 'Complete all event fields with valid values.';
    } elseif (!is_numeric($budget) || $budget < 0 || $budget > 9999999999999.99) {
        $error = 'Budget must be a number between 0 and 9,999,999,999,999.99.';
    }

    if (!isset($error)) {
        $stmt = $conn->prepare('INSERT INTO Event (`EventID`, `Name`, `Date_OF_Booking`, `Time`, `Venue`, `Contact_info`, `Status`, `Budget`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        if ($stmt) {
            $statusValue = (int) $status;
            $budgetValue = (float) $budget;
            $stmt->bind_param('isssssid', $eventID, $name, $bookingDate, $time, $venue, $contactInfo, $statusValue, $budgetValue);
        }
        if (isset($stmt) && $stmt->execute()) {
            $success = "Event added successfully!";
            $stmt->close();
        } else {
            $error = "Unable to add the event. Check that the event ID is unique.";
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h3 class="mb-4">Add New Event</h3>

    <?php if (isset($success)) echo "<div class='alert alert-success'>" . htmlspecialchars($success, ENT_QUOTES, 'UTF-8') . "</div>"; ?>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</div>"; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label>Event ID</label>
                <input type="text" name="event_id" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Customer Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Booking Date</label>
                <input type="date" name="booking_date" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label>Time</label>
                <input type="time" name="time" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label>Venue</label>
                <input type="text" name="venue" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label>Contact Info</label>
                <input type="text" name="contact_info" class="form-control" placeholder="Separate multiple by commas" required>
            </div>
            <div class="col-md-3">
                <label>Status</label>
                <select name="status" class="form-select">
                    <option value="1">1</option>
                    <option value="0">0</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Budget</label>
                <input type="number" name="budget" class="form-control" step="0.01" min="0" max="9999999999999.99" required>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-success">Add Event</button>
            <a href="events.php" class="btn btn-secondary">Back to Events</a>
        </div>
    </form>
</div>
</body>
</html>

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
    $capacity = $_POST['capacity'] ?? '';
    $recurrenceRule = $_POST['recurrence_rule'] ?? 'None';
    $recurrenceEndDate = $_POST['recurrence_end_date'] ?? '';
    $error = null;

    if ($name === '' || $venue === '' || $contactInfo === '' || !in_array($status, ['0', '1'], true) || !in_array($recurrenceRule, ['None', 'Daily', 'Weekly', 'Monthly'], true)) {
        $error = 'Complete all event fields with valid values.';
    } elseif (!is_numeric($budget) || $budget < 0 || $budget > 9999999999999.99) {
        $error = 'Budget must be a number between 0 and 9,999,999,999,999.99.';
    } elseif (($capacity !== '' && filter_var($capacity, FILTER_VALIDATE_INT) === false) || ($capacity !== '' && (int) $capacity < 1)) {
        $error = 'Capacity must be a positive whole number or left blank for unlimited attendance.';
    } elseif ($recurrenceRule !== 'None' && ($recurrenceEndDate === '' || $recurrenceEndDate < $bookingDate)) {
        $error = 'Choose an end date for recurring events.';
    }

    if ($error === null) {
        $stmt = $conn->prepare('UPDATE Event SET `Name` = ?, `Date_OF_Booking` = ?, `Time` = ?, `Venue` = ?, `Contact_info` = ?, `Status` = ?, `Budget` = ?, `Capacity` = NULLIF(?, \'\'), `Recurrence_Rule` = ?, `Recurrence_End_Date` = NULLIF(?, \'\') WHERE `EventID` = ?');
        $statusValue = (int) $status;
        $budgetValue = (float) $budget;
        $stmt->bind_param('sssssidsssi', $name, $bookingDate, $time, $venue, $contactInfo, $statusValue, $budgetValue, $capacity, $recurrenceRule, $recurrenceEndDate, $eventID);
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

<?php
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Edit Event';
require __DIR__ . '/../includes/header.php';
?>
<section class="form-page-heading"><div><p class="app-eyebrow">Event management</p><h1>Edit event</h1><p class="app-muted mb-0">Refine the schedule and keep every detail aligned.</p></div><a href="events.php" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i>Back to events</a></section>
<section class="app-card form-studio">
    <div class="form-studio-heading"><span class="form-studio-icon"><i class="bi bi-calendar2-week"></i></span><div><h2 class="h4 mb-1">Event details</h2><p class="app-muted mb-0">Update the information used across your event workspace.</p></div></div>
    <?php if (isset($success)) echo "<div class='alert alert-success'>" . htmlspecialchars($success, ENT_QUOTES, 'UTF-8') . "</div>"; ?>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</div>"; ?>

    <form method="POST" class="eventspace-form">
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
            <div class="col-md-3">
                <label>Capacity <small class="text-muted">(optional)</small></label>
                <input type="number" name="capacity" value="<?php echo htmlspecialchars((string) ($event['Capacity'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="form-control" min="1" placeholder="Unlimited">
            </div>
            <div class="col-md-3">
                <label>Repeats</label>
                <select name="recurrence_rule" class="form-select">
                    <?php foreach (['None' => 'Does not repeat', 'Daily' => 'Daily', 'Weekly' => 'Weekly', 'Monthly' => 'Monthly'] as $rule => $label): ?>
                        <option value="<?= $rule ?>" <?= ($event['Recurrence_Rule'] ?? 'None') === $rule ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Repeat until <small class="text-muted">(if repeating)</small></label>
                <input type="date" name="recurrence_end_date" value="<?php echo htmlspecialchars((string) ($event['Recurrence_End_Date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" class="form-control">
            </div>
        </div>

        <div class="form-actions">
            <a href="events.php" class="btn btn-light border">Cancel</a>
            <button class="btn app-btn-primary"><i class="bi bi-check2 me-1"></i>Save changes</button>
        </div>
    </form>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

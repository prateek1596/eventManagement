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
    $capacity = $_POST['capacity'] ?? '';
    $recurrenceRule = $_POST['recurrence_rule'] ?? 'None';
    $recurrenceEndDate = $_POST['recurrence_end_date'] ?? '';

    if (!$eventID || $name === '' || $venue === '' || $contactInfo === '' || !in_array($status, ['0', '1'], true) || !in_array($recurrenceRule, ['None', 'Daily', 'Weekly', 'Monthly'], true)) {
        $error = 'Complete all event fields with valid values.';
    } elseif (!is_numeric($budget) || $budget < 0 || $budget > 9999999999999.99) {
        $error = 'Budget must be a number between 0 and 9,999,999,999,999.99.';
    } elseif (($capacity !== '' && filter_var($capacity, FILTER_VALIDATE_INT) === false) || ($capacity !== '' && (int) $capacity < 1)) {
        $error = 'Capacity must be a positive whole number or left blank for unlimited attendance.';
    } elseif ($recurrenceRule !== 'None' && ($recurrenceEndDate === '' || $recurrenceEndDate < $bookingDate)) {
        $error = 'Choose an end date for recurring events.';
    }

    if (!isset($error)) {
        $stmt = $conn->prepare('INSERT INTO Event (`EventID`, `Name`, `Date_OF_Booking`, `Time`, `Venue`, `Contact_info`, `Status`, `Budget`, `Capacity`, `Recurrence_Rule`, `Recurrence_End_Date`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULLIF(?, \'\'), ?, NULLIF(?, \'\'))');
        if ($stmt) {
            $statusValue = (int) $status;
            $budgetValue = (float) $budget;
            $stmt->bind_param('isssssidsss', $eventID, $name, $bookingDate, $time, $venue, $contactInfo, $statusValue, $budgetValue, $capacity, $recurrenceRule, $recurrenceEndDate);
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

<?php
require_once __DIR__ . '/../includes/helpers.php';
$pageTitle = 'Add Event';
$includeAi = true;
require __DIR__ . '/../includes/header.php';
?>
<section class="form-page-heading"><div><p class="app-eyebrow">Event management</p><h1>Create an event</h1><p class="app-muted mb-0">Shape the details now, then let your team bring it to life.</p></div><a href="events.php" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i>Back to events</a></section>
<section class="app-card form-studio">
    <div class="form-studio-heading"><span class="form-studio-icon"><i class="bi bi-calendar2-plus"></i></span><div><h2 class="h4 mb-1">Event details</h2><p class="app-muted mb-0">Set the schedule, capacity, and booking information.</p></div></div>

    <?php if (isset($success)) echo "<div class='alert alert-success'>" . htmlspecialchars($success, ENT_QUOTES, 'UTF-8') . "</div>"; ?>
    <?php if (isset($error)) echo "<div class='alert alert-danger'>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</div>"; ?>

    <form method="POST" class="eventspace-form">
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
            <div class="col-md-3">
                <label>Capacity <small class="text-muted">(optional)</small></label>
                <input type="number" name="capacity" class="form-control" min="1" placeholder="Unlimited">
            </div>
            <div class="col-md-3">
                <label>Repeats</label>
                <select name="recurrence_rule" class="form-select">
                    <option value="None">Does not repeat</option>
                    <option value="Daily">Daily</option>
                    <option value="Weekly">Weekly</option>
                    <option value="Monthly">Monthly</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Repeat until <small class="text-muted">(if repeating)</small></label>
                <input type="date" name="recurrence_end_date" class="form-control">
            </div>
        </div>

        <div class="form-actions">
            <a href="events.php" class="btn btn-light border">Cancel</a>
            <button class="btn app-btn-primary"><i class="bi bi-check2 me-1"></i>Create event</button>
        </div>
    </form>

    <section class="app-card ai-form-card mt-4">
        <p class="text-uppercase text-muted mb-1" style="letter-spacing:.13em;font-size:.72rem;font-weight:800">Organizer toolkit</p>
        <h2 class="h3">Write the event story with AI</h2>
        <p class="text-muted">Create a polished description without changing the event form above.</p>
        <form data-ai-description class="row g-2">
            <div class="col-md-5"><label class="visually-hidden" for="ai-title">Event title</label><input id="ai-title" class="form-control" name="ai_title" placeholder="Event title" required></div>
            <div class="col-md-5"><label class="visually-hidden" for="ai-keywords">Keywords</label><input id="ai-keywords" class="form-control" name="ai_keywords" placeholder="Keywords, mood, audience"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Draft copy</button></div>
            <div data-description-output class="alert alert-success ai-output mt-3 mb-0" hidden aria-live="polite"></div>
        </form>
    </section>
</section>
<script src="../components/ai-description.js"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>

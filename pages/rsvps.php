<?php
include '../db.php';
include '../auth.php';
session_start();
requireLogin('../login.php');

$eventID = filter_input(INPUT_GET, 'event', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
if (!$eventID) {
    header('Location: events.php?error=invalid_event');
    exit();
}

$eventStmt = $conn->prepare('SELECT EventID, Name, Date_OF_Booking, Venue FROM Event WHERE EventID = ?');
$eventStmt->bind_param('i', $eventID);
$eventStmt->execute();
$event = $eventStmt->get_result()->fetch_assoc();
$eventStmt->close();
if (!$event) {
    header('Location: events.php?error=not_found');
    exit();
}

$statuses = ['Pending', 'Confirmed', 'Declined', 'Waitlisted'];
$status = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$csrf = csrfToken();
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $rsvpID = filter_input(INPUT_POST, 'rsvp_id', FILTER_VALIDATE_INT);
    $newStatus = $_POST['rsvp_status'] ?? '';
    $paymentStatus = $_POST['payment_status'] ?? '';

    if (!$rsvpID || !in_array($newStatus, $statuses, true) || ($paymentStatus !== '' && !in_array($paymentStatus, ['Not Required', 'Pending', 'Paid', 'Refunded'], true))) {
        $error = 'Choose a valid RSVP status.';
    } else {
        $update = $conn->prepare('UPDATE event_rsvps SET RSVP_Status = ? WHERE RSVP_ID = ? AND EventID = ?');
        if ($paymentStatus !== '') {
            $update = $conn->prepare('UPDATE event_rsvps SET RSVP_Status = ?, Payment_Status = ? WHERE RSVP_ID = ? AND EventID = ?');
            $update->bind_param('ssii', $newStatus, $paymentStatus, $rsvpID, $eventID);
        } else {
            $update->bind_param('sii', $newStatus, $rsvpID, $eventID);
        }
        if ($update->execute() && $update->affected_rows >= 0) {
            $message = 'RSVP status updated.';
            if ($newStatus === 'Declined') {
                $promote = $conn->prepare("UPDATE event_rsvps SET RSVP_Status = 'Confirmed' WHERE EventID = ? AND RSVP_Status = 'Waitlisted' ORDER BY Created_At ASC LIMIT 1");
                $promote->bind_param('i', $eventID);
                $promote->execute();
                if ($promote->affected_rows > 0) {
                    $message .= ' The oldest waitlisted guest was promoted to confirmed.';
                }
                $promote->close();
            }
        } else {
            $error = 'Unable to update the RSVP status.';
        }
        $update->close();
    }
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $exportStmt = $conn->prepare('SELECT Guest_Name, Guest_Email, Guest_Phone, RSVP_Status, Payment_Status, Checked_In_At, Created_At FROM event_rsvps WHERE EventID = ? ORDER BY Created_At DESC');
    $exportStmt->bind_param('i', $eventID);
    $exportStmt->execute();
    $exportResult = $exportStmt->get_result();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="event-' . $eventID . '-rsvps.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Guest name', 'Email', 'Phone', 'Status', 'Payment', 'Checked in at', 'Submitted at']);
    while ($row = $exportResult->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fclose($output);
    $exportStmt->close();
    exit();
}

$where = ['EventID = ?'];
$types = 'i';
$params = [$eventID];
if (in_array($status, $statuses, true)) {
    $where[] = 'RSVP_Status = ?';
    $types .= 's';
    $params[] = $status;
}
if ($search !== '') {
    $where[] = '(Guest_Name LIKE ? OR Guest_Email LIKE ? OR Guest_Phone LIKE ?)';
    $types .= 'sss';
    $term = '%' . $search . '%';
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}
$rsvpStmt = $conn->prepare('SELECT RSVP_ID, Guest_Name, Guest_Email, Guest_Phone, RSVP_Status, Payment_Status, Checked_In_At, Created_At FROM event_rsvps WHERE ' . implode(' AND ', $where) . ' ORDER BY Created_At DESC');
$bindValues = [$types];
foreach ($params as $index => $value) {
    $bindValues[] = $value;
}
$bindReferences = [];
foreach ($bindValues as $index => &$value) {
    $bindReferences[$index] = &$value;
}
call_user_func_array([$rsvpStmt, 'bind_param'], $bindReferences);
$rsvpStmt->execute();
$rsvps = $rsvpStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RSVPs | EventSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/legacy.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 py-2">
    <a class="navbar-brand" href="../dashboard.php">EventSpace</a>
    <div class="collapse navbar-collapse show">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
            <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
            <li class="nav-item"><a class="nav-link" href="tasks.php?event=<?= (int) $eventID ?>">Tasks</a></li>
        </ul>
        <span class="text-white"><?= htmlspecialchars((string) $_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>
</nav>

<main class="container">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted mb-2">Guest responses</p>
            <h3 class="mb-1"><?= htmlspecialchars($event['Name'], ENT_QUOTES, 'UTF-8') ?></h3>
            <p class="text-muted mb-0"><?= htmlspecialchars($event['Date_OF_Booking'] . ' · ' . $event['Venue'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="?event=<?= (int) $eventID ?>&export=csv" class="btn btn-outline-primary">Export CSV</a>
            <a href="events.php" class="btn btn-secondary">Back to events</a>
        </div>
    </div>

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <form method="GET" class="row g-2 mb-4">
        <input type="hidden" name="event" value="<?= (int) $eventID ?>">
        <div class="col-md-7"><label class="visually-hidden" for="search">Search guests</label><input class="form-control" id="search" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search name, email, or phone"></div>
        <div class="col-md-3"><label class="visually-hidden" for="status">Filter status</label><select class="form-select" id="status" name="status"><option value="">All statuses</option><?php foreach ($statuses as $option): ?><option value="<?= $option ?>" <?= $status === $option ? 'selected' : '' ?>><?= $option ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Filter</button></div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead><tr><th>Guest</th><th>Contact</th><th>Status</th><th>Payment</th><th>Check-in</th><th>Submitted</th><th>Update</th></tr></thead>
            <tbody>
            <?php if ($rsvps->num_rows === 0): ?><tr>            <td colspan="7" class="text-center text-muted py-4">No RSVPs match this filter.</td></tr><?php endif; ?>
            <?php while ($rsvp = $rsvps->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($rsvp['Guest_Name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                    <td><?= htmlspecialchars($rsvp['Guest_Email'], ENT_QUOTES, 'UTF-8') ?><br><small><?= htmlspecialchars($rsvp['Guest_Phone'] ?: 'No phone', ENT_QUOTES, 'UTF-8') ?></small></td>
                    <td><?= htmlspecialchars($rsvp['RSVP_Status'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="event_id" value="<?= (int) $eventID ?>">
                            <input type="hidden" name="rsvp_id" value="<?= (int) $rsvp['RSVP_ID'] ?>">
                            <input type="hidden" name="rsvp_status" value="<?= htmlspecialchars($rsvp['RSVP_Status'], ENT_QUOTES, 'UTF-8') ?>">
                            <select name="payment_status" class="form-select form-select-sm"><option <?= $rsvp['Payment_Status'] === 'Not Required' ? 'selected' : '' ?>>Not Required</option><option <?= $rsvp['Payment_Status'] === 'Pending' ? 'selected' : '' ?>>Pending</option><option <?= $rsvp['Payment_Status'] === 'Paid' ? 'selected' : '' ?>>Paid</option><option <?= $rsvp['Payment_Status'] === 'Refunded' ? 'selected' : '' ?>>Refunded</option></select>
                            <button class="btn btn-sm btn-outline-primary">Save</button>
                        </form>
                    </td>
                    <td><?= $rsvp['Checked_In_At'] ? htmlspecialchars($rsvp['Checked_In_At'], ENT_QUOTES, 'UTF-8') : 'Not checked in' ?></td>
                    <td><?= htmlspecialchars($rsvp['Created_At'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="event_id" value="<?= (int) $eventID ?>">
                            <input type="hidden" name="rsvp_id" value="<?= (int) $rsvp['RSVP_ID'] ?>">
                            <select name="rsvp_status" class="form-select form-select-sm"><?php foreach ($statuses as $option): ?><option value="<?= $option ?>" <?= $rsvp['RSVP_Status'] === $option ? 'selected' : '' ?>><?= $option ?></option><?php endforeach; ?></select>
                            <button class="btn btn-sm btn-primary">Save</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>

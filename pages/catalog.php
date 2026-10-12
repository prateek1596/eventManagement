<?php
include '../db.php';
include '../auth.php';
session_start();
requireLogin('../login.php');

$query = trim($_GET['q'] ?? '');
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$where = ['Date_OF_Booking >= CURDATE()'];
$params = [];
$types = '';

if ($query !== '') {
    $where[] = '(Name LIKE ? OR Venue LIKE ? OR Contact_info LIKE ?)';
    $term = '%' . $query . '%';
    $params = [$term, $term, $term];
    $types = 'sss';
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
    $where[] = 'Date_OF_Booking >= ?';
    $params[] = $from;
    $types .= 's';
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
    $where[] = 'Date_OF_Booking <= ?';
    $params[] = $to;
    $types .= 's';
}

$stmt = $conn->prepare('SELECT EventID, Name, Date_OF_Booking, Time, Venue, Contact_info FROM Event WHERE ' . implode(' AND ', $where) . ' ORDER BY Date_OF_Booking, Time');
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$events = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Discover events | EventSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><p class="text-uppercase text-muted mb-1">Discover</p><h1>Upcoming events</h1></div>
        <a class="btn btn-outline-secondary" href="../dashboard.php">Dashboard</a>
    </div>
    <form class="row g-2 mb-4">
        <div class="col-md-5"><input class="form-control" name="q" value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search event, venue, or contact"></div>
        <div class="col-md-3"><input class="form-control" type="date" name="from" value="<?= htmlspecialchars($from, ENT_QUOTES, 'UTF-8') ?>" aria-label="From date"></div>
        <div class="col-md-3"><input class="form-control" type="date" name="to" value="<?= htmlspecialchars($to, ENT_QUOTES, 'UTF-8') ?>" aria-label="To date"></div>
        <div class="col-md-1"><button class="btn btn-primary w-100">Go</button></div>
    </form>
    <div class="row g-3">
        <?php while ($event = $events->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4"><article class="card h-100 p-3">
                <p class="text-muted mb-2"><?= htmlspecialchars($event['Date_OF_Booking'] . ' · ' . $event['Time'], ENT_QUOTES, 'UTF-8') ?></p>
                <h3 class="h5"><?= htmlspecialchars($event['Name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <p class="mb-3"><?= htmlspecialchars($event['Venue'], ENT_QUOTES, 'UTF-8') ?></p>
                <a class="btn btn-primary mt-auto" href="publicEvent.php?event=<?= (int) $event['EventID'] ?>">Register / RSVP</a>
            </article></div>
        <?php endwhile; ?>
    </div>
</main>
</body>
</html>

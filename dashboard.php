<?php
include 'db.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$dashboardCounts = [];
foreach (['Event' => 'events', 'Venue' => 'venues', 'Attendee' => 'attendees', 'Organiser' => 'organisers'] as $table => $label) {
    $countResult = $conn->query("SELECT COUNT(*) AS total FROM `$table`");
    $dashboardCounts[$label] = $countResult ? (int) $countResult->fetch_assoc()['total'] : 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Event Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="ui.css" rel="stylesheet">
</head>
<body>

<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 py-2">
    <a class="navbar-brand" href="#">EventSpace</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse align-items-center" id="navbarContent">
        <!-- Left Links -->
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
            <li class="nav-item"><a class="nav-link" href="pages/events.php">Events</a></li>
            <li class="nav-item"><a class="nav-link" href="pages/venue.php">Venues</a></li>
            <li class="nav-item"><a class="nav-link" href="pages/attendee.php">Attendees</a></li>
            <li class="nav-item"><a class="nav-link" href="pages/organiser.php">Organisers</a></li>
        </ul>

        <!-- Search -->
        <form class="d-flex me-3" method="GET" action="search.php">
            <input class="form-control me-2" type="search" name="query" placeholder="Search..." aria-label="Search">
            <button class="btn btn-outline-light" type="submit">Search</button>
        </form>

        <!-- Profile -->
        <div class="d-flex align-items-center">
            <img src="PP.jpeg" alt="Profile" class="rounded-circle me-2" style="width: 35px; height: 35px; object-fit: cover;">
            <div class="dropdown">
                <a class="text-white dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    <?php echo $_SESSION['username']; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="profile.php">View Profile</a></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>



<main class="container mt-4">
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <p class="text-uppercase text-muted mb-2" style="font: 700 .72rem 'Trebuchet MS', sans-serif; letter-spacing: .12em;">Operations overview</p>
            <h1 class="mb-2">Good to see you, <?php echo htmlspecialchars($_SESSION['username']); ?>.</h1>
            <p class="text-muted mb-0">Keep the next experience moving from one calm workspace.</p>
        </div>
        <a href="pages/addEvent.php" class="btn btn-primary">+ Create event</a>
    </div>

    <div class="row row-cols-2 row-cols-lg-4 g-3 mb-4">
        <?php foreach ([['events', 'Events', 'pages/events.php'], ['venues', 'Venues', 'pages/venue.php'], ['attendees', 'Attendees', 'pages/attendee.php'], ['organisers', 'Organisers', 'pages/organiser.php']] as $metric): ?>
            <div class="col">
                <a href="<?= $metric[2] ?>" class="text-decoration-none">
                    <div class="card h-100 p-3">
                        <p class="text-muted mb-2" style="font: 700 .75rem 'Trebuchet MS', sans-serif; text-transform: uppercase; letter-spacing: .08em;"><?= $metric[1] ?></p>
                        <div class="d-flex justify-content-between align-items-end">
                            <strong style="font-size: 2rem; line-height: 1;"><?= $dashboardCounts[$metric[0]] ?></strong>
                            <span style="color: #087f73; font-size: 1.4rem;">↗</span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0" style="font-size: 1.45rem;">Manage your workspace</h3>
                <span class="text-muted" style="font: .82rem 'Trebuchet MS', sans-serif;">Core records</span>
            </div>
            <div class="row row-cols-1 row-cols-md-2 g-3">
                <div class="col"><a href="pages/events.php" class="text-decoration-none"><div class="card h-100 p-3"><h5>Events</h5><p class="text-muted mb-0">Plan dates, budgets, venues, and status.</p></div></a></div>
                <div class="col"><a href="pages/venue.php" class="text-decoration-none"><div class="card h-100 p-3"><h5>Venues</h5><p class="text-muted mb-0">Keep locations and availability current.</p></div></a></div>
                <div class="col"><a href="pages/attendee.php" class="text-decoration-none"><div class="card h-100 p-3"><h5>Attendees</h5><p class="text-muted mb-0">Track contacts and RSVP readiness.</p></div></a></div>
                <div class="col"><a href="pages/organiser.php" class="text-decoration-none"><div class="card h-100 p-3"><h5>Organisers</h5><p class="text-muted mb-0">Manage the people behind every event.</p></div></a></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100 p-4" style="background: #172126; color: #f8fbf5; border: 0;">
                <p class="text-uppercase mb-3" style="color: #9bcfc4; font: 700 .72rem 'Trebuchet MS', sans-serif; letter-spacing: .12em;">Quick find</p>
                <h3 style="color: #fff; font-size: 1.7rem;">Need a record fast?</h3>
                <p style="color: #b9c8c5;">Search across your event data from one place.</p>
                <form class="mt-auto" method="GET" action="search.php">
                    <div class="input-group">
                        <input class="form-control" type="search" name="query" placeholder="Search events, people, venues..." aria-label="Search records">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

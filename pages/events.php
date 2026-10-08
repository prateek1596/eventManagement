<?php
include '../db.php';
include '../auth.php';
session_start();
requireLogin('../login.php');
$csrf = csrfToken();
$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 8;
$where = [];

if ($search !== '') {
    $safeSearch = $conn->real_escape_string($search);
    $where[] = "(`Name` LIKE '%$safeSearch%' OR `Venue` LIKE '%$safeSearch%' OR `Contact_info` LIKE '%$safeSearch%')";
}
if ($statusFilter === '0' || $statusFilter === '1') {
    $where[] = "`Status` = " . (int) $statusFilter;
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFilter)) {
    $safeDate = $conn->real_escape_string($dateFilter);
    $where[] = "`Date_OF_Booking` = '$safeDate'";
}

$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$countResult = $conn->query("SELECT COUNT(*) AS total FROM Event$whereSql");
$totalEvents = $countResult ? (int) $countResult->fetch_assoc()['total'] : 0;
$totalPages = max(1, (int) ceil($totalEvents / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
<!-- Top Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 py-2">
    <a class="navbar-brand" href="../dashboard.php">EventSpace</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse align-items-center" id="navbarContent">
        <!-- Left Links -->
        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
            <li class="nav-item"><a class="nav-link" href="events.php">Events</a></li>
            <li class="nav-item"><a class="nav-link" href="venue.php">Venues</a></li>
            <li class="nav-item"><a class="nav-link" href="attendee.php">Attendees</a></li>
            <li class="nav-item"><a class="nav-link" href="organiser.php">Organisers</a></li>
        </ul>

        <!-- Search -->
        <form class="d-flex me-3" method="GET" action="../search.php">
            <input class="form-control me-2" type="search" name="query" placeholder="Search..." aria-label="Search">
            <button class="btn btn-outline-light" type="submit">Search</button>
        </form>

        <!-- Profile -->
        <div class="d-flex align-items-center">
            <img src="../PP.jpeg" alt="Profile" class="rounded-circle me-2" style="width: 35px; height: 35px; object-fit: cover;">
            <div class="dropdown">
                <a class="text-white dropdown-toggle" href="../profile.php" role="button" data-bs-toggle="dropdown">
                    <?php echo $_SESSION['username']; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="../profile.php">View Profile</a></li>
                    <li><a class="dropdown-item text-danger" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>


<div class="container mt-4">
    <h3 class="mb-4">All Events</h3>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success">Event updated successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Event and its feedback were deleted successfully.</div>
    <?php elseif (isset($_GET['delete_error'])): ?>
        <div class="alert alert-danger">This event could not be deleted because another record still depends on it.</div>
    <?php endif; ?>

    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="addEvent.php" class="btn btn-success">➕ Add New Event</a>
        <a href="exportEvents.php?search=<?= urlencode($search) ?>" class="btn btn-outline-secondary">Export CSV</a>
    </div>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-5"><input type="search" name="search" class="form-control" placeholder="Search name, venue, or contact" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="col-md-3"><select name="status" class="form-select"><option value="">All statuses</option><option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Status 1</option><option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Status 0</option></select></div>
        <div class="col-md-3"><input type="date" name="date" class="form-control" value="<?= htmlspecialchars($dateFilter, ENT_QUOTES, 'UTF-8') ?>"></div>
        <div class="col-md-1"><button class="btn btn-primary w-100" type="submit">Go</button></div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Event ID</th>
                <th>Name</th>
                <th>Booking Date</th>
                <th>Time</th>
                <th>Venue</th>
                <th>Contact Info</th>
                <th>Status</th>
                <th>Budget</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody class="table-dark">
            <?php
            $sql = "SELECT * FROM Event$whereSql ORDER BY Date_OF_Booking ASC, Time ASC LIMIT $offset, $perPage";
            $result = $conn->query($sql);

            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['EventID']}</td>
                        <td>{$row['Name']}</td>
                        <td>{$row['Date_OF_Booking']}</td>
                        <td>{$row['Time']}</td>
                        <td>{$row['Venue']}</td>
                        <td>{$row['Contact_info']}</td>
                        <td>";

                // Status badge colors
                if ($row['Status'] == 'Planned') {
                    echo "<span class='badge bg-warning text-dark'>Planned</span>";
                } elseif ($row['Status'] == 'Confirmed') {
                    echo "<span class='badge bg-success'>Confirmed</span>";
                } elseif ($row['Status'] == 'Cancelled') {
                    echo "<span class='badge bg-danger'>Cancelled</span>";
                } else {
                    echo $row['Status'];
                }

                echo "</td>
                        <td>₹ {$row['Budget']}</td>
                        <td>
                            <a href='editEvent.php?id={$row['EventID']}' class='btn btn-sm btn-primary'>Edit</a>
                            <form method='POST' action='deleteEvent.php' class='d-inline' onsubmit='return confirm(\"Are you sure?\")'>
                                <input type='hidden' name='event_id' value='{$row['EventID']}'>
                                <input type='hidden' name='csrf_token' value='{$csrf}'>
                                <button type='submit' class='btn btn-sm btn-danger'>Delete</button>
                            </form>
                        </td>
                    </tr>";
            }
            ?>
        </tbody>
    </table>
    <?php if ($totalEvents === 0): ?><div class="alert alert-warning">No events match your filters.</div><?php endif; ?>
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Event pages"><ul class="pagination">
            <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                <li class="page-item <?= $pageNumber === $page ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query(['search' => $search, 'status' => $statusFilter, 'date' => $dateFilter, 'page' => $pageNumber]) ?>"><?= $pageNumber ?></a></li>
            <?php endfor; ?>
        </ul></nav>
    <?php endif; ?>
</div>
</body>
</html>

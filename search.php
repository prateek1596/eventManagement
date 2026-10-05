<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get the search term and table from the URL
$searchTerm = isset($_GET['query']) ? trim($_GET['query']) : '';
$table = isset($_GET['table']) ? $_GET['table'] : '';

// Define all the available tables and their search column(s)
$availableTables = [
    'event' => ['Name', 'Venue'],
    'venue' => ['Name', 'Location'],
    'attendee' => ['Name', 'Email'],
    'organiser' => ['Name', 'Address'],
    'vendor' => ['Name', 'Services'],
    'staff' => ['Name', 'Type'],
    'task' => ['Name', 'Details'],
    'ticket' => ['Type'],
    'payment' => ['Type'],
    'sponsor' => ['Name', 'Type'],
    'feedback' => ['Details'],
    'amenities' => ['Name', 'Details'],
    'promotion' => ['Name', 'Type'],
    'schedule' => ['Name', 'Description'],
    'eventspace' => ['Chairman']
];

$results = [];

if ($searchTerm !== '') {
    $escaped = mysqli_real_escape_string($conn, $searchTerm);
    $lowerSearchTerm = strtolower($escaped);

    if (array_key_exists($lowerSearchTerm, $availableTables)) {
        // If search term matches a table name, get the whole table
        $query = "SELECT * FROM $lowerSearchTerm";
        $res = mysqli_query($conn, $query);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $row['__table'] = ucfirst($lowerSearchTerm); // For tagging the table name
                $results[] = $row;
            }
        }
    } else {
        // Search across all tables
        foreach ($availableTables as $tbl => $columns) {
            $columnList = implode(" LIKE '%$escaped%' OR ", $columns) . " LIKE '%$escaped%'";
            $query = "SELECT * FROM $tbl WHERE $columnList";
            $res = mysqli_query($conn, $query);
            if ($res) {
                while ($row = mysqli_fetch_assoc($res)) {
                    $row['__table'] = ucfirst($tbl); // For tagging the table name
                    $results[] = $row;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results - EventSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="ui.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4 py-2">
    <a class="navbar-brand" href="dashboard.php">EventSpace</a>
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


<div class="container mt-4">
    <h3 class="mb-4">Search Results for "<?php echo htmlspecialchars($searchTerm); ?>"</h3>

    <?php if (empty($results)): ?>
        <div class="alert alert-warning">No results found.</div>
    <?php else: ?>
        <table class="table table-bordered table-striped table-dark">
            <thead>
                <tr>
                    <?php foreach (array_keys($results[0]) as $column): ?>
                        <th><?php echo htmlspecialchars($column); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                            <td><?php echo htmlspecialchars($cell); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-light mt-4">🔙 Back to Dashboard</a>
</div>

</body>
</html>

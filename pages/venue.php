<?php
include '../db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$result = $conn->query("SELECT * FROM Venue");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Venues</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/ui.css" rel="stylesheet"> <!-- optional -->
    <link href="style.css" rel="stylesheet">
</head>
<body>
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

<div class="container">
    <h3 class="mb-4">
        Venues 
        <a href="addVenue.php" class="btn btn-success float-end">+ Add</a>
    </h3>

    <?php if (isset($_GET['success'])) { ?>
        <div class="alert alert-success">Venue operation successful!</div>
    <?php } ?>

    <div class="table-responsive">
        <table class="table table-bordered table-dark table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Cost</th>
                    <th>Facilities</th>
                    <th>Availability</th>
                    <th>Event ID</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['VenueID'] ?></td>
                        <td><?= $row['Name'] ?></td>
                        <td><?= $row['Location'] ?></td>
                        <td><?= $row['Cost'] ?></td>
                        <td><?= $row['Facilities'] ?></td>
                        <td><?= $row['Availability'] ? 'Available' : 'Not Available' ?></td>
                        <td><?= $row['EventID'] ?></td>
                        <td>
                            <a href="editVenue.php?id=<?= $row['VenueID'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="deleteVenue.php?id=<?= $row['VenueID'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this venue?')">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>

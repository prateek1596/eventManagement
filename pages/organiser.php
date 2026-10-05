<?php
include '../db.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}
$result = $conn->query("SELECT * FROM Organiser");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Organisers</title>
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
    <h3>Organisers <a href="addOrganiser.php" class="btn btn-success float-end">+ Add</a></h3>
    <table class="table table-bordered mt-3">
        <thead class="table-dark">
            <tr>
                <th>ID</th><th>Name</th><th>Address</th><th>Ratings</th><th>Event ID</th><th>Actions</th>
            </tr>
        </thead>
        <tbody class="table-dark">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['OrganiserID'] ?></td>
                    <td><?= $row['Name'] ?></td>
                    <td><?= $row['Address'] ?></td>
                    <td><?= $row['Rating'] ?></td>
                    <td><?= $row['EventID'] ?></td>
                    <td>
                        <a href="editOrganiser.php?id=<?= $row['OrganiserID'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <a href="deleteOrganiser.php?id=<?= $row['OrganiserID'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this organiser?')">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>

<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM Users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fallbacks for optional fields
$role = isset($user['role']) ? $user['role'] : 'N/A';
$phone = isset($user['phone']) ? $user['phone'] : 'N/A';
$name = isset($user['name']) ? $user['name'] : $user['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?= htmlspecialchars($username) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/dark-theme.css" rel="stylesheet">
    <link href="ui.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">EventSpace</a>
        <a href="logout.php" class="btn btn-danger logout-btn">Logout</a>
    </div>
</nav>

<div class="container mt-5 text-center profile-container">
    <?php
    $profileImg = file_exists("assets/img/users/{$username}.png")
        ? "assets/img/users/{$username}.png"
        : "assets/img/users/default.png";
    ?>
    <img src="PP.jpeg" alt="Profile Picture" class="profile-img mb-4" width="150" height="150">

    <h2><?= htmlspecialchars($name) ?></h2>
    <p class="email-muted"><?= htmlspecialchars($user['email']) ?></p>
    <hr class="my-4">

    <div class="text-start">
        <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Role:</strong> <?= htmlspecialchars($role) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($phone) ?></p>
        <p><strong>Joined:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
    </div>

    <div class="mt-4">
        <a href="editProfile.php" class="btn btn-outline-primary">Edit Profile</a>
    </div>
</div>

</body>
</html>

<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch current data
$stmt = $conn->prepare("SELECT * FROM Users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];

    $update = $conn->prepare("UPDATE Users SET name = ?, email = ?, role = ?, phone = ? WHERE username = ?");
    $update->bind_param("sssis", $name, $email, $role, $phone, $username);
    $update->execute();

    header("Location: profile.php?update=success");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/dark-theme.css" rel="stylesheet">
    <link href="assets/css/legacy.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5 text-light">
    <h2>Edit Profile</h2>
    <form method="POST" class="mt-4">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control bg-dark text-light" required value="<?= htmlspecialchars($user['name']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control bg-dark text-light" required value="<?= htmlspecialchars($user['email']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <input type="text" name="role" class="form-control bg-dark text-light" value="<?= htmlspecialchars($user['role']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="number" name="phone" class="form-control bg-dark text-light" value="<?= htmlspecialchars($user['phone']) ?>">
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="profile.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
</body>
</html>

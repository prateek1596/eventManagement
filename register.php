<?php
// Include the database connection
include('db.php');

// Define a variable to store the error message
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form input values
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];

    // Check if username or email already exists in the database
    $query = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $error_message = "Username or email is already taken.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user into the database (add phone and role)
        $query = "INSERT INTO users (username, password, email, name, phone, role) 
                  VALUES ('$username', '$hashed_password', '$email', '$name', '$phone', '$role')";
        if (mysqli_query($conn, $query)) {
            header('Location: login.php');
        } else {
            $error_message = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="ui.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <h2 class="mt-5">Register</h2>
    <?php if ($error_message) { echo "<div class='alert alert-danger'>$error_message</div>"; } ?>
    <form action="register.php" method="POST">
        <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" class="form-control" id="phone" name="phone" required pattern="[0-9]{10}" title="Enter a 10-digit phone number">
        </div>
        <div class="form-group">
            <label for="role">Select Role</label>
            <select class="form-control" id="role" name="role" required>
                <option value="">-- Select Role --</option>
                <option value="Admin">Admin</option>
                <option value="Organizer">Organizer</option>
                <option value="Staff">Staff</option>
            </select>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

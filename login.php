<?php
session_start();
include ('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM Users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $username;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="glassstyle.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="circle blue"></div>
        <div class="circle orange"></div>
        <div class="glass-card">
            <h2>Login Here</h2>
            <?php if (isset($error)) echo "<div class='alert'>$error</div>"; ?>
            <form method="POST">
                <label for="username">Username</label>
                <input type="text" name="username" placeholder="Username" required>

                <label for="password">Password</label>
                <input type="password" name="password" placeholder="Password" required>

                <button type="submit" class="login-btn">Log In</button>

                <div class="register-text">
                    <p>Don't have an account? <a href="register.php">Register</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

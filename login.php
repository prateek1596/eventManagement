<?php
session_start();
include ('db.php');
include ('auth.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare('SELECT username, password, role FROM users WHERE username = ? LIMIT 1');
    if (!$stmt) {
        $error = 'Login is not configured yet. Create the users table using database.sql.';
    } else {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                session_regenerate_id(true);
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                csrfToken();
                header('Location: dashboard.php');
                exit();
            }
        } else {
            $error = 'Invalid username or password';
        }
        $error = 'Invalid username or password';
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in | EventSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="glassstyle.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="circle blue"></div>
        <div class="circle orange"></div>
        <div class="glass-card">
            <p class="eyebrow">EVENTSPACE / OPERATIONS</p>
            <h1>Welcome back.</h1>
            <p class="intro">Sign in to keep your events moving.</p>
            <?php if (isset($error)) echo '<div class="alert" role="alert">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>'; ?>
            <form method="POST">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Your username" autocomplete="username" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Your password" autocomplete="current-password" required>

                <button type="submit" class="login-btn">Sign in <span aria-hidden="true">→</span></button>

                <div class="register-text">
                    <p>New to EventSpace? <a href="register.php">Create an account</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

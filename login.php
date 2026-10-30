<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

start_eventspace_session();
if (current_user()) {
    header('Location: dashboard.php');
    exit();
}

if (request_is_post()) {
    verify_csrf_token();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = strtolower(trim((string) ($_POST['role'] ?? '')));
    $allowedRoles = ['organizer', 'attendee', 'staff'];

    $stmt = $conn->prepare('SELECT username, password, role FROM users WHERE username = ? AND LOWER(role) = ? LIMIT 1');
    if ($stmt && $username !== '' && is_string($password) && in_array($role, $allowedRoles, true)) {
        $stmt->bind_param('ss', $username, $role);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                session_regenerate_id(true);
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                csrfToken();
                header('Location: dashboard.php');
                exit();
            }
        }
        $stmt->close();
    }
    $error = 'Invalid username or password.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in | EventSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/auth.css" rel="stylesheet">
</head>
<body class="auth-page">
    <main class="auth-layout">
        <section class="auth-art">
            <a class="auth-brand" href="login.php"><span class="auth-mark">✦</span> EventSpace</a>
            <div class="auth-copy"><p class="auth-kicker">Event operations, reimagined</p><h1>Bring people<br><em>together beautifully.</em></h1><p>One calm, considered place to plan experiences, manage guests, and keep every moving part in rhythm.</p></div>
        </section>
        <section class="auth-panel">
            <div class="auth-form">
                <p class="auth-kicker">Welcome back</p>
                <h2>Sign in to your studio.</h2>
                <p class="intro">Keep your events moving with clarity and confidence.</p>
                <?php if (isset($error)) echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>'; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="mb-3"><label class="form-label" for="username">Username</label><input class="form-control" type="text" id="username" name="username" placeholder="Your username" autocomplete="username" required></div>
                    <div class="mb-3"><label class="form-label" for="role">Sign in as</label><select class="form-select" id="role" name="role" required><option value="">Choose your role</option><option value="organizer">Organizer</option><option value="attendee">Attendee</option><option value="staff">Staff</option></select></div>
                    <div class="mb-4"><label class="form-label" for="password">Password</label><input class="form-control" type="password" id="password" name="password" placeholder="Your password" autocomplete="current-password" required></div>
                    <button type="submit" class="btn btn-primary w-100">Sign in <span aria-hidden="true">→</span></button>
                    <p class="text-muted small mt-4 mb-0">New to EventSpace? <a class="auth-link" href="register.php">Create an account</a></p>
                </form>
            </div>
        </section>
    </main>
    <script src="assets/js/app.js"></script>
</body>
</html>

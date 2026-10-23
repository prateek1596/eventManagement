<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

start_eventspace_session();
$error_message = '';

if (request_is_post()) {
    verify_csrf_token();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $email = trim((string) ($_POST['email'] ?? ''));
    $name = trim((string) ($_POST['name'] ?? ''));
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $role = $_POST['role'] ?? 'Staff';
    $allowedRoles = ['Organizer', 'Staff'];

    if (!in_array($role, $allowedRoles, true)) {
        $error_message = 'Choose a valid account role.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,50}$/', $username)) {
        $error_message = 'Username must be 3-50 characters using letters, numbers, or underscores.';
    } elseif ($name === '' || mb_strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Enter a valid name and email address.';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error_message = 'Phone number must contain 10 digits.';
    } elseif (strlen($password) < 8) {
        $error_message = 'Password must be at least 8 characters.';
    }

    if ($error_message === '') {
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
        if (!$stmt) {
            $error_message = 'Registration is temporarily unavailable.';
        } else {
            $stmt->bind_param('ss', $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($result->num_rows > 0) {
                $error_message = 'Username or email is already taken.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insert = $conn->prepare('INSERT INTO users (username, password, email, name, phone, role) VALUES (?, ?, ?, ?, ?, ?)');
                if ($insert) {
                    $insert->bind_param('ssssss', $username, $hashedPassword, $email, $name, $phone, $role);
                }
                if (!$insert || !$insert->execute()) {
                    $error_message = 'Unable to create the account.';
                } else {
                    $insert->close();
                    header('Location: login.php?registered=1');
                    exit();
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create account | EventSpace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="ui.css" rel="stylesheet">
</head>
<body>
<main class="container auth-shell">
    <p class="text-uppercase text-muted mb-2" style="font-size:.72rem;font-weight:800;letter-spacing:.14em;color:#635bff !important;">EventSpace / Team access</p>
    <h1 class="mt-4">Create your account</h1>
    <p class="text-muted">Set up access to your event workspace.</p>
    <?php if ($error_message): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form action="register.php" method="POST" novalidate>
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <div class="mb-3"><label class="form-label" for="name">Full name</label><input type="text" class="form-control" id="name" name="name" maxlength="100" required></div>
        <div class="mb-3"><label class="form-label" for="username">Username</label><input type="text" class="form-control" id="username" name="username" pattern="[A-Za-z0-9_]{3,50}" maxlength="50" autocomplete="username" required></div>
        <div class="mb-3"><label class="form-label" for="email">Email address</label><input type="email" class="form-control" id="email" name="email" maxlength="150" autocomplete="email" required></div>
        <div class="mb-3"><label class="form-label" for="phone">Phone number</label><input type="tel" class="form-control" id="phone" name="phone" pattern="[0-9]{10}" maxlength="10" required></div>
        <div class="mb-3"><label class="form-label" for="role">Role</label><select class="form-select" id="role" name="role" required><option value="">Choose a role</option><option value="Organizer">Organizer</option><option value="Staff">Staff</option></select></div>
        <div class="mb-3"><label class="form-label" for="password">Password</label><input type="password" class="form-control" id="password" name="password" minlength="8" autocomplete="new-password" required></div>
        <button type="submit" class="btn btn-primary">Create account</button>
        <a class="btn btn-link" href="login.php">Back to sign in</a>
    </form>
</main>
</body>
</html>

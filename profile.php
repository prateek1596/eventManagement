<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';

start_eventspace_session();
require_auth('login.php');
$username = (string) $_SESSION['username'];
$stmt = $conn->prepare('SELECT * FROM Users WHERE username = ?');
$stmt->bind_param('s', $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc() ?: [];
$role = $user['role'] ?? 'N/A';
$phone = $user['phone'] ?? 'N/A';
$name = $user['name'] ?? $username;
$pageTitle = 'Profile';
require __DIR__ . '/includes/header.php';
?>
<section class="page-heading-row mb-4">
    <div><p class="app-eyebrow">Your identity</p><h1 class="mb-2">Profile settings</h1><p class="app-muted mb-0">Keep your workspace details current.</p></div>
    <a href="<?= e(eventspace_url('editProfile.php')) ?>" class="btn app-btn-primary"><i class="bi bi-pencil me-1"></i>Edit profile</a>
</section>
<section class="profile-studio app-card">
    <div class="profile-studio-top">
        <img src="<?= e(eventspace_url('PP.jpeg')) ?>" alt="Profile picture for <?= e($name) ?>" class="profile-studio-image" width="112" height="112">
        <div><p class="app-eyebrow mb-1">Team member</p><h2 class="mb-1"><?= e($name) ?></h2><p class="app-muted mb-0"><?= e($user['email'] ?? '') ?></p></div>
    </div>
    <div class="profile-details">
        <div><span>Username</span><strong><?= e($username) ?></strong></div>
        <div><span>Role</span><strong><?= e($role) ?></strong></div>
        <div><span>Phone</span><strong><?= e($phone) ?></strong></div>
        <div><span>Joined</span><strong><?= e($user['created_at'] ?? 'N/A') ?></strong></div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>

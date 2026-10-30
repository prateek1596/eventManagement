<?php
$user = current_user();
$role = strtolower((string) ($user['role'] ?? ''));
$navItems = [
    ['dashboard.php', 'grid-1x2', 'Dashboard'],
    ['pages/catalog.php', 'compass', 'Discover'],
    ['pages/feedback.php', 'chat-heart', 'Feedback'],
    ['pages/ai-insights.php', 'stars', 'AI insights'],
];
if ($role !== 'attendee') {
    $navItems = array_merge($navItems, [
        ['pages/events.php', 'calendar-event', 'Events'],
        ['pages/venue.php', 'building', 'Venues'],
        ['pages/attendee.php', 'people', 'Attendees'],
    ]);
}
if ($role === 'organizer' || $role === 'staff') {
    $navItems[] = ['pages/organiser.php', 'person-badge', 'Organisers'];
}
?>
<div class="sidebar-backdrop" data-sidebar-backdrop></div>
<aside class="app-sidebar" data-sidebar aria-label="Primary navigation">
    <div class="sidebar-brand">
        <a href="<?= e(eventspace_url('dashboard.php')) ?>"><span class="brand-mark">✦</span><span>EventSpace</span></a>
        <button class="sidebar-close d-lg-none" type="button" data-sidebar-close aria-label="Close navigation"><i class="bi bi-x-lg"></i></button>
    </div>
    <p class="sidebar-label">Workspace</p>
    <nav class="sidebar-nav">
        <?php foreach ($navItems as [$path, $icon, $label]): ?>
            <a class="sidebar-link <?= is_active_path($path) ? 'active' : '' ?>" href="<?= e(eventspace_url($path)) ?>">
                <i class="bi bi-<?= e($icon) ?>" aria-hidden="true"></i><span><?= e($label) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-bottom">
        <a class="sidebar-link" href="<?= e(eventspace_url('pages/notifications.php')) ?>"><i class="bi bi-bell" aria-hidden="true"></i><span>Notifications</span></a>
        <a class="sidebar-link" href="<?= e(eventspace_url('profile.php')) ?>"><i class="bi bi-gear" aria-hidden="true"></i><span>Settings</span></a>
        <a class="sidebar-link sidebar-logout" href="<?= e(eventspace_url('logout.php')) ?>"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span>Sign out</span></a>
    </div>
</aside>
<header class="app-topbar">
    <button class="sidebar-toggle d-lg-none" type="button" data-sidebar-open aria-label="Open navigation"><i class="bi bi-list"></i></button>
    <form class="topbar-search" method="GET" action="<?= e(eventspace_url('search.php')) ?>" role="search">
        <i class="bi bi-search" aria-hidden="true"></i>
        <input name="query" type="search" placeholder="Search events, venues, people..." aria-label="Global search">
        <kbd>⌘ K</kbd>
    </form>
    <div class="topbar-actions">
        <button class="icon-button" type="button" data-theme-toggle aria-label="Toggle dark mode"><i class="bi bi-moon-stars"></i></button>
        <button class="icon-button notification-button" type="button" data-notification-toggle aria-expanded="false" aria-controls="notification-popover" aria-label="Open notifications"><i class="bi bi-bell"></i><span class="notification-dot"></span></button>
        <div class="dropdown">
            <button class="user-menu dropdown-toggle" data-bs-toggle="dropdown" type="button">
                <span class="user-avatar"><?= e(strtoupper(substr($user['username'] ?? 'U', 0, 1))) ?></span>
                <span class="d-none d-sm-block text-start"><strong><?= e($user['username'] ?? 'Guest') ?></strong><small><?= e($user['role'] ?? 'Member') ?></small></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= e(eventspace_url('profile.php')) ?>"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="<?= e(eventspace_url('logout.php')) ?>"><i class="bi bi-box-arrow-right me-2"></i>Sign out</a></li>
            </ul>
        </div>
    </div>
</header>
<aside id="notification-popover" class="notification-popover" data-notification-popover hidden aria-label="Notifications">
    <div class="notification-popover-head"><div><p class="app-eyebrow mb-1">Your inbox</p><h2 class="h5 mb-0">Notifications</h2></div><button type="button" class="notification-close" data-notification-close aria-label="Close notifications"><i class="bi bi-x-lg"></i></button></div>
    <div class="notification-preview"><i class="bi bi-calendar-heart"></i><div><strong>Stay close to your next event.</strong><p class="mb-0">Open your notifications to see reminders and booking updates.</p></div></div>
    <a class="notification-view-all" href="<?= e(eventspace_url('pages/notifications.php')) ?>">View all notifications <i class="bi bi-arrow-up-right"></i></a>
</aside>

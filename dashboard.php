<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';

start_eventspace_session();
require_auth('login.php');

$pageTitle = 'Dashboard';
$includeCharts = true;
$username = (string) ($_SESSION['username'] ?? 'there');
$dashboardCounts = ['events' => 0, 'venues' => 0, 'attendees' => 0, 'organisers' => 0];
$countTables = ['events' => 'Event', 'venues' => 'Venue', 'attendees' => 'Attendee', 'organisers' => 'Organiser'];
foreach ($countTables as $label => $table) {
    $result = $conn->query("SELECT COUNT(*) AS total FROM `$table`");
    if ($result) {
        $dashboardCounts[$label] = (int) $result->fetch_assoc()['total'];
    }
}

$notificationCount = 0;
$notificationStmt = $conn->prepare('SELECT COUNT(*) AS total FROM event_notifications WHERE Username = ? AND Is_Read = 0');
if ($notificationStmt) {
    $notificationStmt->bind_param('s', $username);
    $notificationStmt->execute();
    $notificationCount = (int) $notificationStmt->get_result()->fetch_assoc()['total'];
    $notificationStmt->close();
}

$confirmedRsvps = 0;
$waitlistedRsvps = 0;
$rsvpStats = $conn->query("SELECT RSVP_Status, COUNT(*) AS total FROM event_rsvps GROUP BY RSVP_Status");
if ($rsvpStats) {
    while ($rsvpStat = $rsvpStats->fetch_assoc()) {
        if ($rsvpStat['RSVP_Status'] === 'Confirmed') {
            $confirmedRsvps = (int) $rsvpStat['total'];
        }
        if ($rsvpStat['RSVP_Status'] === 'Waitlisted') {
            $waitlistedRsvps = (int) $rsvpStat['total'];
        }
    }
}

$upcomingEvents = [];
$upcomingResult = $conn->query("SELECT EventID, Name, Date_OF_Booking, Time, Venue, Capacity FROM Event WHERE Date_OF_Booking >= CURDATE() ORDER BY Date_OF_Booking, Time LIMIT 5");
if ($upcomingResult) {
    while ($row = $upcomingResult->fetch_assoc()) {
        $upcomingEvents[] = $row;
    }
}

$recentAttendees = [];
$attendeeResult = $conn->query("SELECT AttendeeID, Name, Email, RSVP FROM Attendee LIMIT 5");
if ($attendeeResult) {
    while ($row = $attendeeResult->fetch_assoc()) {
        $recentAttendees[] = $row;
    }
}

$bookingLabels = [];
$bookingValues = [];
$bookingResult = $conn->query("SELECT DATE_FORMAT(Date_OF_Booking, '%b') AS month_label, COUNT(*) AS total FROM Event WHERE Date_OF_Booking >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) GROUP BY YEAR(Date_OF_Booking), MONTH(Date_OF_Booking) ORDER BY YEAR(Date_OF_Booking), MONTH(Date_OF_Booking)");
if ($bookingResult) {
    while ($row = $bookingResult->fetch_assoc()) {
        $bookingLabels[] = $row['month_label'];
        $bookingValues[] = (int) $row['total'];
    }
}

$categoryLabels = ['Events'];
$categoryValues = [$dashboardCounts['events']];
$revenue = 0.0;
$revenueResult = $conn->query("SELECT COALESCE(SUM(t.Price), 0) AS revenue FROM event_tickets t INNER JOIN event_rsvps r ON r.Ticket_ID = t.Ticket_ID WHERE r.Payment_Status = 'Paid'");
if ($revenueResult) {
    $revenue = (float) $revenueResult->fetch_assoc()['revenue'];
}

require __DIR__ . '/includes/header.php';
?>
<section class="dashboard-hero app-card mb-4">
    <div>
        <p class="app-eyebrow">Friday, your event command centre</p>
        <h1 class="display-5 mb-2">Make every gathering<br><em>feel effortless.</em></h1>
        <p class="app-muted mb-0">Welcome back, <?= e($username) ?>. Here is the pulse of your workspace.</p>
    </div>
    <div class="dashboard-hero-actions d-flex flex-wrap gap-2">
        <a class="btn btn-light border" href="<?= e(eventspace_url('pages/addVenue.php')) ?>"><i class="bi bi-building-add me-1"></i>Add venue</a>
        <a class="btn app-btn-primary" href="<?= e(eventspace_url('pages/addEvent.php')) ?>"><i class="bi bi-plus-lg me-1"></i>Create an event</a>
    </div>
</section>

<section class="row g-3 mb-4" aria-label="Key performance indicators">
    <?php
    $kpis = [
        ['Total events', $dashboardCounts['events'], 'calendar-event', 'pages/events.php', 'All events'],
        ['Upcoming events', count($upcomingEvents), 'calendar-check', 'pages/catalog.php', 'Next experiences'],
        ['Total attendees', $dashboardCounts['attendees'], 'people', 'pages/attendee.php', 'Guest records'],
        ['Revenue', '₹' . number_format($revenue, 0), 'graph-up-arrow', 'pages/analytics.php', 'Paid ticket revenue'],
    ];
    foreach ($kpis as $index => [$label, $value, $icon, $href, $caption]):
    ?>
    <div class="col-6 col-xl-3">
        <a class="text-decoration-none" href="<?= e(eventspace_url($href)) ?>">
            <article class="app-card app-card-hover h-100 metric-card metric-card-<?= $index ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-icon"><i class="bi bi-<?= e($icon) ?>"></i></span>
                    <span class="kpi-trend"><i class="bi bi-arrow-up-right"></i></span>
                </div>
                <p class="app-muted small mb-1 mt-3"><?= e($label) ?></p>
                <strong class="kpi-value"><?= e((string) $value) ?></strong>
                <small class="app-muted d-block mt-2"><?= e($caption) ?></small>
            </article>
        </a>
    </div>
    <?php endforeach; ?>
</section>

<section class="row g-4 mb-4">
    <div class="col-xl-8">
        <article class="app-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div><p class="app-eyebrow mb-1">Your momentum</p><h2 class="h4 mb-0">Bookings over time</h2></div>
                <span class="chart-caption">Last 6 months</span>
            </div>
            <div class="chart-container"><canvas id="bookingsChart" aria-label="Bookings over the last six months" role="img"></canvas></div>
        </article>
    </div>
    <div class="col-xl-4">
        <article class="app-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div><p class="app-eyebrow mb-1">Your mix</p><h2 class="h4 mb-0">Events by category</h2></div>
            </div>
            <div class="chart-container chart-container-doughnut"><canvas id="categoryChart" aria-label="Events by category" role="img"></canvas></div>
            <p class="chart-empty app-muted small text-center mb-0" hidden>Category data will appear as events are categorized.</p>
        </article>
    </div>
</section>

<section class="row g-4 mb-4">
    <div class="col-xl-7">
        <article class="app-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div><p class="app-eyebrow mb-1">On the horizon</p><h2 class="h4 mb-0">Upcoming events</h2></div>
                <a class="small fw-bold" href="<?= e(eventspace_url('pages/events.php')) ?>">View all</a>
            </div>
            <?php if (!$upcomingEvents): ?>
                <div class="app-empty"><i class="bi bi-calendar2-plus fs-2 d-block mb-2"></i>No upcoming events yet.</div>
            <?php else: ?>
                <div class="dashboard-event-list">
                <?php foreach ($upcomingEvents as $event): ?>
                    <a class="dashboard-event" href="<?= e(eventspace_url('pages/publicEvent.php?event=' . (int) $event['EventID'])) ?>">
                        <div class="event-thumb" role="img" aria-label="Event image placeholder"></div>
                        <div class="flex-grow-1 min-width-0"><strong class="d-block text-truncate"><?= e($event['Name']) ?></strong><small class="app-muted"><?= e(date('M j, Y', strtotime((string) $event['Date_OF_Booking']))) ?> · <?= e($event['Venue']) ?></small></div>
                        <span class="event-arrow"><i class="bi bi-arrow-up-right"></i></span>
                    </a>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>
    </div>
    <div class="col-xl-5">
        <article class="app-card insight-card h-100">
            <p class="app-eyebrow mb-1">Smart insights</p>
            <h2 class="h4 mb-3">Signals worth your attention</h2>
            <div class="insight-item"><i class="bi bi-lightning-charge"></i><span><?= $confirmedRsvps > 0 ? e($confirmedRsvps . ' confirmed guests are ready to attend.') : 'Confirm your first RSVP to start seeing booking momentum.' ?></span></div>
            <div class="insight-item"><i class="bi bi-hourglass-split"></i><span><?= $waitlistedRsvps > 0 ? e($waitlistedRsvps . ' guests are currently waitlisted.') : 'No waitlist pressure detected across current events.' ?></span></div>
            <div class="insight-item"><i class="bi bi-stars"></i><span>Keep event capacity and ticket data current for sharper recommendations.</span></div>
        </article>
    </div>
</section>

<section class="app-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><p class="app-eyebrow mb-1">Guest pulse</p><h2 class="h4 mb-0">Recent attendees</h2></div>
        <a class="small fw-bold" href="<?= e(eventspace_url('pages/attendee.php')) ?>">Manage attendees</a>
    </div>
    <?php if (!$recentAttendees): ?>
        <div class="app-empty">No attendee records yet.</div>
    <?php else: ?>
        <div class="table-responsive dashboard-table"><table class="table align-middle mb-0">
            <thead><tr><th>Name</th><th>Email</th><th>RSVP</th><th class="text-end">ID</th></tr></thead>
            <tbody><?php foreach ($recentAttendees as $attendee): ?><tr><td><span class="table-avatar"><?= e(strtoupper(substr((string) $attendee['Name'], 0, 1))) ?></span><?= e($attendee['Name']) ?></td><td class="app-muted"><?= e($attendee['Email']) ?></td><td><span class="status-badge <?= !empty($attendee['RSVP']) ? 'status-success' : 'status-muted' ?>"><?= !empty($attendee['RSVP']) ? 'Confirmed' : 'Pending' ?></span></td><td class="text-end app-muted">#<?= (int) $attendee['AttendeeID'] ?></td></tr><?php endforeach; ?></tbody>
        </table></div>
    <?php endif; ?>
</section>

<script>
window.eventSpaceDashboardData = {
    bookingLabels: <?= json_encode($bookingLabels, JSON_UNESCAPED_UNICODE) ?>,
    bookingValues: <?= json_encode($bookingValues) ?>,
    categoryLabels: <?= json_encode($categoryLabels, JSON_UNESCAPED_UNICODE) ?>,
    categoryValues: <?= json_encode($categoryValues) ?>
};
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>

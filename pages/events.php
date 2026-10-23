<?php
declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/helpers.php';

start_eventspace_session();
require_auth('../login.php');
$csrf = csrf_token();
$pageTitle = 'Events';

$view = ($_GET['view'] ?? 'grid') === 'calendar' ? 'calendar' : (($_GET['view'] ?? 'grid') === 'table' ? 'table' : 'grid');
$sort = $_GET['sort'] ?? 'date';
$sortSql = ['date' => 'Date_OF_Booking ASC, Time ASC', 'name' => 'Name ASC', 'budget' => 'Budget DESC'][$sort] ?? 'Date_OF_Booking ASC, Time ASC';
$calendarMonth = $_GET['month'] ?? date('Y-m');
$calendarDate = DateTime::createFromFormat('!Y-m', $calendarMonth);
if (!$calendarDate || $calendarDate->format('Y-m') !== $calendarMonth) {
    $calendarDate = new DateTime('first day of this month');
}

$calendarEvents = [];
$calendarStmt = $conn->prepare('SELECT EventID, Name, Date_OF_Booking, Time, Venue FROM Event WHERE Date_OF_Booking >= ? AND Date_OF_Booking < ? ORDER BY Date_OF_Booking, Time');
$calendarStart = $calendarDate->format('Y-m-01');
$calendarEnd = (clone $calendarDate)->modify('first day of next month')->format('Y-m-01');
$calendarStmt->bind_param('ss', $calendarStart, $calendarEnd);
$calendarStmt->execute();
$calendarResult = $calendarStmt->get_result();
while ($calendarEvent = $calendarResult->fetch_assoc()) {
    $calendarEvents[$calendarEvent['Date_OF_Booking']][] = $calendarEvent;
}
$calendarStmt->close();

$search = trim($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$venueFilter = trim($_GET['venue'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 8;
$where = [];

if ($search !== '') {
    $safeSearch = $conn->real_escape_string($search);
    $where[] = "(`Name` LIKE '%$safeSearch%' OR `Venue` LIKE '%$safeSearch%' OR `Contact_info` LIKE '%$safeSearch%')";
}
if ($statusFilter === '0' || $statusFilter === '1') {
    $where[] = '`Status` = ' . (int) $statusFilter;
}
if ($venueFilter !== '') {
    $where[] = "Venue = '" . $conn->real_escape_string($venueFilter) . "'";
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFilter)) {
    $where[] = "`Date_OF_Booking` = '" . $conn->real_escape_string($dateFilter) . "'";
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';
$countResult = $conn->query("SELECT COUNT(*) AS total FROM Event$whereSql");
$totalEvents = $countResult ? (int) $countResult->fetch_assoc()['total'] : 0;
$totalPages = max(1, (int) ceil($totalEvents / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;
$events = [];
$venueOptions = [];
$venueResult = $conn->query("SELECT DISTINCT Venue FROM Event WHERE Venue IS NOT NULL AND Venue <> '' ORDER BY Venue");
if ($venueResult) {
    while ($venueRow = $venueResult->fetch_assoc()) {
        $venueOptions[] = (string) $venueRow['Venue'];
    }
}
$eventResult = $conn->query("SELECT EventID, Name, Date_OF_Booking, Time, Venue, Contact_info, Status, Budget, Capacity, COALESCE((SELECT COUNT(*) FROM event_rsvps r WHERE r.EventID = Event.EventID AND r.RSVP_Status IN ('Pending', 'Confirmed')), 0) AS Seats_Booked FROM Event$whereSql ORDER BY $sortSql LIMIT $offset, $perPage");
if ($eventResult) {
    while ($row = $eventResult->fetch_assoc()) {
        $events[] = $row;
    }
}

$queryParams = ['search' => $search, 'status' => $statusFilter, 'date' => $dateFilter, 'venue' => $venueFilter, 'sort' => $sort];
require __DIR__ . '/../includes/header.php';
?>
<section class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div><p class="app-eyebrow">Workspace / Events</p><h1 class="mb-2">All events</h1><p class="app-muted mb-0">Plan, publish, and keep every experience on track.</p></div>
    <a href="<?= e(eventspace_url('pages/addEvent.php')) ?>" class="btn app-btn-primary"><i class="bi bi-plus-lg me-1"></i>New event</a>
</section>

<?php if (isset($_GET['success'])): ?><div class="alert alert-success">Event updated successfully.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">Event and its feedback were deleted successfully.</div><?php elseif (isset($_GET['delete_error'])): ?><div class="alert alert-danger">This event could not be deleted because another record still depends on it.</div><?php endif; ?>

<section class="app-card mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="view" value="<?= e($view) ?>">
        <div class="col-12 col-lg-5"><label class="form-label small app-muted" for="event-search">Search events</label><input id="event-search" type="search" name="search" class="form-control" placeholder="Name, venue, or contact" value="<?= e($search) ?>"></div>
        <div class="col-6 col-lg-2"><label class="form-label small app-muted" for="event-status">Status</label><select id="event-status" name="status" class="form-select"><option value="">All statuses</option><option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Active</option><option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Inactive</option></select></div>
        <div class="col-6 col-lg-2"><label class="form-label small app-muted" for="event-date">Date</label><input id="event-date" type="date" name="date" class="form-control" value="<?= e($dateFilter) ?>"></div>
        <div class="col-6 col-lg-2"><label class="form-label small app-muted" for="event-venue">Venue</label><select id="event-venue" name="venue" class="form-select"><option value="">All venues</option><?php foreach ($venueOptions as $venue): ?><option value="<?= e($venue) ?>" <?= $venueFilter === $venue ? 'selected' : '' ?>><?= e($venue) ?></option><?php endforeach; ?></select></div>
        <div class="col-8 col-lg-1"><label class="form-label small app-muted" for="event-sort">Sort</label><select id="event-sort" name="sort" class="form-select"><option value="date" <?= $sort === 'date' ? 'selected' : '' ?>>Date</option><option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Name</option><option value="budget" <?= $sort === 'budget' ? 'selected' : '' ?>>Budget</option></select></div>
        <div class="col-4 col-lg-1"><button class="btn app-btn-primary w-100" type="submit">Go</button></div>
    </form>
</section>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div class="btn-group" role="group" aria-label="Event view">
        <a class="btn <?= $view === 'grid' ? 'app-btn-primary' : 'btn-light border' ?>" href="?<?= http_build_query($queryParams + ['view' => 'grid']) ?>"><i class="bi bi-grid me-1"></i>Grid</a>
        <a class="btn <?= $view === 'table' ? 'app-btn-primary' : 'btn-light border' ?>" href="?<?= http_build_query($queryParams + ['view' => 'table']) ?>"><i class="bi bi-list me-1"></i>Table</a>
        <a class="btn <?= $view === 'calendar' ? 'app-btn-primary' : 'btn-light border' ?>" href="?<?= http_build_query($queryParams + ['view' => 'calendar']) ?>"><i class="bi bi-calendar3 me-1"></i>Calendar</a>
    </div>
    <div class="d-flex gap-2"><a href="exportEvents.php?search=<?= urlencode($search) ?>" class="btn btn-light border"><i class="bi bi-download me-1"></i>Export</a><a href="checkin.php" class="btn btn-light border">Guest check-in</a></div>
</div>

<?php if ($view === 'calendar'): ?>
    <section class="app-card">
        <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h4 mb-0"><?= e($calendarDate->format('F Y')) ?></h2><div class="btn-group"><a class="btn btn-light border btn-sm" href="?view=calendar&month=<?= e((clone $calendarDate)->modify('-1 month')->format('Y-m')) ?>">Previous</a><a class="btn btn-light border btn-sm" href="?view=calendar&month=<?= e(date('Y-m')) ?>">Today</a><a class="btn btn-light border btn-sm" href="?view=calendar&month=<?= e((clone $calendarDate)->modify('+1 month')->format('Y-m')) ?>">Next</a></div></div>
        <div class="table-responsive"><table class="table align-middle text-center"><thead><tr><?php foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $weekday): ?><th><?= e($weekday) ?></th><?php endforeach; ?></tr></thead><tbody><tr>
        <?php for ($blank = 0; $blank < (int) $calendarDate->format('w'); $blank++): ?><td class="calendar-cell"></td><?php endfor; ?>
        <?php for ($day = 1; $day <= (int) $calendarDate->format('t'); $day++): $calendarDay = $calendarDate->format('Y-m-') . str_pad((string) $day, 2, '0', STR_PAD_LEFT); ?><td class="calendar-cell text-start"><strong><?= $day ?></strong><?php foreach ($calendarEvents[$calendarDay] ?? [] as $event): ?><a class="calendar-event" href="publicEvent.php?event=<?= (int) $event['EventID'] ?>"><?= e($event['Name']) ?></a><?php endforeach; ?></td><?php if (((int) $calendarDate->format('w') + $day) % 7 === 0 && $day !== (int) $calendarDate->format('t')): ?></tr><tr><?php endif; ?><?php endfor; ?>
        </tr></tbody></table></div>
    </section>
<?php elseif ($view === 'table'): ?>
    <section class="app-card">
        <?php if (!$events): ?><div class="app-empty"><i class="bi bi-calendar-x fs-2 d-block mb-2"></i>No events match your filters.</div><?php else: ?><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Event</th><th>Date</th><th>Venue</th><th>Status</th><th>Budget</th><th class="text-end">Actions</th></tr></thead><tbody><?php foreach ($events as $event): ?><tr><td><strong><?= e($event['Name']) ?></strong><small class="d-block app-muted">#<?= (int) $event['EventID'] ?></small></td><td><?= e(date('M j, Y', strtotime((string) $event['Date_OF_Booking']))) ?><small class="d-block app-muted"><?= e($event['Time']) ?></small></td><td><?= e($event['Venue']) ?></td><td><span class="status-badge <?= (int) $event['Status'] === 1 ? 'status-success' : 'status-muted' ?>"><?= (int) $event['Status'] === 1 ? 'Active' : 'Inactive' ?></span></td><td>₹<?= e(number_format((float) $event['Budget'], 0)) ?></td><td class="text-end"><a class="btn btn-sm btn-light border" href="publicEvent.php?event=<?= (int) $event['EventID'] ?>">View</a> <a class="btn btn-sm btn-light border" href="editEvent.php?id=<?= (int) $event['EventID'] ?>">Edit</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
    </section>
<?php else: ?>
    <?php if (!$events): ?><div class="app-empty"><i class="bi bi-calendar-x fs-2 d-block mb-2"></i><h2 class="h4">No events found</h2><p class="mb-0">Try changing your filters or create a new event.</p></div><?php else: ?><div class="row g-4"><?php foreach ($events as $event): $capacity = $event['Capacity'] !== null ? (int) $event['Capacity'] : 0; $seatsBooked = (int) $event['Seats_Booked']; $progress = $capacity > 0 ? min(100, (int) round(($seatsBooked / $capacity) * 100)) : 0; $almostFull = $capacity > 0 && $progress >= 80; ?><div class="col-md-6 col-xl-4"><article class="event-management-card app-card app-card-hover h-100 p-0 overflow-hidden"><div class="management-cover"><img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80" alt="<?= e($event['Name']) ?> cover image" onerror="this.style.display='none'"><span class="event-category">Event</span><?php if ($almostFull): ?><span class="almost-full">Almost full</span><?php endif; ?></div><div class="p-3 d-flex flex-column h-100"><div class="d-flex justify-content-between gap-2"><div><h2 class="h4 mb-1"><?= e($event['Name']) ?></h2><p class="app-muted small mb-2"><i class="bi bi-calendar3 me-1"></i><?= e(date('M j, Y', strtotime((string) $event['Date_OF_Booking']))) ?> · <?= e($event['Time']) ?></p></div><span class="status-badge <?= (int) $event['Status'] === 1 ? 'status-success' : 'status-muted' ?>"><?= (int) $event['Status'] === 1 ? 'Active' : 'Inactive' ?></span></div><p class="app-muted small"><i class="bi bi-geo-alt me-1"></i><?= e($event['Venue']) ?></p><?php if ($capacity > 0): ?><div class="d-flex justify-content-between small mb-1"><span class="app-muted">Seats left</span><strong><?= max(0, $capacity - $seatsBooked) ?>/<?= $capacity ?></strong></div><div class="progress event-progress" aria-label="<?= $progress ?> percent booked"><div class="progress-bar" style="width:<?= $progress ?>%"></div></div><?php else: ?><p class="app-muted small mb-3">Unlimited capacity</p><?php endif; ?><div class="d-flex gap-2 mt-auto pt-3"><a class="btn btn-sm app-btn-primary flex-grow-1" href="publicEvent.php?event=<?= (int) $event['EventID'] ?>">View event</a><a class="btn btn-sm btn-light border" href="editEvent.php?id=<?= (int) $event['EventID'] ?>" aria-label="Edit <?= e($event['Name']) ?>"><i class="bi bi-pencil"></i></a></div></div></article></div><?php endforeach; ?></div><?php endif; ?>
<?php endif; ?>

<?php if ($view !== 'calendar' && $totalPages > 1): ?><nav class="mt-4" aria-label="Event pages"><ul class="pagination"><?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?><li class="page-item <?= $pageNumber === $page ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query($queryParams + ['view' => $view, 'page' => $pageNumber]) ?>"><?= $pageNumber ?></a></li><?php endfor; ?></ul></nav><?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>

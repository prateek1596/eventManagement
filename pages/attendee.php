<?php
declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/helpers.php';

start_eventspace_session();
require_auth('../login.php');
$pageTitle = 'Attendees';
$search = trim($_GET['search'] ?? '');
$where = '';
if ($search !== '') {
    $safeSearch = $conn->real_escape_string($search);
    $where = " WHERE Name LIKE '%$safeSearch%' OR Email LIKE '%$safeSearch%' OR Phone_No LIKE '%$safeSearch%'";
}
$result = $conn->query("SELECT * FROM Attendee$where ORDER BY Name ASC");
require __DIR__ . '/../includes/header.php';
?>
<section class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div><p class="app-eyebrow">Workspace / People</p><h1 class="mb-2">Attendees</h1><p class="app-muted mb-0">Keep guest details and RSVP readiness in one place.</p></div>
    <a href="<?= e(eventspace_url('pages/addAttendee.php')) ?>" class="btn app-btn-primary"><i class="bi bi-person-plus me-1"></i>Add attendee</a>
</section>
<section class="app-card mb-4">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-8"><label class="form-label small app-muted" for="attendee-search">Search attendees</label><input id="attendee-search" class="form-control" type="search" name="search" value="<?= e($search) ?>" placeholder="Search by name, email, or phone"></div>
        <div class="col-md-2"><button class="btn app-btn-primary w-100" type="submit"><i class="bi bi-search me-1"></i>Search</button></div>
        <div class="col-md-2"><a class="btn btn-light border w-100" href="exportAttendees.php"><i class="bi bi-download me-1"></i>Export CSV</a></div>
    </form>
</section>
<section class="app-card">
    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h4 mb-0">Guest directory</h2><span class="app-muted small"><?= $result ? $result->num_rows : 0 ?> records</span></div>
    <?php if (!$result || $result->num_rows === 0): ?>
        <div class="app-empty"><i class="bi bi-people fs-2 d-block mb-2"></i><h2 class="h4">No attendees found</h2><p class="mb-0">Add a guest or try a broader search.</p></div>
    <?php else: ?>
        <div class="table-responsive dashboard-table"><table class="table align-middle mb-0"><thead><tr><th>Attendee</th><th>Email</th><th>Phone</th><th>RSVP</th><th class="text-end">Actions</th></tr></thead><tbody>
        <?php while ($row = $result->fetch_assoc()): ?><tr><td><span class="table-avatar"><?= e(strtoupper(substr((string) $row['Name'], 0, 1))) ?></span><strong><?= e($row['Name']) ?></strong><small class="d-block app-muted ms-5">#<?= (int) $row['AttendeeID'] ?></small></td><td class="app-muted"><?= e($row['Email']) ?></td><td class="app-muted"><?= e($row['Phone_No']) ?></td><td><span class="status-badge <?= !empty($row['RSVP']) ? 'status-success' : 'status-muted' ?>"><?= !empty($row['RSVP']) ? 'Confirmed' : 'Pending' ?></span></td><td class="text-end"><a href="editAttendee.php?id=<?= (int) $row['AttendeeID'] ?>" class="btn btn-sm btn-light border">Edit</a><a href="deleteAttendee.php?id=<?= (int) $row['AttendeeID'] ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Delete this attendee?')">Delete</a></td></tr><?php endwhile; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

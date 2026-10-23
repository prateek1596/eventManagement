<?php
declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/helpers.php';

start_eventspace_session();
require_auth('../login.php');
$pageTitle = 'Organisers';
$search = trim($_GET['search'] ?? '');
$where = '';
if ($search !== '') {
    $safeSearch = $conn->real_escape_string($search);
    $where = " WHERE Name LIKE '%$safeSearch%' OR Address LIKE '%$safeSearch%'";
}
$result = $conn->query("SELECT * FROM Organiser$where ORDER BY Name ASC");
require __DIR__ . '/../includes/header.php';
?>
<section class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div><p class="app-eyebrow">Workspace / Partners</p><h1 class="mb-2">Organisers</h1><p class="app-muted mb-0">Manage the people creating memorable experiences.</p></div>
    <a href="<?= e(eventspace_url('pages/addOrganiser.php')) ?>" class="btn app-btn-primary"><i class="bi bi-person-badge me-1"></i>Add organiser</a>
</section>
<section class="app-card mb-4">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-8"><label class="form-label small app-muted" for="organiser-search">Search organisers</label><input id="organiser-search" class="form-control" type="search" name="search" value="<?= e($search) ?>" placeholder="Search by name or address"></div>
        <div class="col-md-2"><button class="btn app-btn-primary w-100" type="submit"><i class="bi bi-search me-1"></i>Search</button></div>
        <div class="col-md-2"><a class="btn btn-light border w-100" href="exportOrganisers.php"><i class="bi bi-download me-1"></i>Export CSV</a></div>
    </form>
</section>
<section class="app-card">
    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h4 mb-0">Organiser directory</h2><span class="app-muted small"><?= $result ? $result->num_rows : 0 ?> records</span></div>
    <?php if (!$result || $result->num_rows === 0): ?>
        <div class="app-empty"><i class="bi bi-person-badge fs-2 d-block mb-2"></i><h2 class="h4">No organisers found</h2><p class="mb-0">Add a partner or try a broader search.</p></div>
    <?php else: ?>
        <div class="table-responsive dashboard-table"><table class="table align-middle mb-0"><thead><tr><th>Organiser</th><th>Address</th><th>Rating</th><th>Event</th><th class="text-end">Actions</th></tr></thead><tbody>
        <?php while ($row = $result->fetch_assoc()): ?><tr><td><span class="table-avatar"><?= e(strtoupper(substr((string) $row['Name'], 0, 1))) ?></span><strong><?= e($row['Name']) ?></strong><small class="d-block app-muted ms-5">#<?= (int) $row['OrganiserID'] ?></small></td><td class="app-muted"><?= e($row['Address']) ?></td><td><span class="rating-stars" aria-label="<?= e((string) $row['Rating']) ?> out of 5"><?= str_repeat('★', max(0, min(5, (int) round((float) $row['Rating'])))) ?></span><span class="app-muted small ms-1"><?= e((string) $row['Rating']) ?></span></td><td class="app-muted">#<?= (int) $row['EventID'] ?></td><td class="text-end"><a href="editOrganiser.php?id=<?= (int) $row['OrganiserID'] ?>" class="btn btn-sm btn-light border">Edit</a><form method="POST" action="deleteOrganiser.php" class="d-inline" onsubmit="return confirm('Delete this organiser?')"><input type="hidden" name="id" value="<?= (int) $row['OrganiserID'] ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button type="submit" class="btn btn-sm btn-outline-danger ms-1">Delete</button></form></td></tr><?php endwhile; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>

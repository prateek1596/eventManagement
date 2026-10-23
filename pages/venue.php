<?php
declare(strict_types=1);

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../includes/helpers.php';

start_eventspace_session();
require_auth('../login.php');
$pageTitle = 'Venues';
$search = trim($_GET['search'] ?? '');
$where = '';
if ($search !== '') {
    $safeSearch = $conn->real_escape_string($search);
    $where = " WHERE Name LIKE '%$safeSearch%' OR Location LIKE '%$safeSearch%' OR Facilities LIKE '%$safeSearch%'";
}
$result = $conn->query("SELECT * FROM Venue$where ORDER BY Name ASC");
require __DIR__ . '/../includes/header.php';
?>
<section class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div><p class="app-eyebrow">Workspace / Places</p><h1 class="mb-2">Venues</h1><p class="app-muted mb-0">Find the right setting for every gathering.</p></div>
    <a href="<?= e(eventspace_url('pages/addVenue.php')) ?>" class="btn app-btn-primary"><i class="bi bi-building-add me-1"></i>Add venue</a>
</section>
<section class="app-card mb-4">
    <form class="row g-2 align-items-end" method="GET">
        <div class="col-md-8"><label class="form-label small app-muted" for="venue-search">Search venues</label><input id="venue-search" class="form-control" type="search" name="search" value="<?= e($search) ?>" placeholder="Search by venue, location, or facilities"></div>
        <div class="col-md-2"><button class="btn app-btn-primary w-100" type="submit"><i class="bi bi-search me-1"></i>Search</button></div>
        <div class="col-md-2"><a class="btn btn-light border w-100" href="?export=csv"><i class="bi bi-download me-1"></i>Export CSV</a></div>
    </form>
</section>
<?php if (!$result || $result->num_rows === 0): ?>
    <div class="app-empty"><i class="bi bi-building fs-2 d-block mb-2"></i><h2 class="h4">No venues found</h2><p class="mb-0">Add a venue or try a broader search.</p></div>
<?php else: ?>
    <div class="row g-4">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="col-md-6 col-xl-4"><article class="app-card app-card-hover h-100 p-0 overflow-hidden"><div class="venue-cover"><img src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=900&q=80" alt="<?= e($row['Name']) ?> venue" onerror="this.style.display='none'"><span class="venue-status <?= !empty($row['Availability']) ? 'status-success' : 'status-muted' ?>"><?= !empty($row['Availability']) ? 'Available' : 'Unavailable' ?></span></div><div class="p-3"><div class="d-flex justify-content-between gap-2"><h2 class="h4 mb-1"><?= e($row['Name']) ?></h2><span class="venue-capacity"><i class="bi bi-people me-1"></i><?= e((string) ($row['Capacity'] ?? '—')) ?></span></div><p class="app-muted small mb-2"><i class="bi bi-geo-alt me-1"></i><?= e($row['Location']) ?></p><p class="app-muted small mb-3"><?= e($row['Facilities']) ?></p><div class="d-flex justify-content-between align-items-center"><span class="fw-bold">₹<?= e(number_format((float) $row['Cost'], 0)) ?></span><div><a href="editVenue.php?id=<?= (int) $row['VenueID'] ?>" class="btn btn-sm btn-light border">Edit</a><a href="deleteVenue.php?id=<?= (int) $row['VenueID'] ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Delete this venue?')">Delete</a></div></div></div></article></div>
    <?php endwhile; ?>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>

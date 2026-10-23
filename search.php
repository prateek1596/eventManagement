<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/includes/helpers.php';
start_eventspace_session();
require_auth('login.php');
$searchTerm = trim((string) ($_GET['query'] ?? ''));
$availableTables = ['event'=>['Name','Venue'],'venue'=>['Name','Location'],'attendee'=>['Name','Email'],'organiser'=>['Name','Address'],'vendor'=>['Name','Services'],'staff'=>['Name','Type'],'task'=>['Name','Details'],'ticket'=>['Type'],'payment'=>['Type'],'sponsor'=>['Name','Type'],'feedback'=>['Details'],'amenities'=>['Name','Details'],'promotion'=>['Name','Type'],'schedule'=>['Name','Description'],'eventspace'=>['Chairman']];
$results = [];
if ($searchTerm !== '') {
    $escaped = $conn->real_escape_string($searchTerm);
    $table = strtolower($searchTerm);
    $tables = array_key_exists($table, $availableTables) ? [$table] : array_keys($availableTables);
    foreach ($tables as $tbl) {
        $conditions = implode(" LIKE '%{$escaped}%' OR ", $availableTables[$tbl]) . " LIKE '%{$escaped}%'";
        $res = $conn->query("SELECT * FROM {$tbl}" . (array_key_exists($table, $availableTables) ? '' : " WHERE {$conditions}"));
        if ($res) while ($row = $res->fetch_assoc()) { $row['__table'] = ucfirst($tbl); $results[] = $row; }
    }
}
$pageTitle = 'Search';
require __DIR__ . '/includes/header.php';
?>
<section class="page-heading-row mb-4"><div><p class="app-eyebrow">Workspace search</p><h1 class="mb-2">Search results</h1><p class="app-muted mb-0">Showing matches for “<?= e($searchTerm ?: 'everything') ?>”.</p></div></section>
<form class="app-card p-3 mb-4" method="GET" action="<?= e(eventspace_url('search.php')) ?>"><div class="input-group"><label class="visually-hidden" for="global-search-page">Search</label><input id="global-search-page" class="form-control" type="search" name="query" value="<?= e($searchTerm) ?>" placeholder="Search events, venues, people..."><button class="btn app-btn-primary" type="submit"><i class="bi bi-search me-1"></i>Search</button></div></form>
<?php if (!$results): ?><div class="app-card app-empty"><i class="bi bi-search fs-2 d-block mb-2"></i><h2 class="h5">No matches found</h2><p class="app-muted mb-0">Try a broader keyword or search a different area.</p></div>
<?php else: ?><div class="app-card table-responsive"><table class="table app-table align-middle mb-0"><thead><tr><?php foreach (array_keys($results[0]) as $column): ?><th><?= e($column) ?></th><?php endforeach; ?></tr></thead><tbody><?php foreach ($results as $row): ?><tr><?php foreach ($row as $cell): ?><td><?= e((string) $cell) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>

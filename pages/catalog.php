<?php
include '../db.php';
include '../auth.php';
session_start();
requireLogin('../login.php');
require_once __DIR__ . '/../includes/helpers.php';

$query = trim($_GET['q'] ?? '');
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$location = trim((string) ($_GET['location'] ?? ''));
$maxPrice = filter_var($_GET['max_price'] ?? null, FILTER_VALIDATE_FLOAT);
$where = ['Date_OF_Booking >= CURDATE()'];
$params = [];
$types = '';

if ($query !== '') {
    $where[] = '(Name LIKE ? OR Venue LIKE ? OR Contact_info LIKE ?)';
    $term = '%' . $query . '%';
    $params = [$term, $term, $term];
    $types = 'sss';
}
if ($location !== '') {
    $where[] = 'Venue LIKE ?';
    $params[] = '%' . $location . '%';
    $types .= 's';
}
if ($maxPrice !== false && $maxPrice !== null) {
    $where[] = 'Budget <= ?';
    $params[] = (float) $maxPrice;
    $types .= 'd';
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) {
    $where[] = 'Date_OF_Booking >= ?';
    $params[] = $from;
    $types .= 's';
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
    $where[] = 'Date_OF_Booking <= ?';
    $params[] = $to;
    $types .= 's';
}

$stmt = $conn->prepare('SELECT EventID, Name, Date_OF_Booking, Time, Venue, Contact_info, Image_Path FROM Event WHERE ' . implode(' AND ', $where) . ' ORDER BY Date_OF_Booking, Time');
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$events = $stmt->get_result();
$pageTitle = 'Discover';
$includeAi = true;
require __DIR__ . '/../includes/header.php';
?>
    <section class="discover-hero app-card mb-5">
        <div>
            <p class="text-uppercase mb-2" style="letter-spacing:.16em;font-size:.75rem;font-weight:800;color:#edc77f">Curated experiences</p>
            <h1>Make room for moments worth remembering.</h1>
            <p>Find intimate gatherings, bold celebrations, and the people who make every occasion feel effortless.</p>
            <a class="btn btn-light mt-2" href="#event-list">Explore events <span aria-hidden="true">↓</span></a>
        </div>
    </section>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><p class="text-uppercase text-muted mb-1" style="letter-spacing:.14em;font-size:.72rem;font-weight:800">Discover</p><h1 class="mb-0">Upcoming events</h1></div>
        <a class="btn btn-outline-secondary" href="../dashboard.php">Dashboard</a>
    </div>
    <form class="row g-2 mb-4" data-natural-search>
        <div class="col-12"><label class="visually-hidden" for="natural-query">Describe the event you want</label><div class="input-group"><span class="input-group-text">✦</span><input id="natural-query" class="form-control" name="natural_query" placeholder="Try: music events in Goa under 3000"><button class="btn btn-primary" type="submit">Smart search</button></div><div data-search-status class="small text-muted mt-2" hidden aria-live="polite"></div></div>
    </form>
    <form class="row g-2 mb-4">
        <div class="col-md-5"><input class="form-control" name="q" value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search event, venue, or contact"></div>
        <div class="col-md-3"><input class="form-control" type="date" name="from" value="<?= htmlspecialchars($from, ENT_QUOTES, 'UTF-8') ?>" aria-label="From date"></div>
        <div class="col-md-3"><input class="form-control" type="date" name="to" value="<?= htmlspecialchars($to, ENT_QUOTES, 'UTF-8') ?>" aria-label="To date"></div>
        <div class="col-md-1"><button class="btn btn-primary w-100">Go</button></div>
    </form>
    <div id="event-list" class="row g-4">
        <?php while ($event = $events->fetch_assoc()): ?>
            <div class="col-md-6 col-lg-4"><article class="card event-card event-card h-100">
                <div class="event-image">
                    <img src="<?= e($event['Image_Path'] ? eventspace_url($event['Image_Path']) : 'https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=900&q=80') ?>" alt="<?= htmlspecialchars($event['Name'], ENT_QUOTES, 'UTF-8') ?> event" onerror="this.style.display='none'">
                </div>
                <div class="event-copy d-flex flex-column h-100">
                    <p class="text-muted mb-2 small"><?= htmlspecialchars($event['Date_OF_Booking'] . ' · ' . $event['Time'], ENT_QUOTES, 'UTF-8') ?></p>
                    <h3 class="h5"><?= htmlspecialchars($event['Name'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p class="mb-3"><?= htmlspecialchars($event['Venue'], ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="btn btn-primary mt-auto" href="publicEvent.php?event=<?= (int) $event['EventID'] ?>">Register / RSVP</a>
                </div>
            </article></div>
        <?php endwhile; ?>
    </div>
    <?php if ($events->num_rows === 0): ?>
        <div class="card p-5 text-center">
            <h2>No events found yet</h2>
            <p class="text-muted mb-0">Try a broader search or check back soon for new experiences.</p>
        </div>
    <?php endif; ?>

    <section class="row g-4 mt-5 align-items-start">
        <div class="col-lg-7">
            <div class="card p-4">
                <p class="text-uppercase text-muted mb-1" style="letter-spacing:.13em;font-size:.72rem;font-weight:800">Plan with clarity</p>
                <h2>Meet your AI event planner</h2>
                <p class="text-muted">Describe the occasion, and get a practical first plan in seconds.</p>
                <form data-ai-planner>
                    <label class="visually-hidden" for="planner-prompt">Describe your event</label>
                    <div class="input-group">
                        <input id="planner-prompt" name="prompt" class="form-control" placeholder="Birthday for 50 people, ₹80k budget" required>
                        <button class="btn btn-primary" type="submit">Build plan</button>
                    </div>
                    <div data-planner-result class="alert alert-success mt-3 mb-0" hidden aria-live="polite"></div>
                </form>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card p-4">
                <h2 class="h4">A little help, whenever you need it.</h2>
                <p class="text-muted mb-0">Ask about events, tickets, venues, or booking steps.</p>
            </div>
        </div>
    </section>

<button type="button" data-ai-chat-toggle class="btn btn-primary position-fixed shadow-lg" style="right:20px;bottom:20px;border-radius:999px;z-index:5" aria-label="Open AI event assistant">✦ Ask EventSpace AI</button>
<aside data-ai-chat class="card position-fixed p-3" style="right:20px;bottom:78px;width:min(360px,calc(100vw - 40px));z-index:6" hidden aria-label="AI event assistant">
    <div class="d-flex justify-content-between align-items-center mb-2"><h2 class="h5 mb-0">EventSpace AI</h2><span class="badge bg-success">Online</span></div>
    <div data-chat-log class="small mb-2"><p class="text-muted">Hi! What can I help you plan?</p></div>
    <form class="input-group"><label class="visually-hidden" for="chat-question">Ask a question</label><input id="chat-question" class="form-control" placeholder="Ask about your booking"><button class="btn btn-primary" type="submit">Send</button></form>
</aside>
<script src="<?= e(eventspace_url('components/ai-planner.js')) ?>"></script>
<script src="<?= e(eventspace_url('components/ai-chat.js')) ?>"></script>
<script src="<?= e(eventspace_url('components/natural-search.js')) ?>"></script>
<?php require __DIR__ . '/../includes/footer.php'; ?>

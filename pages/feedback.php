<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/includes/auth_guard.php';
require_once dirname(__DIR__) . '/includes/helpers.php';
require_once dirname(__DIR__) . '/includes/flash.php';
require_once dirname(__DIR__) . '/includes/sentiment.php';

start_eventspace_session();
require_auth('../login.php');

$user = current_user();
$message = '';
$error = '';

if (request_is_post()) {
    verify_csrf_token();
    $eventId = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $comments = trim((string) ($_POST['comments'] ?? ''));

    if (!$eventId || !$rating || $rating < 1 || $rating > 5 || mb_strlen($comments) > 2000) {
        $error = 'Choose an event, a rating from 1 to 5, and keep comments under 2,000 characters.';
    } else {
        $eventCheck = $conn->prepare('SELECT EventID FROM Event WHERE EventID = ?');
        $eventCheck->bind_param('i', $eventId);
        $eventCheck->execute();
        $eventExists = $eventCheck->get_result()->num_rows === 1;
        $eventCheck->close();
        if (!$eventExists) {
            $error = 'That event is no longer available.';
        } else {
            $email = '';
            $emailStmt = $conn->prepare('SELECT email FROM users WHERE username = ? LIMIT 1');
            $emailStmt->bind_param('s', $user['username']);
            $emailStmt->execute();
            $emailRow = $emailStmt->get_result()->fetch_assoc();
            $emailStmt->close();
            $email = (string) ($emailRow['email'] ?? '');
            $sentiment = classify_feedback_sentiment($comments, $rating);
            $insert = $conn->prepare('INSERT INTO event_feedback (EventID, Guest_Email, Rating, Comments, Sentiment) VALUES (?, ?, ?, ?, ?)');
            if (!$insert) {
                $error = 'Feedback is temporarily unavailable. Please try again.';
            } else {
                $insert->bind_param('isiss', $eventId, $email, $rating, $comments, $sentiment);
                if ($insert->execute()) {
                    $message = 'Thanks for sharing. Your feedback was classified as ' . $sentiment . '.';
                } else {
                    $error = 'We could not save your feedback. Please try again.';
                }
                $insert->close();
            }
        }
    }
}

$events = [];
$eventResult = $conn->query('SELECT EventID, Name, Date_OF_Booking, Venue FROM Event ORDER BY Date_OF_Booking DESC, Name ASC');
if ($eventResult) {
    while ($event = $eventResult->fetch_assoc()) {
        $events[] = $event;
    }
}

$summary = [];
$recentFeedback = [];
if (in_array(strtolower((string) $user['role']), ['organizer', 'staff'], true)) {
    $summaryResult = $conn->query('SELECT Sentiment, COUNT(*) AS total FROM event_feedback GROUP BY Sentiment ORDER BY total DESC');
    if ($summaryResult) {
        while ($row = $summaryResult->fetch_assoc()) {
            $summary[] = $row;
        }
    }
    $recentResult = $conn->query('SELECT f.Rating, f.Comments, f.Sentiment, f.Created_At, e.Name AS Event_Name FROM event_feedback f INNER JOIN Event e ON e.EventID = f.EventID ORDER BY f.Created_At DESC LIMIT 10');
    if ($recentResult) {
        while ($row = $recentResult->fetch_assoc()) {
            $recentFeedback[] = $row;
        }
    }
}

$pageTitle = 'Feedback';
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="page-heading-row mb-4">
    <div><p class="app-eyebrow">Listen and learn</p><h1 class="h2 mb-1">Event feedback</h1><p class="app-muted mb-0">Share what worked and help make the next experience better.</p></div>
</section>
<?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<div class="row g-4">
    <div class="col-lg-5">
        <article class="app-card">
            <p class="app-eyebrow">Your voice</p><h2 class="h4">How was your event?</h2>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="mb-3"><label class="form-label" for="event_id">Event</label><select class="form-select" id="event_id" name="event_id" required><option value="">Choose an event</option><?php foreach ($events as $event): ?><option value="<?= (int) $event['EventID'] ?>"><?= e($event['Name'] . ' · ' . $event['Date_OF_Booking']) ?></option><?php endforeach; ?></select></div>
                <div class="mb-3"><label class="form-label" for="rating">Rating</label><select class="form-select" id="rating" name="rating" required><option value="">Choose a rating</option><option value="5">5 — Excellent</option><option value="4">4 — Good</option><option value="3">3 — Okay</option><option value="2">2 — Needs improvement</option><option value="1">1 — Poor</option></select></div>
                <div class="mb-3"><label class="form-label" for="comments">Comments <span class="app-muted">(optional)</span></label><textarea class="form-control" id="comments" name="comments" rows="5" maxlength="2000" placeholder="Tell us what stood out..."></textarea></div>
                <button class="btn app-btn-primary" type="submit">Submit feedback</button>
            </form>
        </article>
    </div>
    <?php if ($summary): ?>
    <div class="col-lg-7">
        <article class="app-card mb-4"><p class="app-eyebrow">AI-assisted insight</p><h2 class="h4">Sentiment snapshot</h2><div class="row g-3"><?php foreach ($summary as $item): ?><div class="col-6 col-md-3"><div class="app-card p-3"><strong class="d-block"><?= (int) $item['total'] ?></strong><small class="app-muted"><?= e($item['Sentiment']) ?></small></div></div><?php endforeach; ?></div><p class="app-muted small mb-0 mt-3">Classification combines the rating with transparent keyword scoring and does not send guest comments to a third party.</p></article>
        <article class="app-card"><h2 class="h4 mb-3">Recent feedback</h2><?php if (!$recentFeedback): ?><p class="app-muted mb-0">No feedback yet.</p><?php else: ?><div class="table-responsive"><table class="table dashboard-table align-middle mb-0"><thead><tr><th>Event</th><th>Rating</th><th>Sentiment</th><th>Comment</th></tr></thead><tbody><?php foreach ($recentFeedback as $item): ?><tr><td><?= e($item['Event_Name']) ?></td><td><?= (int) $item['Rating'] ?>/5</td><td><span class="status-badge status-success"><?= e($item['Sentiment']) ?></span></td><td><?= e($item['Comments'] ?: 'No comment') ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></article>
    </div>
    <?php endif; ?>
</div>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>

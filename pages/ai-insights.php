<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/db.php';
require_once dirname(__DIR__) . '/includes/auth_guard.php';
require_once dirname(__DIR__) . '/includes/helpers.php';

start_eventspace_session();
require_auth('../login.php');
$pageTitle = 'AI insights';
$includeAi = true;
require dirname(__DIR__) . '/includes/header.php';
?>
<section class="page-heading-row mb-4">
    <div><p class="app-eyebrow">Intelligence layer</p><h1 class="h2 mb-1">AI &amp; ML insights</h1><p class="app-muted mb-0">Forecast attendance, spot event risks, and turn feedback into action.</p></div>
    <span class="badge rounded-pill text-bg-light border p-2"><i class="bi bi-shield-check me-1"></i>Private workspace data</span>
</section>
<div id="ai-insights-status" class="alert alert-info" aria-live="polite">Calculating signals from your events...</div>
<section id="ai-insights-content" class="row g-4" hidden>
    <div class="col-md-4"><article class="app-card h-100"><p class="app-eyebrow">ML baseline</p><h2 class="h5">Average confirmed attendance</h2><strong id="average-attendance" class="display-5">0</strong><p class="app-muted mb-0">Historical confirmed RSVPs per event.</p></article></div>
    <div class="col-md-8"><article class="app-card h-100"><p class="app-eyebrow">Recommendation engine</p><h2 class="h5">Upcoming event signals</h2><div id="event-recommendations"></div></article></div>
    <div class="col-12"><article class="app-card"><p class="app-eyebrow">Feedback intelligence</p><h2 class="h5">Sentiment distribution</h2><div id="sentiment-summary" class="row g-3"></div><p class="app-muted small mb-0 mt-3">Sentiment is classified locally from ratings and transparent keyword rules; guest comments are not sent to the AI provider.</p></article></div>
</section>
<script>
(function () {
    var status = document.getElementById("ai-insights-status");
    var content = document.getElementById("ai-insights-content");
    var base = window.EVENTSPACE_BASE_URL || "/eventManagement";
    fetch(base + "/api/ai/insights.php", { headers: { "Accept": "application/json" } })
        .then(function (response) { if (!response.ok) throw new Error("Insights are unavailable."); return response.json(); })
        .then(function (data) {
            document.getElementById("average-attendance").textContent = data.average_attendance || "0";
            document.getElementById("event-recommendations").innerHTML = (data.recommendations || []).map(function (item) {
                var rate = item.fill_rate === null ? "No capacity set" : item.fill_rate + "% full";
                var match = item.personal_match ? " · Matches your history" : "";
                return '<div class="d-flex justify-content-between align-items-center border-bottom py-2 gap-3"><div><strong>' + escapeHtml(item.Name) + '</strong><small class="app-muted d-block">' + escapeHtml(item.Date_OF_Booking) + ' · ' + escapeHtml(item.Venue) + '</small></div><span class="badge ' + (item.signal === "Needs promotion" ? "text-bg-warning" : "text-bg-success") + '">' + escapeHtml(item.signal) + ' · ' + rate + escapeHtml(match) + '</span></div>';
            }).join("") || '<p class="app-muted mb-0">Create events and collect RSVPs to generate signals.</p>';
            document.getElementById("sentiment-summary").innerHTML = (data.sentiment || []).map(function (item) {
                return '<div class="col-6 col-md-3"><div class="border rounded p-3"><strong class="d-block">' + item.total + '</strong><span class="app-muted">' + escapeHtml(item.label) + '</span></div></div>';
            }).join("") || '<p class="app-muted mb-0">No feedback has been submitted yet.</p>';
            status.hidden = true;
            content.hidden = false;
        })
        .catch(function (error) { status.className = "alert alert-warning"; status.textContent = error.message; });
    function escapeHtml(value) { return String(value || "").replace(/[&<>"']/g, function (character) { return {"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[character]; }); }
}());
</script>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>

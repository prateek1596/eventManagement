<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/app.php';
require_once dirname(__DIR__, 2) . '/db.php';
require_once dirname(__DIR__, 2) . '/includes/auth_guard.php';
require_once dirname(__DIR__, 2) . '/includes/ai_client.php';

start_eventspace_session();
header('Content-Type: application/json; charset=utf-8');

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['error' => 'POST is required']);
    exit;
}
if (!current_user()) {
    http_response_code(401);
    echo json_encode(['error' => 'Sign in to use the assistant.']);
    exit;
}

$payload = json_decode(file_get_contents('php://input') ?: '', true);
$message = is_array($payload) ? trim((string) ($payload['message'] ?? '')) : '';
if ($message === '' || mb_strlen($message) > 1000) {
    http_response_code(422);
    echo json_encode(['error' => 'Ask a question between 1 and 1,000 characters.']);
    exit;
}

$events = [];
$eventResult = $conn->query("SELECT Name, Date_OF_Booking, Venue, Capacity FROM Event WHERE Date_OF_Booking >= CURDATE() ORDER BY Date_OF_Booking LIMIT 8");
if ($eventResult) {
    while ($event = $eventResult->fetch_assoc()) {
        $events[] = $event;
    }
}
$context = json_encode(['upcoming_events' => $events], JSON_UNESCAPED_UNICODE);
$fallback = 'I can help with upcoming events, venues, RSVPs, tickets, and planning. Try asking about a specific event or how to organize one.';

$content = eventspace_ai_completion([
    ['role' => 'system', 'content' => 'You are EventSpace AI, a concise event-management assistant. Answer only from the supplied workspace context or general event-planning knowledge. Never claim to change records, send messages, or complete bookings. If workspace data is insufficient, say so and suggest the relevant page. Workspace context: ' . $context],
    ['role' => 'user', 'content' => $message],
], 0.3);

echo json_encode(['answer' => $content ?: $fallback, 'source' => $content ? 'ai' : 'local']);

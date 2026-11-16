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

verify_csrf_token($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

$payload = json_decode(file_get_contents('php://input') ?: '', true);
$message = is_array($payload) ? trim((string) ($payload['message'] ?? '')) : '';
if ($message === '' || mb_strlen($message) > 1000) {
    http_response_code(422);
    echo json_encode(['error' => 'Ask a question between 1 and 1,000 characters.']);
    exit;
}

$history = [];
if (is_array($payload['history'] ?? null)) {
    foreach (array_slice($payload['history'], -6) as $entry) {
        if (!is_array($entry) || !in_array($entry['role'] ?? '', ['user', 'assistant'], true)) {
            continue;
        }
        $historyMessage = trim((string) ($entry['content'] ?? ''));
        if ($historyMessage !== '' && mb_strlen($historyMessage) <= 1000) {
            $history[] = [
                'role' => $entry['role'],
                'content' => $historyMessage,
            ];
        }
    }
}

$events = [];
$eventResult = $conn->query("SELECT Name, Date_OF_Booking, Venue, Capacity FROM Event WHERE Date_OF_Booking >= CURDATE() ORDER BY Date_OF_Booking LIMIT 8");
if ($eventResult) {
    while ($event = $eventResult->fetch_assoc()) {
        $events[] = $event;
    }
}
$sentimentSummary = [];
$sentimentResult = $conn->query('SELECT Sentiment, COUNT(*) AS total FROM event_feedback GROUP BY Sentiment ORDER BY total DESC');
if ($sentimentResult) {
    while ($sentiment = $sentimentResult->fetch_assoc()) {
        $sentimentSummary[] = [
            'label' => (string) $sentiment['Sentiment'],
            'total' => (int) $sentiment['total'],
        ];
    }
}
$siteKnowledge = [
    'purpose' => 'EventSpace is an event-management workspace for discovering events, creating and editing events, managing venues, attendees, organisers, RSVPs, tickets, tasks, notifications, analytics, and feedback.',
    'navigation' => [
        'Discover' => 'Browse upcoming events, use filters or smart search, RSVP, and open the AI assistant.',
        'Events' => 'Create, edit, publish, and manage event capacity, budgets, dates, venues, and images.',
        'Venues' => 'Review and manage venue availability, facilities, capacity, and cost.',
        'Attendees' => 'View attendee details and RSVP status.',
        'Feedback' => 'Submit event ratings and comments; sentiment is classified as Positive, Neutral, Negative, or Mixed.',
        'AI & ML insights' => 'View attendance signals, event recommendations, and aggregate feedback sentiment.',
    ],
    'ai_tools' => 'EventSpace also provides an event planner, event description assistance, natural-language event search, event brief extraction, and sentiment analysis.',
    'safety' => 'The assistant can explain the site and provide guidance, but it cannot change records, send messages, complete bookings, or access private comments.',
];
$context = json_encode([
    'site_knowledge' => $siteKnowledge,
    'user_role' => current_user()['role'],
    'upcoming_events' => $events,
    'feedback_sentiment_summary' => $sentimentSummary,
], JSON_UNESCAPED_UNICODE);
$fallback = eventspace_local_assistant_answer($message, $events, $sentimentSummary);

$messages = [
    ['role' => 'system', 'content' => 'You are EventSpace AI, the helpful in-product assistant for the EventSpace website. Explain how the site works in plain language and guide users to the correct page. Use the supplied site knowledge and workspace data. If a feature or record is not in the context, say so instead of inventing it. You may summarize aggregate feedback sentiment counts, but do not infer individual identities or quote private comments. Never claim to change records, send messages, complete bookings, or perform actions you cannot actually perform. Keep answers under 160 words unless the user asks for a plan. Workspace context: ' . $context],
];
foreach ($history as $entry) {
    $messages[] = $entry;
}
$messages[] = ['role' => 'user', 'content' => $message];

$content = eventspace_ai_completion($messages, 0.3);

echo json_encode(['answer' => $content ?: $fallback, 'source' => $content ? 'ai' : 'local']);

function eventspace_local_assistant_answer(string $message, array $events, array $sentimentSummary): string
{
    $query = strtolower($message);
    if (preg_match('/\b(what|tell|explain).*(eventspace|site|website)|how does.*work/', $query)) {
        return 'EventSpace helps you discover events, manage event details, venues, attendees, RSVPs, tickets, tasks, analytics, and feedback. Use Discover to browse events, Events to manage them, and AI & ML insights to review attendance and sentiment.';
    }
    if (preg_match('/\b(feedback|sentiment|review|opinion|feel|happy)\b/', $query)) {
        if (!$sentimentSummary) {
            return 'No feedback has been submitted yet. Open Feedback to rate an event and add a comment; EventSpace will classify it as Positive, Neutral, Negative, or Mixed.';
        }
        $parts = [];
        foreach ($sentimentSummary as $item) {
            $parts[] = $item['label'] . ': ' . $item['total'];
        }
        return 'The current feedback sentiment distribution is ' . implode(', ', $parts) . '. Open AI & ML insights or Feedback for the full snapshot.';
    }
    if (preg_match('/\b(find|browse|discover|search|upcoming)\b/', $query)) {
        if (!$events) {
            return 'There are no upcoming events available right now. Open Discover to search or check back after events are added.';
        }
        $names = array_map(static fn (array $event): string => (string) $event['Name'], array_slice($events, 0, 3));
        return 'You can find upcoming events on Discover using the search box, date filters, or Smart search. Some upcoming events include ' . implode(', ', $names) . '.';
    }
    if (preg_match('/\b(rsvp|register|ticket|booking)\b/', $query)) {
        return 'Open Discover, choose an event, and select Register / RSVP. The event page shows the available booking options. You can manage event tickets from the Tickets page.';
    }
    if (preg_match('/\b(create|add|edit|manage).*(event|venue|attendee|organis)/', $query)) {
        return 'Use Events to create or edit events, Venues to manage locations, and Attendees or Organisers to manage people connected to your workspace.';
    }
    if (preg_match('/\b(ai|planner|smart|insight|description)\b/', $query)) {
        return 'EventSpace includes an AI planner, smart event search, event brief extraction, description assistance, attendance insights, and feedback sentiment analysis.';
    }
    return 'I can explain EventSpace features, help you find events, guide you through RSVPs and tickets, or summarize feedback sentiment. What would you like to know?';
}

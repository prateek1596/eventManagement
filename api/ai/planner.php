<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/app.php';

header('Content-Type: application/json; charset=utf-8');

if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['error' => 'POST is required']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode($raw ?: '', true);
$prompt = is_array($payload) ? trim((string) ($payload['prompt'] ?? '')) : '';

if ($prompt === '' || strlen($prompt) > 500) {
    http_response_code(422);
    echo json_encode(['error' => 'Describe an event in 1-500 characters.']);
    exit;
}

$fallback = [
    'summary' => 'A practical first plan for ' . $prompt,
    'budget' => ['Venue and permits (40%)', 'Food and beverages (25%)', 'Decor and production (15%)', 'Contingency (20%)'],
    'timeline' => ['Confirm the guest list and budget', 'Shortlist the venue and vendors', 'Send invitations and confirm suppliers', 'Prepare the day-of run sheet'],
    'checklist' => ['Guest list', 'Venue', 'Catering', 'Decor', 'Music', 'Day-of schedule'],
    'vendors' => ['Local caterer', 'Decor and lighting partner', 'Photographer', 'Event host or DJ'],
];

if (EVENTSPACE_AI_API_KEY === '' || EVENTSPACE_AI_API_URL === '') {
    echo json_encode($fallback);
    exit;
}

$request = [
    'model' => getenv('AI_MODEL') ?: 'gpt-4o-mini',
    'temperature' => 0.4,
    'response_format' => ['type' => 'json_object'],
    'messages' => [
        ['role' => 'system', 'content' => 'You are an event planner. Return only JSON with string summary and arrays budget, timeline, checklist, vendors. Keep advice practical and concise.'],
        ['role' => 'user', 'content' => $prompt],
    ],
];

$curl = curl_init(EVENTSPACE_AI_API_URL);
curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 12,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . EVENTSPACE_AI_API_KEY,
    ],
    CURLOPT_POSTFIELDS => json_encode($request),
]);
$response = curl_exec($curl);
$status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
$curlError = curl_error($curl);
curl_close($curl);

if ($response === false || $curlError !== '' || $status < 200 || $status >= 300) {
    error_log('AI planner request failed: ' . ($curlError ?: 'HTTP ' . $status));
    echo json_encode($fallback);
    exit;
}

$decoded = json_decode($response, true);
$content = $decoded['choices'][0]['message']['content'] ?? null;
$result = is_string($content) ? json_decode(trim($content), true) : null;

if (!is_array($result) || !isset($result['summary'], $result['budget'], $result['timeline'], $result['checklist'], $result['vendors'])) {
    error_log('AI planner returned an unexpected response shape.');
    echo json_encode($fallback);
    exit;
}

echo json_encode([
    'summary' => (string) $result['summary'],
    'budget' => array_values(array_map('strval', (array) $result['budget'])),
    'timeline' => array_values(array_map('strval', (array) $result['timeline'])),
    'checklist' => array_values(array_map('strval', (array) $result['checklist'])),
    'vendors' => array_values(array_map('strval', (array) $result['vendors'])),
]);

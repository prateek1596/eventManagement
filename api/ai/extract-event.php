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

$payload = json_decode(file_get_contents('php://input') ?: '', true);
$prompt = is_array($payload) ? trim((string) ($payload['prompt'] ?? '')) : '';

if ($prompt === '' || strlen($prompt) > 2000) {
    http_response_code(422);
    echo json_encode(['error' => 'Describe the event in 1-2,000 characters.']);
    exit;
}

$fallback = [
    'name' => '',
    'booking_date' => '',
    'time' => '',
    'venue' => '',
    'contact_info' => '',
    'budget' => '',
    'capacity' => '',
];

if (EVENTSPACE_AI_API_KEY === '' || EVENTSPACE_AI_API_URL === '') {
    echo json_encode(['result' => $fallback, 'message' => 'Add an AI API key to enable event extraction.']);
    exit;
}

$request = [
    'model' => getenv('AI_MODEL') ?: 'gpt-4o-mini',
    'temperature' => 0.1,
    'response_format' => ['type' => 'json_object'],
    'messages' => [
        [
            'role' => 'system',
            'content' => 'Extract event details from the user text. Return only JSON with string fields name, booking_date (YYYY-MM-DD or empty), time (HH:MM or empty), venue, contact_info, budget, capacity. Never invent missing values.',
        ],
        ['role' => 'user', 'content' => $prompt],
    ],
];

$curl = curl_init(EVENTSPACE_AI_API_URL);
curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 12,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . EVENTSPACE_AI_API_KEY],
    CURLOPT_POSTFIELDS => json_encode($request),
]);
$response = curl_exec($curl);
$status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
$curlError = curl_error($curl);
curl_close($curl);

if ($response === false || $curlError !== '' || $status < 200 || $status >= 300) {
    error_log('AI event extraction failed: ' . ($curlError ?: 'HTTP ' . $status));
    http_response_code(502);
    echo json_encode(['error' => 'The AI service could not extract event details.']);
    exit;
}

$decoded = json_decode($response, true);
$content = $decoded['choices'][0]['message']['content'] ?? null;
$result = is_string($content) ? json_decode(trim($content), true) : null;

if (!is_array($result)) {
    error_log('AI event extraction returned an unexpected response shape.');
    http_response_code(502);
    echo json_encode(['error' => 'The AI service returned invalid event details.']);
    exit;
}

$keys = array_keys($fallback);
foreach ($keys as $key) {
    $result[$key] = isset($result[$key]) ? trim((string) $result[$key]) : '';
}

echo json_encode(['result' => array_intersect_key($result, $fallback)]);

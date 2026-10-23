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
$query = is_array($payload) ? trim((string) ($payload['query'] ?? '')) : '';
if ($query === '' || strlen($query) > 500) {
    http_response_code(422);
    echo json_encode(['error' => 'Enter a search request between 1 and 500 characters.']);
    exit;
}

$fallback = ['query' => $query, 'location' => '', 'from' => '', 'to' => '', 'maxPrice' => ''];
if (EVENTSPACE_AI_API_KEY === '' || EVENTSPACE_AI_API_URL === '') {
    echo json_encode($fallback);
    exit;
}

$request = [
    'model' => getenv('AI_MODEL') ?: 'gpt-4o-mini',
    'temperature' => 0.1,
    'response_format' => ['type' => 'json_object'],
    'messages' => [
        ['role' => 'system', 'content' => 'Convert event search text to JSON with string fields query, location, from, to, maxPrice. Dates must be YYYY-MM-DD. Use empty strings for unknown values. Keep query as the useful event category or keyword, not the entire sentence. Return only JSON.'],
        ['role' => 'user', 'content' => $query],
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
    error_log('AI search request failed: ' . ($curlError ?: 'HTTP ' . $status));
    echo json_encode($fallback);
    exit;
}

$decoded = json_decode($response, true);
$content = $decoded['choices'][0]['message']['content'] ?? null;
$result = is_string($content) ? json_decode(trim($content), true) : null;
if (!is_array($result)) {
    error_log('AI search returned an unexpected response shape.');
    echo json_encode($fallback);
    exit;
}

foreach (array_keys($fallback) as $key) {
    $result[$key] = isset($result[$key]) ? trim((string) $result[$key]) : $fallback[$key];
}
echo json_encode(array_intersect_key($result, $fallback));

<?php
declare(strict_types=1);

function eventspace_ai_completion(array $messages, float $temperature = 0.2): ?string
{
    if (EVENTSPACE_AI_API_KEY === '' || EVENTSPACE_AI_API_URL === '') {
        return null;
    }

    $request = json_encode([
        'model' => getenv('AI_MODEL') ?: 'gpt-4o-mini',
        'temperature' => $temperature,
        'messages' => $messages,
    ]);
    if ($request === false) {
        error_log('AI request could not be encoded.');
        return null;
    }

    $curl = curl_init(EVENTSPACE_AI_API_URL);
    curl_setopt_array($curl, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 12,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . EVENTSPACE_AI_API_KEY,
        ],
        CURLOPT_POSTFIELDS => $request,
    ]);
    $response = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($response === false || $curlError !== '' || $status < 200 || $status >= 300) {
        error_log('AI completion request failed: ' . ($curlError ?: 'HTTP ' . $status));
        return null;
    }

    $decoded = json_decode($response, true);
    $content = $decoded['choices'][0]['message']['content'] ?? null;
    return is_string($content) ? trim($content) : null;
}

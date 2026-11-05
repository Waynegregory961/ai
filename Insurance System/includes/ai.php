<?php
// includes/ai.php – small wrapper around OpenAI Chat Completions
require_once __DIR__ . '/config.php';

/**
 * Call the AI API.
 * @param array $messages  e.g. [['role'=>'system','content'=>'...'],['role'=>'user','content'=>'...']]
 * @return array{ok:bool,text?:string,error?:string}
 */
function call_ai(array $messages, int $maxTokens = 500): array
{
    if (AI_API_KEY === '' || strpos(AI_API_KEY, 'REPLACE') !== false) {
        return ['ok' => false, 'error' => 'AI API key is not configured.'];
    }

    $payload = json_encode([
        'model'       => AI_MODEL,
        'messages'    => $messages,
        'max_tokens'  => $maxTokens,
        'temperature' => 0.4,
    ]);

    $ch = curl_init(AI_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . AI_API_KEY,
        ],
        CURLOPT_TIMEOUT        => 30,
    ]);
    $raw    = curl_exec($ch);
    $err    = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false) {
        return ['ok' => false, 'error' => 'Network error: ' . $err];
    }
    if ($status >= 400) {
        return ['ok' => false, 'error' => 'AI service returned HTTP ' . $status];
    }
    $data = json_decode($raw, true);
    $text = $data['choices'][0]['message']['content'] ?? '';
    if (trim($text) === '') {
        return ['ok' => false, 'error' => 'Empty AI response.'];
    }
    return ['ok' => true, 'text' => trim($text)];
}
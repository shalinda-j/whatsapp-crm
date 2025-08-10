<?php
// include/gemini.php
// Helper for calling Google Gemini (Generative Language) API
// Usage:
//   include_once __DIR__ . '/gemini.php';
//   $text = gemini_generate('Write a short greeting');
// API key resolution order:
//   1) Environment variable GEMINI_API_KEY
//   2) include/secrets.php returning ['GEMINI_API_KEY' => '...']

if (!function_exists('gemini_resolve_api_key')) {
    function gemini_resolve_api_key(?string $apiKey = null): string
    {
        if ($apiKey && trim($apiKey) !== '') {
            return $apiKey;
        }
        $env = getenv('GEMINI_API_KEY');
        if ($env && trim($env) !== '') {
            return $env;
        }
        $secretsPath = __DIR__ . '/secrets.php';
        if (file_exists($secretsPath)) {
            $secrets = include $secretsPath; // expects array ['GEMINI_API_KEY' => '...']
            if (is_array($secrets) && !empty($secrets['GEMINI_API_KEY'])) {
                return $secrets['GEMINI_API_KEY'];
            }
        }
        throw new Exception('Gemini API key not configured. Set GEMINI_API_KEY env var or include/secrets.php.');
    }
}

if (!function_exists('gemini_generate')) {
    function gemini_generate(string $prompt, string $model = 'gemini-1.5-flash', ?string $apiKey = null): string
    {
        $key = gemini_resolve_api_key($apiKey);
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent?key=' . urlencode($key);

        $payload = [
            'contents' => [
                [
                    'parts' => [ ['text' => $prompt] ]
                ]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);

        $resp = curl_exec($ch);
        if ($resp === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new Exception('Gemini request failed: ' . $err);
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code < 200 || $code >= 300) {
            throw new Exception('Gemini HTTP ' . $code . ': ' . $resp);
        }

        $data = json_decode($resp, true);
        if (!is_array($data)) {
            throw new Exception('Invalid JSON response from Gemini');
        }

        // Extract first candidate text
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        return (string)$text;
    }
}

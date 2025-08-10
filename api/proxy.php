<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, r-unique');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include your existing authentication and session checks
include("../include/conn.php");
include("../include/function.php");

$login = cekSession();
if ($login != 1) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Please login.']);
    exit();
}

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['url'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

$targetUrl = $input['url'];
$method = $input['method'] ?? 'GET';
$headers = $input['headers'] ?? [];
$body = $input['body'] ?? null;

// Initialize cURL
$ch = curl_init();

// Set basic cURL options
curl_setopt_array($ch, [
    CURLOPT_URL => $targetUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_USERAGENT => 'WhatsApp-CRM-Proxy/1.0'
]);

// Set headers
if (!empty($headers)) {
    $curlHeaders = [];
    foreach ($headers as $key => $value) {
        $curlHeaders[] = "$key: $value";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
}

// Set body for POST/PUT requests
if ($body && in_array($method, ['POST', 'PUT', 'PATCH'])) {
    if (is_array($body) || is_object($body)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    } else {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
}

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

// Handle cURL errors
if ($error) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Proxy request failed',
        'details' => $error
    ]);
    exit();
}

// Return response
http_response_code($httpCode);
echo $response;
?>

<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, r-unique, X-API-Key');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include your existing authentication and session checks
include("../include/conn.php");
include("../include/function.php");

// Enhanced logging function
function logProxyRequest($url, $method, $status, $error = null) {
    $logFile = '../logs/proxy.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $logEntry = "[$timestamp] $ip - $method $url - Status: $status";
    if ($error) {
        $logEntry .= " - Error: $error";
    }
    $logEntry .= PHP_EOL;
    
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Rate limiting function
function checkRateLimit($ip) {
    $rateLimitFile = '../cache/rate_limit_' . md5($ip) . '.json';
    $maxRequests = 100; // requests per hour
    $timeWindow = 3600; // 1 hour in seconds
    
    if (!file_exists(dirname($rateLimitFile))) {
        mkdir(dirname($rateLimitFile), 0755, true);
    }
    
    $currentTime = time();
    $rateLimitData = [];
    
    if (file_exists($rateLimitFile)) {
        $rateLimitData = json_decode(file_get_contents($rateLimitFile), true) ?: [];
    }
    
    // Clean old entries
    $rateLimitData = array_filter($rateLimitData, function($timestamp) use ($currentTime, $timeWindow) {
        return ($currentTime - $timestamp) < $timeWindow;
    });
    
    if (count($rateLimitData) >= $maxRequests) {
        return false;
    }
    
    // Add current request
    $rateLimitData[] = $currentTime;
    file_put_contents($rateLimitFile, json_encode($rateLimitData), LOCK_EX);
    
    return true;
}

// Cache function
function getCachedResponse($cacheKey) {
    $cacheFile = '../cache/proxy_' . md5($cacheKey) . '.json';
    $cacheTime = 300; // 5 minutes
    
    if (file_exists($cacheFile)) {
        $cacheData = json_decode(file_get_contents($cacheFile), true);
        if ($cacheData && (time() - $cacheData['timestamp']) < $cacheTime) {
            return $cacheData['response'];
        }
    }
    return null;
}

function setCachedResponse($cacheKey, $response) {
    $cacheFile = '../cache/proxy_' . md5($cacheKey) . '.json';
    
    if (!file_exists(dirname($cacheFile))) {
        mkdir(dirname($cacheFile), 0755, true);
    }
    
    $cacheData = [
        'timestamp' => time(),
        'response' => $response
    ];
    file_put_contents($cacheFile, json_encode($cacheData), LOCK_EX);
}

// Authentication check
$login = cekSession();
if ($login != 1) {
    logProxyRequest('AUTH_FAILED', 'UNKNOWN', 403, 'Access denied');
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Please login.']);
    exit();
}

// Rate limiting check
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!checkRateLimit($clientIP)) {
    logProxyRequest('RATE_LIMITED', 'UNKNOWN', 429, 'Rate limit exceeded');
    http_response_code(429);
    echo json_encode(['error' => 'Rate limit exceeded. Try again later.']);
    exit();
}

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['url'])) {
    logProxyRequest('INVALID_REQUEST', 'UNKNOWN', 400, 'Missing URL parameter');
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

$targetUrl = $input['url'];
$method = $input['method'] ?? 'GET';
$headers = $input['headers'] ?? [];
$body = $input['body'] ?? null;
$useCache = $input['cache'] ?? false;

// Validate URL
if (!filter_var($targetUrl, FILTER_VALIDATE_URL)) {
    logProxyRequest($targetUrl, $method, 400, 'Invalid URL format');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid URL format']);
    exit();
}

// Check cache for GET requests
$cacheKey = $targetUrl . serialize($headers);
if ($method === 'GET' && $useCache) {
    $cachedResponse = getCachedResponse($cacheKey);
    if ($cachedResponse) {
        logProxyRequest($targetUrl, $method, 200, 'Cache hit');
        echo json_encode($cachedResponse);
        exit();
    }
}

// Initialize cURL
$ch = curl_init();

// Set basic cURL options
curl_setopt_array($ch, [
    CURLOPT_URL => $targetUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_USERAGENT => 'WhatsApp-CRM-Proxy/2.0',
    CURLOPT_HEADER => true,
    CURLOPT_NOBODY => false
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
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$error = curl_error($ch);

curl_close($ch);

// Handle cURL errors
if ($error) {
    logProxyRequest($targetUrl, $method, 0, $error);
    http_response_code(500);
    echo json_encode([
        'error' => 'Proxy request failed',
        'details' => $error
    ]);
    exit();
}

// Separate headers and body
$responseHeaders = substr($response, 0, $headerSize);
$responseBody = substr($response, $headerSize);

// Parse response
$contentType = '';
if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $responseHeaders, $matches)) {
    $contentType = trim($matches[1]);
}

$data = $responseBody;
if (strpos($contentType, 'application/json') !== false) {
    $jsonData = json_decode($responseBody, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $data = $jsonData;
    }
}

$result = [
    'ok' => $httpCode >= 200 && $httpCode < 300,
    'status' => $httpCode,
    'data' => $data,
    'headers' => $contentType
];

// Cache successful GET requests
if ($method === 'GET' && $useCache && $result['ok']) {
    setCachedResponse($cacheKey, $result);
}

// Log request
logProxyRequest($targetUrl, $method, $httpCode);

// Return response
http_response_code($httpCode);
echo json_encode($result);
?>

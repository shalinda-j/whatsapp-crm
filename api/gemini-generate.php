<?php
// api/gemini-generate.php
// POST JSON: { "prompt": "...", "model": "gemini-1.5-flash" }
// Response JSON: { "success": true, "text": "..." } or { "success": false, "error": "..." }

header('Content-Type: application/json');
header('Cache-Control: no-store');

require_once __DIR__ . '/../include/function.php';
require_once __DIR__ . '/../include/gemini.php';

// Auth/session check
if (cekSession() !== 1) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// License check
check_license_if_needed($conn);
if (!is_license_active($conn)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'License inactive']);
    exit;
}

// Role check: only super_admin can access Gemini endpoint
$userType = $_SESSION['user_type'] ?? '';
if ($userType !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden: super_admin only']);
    exit;
}

// Read JSON input
$raw = file_get_contents('php://input');
$body = json_decode($raw, true);
if (!is_array($body)) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON body']);
    exit;
}

$prompt = trim((string)($body['prompt'] ?? ''));
$model = (string)($body['model'] ?? 'gemini-1.5-flash');

if ($prompt === '') {
    echo json_encode(['success' => false, 'error' => 'Missing prompt']);
    exit;
}

try {
    $text = gemini_generate($prompt, $model);
    echo json_encode(['success' => true, 'text' => $text]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

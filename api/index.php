<?php
// Hardened: prevent exposing phpinfo in production
http_response_code(404);
header('Content-Type: application/json');
echo json_encode(['status' => false, 'message' => 'Not Found']);
exit;
?>
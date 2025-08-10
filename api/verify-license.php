<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, x-requested-with");
header('Content-Type: application/json');

include("../include/conn.php");
include("../include/function.php");

date_default_timezone_set('America/Sao_Paulo');

$sub_key   = $_POST['sub_key']   ?? '';
$unique_id = $_POST['unique_id'] ?? '';
$mo_no_raw = $_POST['mo_no']     ?? '';
$b_version = $_POST['b_version'] ?? '';
$r_id      = $_POST['r_id']      ?? '';

// Normalize phone: keep digits only
$mo_no = preg_replace('/\D/', '', $mo_no_raw);

// Only require key, device id and phone. b_version and r_id are optional.
if ($sub_key === '' || $unique_id === '' || $mo_no === '') {
    echo json_encode(['status' => 400, 'message' => 'Missing Parameters']);
    exit;
}

// Build Brazil variants (55 + DDD + number with/without 9)
$mo_no_with_9 = $mo_no;
$mo_no_without_9 = $mo_no;
if (strpos($mo_no, '55') === 0) {
    $ddd = substr($mo_no, 2, 2);
    $numero = substr($mo_no, 4);
    if (strlen($numero) === 8) {
        $mo_no_with_9 = '55' . $ddd . '9' . $numero;
    }
    if (strlen($numero) === 9 && substr($numero, 0, 1) === '9') {
        $mo_no_without_9 = '55' . $ddd . substr($numero, 1);
    }
}

$stmt = $conn->prepare("SELECT `whatsapp_number`, `license_key`, `act_date`, `end_date`, `life_time`, `plan_type`, `email`, `skd_id`, `pc_id`, `status`, `plan` FROM `users` WHERE `license_key` = ? AND (`whatsapp_number` = ? OR `whatsapp_number` = ? OR `whatsapp_number` = ?) LIMIT 1");
$stmt->bind_param('ssss', $sub_key, $mo_no, $mo_no_with_9, $mo_no_without_9);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $endDate = new DateTime($row['end_date']);
    $now = new DateTime();

    if ($now >= $endDate || $row['status'] === 'false' || $row['plan'] === 'false') {
        echo json_encode(['status' => 400, 'message' => 'License expired or inactive', 'code' => 42]);
        exit;
    }

    $daysRemaining = (int)$now->diff($endDate)->format('%a');

    if (empty($row['pc_id'])) {
        $stmt2 = $conn->prepare("UPDATE `users` SET `pc_id` = ? WHERE `license_key` = ?");
        $stmt2->bind_param('ss', $unique_id, $sub_key);
        $stmt2->execute();
        $stmt2->close();
    }

    echo json_encode([
        'status' => 200,
        'message' => 'OK',
        'data' => [
            'success' => true,
            'validate' => [
                'is_pro' => true,
                'end_date' => $endDate->format('Y-m-d H:i:s'),
                'day_remaining' => $daysRemaining,
                'life_time' => false,
            ],
            'plan_type' => $row['plan'],
            'sub_email' => $row['email'] ?? null,
            'device_data' => [
                'skd_id' => $row['skd_id'] ?? null,
                'pc_id' => $row['pc_id'] ?? null,
            ],
        ],
    ]);
} else {
    echo json_encode(['status' => 400, 'message' => 'INVALID_SUBSCRIPTION_KEY_OR_MOBILE_NUMBER', 'code' => 41]);
}

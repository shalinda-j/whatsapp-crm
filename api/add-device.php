<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Authorization, Content-Type, x-requested-with");
header('Content-Type: application/json');

include("../include/conn.php");
include("../include/function.php");

$data = json_decode(file_get_contents("php://input"), true) ?? [];
$sub_key   = $data['sub_key']   ?? '';
$unique_id = $data['unique_id'] ?? '';
$mo_no_raw = $data['mo_no']     ?? '';
$r_id      = $data['r_id']      ?? '';

// Require only sub_key, unique_id and phone
if ($sub_key === '' || $unique_id === '' || $mo_no_raw === '') {
    echo json_encode(['status' => 400, 'message' => 'Missing Parameters']);
    exit;
}

// Normalize phone input
$mo_no = preg_replace('/\D/', '', $mo_no_raw);
// Build Brazil variants
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

$stmt = $conn->prepare("SELECT * FROM `users` WHERE `license_key` = ? LIMIT 1");
$stmt->bind_param('s', $sub_key);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $endDate = DateTime::createFromFormat('Y-m-d H:i:s', $row['end_date']);
    $actDate = DateTime::createFromFormat('Y-m-d H:i:s', $row['act_date']);
    $now = new DateTime();

    $daysRemaining = (int)floor((strtotime($row['end_date']) - time()) / 86400);
    // Normalize stored number for comparison
    $whatsapp_number = preg_replace('/\D/', '', $row['whatsapp_number']);

    if ($daysRemaining <= 0 || $row['plan'] === 'false' || $row['status'] === 'false') {
        // Expire locally
        $stmt2 = $conn->prepare("UPDATE `users` SET `plan`='false' WHERE `license_key` = ?");
        $stmt2->bind_param('s', $sub_key);
        $stmt2->execute();
        $stmt2->close();
        echo json_encode(['status' => 400, 'message' => 'License Expired']);
        exit;
    }

    // Accept BR with/without leading 9
    if (!in_array($whatsapp_number, [$mo_no, $mo_no_with_9, $mo_no_without_9], true)) {
        echo json_encode(['status' => 400, 'message' => 'Invalid License Key']);
        exit;
    }

    if (empty($row['pc_id'])) {
        $stmt3 = $conn->prepare("UPDATE `users` SET `pc_id` = ? WHERE `license_key` = ?");
        $stmt3->bind_param('ss', $unique_id, $sub_key);
        $stmt3->execute();
        $stmt3->close();
        $row['pc_id'] = $unique_id;
    }

    $pc_name = 'WA-' . ($row['id'] + 500);

    echo json_encode([
        'status' => 200,
        'message' => 'OK',
        'data' => 'License key is valid. Device added successfully.',
        'dDate' => [
            'userDeviceDate' => [
                'sub_key' => $sub_key,
                'success' => true,
                'validate' => [
                    'is_pro' => true,
                    'end_date' => $endDate ? $endDate->format('Y-m-d H:i:s') : $row['end_date'],
                    'sk_licence_key' => $sub_key,
                    'day_remaining' => $daysRemaining,
                    'life_time' => false
                ],
                'is_subscription' => true,
                'plan_type' => $row['plan_type'],
                'sub_email' => $row['email'],
                'skey' => $sub_key,
                'device_data' => [
                    'skd_id' => 26444,
                    'skd_fk_sk_id' => 6433,
                    'skd_device_id' => $row['pc_id'],
                    'skd_wa_no' => $whatsapp_number,
                    'skd_config' => null,
                    'skd_archive' => false,
                    'skd_created_at' => $actDate ? $actDate->format('Y-m-d H:i:s') : $row['act_date'],
                    'skd_modified_at' => $now->format('Y-m-d H:i:s'),
                    'skd_device_name' => $pc_name,
                    'skd_removed_at' => $endDate ? $endDate->format('Y-m-d H:i:s') : $row['end_date'],
                    'skd_removed_manual' => false,
                    'skd_removed_manual_at' => null,
                    'skd_build_version' => $data['b_version'] ?? '1.0.6'
                ]
            ],
            'userkeyuniquedata' => [
                'licence_key' => $sub_key,
                'uniqueId' => $row['pc_id'],
                'version' => $data['b_version'] ?? '1.0.6'
            ],
            'userPurchaseStatus' => $row['plan_type']
        ]
    ]);
} else {
    echo json_encode(['status' => 400, 'message' => 'License Key Not Found']);
}

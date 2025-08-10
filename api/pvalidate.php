<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

include("../include/conn.php");
include("../include/function.php");

date_default_timezone_set('America/Sao_Paulo');

$unique_id = $_GET['unique_id'] ?? '';
$mo_no_raw = $_GET['phone'] ?? '';
$license   = $_GET['license'] ?? '';
$r_id      = $_GET['reseller_id'] ?? '';

$sub_key = trim($license);
$mo_no = preg_replace('/\D/', '', $mo_no_raw);

if ($sub_key === '' || $mo_no === '') {
    echo json_encode(['status' => 400, 'message' => 'Missing Parameters']);
    exit;
}

$variations = [$mo_no];

// Normalize based on length, assuming Brazilian numbers
if (strlen($mo_no) === 13 && strpos($mo_no, '55') === 0) { // 55 + DD + 9-digit number
    $ddd = substr($mo_no, 2, 2);
    $num_part = substr($mo_no, 4);
    if (substr($num_part, 0, 1) === '9') {
        $variations[] = '55' . $ddd . substr($num_part, 1); // without 9
    }
} elseif (strlen($mo_no) === 12 && strpos($mo_no, '55') === 0) { // 55 + DD + 8-digit number
    $ddd = substr($mo_no, 2, 2);
    $num_part = substr($mo_no, 4);
    $variations[] = '55' . $ddd . '9' . $num_part; // with 9
} elseif (strlen($mo_no) === 11) { // DD + 9-digit number
    $ddd = substr($mo_no, 0, 2);
    $num_part = substr($mo_no, 2);
    $variations[] = '55' . $mo_no; // with CC
    if (substr($num_part, 0, 1) === '9') {
        $variations[] = '55' . $ddd . substr($num_part, 1); // with CC and without 9
    }
} elseif (strlen($mo_no) === 10) { // DD + 8-digit number
    $ddd = substr($mo_no, 0, 2);
    $num_part = substr($mo_no, 2);
    $variations[] = '55' . $mo_no; // with CC
    $variations[] = '55' . $ddd . '9' . $num_part; // with CC and with 9
}

$unique_variations = array_values(array_unique($variations));
$placeholders = rtrim(str_repeat('?,', count($unique_variations)), ',');

$query = "SELECT * FROM users WHERE license_key = ? AND whatsapp_number IN ($placeholders) LIMIT 1";
$stmt = $conn->prepare($query);

$types = 's' . str_repeat('s', count($unique_variations));
$params = array_merge([$sub_key], $unique_variations);

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'false' || strtolower((string)($row['deleted_key'] ?? '')) === 'yes') {
        echo json_encode(['status' => 400, 'message' => 'License inactive or deleted']);
        exit;
    }

    $endDate = new DateTime($row['end_date']);
    $now = new DateTime();

    if ($now < $endDate) {
        $daysRemaining = (int)$now->diff($endDate)->format('%a');
        echo json_encode([
            'message' => 'OK',
            'status' => 200,
            'dDate' => [
                'userDeviceDate' => [
                    'sub_key' => $sub_key,
                    'success' => true,
                    'validate' => [
                        'is_pro' => true,
                        'end_date' => $endDate->format('d/m/Y H:i'),
                        'sk_licence_key' => $sub_key,
                        'day_remaining' => $daysRemaining,
                        'life_time' => false
                    ],
                    'is_subscription' => true,
                    'plan_type' => $row['plan_type'] ?? 'Premium',
                    'sub_email' => $row['email'] ?? null,
                    'skey' => $sub_key,
                    'device_data' => [
                        'skd_id' => 26444,
                        'skd_fk_sk_id' => 6433,
                        'skd_device_id' => $unique_id,
                        'skd_wa_no' => $mo_no,
                        'skd_name' => $row['customer_name'],
                        'skd_config' => null,
                        'skd_archive' => false,
                        'skd_created_at' => $row['act_date'],
                        'skd_modified_at' => date('Y-m-d H:i:s'),
                        'skd_device_name' => 'WA-' . ($row['id'] + 500),
                        'skd_removed_at' => null,
                        'skd_removed_manual' => false,
                        'skd_removed_manual_at' => null,
                        'skd_build_version' => $_GET['version'] ?? '1.0.6'
                    ]
                ],
                'userkeyuniquedata' => [
                    'licence_key' => $sub_key,
                    'uniqueId' => $unique_id,
                    'version' => $_GET['version'] ?? '1.0.6'
                ],
                'userPurchaseStatus' => $row['plan_type'] ?? 'PREMIUM'
            ]
        ]);
        exit;
    }

    echo json_encode(['status' => 400, 'message' => 'License expired']);
    exit;
}


echo json_encode(['status' => 400, 'message' => 'License inválida']);
exit;

<?php
include("../include/conn.php");
include("../include/function.php");
session_start();
header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Método não permitido.']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : null;
$status = ($_POST['status'] ?? '') === 'true' ? 'true' : 'false';

if (!$id) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'ID inválido.']);
    exit;
}

$stmt = $conn->prepare("UPDATE `admin` SET `status` = ? WHERE `id` = ? AND `user_type` = 'reseller'");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    $stmt->close();

    $userStmt = $conn->prepare("UPDATE `users` SET `status` = ? WHERE `user_id` = ?");
    $userStmt->bind_param("si", $status, $id);
    $userStmt->execute();
    $userStmt->close();

    $licenses = mysqli_query($conn, "SELECT license_key FROM users WHERE user_id = '$id'");
    
    $config = mysqli_fetch_assoc(mysqli_query($conn, "SELECT email FROM configurations WHERE id = 1"));
    $emailPurchase = $config['email'];

    while ($row = mysqli_fetch_assoc($licenses)) {
        $licenseKey = $row['license_key'];
        $payload = json_encode([
            'email_purchase' => $emailPurchase,
            'license_key'    => $licenseKey,
            'status'         => $status
        ]);

        $ch = curl_init(api_url("/editar"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }

    echo json_encode(['status' => true, 'message' => 'Reseller e licenças associadas atualizados com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Error ao atualizar status do revendedor.']);
}

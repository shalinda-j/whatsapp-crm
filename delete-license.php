<?php
ob_start();
header('Content-Type: application/json');
include("include/conn.php");
include("include/function.php");

// Verifica login
if (cekSession() != 1) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Acesso não autorizado.']);
    exit();
}
check_license_if_needed($conn);
if (!is_license_active($conn)) {
    header("Location: license.php");
    exit;
}

// Verifica se o ID foi enviado
if (empty($_POST['id'])) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'ID da licença não fornecido.']);
    exit();
}

$licenseId = intval($_POST['id']);

// Busca a chave da licença local
$stmt = $conn->prepare("SELECT license_key FROM users WHERE id = ?");
$stmt->bind_param("i", $licenseId);
$stmt->execute();
$result = $stmt->get_result();
$licenseDate = $result->fetch_assoc();
$stmt->close();

if (!$licenseDate) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'License não encontrada.']);
    exit();
}

$licenseKey = $licenseDate['license_key'];

$config = mysqli_fetch_assoc(mysqli_query($conn, "SELECT email FROM configurations WHERE id = 1"));
$emailPurchase = $config['email'] ?? '';

if (empty($emailPurchase)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Email de autenticação não configurado.']);
    exit();
}

$payload = json_encode([
    'email_purchase' => $emailPurchase,
    'license_key'    => $licenseKey
]);

$ch = curl_init(api_url("/deletar"));
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$apiResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErrorr = curl_error($ch);
curl_close($ch);

// Trata erro cURL
if ($curlErrorr) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Error cURL: ' . $curlErrorr]);
    exit();
}

// Decodifica e verifica resposta da API
$decoded = json_decode($apiResponse, true);
if ($httpCode !== 200 || !isset($decoded['success']) || $decoded['success'] === false) {
    $msg = $decoded['message'] ?? $decoded['error'] ?? 'Error desconhecido na exclusão da API.';
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => $msg]);
    exit();
}

// Remove do banco local
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $licenseId);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'License deletada com sucesso.']);
} else {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Error ao excluir localmente.']);
}

$stmt->close();
$conn->close();
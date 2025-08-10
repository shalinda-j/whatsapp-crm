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

$stmt = $conn->prepare("UPDATE `admin` SET `status` = ? WHERE `id` = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    echo json_encode(['status' => true, 'message' => 'Status atualizado com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Falha ao atualizar status.']);
}

$stmt->close();
?>

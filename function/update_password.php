<?php
include("../include/conn.php");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Método não permitido.']);
    exit;
}

$id = $_POST['id'] ?? null;
$password = $_POST['password'] ?? null;

if (!$id || !$password) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'ID e senha são obrigatórios.']);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
$stmt->bind_param("si", $hashedPassword, $id);

if ($stmt->execute()) {
    echo json_encode(['status' => true, 'message' => 'Password atualizada com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Error ao atualizar a senha.']);
}

$stmt->close();
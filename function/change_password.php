<?php
include("../include/conn.php");
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Método não permitido.']);
    exit;
}

$adminId = $_SESSION['id'] ?? null;
if (!$adminId) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Session expired ou usuário não autenticado.']);
    exit;
}

$currentPassword = $_POST['current_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';

if (empty($currentPassword) || empty($newPassword)) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Todos os campos são obrigatórios.']);
    exit;
}

$stmt = $conn->prepare("SELECT password FROM admin WHERE id = ?");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    http_response_code(404);
    echo json_encode(['status' => false, 'message' => 'User não encontrado.']);
    exit;
}

$row = $result->fetch_assoc();
$storedHash = $row['password'];
$senhaValida = false;

if (password_verify($currentPassword, $storedHash)) {
    $senhaValida = true;
} elseif (sha1($currentPassword) === $storedHash) {
    $senhaValida = true;
}

if (!$senhaValida) {
    http_response_code(403);
    echo json_encode(['status' => false, 'message' => 'Password atual incorreta.']);
    exit;
}

$hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$updateStmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
$updateStmt->bind_param("si", $hashedNewPassword, $adminId);

if ($updateStmt->execute()) {
    echo json_encode(['status' => true, 'message' => 'Password alterada com sucesso!']);
} else {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Error ao atualizar a senha.']);
}

$stmt->close();
$updateStmt->close();
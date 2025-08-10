<?php
include("../include/conn.php");
include("../include/function.php");
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => false, 'message' => 'Método não permitido.']);
    exit;
}

$cname         = trim($_POST['cname'] ?? '');
$wnumber       = trim($_POST['wnumber'] ?? '');
$username      = trim($_POST['username'] ?? '');
$password      = $_POST['password'] ?? '';
$user_type     = trim($_POST['user_type'] ?? '');
$status        = trim($_POST['status'] ?? '');
$start_date    = trim($_POST['start_date'] ?? '');
$expired_date  = trim($_POST['expired_date'] ?? '');
$admin_id      = $_SESSION['id'] ?? null;

if (
    empty($cname) || empty($wnumber) || empty($username) || empty($password) ||
    empty($user_type) || empty($status) || empty($start_date) || empty($expired_date) || !$admin_id
) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Preencha todos os campos obrigatórios.']);
    exit;
}

$stmt = $conn->prepare("SELECT id FROM `admin` WHERE `username` = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    echo json_encode(['status' => false, 'message' => 'Name de usuário já existe.']);
    exit;
}
$stmt->close();

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$insert = $conn->prepare("INSERT INTO `admin` 
    (`username`, `name`, `contact_number`, `password`, `user_type`, `status`, `start_date`, `expired_date`, `admin_id`) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$insert->bind_param("ssssssssi", $username, $cname, $wnumber, $hashedPassword, $user_type, $status, $start_date, $expired_date, $admin_id);

if ($insert->execute()) {
    echo json_encode(['status' => true, 'message' => 'Reseller adicionado com sucesso.']);
} else {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Falha ao adicionar revendedor.']);
}

$insert->close();
?>
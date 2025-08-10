<?php
session_start();
include("../include/conn.php");
include("../include/function.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => false,
        'message' => 'Método não permitido. Utilize POST.'
    ]);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode([
        'status' => false,
        'message' => 'User e senha são obrigatórios.'
    ]);
    exit;
}

$stmt = $conn->prepare("SELECT id, user_type, password FROM admin WHERE username = ? AND status = 'true'");
if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'status' => false,
        'message' => 'Error interno ao preparar consulta.'
    ]);
    exit;
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_id = $row['id'];
    $user_type = $row['user_type'];
    $stored_hash = $row['password'];

    $senha_valida = false;

    if (password_verify($password, $stored_hash)) {
        $senha_valida = true;
    }
    elseif (sha1($password) === $stored_hash) {
        $senha_valida = true;
        $novo_hash = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
        $update->bind_param("si", $novo_hash, $user_id);
        $update->execute();
        $update->close();
    }

    if ($senha_valida) {
        $_SESSION['login'] = true;
        $_SESSION['id'] = $user_id;
        $_SESSION['user_type'] = $user_type;

        echo json_encode([
            'status' => true,
            'message' => 'Login successful.'
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'status' => false,
            'message' => 'User ou senha inválidos.'
        ]);
    }
} else {
    http_response_code(401);
    echo json_encode([
        'status' => false,
        'message' => 'User ou senha inválidos.'
    ]);
}

$stmt->close();
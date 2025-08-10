<?php
include("../include/conn.php");
include("../include/function.php");
header('Content-Type: application/json');

$response = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido.']);
    exit;
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID inválido.']);
    exit;
}

$id = intval($_POST['id']);

$reseller_update = mysqli_query($conn, "UPDATE `admin` SET `deleted` = 'yes' WHERE `id` = $id");

if ($reseller_update) {
    $users_update = mysqli_query($conn, "UPDATE `users` SET `status` = 'false' WHERE `user_id` = $id");

    if ($users_update) {
        $response['status'] = true;
        $response['message'] = 'Administrador marcado como excluído e usuários associados desativados.';
    } else {
        $response['status'] = false;
        $response['message'] = 'Administrador marcado como excluído, mas falha ao desativar os usuários.';
    }
} else {
    http_response_code(500);
    $response['status'] = false;
    $response['message'] = 'Error ao tentar excluir o administrador.';
}

echo json_encode($response);
?>
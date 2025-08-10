<?php
include("../include/conn.php");
include("../include/function.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailPurchase = $_POST['emailPurchase'] ?? '';
    $supportPhoneNumber = $_POST['supportNumber'] ?? '';
    $trialKeyValidity = intval($_POST['trialValidity'] ?? 0);
    $color_background = $_POST['bgColor'] ?? '#ffffff';
    $colorText = $_POST['txtColor'] ?? '#000000';

    $stmt = $conn->prepare("UPDATE configurations SET email = ?, support_phone_number = ?, trial_key_validity = ?, color_background = ?, color_text = ? WHERE id = 1");
    $stmt->bind_param("ssiss", $emailPurchase, $supportPhoneNumber, $trialKeyValidity, $color_background, $colorText);

    if ($stmt->execute()) {
        echo json_encode(['status' => true, 'message' => 'Settings salvas com sucesso']);
    } else {
        echo json_encode(['status' => false, 'message' => 'Falha ao salvar configurações']);
    }

    exit;
} else {
    echo json_encode(['status' => false, 'message' => 'Método inválido']);
    exit;
}
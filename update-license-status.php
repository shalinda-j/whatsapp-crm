<?php
include("include/conn.php");
include("include/function.php");
header('Content-Type: application/json');

if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = intval($_POST['id']);
    $status = ($_POST['status'] === 'true') ? 'true' : 'false';

    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT license_key FROM users WHERE id = '$id'"));
        $config = mysqli_fetch_assoc(mysqli_query($conn, "SELECT email FROM configurations WHERE id = 1"));

        $license_key = $user['license_key'] ?? null;
        $email_purchase = $config['email'] ?? null;

        if ($license_key && $email_purchase) {
            $payload = [
                "email_purchase" => $email_purchase,
                "license_key"    => $license_key,
                "status"         => $status
            ];

            $ch = curl_init(api_url("/editar"));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);

            $apiResponse = curl_exec($ch);
            $curlErrorr = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($curlErrorr || $httpCode >= 400) {
                echo json_encode([
                    "success" => false,
                    "error" => "Error ao comunicar com a API externa.",
                    "curl_error" => $curlErrorr,
                    "http_code" => $httpCode,
                    "response" => $apiResponse
                ]);
                exit;
            }

            echo json_encode([
                "success" => true,
                "message" => "Status da licença atualizado com sucesso."
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "error" => "Information incompletas para atualizar a API."
            ]);
        }
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Error ao atualizar status local."
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "error" => "Parâmetros inválidos enviados."
    ]);
}
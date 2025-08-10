<?php
include("include/conn.php");

// Check if connection is established
if (!$conn) {
    die("Datebase connection failed: " . mysqli_connect_error());
}

if (isset($_POST['id']) && isset($_POST['field']) && isset($_POST['value'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']); // Sanitize ID
    $field = $_POST['field'];
    $value = mysqli_real_escape_string($conn, $_POST['value']); // Sanitize Value

    if (in_array($field, ['act_date', 'end_date'])) {
        $datetime = date('Y-m-d H:i:s', strtotime($value)); // Normaliza formato

        // Atualiza localmente
        $query = "UPDATE users SET `$field` = '$datetime' WHERE id = '$id'";
        if (mysqli_query($conn, $query)) {

            // Busca dados adicionais para enviar à API WordPress
            if ($field == 'end_date') {
                $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = '$id'"));
                if ($user && !empty($user['license_key']) && !empty($user['email'])) {
                    $payload = [
                        'license_key' => $user['license_key'],
                        'end_date' => $datetime,
                        'email_purchase' => $user['email']
                    ];

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://dropestore.com/wp-json/whatsapp-crm/v1/edit');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json'
                    ]);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

                    $response = curl_exec($ch);
                    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    // Verifica resposta da API WordPress
                    if ($http_code === 200) {
                        echo 'success';
                    } else {
                        echo 'partial_success: api_error';
                    }
                } else {
                    echo 'partial_success: no_license_data';
                }
            } else {
                echo 'success';
            }
        } else {
            echo 'error: ' . mysqli_error($conn);
        }
    } else {
        echo 'invalid_field';
    }
} else {
    echo 'invalid_request';
}
?>
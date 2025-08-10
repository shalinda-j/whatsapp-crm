<?php
include_once("conn.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('base_url', 'aHR0cHM6Ly9saWNlbnNlLmRyb3Blc3RvcmUuY29tL3dwLWpzb24vd2hhdHNhcHAtY3JtL3Yx');
function api_url($path) {
    return base64_decode(base_url) . $path;
}

/**
 * Função segura para obter valor do GET
 */
function get($param) {
    global $conn;
    if (!isset($_GET[$param])) return null;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($_GET[$param])));
}

/**
 * Função segura para obter valor do POST
 */
function post($param) {
    global $conn;
    if (!isset($_POST[$param])) return null;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($_POST[$param])));
}

/**
 * Verifica se o usuário está logado
 */
function cekSession() {
    return isset($_SESSION['login']) && $_SESSION['login'] === true ? 1 : 0;
}

/**
 * Redireciona para uma URL
 */
function redirect($target) {
    echo "<script>window.location = '$target';</script>";
    exit;
}

/**
 * Gera uma licença aleatória com traços
 */
function generate_license($length = 16) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
        if ($i % 4 == 3 && $i < $length - 1) {
            $randomString .= '-';
        }
    }
    return $randomString;
}

/**
 * Verifica se o número de WhatsApp já existe no banco
 */
function check_number($number) {
    global $conn;
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE whatsapp_number = ? LIMIT 1");
    if (!$stmt) {
        return false;
    }
    $stmt->bind_param("s", $number);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

function is_license_active($conn) {
    $query = mysqli_query($conn, "SELECT license_response FROM configurations WHERE id = 1");
    if ($row = mysqli_fetch_assoc($query)) {
        $raw = $row['license_response'];
        if (!empty($raw)) {
            $decoded = base64_decode($raw);
            if ($decoded !== false) {
                $data = @unserialize($decoded);
                if (is_object($data) && !empty($data->is_valid) && $data->is_valid) {
                    if (!empty($data->expire_date)) {
                        $exp = strtolower(trim($data->expire_date));
                        if ($exp === 'no expiry') {
                            return true;
                        }
                        if (strtotime($data->expire_date) >= time()) {
                            return true;
                        }
                    }
                }
            }
        }
    }
    return false;
}

function check_license_if_needed($conn) {
    require_once 'include/local_license.php';

    $result = mysqli_query($conn, "SELECT license_key, license_last_check FROM configurations WHERE id = 1");
    
    if (!$result) {
        error_log("Error na query de verificação da licença: " . mysqli_error($conn));
        return false;
    }

    $row = mysqli_fetch_assoc($result);
    if (!$row) {
        error_log("Nenhum resultado retornado para configuração de licença.");
        return false;
    }

    $license_key = $row['license_key'] ?? '';
    $ultima_verificacao = $row['license_last_check'] ?? '';

    if (empty($license_key)) {
        return false;
    }

    $agora = time();
    $verificacao_antiga = strtotime($ultima_verificacao);
    $verificacao_expirada = $verificacao_antiga < strtotime('-1 day');
    $verificacao_invalida_futura = $verificacao_antiga > strtotime('+1 day');

    if ($verificacao_expirada || $verificacao_invalida_futura) {
        $mensagem = '';
        $responseObj = null;
        $app_version = '1.0.0';

        $resultado = LocalLicense::CheckLicense($license_key, $mensagem, $responseObj, $app_version);

        $agora_str = date('Y-m-d H:i:s');
        $safe_response = $responseObj ? base64_encode(serialize($responseObj)) : null;

        mysqli_query($conn, "UPDATE configurations SET license_last_check = '$agora_str', license_response = '$safe_response' WHERE id = 1");

        return $resultado;
    }

    return true;
}

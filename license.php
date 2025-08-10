<?php
include("include/conn.php");
include("include/function.php");
require_once 'include/local_license.php';
$login = cekSession();

if ($login != 1) {
    redirect("login.php");
}

function save_license_data($conn, $license_key, $responseObj) {
    $license_key = mysqli_real_escape_string($conn, $license_key);
    $encoded_response = base64_encode(serialize($responseObj));
    mysqli_query($conn, "UPDATE configurations SET license_key = '$license_key', license_response = '$encoded_response' WHERE id = 1");
}

function get_license_data($conn) {
    $result = mysqli_query($conn, "SELECT license_key, license_response FROM configurations WHERE id = 1");
    if ($row = mysqli_fetch_assoc($result)) {
        $license_key = $row['license_key'];
        $registro = $row['license_response'] ? unserialize(base64_decode($row['license_response'])) : null;
        return [$license_key, $registro];
    }
    return [null, null];
}

function remove_license_data($conn) {
    mysqli_query($conn, "UPDATE configurations SET license_key = NULL, license_response = NULL WHERE id = 1");
}

$mensagem = '';
$classe_alerta = '';
$ativado = false;
$registro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['license_key'])) {
    $license_key = trim($_POST['license_key']);
    $app_version = '1.0.0';

    $resultado = LocalLicense::CheckLicense($license_key, $mensagem, $responseObj, $app_version);

    if ($resultado) {
        $classe_alerta = 'success';
        save_license_data($conn, $license_key, $responseObj);
        $registro = $responseObj;
    } else {
        $classe_alerta = 'error';
        $mensagem = '❌ Falha na ativação: ' . htmlspecialchars($mensagem);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_license'])) {
    $msg = '';
    $remoteRemoved = LocalLicense::RemoveLicenseKey($msg);

    if ($remoteRemoved) {
        mysqli_query($conn, "UPDATE configurations SET license_key = NULL, license_response = NULL WHERE id = 1");
        $mensagem = '🔓 License desativada com sucesso.';
        $classe_alerta = 'success';
        $ativado = false;
        $license_key = '';
        $registro = null;
    } else {
        $mensagem = '❌ Falha ao desativar licença: ' . htmlspecialchars($msg);
        $classe_alerta = 'error';
    }
}

list($license_key, $registro) = get_license_data($conn);

if ($license_key) {
    // For local license, simply reconstruct a response object and accept it as valid
    $mensagemVerificacao = '';
    $verificado = LocalLicense::CheckLicense($license_key, $mensagemVerificacao, $responseObj, '1.0.0');

    if ($verificado) {
        $registro = $responseObj;
        save_license_data($conn, $license_key, $responseObj);
    } else {
        $registro = null;
        remove_license_data($conn);
        $mensagem = '❌ License inválida: ' . htmlspecialchars($mensagemVerificacao);
        $classe_alerta = 'error';
    }
}

$ativado = $registro && !empty($registro->is_valid);
?>
<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="Chameleon Admin">
    <meta name="keywords" content="admin template, Chameleon, dashboard">
    <meta name="author" content="ThemeSelect">
    <title id="title">License</title>
    <link rel="apple-touch-icon" href="assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/ico/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Muli:300,300i,400,400i,600,600i,700,700i%7CComfortaa:300,400,700" rel="stylesheet">
    <link href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/vendors.css">
    <link rel="stylesheet" type="text/css" href="assets/css/app-lite.css">
    <link rel="stylesheet" type="text/css" href="assets/css/core/menu/menu-types/vertical-menu.css">
    <link rel="stylesheet" type="text/css" href="assets/css/core/colors/palette-gradient.css">
    <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
</head>

<body>
<?php include("include/header.php"); ?>
<?php include("include/sidebar.php"); ?>

<style>
    .license { padding: 20px; }
    div#response-message{
        background: #ff4444;
        color: #fff !important;
        padding: 10px;
        border-radius: 5px;
    }
</style>

<div class="app-content content">
    <div class="content-wrapper">
        <div <?= $style ?? ''; ?> class="content-wrapper-before"></div>
        <div class="content-header row">
            <div class="content-header-left col-md-4 col-12 mb-2">
                <h3 class="content-header-title">License</h3>
            </div>
            <div class="content-header-right col-md-8 col-12">
                <div class="breadcrumbs-top float-md-right">
                    <div class="breadcrumb-wrapper mr-1">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">License</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="col-xl-12 col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-block">
                        <section id="license-form-section">
                            <div class="row license">
                                <div class="col-md-8">
                                    <form method="POST" action="">
                                        <div class="form-group">
                                            <label for="license_key">Chave da License</label>
                                            <input type="text" class="form-control" name="license_key" id="license_key" placeholder="Insira sua chave" required value="<?= htmlspecialchars($license_key) ?>">
                                        </div>
                                        <?php if (!$ativado): ?>
                                            <button type="submit" class="btn btn-primary">Ativar License</button>
                                        <?php else: ?>
                                            <div class="alert alert-success mt-2">License já está ativa</div>
                                            <form method="POST" action="" class="mt-2">
                                                <input type="hidden" name="remove_license" value="1">
                                                <button type="submit" class="btn btn-danger">Desativar License</button>
                                            </form>
                                        <?php endif; ?>
                                    </form>

                                    <?php if (!empty($mensagem)): ?>
                                        <div id="response-message" class="<?= $classe_alerta ?> mt-3"><?= $mensagem ?></div>
                                    <?php endif; ?>

                                    <?php if ($registro && $ativado): ?>
                                        <div class="card mt-2 p-3" style="border:1px solid #e0e0e0; background: #f9f9f9;">
                                            <h5>📋 Details da License</h5>
                                            <ul class="list-unstyled">
                                                <li><strong>Chave:</strong> <?= htmlspecialchars($registro->license_key) ?></li>
                                                <li><strong>Plano:</strong> <?= htmlspecialchars($registro->license_title ?? 'N/A') ?></li>
                                                <li><strong>Expiração:</strong> <?= htmlspecialchars($registro->expire_date ?? 'Sem expiração') ?></li>
                                                <li><strong>Suporte até:</strong> <?= htmlspecialchars($registro->support_end ?? 'Indefinido') ?></li>
                                                <li><strong>Status:</strong> <span class="badge badge-success">Ativada</span></li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("include/footer.php"); ?>
<script src="assets/vendors/js/vendors.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script src="assets/js/core/app-menu-lite.js"></script>
<script src="assets/js/core/app-lite.js"></script>
<script src="assets/vendors/js/forms/tags/form-field.js"></script>

</body>
</html>
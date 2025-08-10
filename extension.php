<?php
ob_start();
session_start();
include("include/conn.php");
include("include/function.php");

$login = cekSession();
if ($login != 1) {
    http_response_code(403);
    die("Access denied. Please login.");
}
check_license_if_needed($conn);
if (!is_license_active($conn)) {
    header("Location: license.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check if ZipArchive extension is available
    if (!class_exists('ZipArchive')) {
        die('Error: ZipArchive extension is not available. Please restart Apache to apply changes to php.ini.');
    }

    function replaceInFile($filePath, $replacements) {
        if (!file_exists($filePath)) {
            error_log("Warning: File not found for replacement: $filePath");
            return;
        }
        $content = file_get_contents($filePath);
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        file_put_contents($filePath, $content);
    }

    $tempDir = __DIR__ . '/temp_' . uniqid();
    mkdir($tempDir, 0777, true);

    $modelZip = __DIR__ . '/downloads/model.zip';
    if (!file_exists($modelZip)) {
        die('Model file not found. Please ensure model.zip exists in downloads folder.');
    }

    $zip = new ZipArchive;
    if ($zip->open($modelZip) === TRUE) {
        $zip->extractTo($tempDir);
        $zip->close();
    } else {
        die('Error opening extension model file.');
    }

    replaceInFile("$tempDir/images/custom.js", [
        'CONTACT_PAGE_PLACEHOLDER' => $_POST['CONTACT_PAGE'],
        'TUTORIAL_PAGE_PLACEHOLDER' => $_POST['TUTORIAL_PAGE'],
        'PRIVACY_PAGE_PLACEHOLDER' => $_POST['PRIVACY_PAGE'],
        'FEATURE_REQUEST_PAGE_PLACEHOLDER' => $_POST['FEATURE_REQUEST_PAGE'],
        'DOCUMENTATION_URL_PLACEHOLDER' => $_POST['DOCUMENTATION_URL'],
        'TRANSMISSIONS_PAGE_PLACEHOLDER' => $_POST['TRANSMISSIONS_PAGE'],
        'TABS_PAGE_PLACEHOLDER' => $_POST['TABS_PAGE'],
        'TEMPLATES_PAGE_PLACEHOLDER' => $_POST['TEMPLATES_PAGE'],
        'CHATBOT_PAGE_PLACEHOLDER' => $_POST['CHATBOT_PAGE'],
        'SCHEDULE_BROADCAST_PAGE_PLACEHOLDER' => $_POST['SCHEDULE_BROADCAST_PAGE'],
        'SCHEDULE_NOTIFICATIONS_PAGE_PLACEHOLDER' => $_POST['SCHEDULE_NOTIFICATIONS_PAGE'],
        'RAPID_RESPONSE_PAGE_PLACEHOLDER' => $_POST['RAPID_RESPONSE_PAGE'],
        'KANBAN_PAGE_PLACEHOLDER' => $_POST['KANBAN_PAGE'],
        'BLUR_PAGE_PLACEHOLDER' => $_POST['BLUR_PAGE'],
        'LINK_GENERATOR_PAGE_PLACEHOLDER' => $_POST['LINK_GENERATOR_PAGE'],
        'IMPORT_EXPORT_PAGE_PLACEHOLDER' => $_POST['IMPORT_EXPORT_PAGE'],
        'GOOGLE_LOGIN_PAGE_PLACEHOLDER' => $_POST['GOOGLE_LOGIN_PAGE'],
        'GOOGLE_CALENDAR_PAGE_PLACEHOLDER' => $_POST['GOOGLE_CALENDAR_PAGE'],
        'REMINDERS_PAGE_PLACEHOLDER' => $_POST['REMINDERS_PAGE'],
        'ONE_MONTH_LINK_PLACEHOLDER' => $_POST['ONE_MONTH_LINK'],
        'TWELVE_MONTH_LINK_PLACEHOLDER' => $_POST['TWELVE_MONTH_LINK'],
        'SUPPORT_NUMBER_PLACEHOLDER' => $_POST['SUPPORT_NUMBER'],
        'TEXT_SUPPORT_MESSAGE_PLACEHOLDER' => $_POST['TEXT_SUPPORT_MESSAGE'],
    ]);

    replaceInFile("$tempDir/background.bundle.js", [
        'UNINSTALL_URL_PLACEHOLDER' => $_POST['UNINSTALL_URL'],
        'INSTALL_URL_PLACEHOLDER' => $_POST['INSTALL_URL'],
    ]);

    replaceInFile("$tempDir/images/bootstrap.js", [
        'ENDPOINT_API_URL' => $_POST['API_ENDPOINT'],
        'URL_SITE' => $_POST['SITE_URL'],
    ]);

    $manifestPath = "$tempDir/manifest.json";
    if (!file_exists($manifestPath)) {
        die('manifest.json not found in extracted files.');
    }

    $manifest = json_decode(file_get_contents($manifestPath), true);
    $manifest['version'] = $_POST['version'];
    $manifest['name'] = $_POST['name'];
    $manifest['short_name'] = $_POST['short_name'];
    $manifest['description'] = $_POST['description'];
    $manifest['chrome_url_overrides'] = new stdClass();
    file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    $zipName = "extension_" . time() . ".zip";
    $zipNew = new ZipArchive;
    if (!$zipNew->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        die("Error creating new ZIP file.");
    }

    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($tempDir));
    foreach ($files as $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($tempDir) + 1);
            $zipNew->addFile($filePath, $relativePath);
        }
    }
    $zipNew->close();

    function deleteDirectory($dir) {
        if (!is_dir($dir)) return;
        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file) : unlink($file);
        }
        rmdir($dir);
    }

    deleteDirectory($tempDir);

    if (isset($_SESSION['user_type']) && in_array($_SESSION['user_type'], ['super_admin'])) {
        copy($zipName, __DIR__ . '/downloads/extension.zip');
    }

    if (ob_get_length()) ob_end_clean();
    clearstatcache();

    if (!file_exists($zipName) || filesize($zipName) === 0) {
        die("Error: Generated ZIP file is empty or missing.");
    }

    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header("Content-Disposition: attachment; filename=\"$zipName\"");
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($zipName));

    flush();
    readfile($zipName);
    unlink($zipName);
    exit;
}
?>

<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="Generate Extension">
    <meta name="keywords" content="admin template, dashboard, metrics">
    <meta name="author" content="ThemeSelect">
    <title>Extension</title>
    <link rel="apple-touch-icon" href="assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/ico/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Muli:300,300i,400,400i,600,600i,700,700i%7CComfortaa:300,400,700" rel="stylesheet">
    <link href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/vendors.css">
    <link rel="stylesheet" type="text/css" href="assets/css/app-lite.css">
    <link rel="stylesheet" type="text/css" href="assets/css/core/menu/menu-types/vertical-menu.css">
    <link rel="stylesheet" type="text/css" href="assets/css/core/colors/palette-gradient.css">
    <style>
        .form-section .form-group label {
            font-weight: 600;
        }
        form .form-section{
            border: none;
            line-height: 2;
        }
        #successMessage {
            animation: fadeIn 0.5s ease-in-out;
            margin-bottom: 50px;
            display: none;
            text-align: center;
            margin-top: 30px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .step {
            display: none;
        }
        .step.active {
            display: block;
        }
    </style>
</head>
<body>
<div class="app-content content">
    <div class="content-wrapper">
        <div <?= $style; ?> class="content-wrapper-before"></div>
        <div class="content-header row">
            <div class="content-header-left col-md-4 col-12 mb-2">
                <h3 class="content-header-title">Extension</h3>
            </div>
            <div class="content-header-right col-md-8 col-12">
                <div class="breadcrumbs-top float-md-right">
                    <div class="breadcrumb-wrapper mr-1">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active">Extension</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="col-xl-12 col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-block">
                        <form method="post" id="formulario">
                            <div class="card-body form-section">
                                <div class="step active" id="step-1">
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>Name</label>
                                            <p><small>Extension name that will be displayed</small></p>
                                            <input type="text" placeholder="Example: WhatsApp CRM" name="name" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Short Name</label>
                                            <p><small>Short name of the extension</small></p>
                                            <input type="text" placeholder="Example: WhatsApp" name="short_name" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Version</label>
                                            <p><small>Extension version</small></p>
                                            <input type="text" placeholder="Example: 1.0.0" name="version" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Description</label>
                                        <p><small>Extension description that will be displayed</small></p>
                                        <input type="text" placeholder="Example: Extension for WhatsApp Web" name="description" class="form-control" required>
                                    </div>
                                </div>

                                <div class="step" id="step-2">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>API Endpoint</label>
                                            <p><small>API connection URL</small></p>
                                            <input type="url" name="API_ENDPOINT" id="siteUrl" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Website URL</label>
                                            <p><small>Main website link</small></p>
                                            <input type="url" placeholder="Example: https://dropestore.com" name="SITE_URL" id="siteUrl" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Installation URL</label>
                                            <p><small>Link that will be opened when installing the extension</small></p>
                                            <input type="url" placeholder="Example: https://dropestore.com/install" name="INSTALL_URL" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Uninstall URL</label>
                                            <p><small>Link that will be opened when uninstalling the extension</small></p>
                                            <input type="url" placeholder="Example: https://dropestore.com/uninstall" name="UNINSTALL_URL" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Monthly Plan URL</label>
                                            <p><small>Monthly plan link</small></p>
                                            <input type="url" placeholder="Example: https://dropestore.com/monthly-plan" name="ONE_MONTH_LINK" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Annual Plan URL</label>
                                            <p><small>Annual plan link</small></p>
                                            <input type="url" placeholder="Example: https://dropestore.com/annual-plan" name="TWELVE_MONTH_LINK" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Support Contact</label>
                                                <p><small>Enter the support number in international format</small></p>
                                                <input type="text" placeholder="Example: 5582994229991" name="SUPPORT_NUMBER" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label>Support Message</label>
                                                <p><small>Enter the support message</small></p>
                                                <input type="text" placeholder="Example: I would like support for the extension" name="TEXT_SUPPORT_MESSAGE" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="step" id="step-3">
                                    <div class="form-group">
                                        <label>Contact Page</label>
                                        <p><small>Contact page link</small></p>
                                        <input type="url" placeholder="Example: https://dropestore.com/contact" name="CONTACT_PAGE" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Tutorial Page</label>
                                        <p><small>Tutorial page link</small></p>
                                        <input type="url" placeholder="Example: https://dropestore.com/tutorial" name="TUTORIAL_PAGE" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Privacy Page</label>
                                        <p><small>Privacy page link</small></p>
                                        <input type="url" placeholder="Example: https://dropestore.com/privacy" name="PRIVACY_PAGE" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Feature Request Page</label>
                                        <p><small>Feature request page link</small></p>
                                        <input type="url" placeholder="Example: https://dropestore.com/features" name="FEATURE_REQUEST_PAGE" class="form-control" required>
                                    </div>
                                </div>

                                <div class="step" id="step-4">
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Quick Transmissions Documentation</label>
                                            <p><small>Enter the documentation page link</small></p>
                                            <input type="url" placeholder="Example: https://dropestore.com/documentation" name="TRANSMISSIONS_PAGE" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Webhook Documentation</label>
                                            <p><small>Enter the documentation page link</small></p>
                                            <input type="url" placeholder="Example: https://dropestore.com/documentation" name="DOCUMENTATION_URL" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Custom Tabs Documentation</label>
                                            <p><small>Enter the documentation page link</small></p>
                                            <input type="url" placeholder="Example: https://dropestore.com/documentation" name="TABS_PAGE" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Templates Documentation</label>
                                            <p><small>Enter the documentation page link</small></p>
                                            <input type="url" placeholder="Example: https://dropestore.com/documentation" name="TEMPLATES_PAGE" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Documentação chatbot</label>
                                            <p><small>Informe o link da página de documentação</small></p>
                                            <input type="url" placeholder="Exemplo: https://dropestore.com/documentation" name="CHATBOT_PAGE" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Documentação transmissões programadas</label>
                                            <p><small>Informe o link da página de documentação</small></p>
                                            <input type="url" placeholder="Exemplo: https://dropestore.com/documentation" name="SCHEDULE_BROADCAST_PAGE" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Documentação notificações programadas</label>
                                            <p><small>Informe o link da página de documentação</small></p>
                                            <input type="url" placeholder="Exemplo: https://dropestore.com/documentation" name="SCHEDULE_NOTIFICATIONS_PAGE" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Documentação respostas rápidas</label>
                                            <p><small>Informe o link da página de documentação</small></p>
                                            <input type="url" placeholder="Exemplo: https://dropestore.com/documentation" name="RAPID_RESPONSE_PAGE" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Documentação kanban</label>
                                            <p><small>Informe o link da página de documentação</small></p>
                                            <input type="url" placeholder="Exemplo: https://dropestore.com/documentation" name="KANBAN_PAGE" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Documentação ferramentas</label>
                                            <p><small>Informe o link da página de documentação</small></p>
                                            <input type="url" placeholder="Exemplo: https://dropestore.com/documentation" name="BLUR_PAGE" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Documentação gerador de link</label>
                                            <p><small>Informe o link da página de documentação</small></p>
                                            <input type="url" placeholder="Exemplo: https://dropestore.com/documentation" name="LINK_GENERATOR_PAGE" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Documentação import/export</label>
                                            <p><small>Informe o link da página de documentação</small></p>
                                            <input type="url" placeholder="Exemplo: https://dropestore.com/documentation" name="IMPORT_EXPORT_PAGE" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Documentação login do Google</label>
                                            <p><small>Informe o link da página de documentação</small></p>
                                            <input type="url" placeholder="Exemplo: https://dropestore.com/documentation" name="GOOGLE_LOGIN_PAGE" class="form-control" required>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Documentação calendário do Google</label>
                                            <p><small>Informe o link da página de documentação</small></p>
                                            <input type="url" placeholder="Exemplo: https://dropestore.com/documentation" name="GOOGLE_CALENDAR_PAGE" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Documentação lembretes</label>
                                            <p><small>Informe o link da página de documentação</small></p>
                                            <input type="url" placeholder="Exemplo: https://dropestore.com/documentation" name="REMINDERS_PAGE" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group d-flex justify-content-between">
                                    <button type="button" id="prevBtn" class="btn btn-secondary" onclick="nextPrev(-1)">Back</button>
                                    <button type="button" id="nextBtn" class="btn btn-primary" onclick="nextPrev(1)">Próximo</button>
                                </div>
                                
                            </div>
                        </form>
                        <div id="successMessage">
                            <div style="font-size: 40px; color: green;">✔</div>
                            <p style="margin: 10px 0; font-weight: bold;">Extension gerada com sucesso!</p>
                            <p style="color: #4b5563;">O download iniciará automaticamente.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include("include/header.php"); ?>
<?php include("include/sidebar.php"); ?>
<?php include("include/footer.php"); ?>
<script>
    let currentStep = 0;
    const steps = document.querySelectorAll(".step");
    const prevBtn = document.getElementById("prevBtn");
    const nextBtn = document.getElementById("nextBtn");

    function showStep(n) {
        steps.forEach((step, index) => step.classList.toggle("active", index === n));
        prevBtn.style.display = n === 0 ? "none" : "inline-block";
        nextBtn.textContent = n === steps.length - 1 ? "Gerar extensão" : "Next";
    }

    function validateForm() {
        const inputs = steps[currentStep].querySelectorAll("input");
        for (let input of inputs) {
            if (!input.reportValidity()) {
                return false;
            }
        }
        return true;
    }

    function nextPrev(n) {
        if (n === 1 && !validateForm()) return;

        currentStep += n;
        if (currentStep >= steps.length) {
            document.getElementById("formulario").style.display = "none";
            document.getElementById("successMessage").style.display = "block";
            const cardHeader = document.querySelector(".card-header");
            if (cardHeader) {
                cardHeader.style.display = "none";
            }
            setTimeout(() => {
                document.getElementById("formulario").submit();
            }, 1200);
            return;
        }

        showStep(currentStep);
    }

    showStep(currentStep);

    window.addEventListener('DOMContentLoaded', () => {
        const baseUrl = window.location.origin + '/';
        document.getElementById('siteUrl').value = baseUrl;
    });
</script>
</body>
</html>
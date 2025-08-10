<?php
include("include/conn.php");
include("include/function.php");

$config = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM configurations WHERE id = 1"));

$supportPhoneNumber = $config['support_phone_number'];
$trialKeyValidity = (int) $config['trial_key_validity'];
$admin_id = 1;
$message = '';

if (isset($_POST['submit'])) {
    $wnumber = preg_replace('/[^0-9]/', '', $_POST['mobileNumber']);
    $fname = trim($_POST['firstName']);
    $lname = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $cname = "$fname $lname";
    $today = date("Y-m-d H:i:s");

    // Verifica duplicidade
    $stmtCheck = $conn->prepare("SELECT id, license_key, end_date FROM users WHERE whatsapp_number = ? ORDER BY id DESC LIMIT 1");
    $stmtCheck->bind_param("s", $wnumber);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $licenseKey = $user['license_key'];
        $message = "<span class='error'>Esse número já foi cadastrado.<br>License existente: <strong>$licenseKey</strong></span>";
        $stmtCheck->close();
    } else {
        $stmtCheck->close();

        $endDate = date("Y-m-d H:i:s", strtotime("+$trialKeyValidity days"));
        $licenseKey = generate_license();

        $stmtInsert = $conn->prepare("INSERT INTO users (user_id, customer_name, whatsapp_number, license_key, act_date, end_date, life_time, plan_type, email) VALUES (?, ?, ?, ?, ?, ?, 'false', 'Trial', ?)");
        $stmtInsert->bind_param("issssss", $admin_id, $cname, $wnumber, $licenseKey, $today, $endDate, $email);

        if ($stmtInsert->execute()) {
          // Envia para API WordPress
          $emailPurchase = $config['email'];

          $payload = json_encode([
            'email_purchase' => $emailPurchase,
            'wnumber'        => $wnumber,
            'validity'       => $trialKeyValidity,
            'cname'          => $cname,
            'email_client'   => $email,
            'status'         => 'true'
          ]);

          $ch = curl_init(api_url("/criar"));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
          curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
          $apiResponse = curl_exec($ch);
          $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
          curl_close($ch);

          if (curl_errno($ch)) {
              $error_msg = curl_error($ch);
              $message = "<span class='error'>Error cURL: $error_msg</span>";
          } else {
              $decoded = json_decode($apiResponse, true);
              if ($httpCode === 200 && isset($decoded['success']) && $decoded['success'] === true) {
                $licenseFromAPI = $decoded['data']['license'] ?? $licenseKey;

                if ($licenseFromAPI !== $licenseKey) {
                    // Atualiza a licença local com a chave real gerada na camada 3
                    $stmtUpdate = $conn->prepare("UPDATE users SET license_key = ? WHERE license_key = ?");
                    $stmtUpdate->bind_param("ss", $licenseFromAPI, $licenseKey);
                    $stmtUpdate->execute();
                    $stmtUpdate->close();

                    // Atualiza a variável local para exibição
                    $licenseKey = $licenseFromAPI;
                }

                $message = "<span class='success'><i class='fa fa-key'></i>&nbsp; License gerada: <span id='key'><strong>$licenseKey</strong></span></span> <span style='margin-left:15px;'><i class='fa fa-copy' id='copy_btn' style='color:#04d88a;font-weight:bold;cursor:pointer;'></i></span>";
            }
          }
      } else {
          $message = "<span class='error'>Error ao salvar a licença. Tente novamente.</span>";
      }

        $stmtInsert->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>WhatsApp CRM - Trial</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="shortcut icon" type="image/x-icon" href="assets/images/ico/favicon.ico">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700&display=swap');
    *,body{margin:0;padding:0}.btn,label{font-weight:600}.footer-dev,.header-dev{text-align:center;font-family:Figtree,sans-serif}*,.footer-dev,.header-dev{font-family:Figtree,sans-serif}.container,.footer-dev,.header-dev{max-width:1000px;width:100%}*{box-sizing:border-box}body{background:#111;min-height:100vh}.page-wrapper{display:flex;flex-direction:column;min-height:100vh;align-items:center;justify-content:center;padding:40px 20px}.container{background:#fff;border-radius:10px;box-shadow:0 5px 20px rgba(0,0,0,.1);display:flex;flex-wrap:wrap;overflow:hidden}.form-section,.tutorial-section{padding:40px;flex:1 1 50%}.form-section{border-right:1px solid #eee}.row{display:flex;gap:15px;margin-bottom:15px;flex-wrap:wrap}.row>div{flex:1;min-width:200px}label{font-size:14px;margin-bottom:5px;display:block;color:#333}.btn,input{font-size:15px}input{width:100%;padding:12px 14px;border:1px solid #ccc;border-radius:5px}.btn{padding:12px 20px;border:none;border-radius:5px;text-decoration:none;display:inline-block;margin-top:10px;cursor:pointer}.btn-warning{background:#fcd00d;color:#000;margin-right:10px}.btn-secondary{background:#eaeaea;color:#000}#response-message{margin-top:20px;background:#d3f8d3;padding:10px;border-radius:6px;font-weight:600}.tutorial-section h3{font-size:20px;margin-bottom:15px}.tutorial-section ol{padding-left:20px;margin-bottom:10px}.tutorial-section li{margin-bottom:10px;line-height:1.6}.tutorial-section code{background:#eee;padding:2px 6px;border-radius:3px;font-size:14px}.tutorial-section .note{background:#fcf8e3;padding:10px;border-left:4px solid #fcd00d;font-size:14px;color:#665c00;border-radius:4px}@media (max-width:900px){.container{flex-direction:column}.form-section{border-right:none;border-bottom:1px solid #ddd}}.footer-dev{font-size:14px;color:#eaeaea;margin-top:20px}.header-dev{font-size:14px;color:#444;margin-bottom:20px}
  </style>
</head>
<body>
    <div class="page-wrapper">
    <div class="header-dev">
      <a href="https://dropestore.com" target="_blank">
        <img src="assets/images/logo-white.png" alt="DROPE">
      </a>
    </div>
    <div class="container">
      <div class="form-section">
        <form method="POST">
          <div class="row">
            <div>
              <label>Name</label>
              <input type="text" name="firstName" required>
            </div>
            <div>
              <label>Sobrenome</label>
              <input type="text" name="lastName">
            </div>
          </div>
          <div class="row">
            <div>
              <label>Email</label>
              <input type="email" name="email">
            </div>
          </div>
          <div class="row">
            <div>
              <label>WhatsApp</label>
              <input type="tel" name="mobileNumber" id="mobileNumber" required>
            </div>
          </div>
          <div class="row">
            <button type="submit" name="submit" class="btn btn-warning"><i class="fa fa-key"></i> Gerar licença</button>
            <a id="downloadBtn" class="btn btn-secondary">
                <i class="fa fa-download"></i> Download extensão
            </a>
          </div>
        </form>
        <?php if (!empty($message)) : ?>
          <div id="response-message"><?= $message; ?></div>
        <?php endif; ?>
      </div>
      <div class="tutorial-section">
        <h3><i class="fa fa-chrome"></i> Como instalar a extensão no Chrome</h3>
        <ol>
          <li>Abra o navegador <strong>Google Chrome</strong>.</li>
          <li>Digite <code>chrome://extensions</code> e pressione <strong>Enter</strong>.</li>
          <li>Ative o <strong>Modo do desenvolvedor</strong>.</li>
          <li>Clique em <strong>“Carregar sem compactação”</strong>.</li>
          <li>Selecione a pasta da extensão que você baixou.</li>
          <li>A extensão estará pronta para uso.</li>
        </ol>
        <div class="note"><i class="fa fa-lightbulb-o"></i> Dica: mantenha a pasta em local fixo para evitar erros futuros.</div>
      </div>
    </div>
    <div class="footer-dev">
      <p>Desenvolvido por <strong>DROPE</strong></p>
    </div>
</div>
<script>
  const input = document.querySelector("#mobileNumber");
  const iti = window.intlTelInput(input, {
    initialCountry: "br",
    preferredCountries: ["br", "us", "in"],
    separateDialCode: true,
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"
  });
  let cleave = new Cleave(input, {
    delimiters: [' ', ' ', '-'],
    blocks: [2, 5, 4],
    numericOnly: true
  });
  input.addEventListener("countrychange", function () {
    cleave.destroy();
    cleave = new Cleave(input, {
      delimiters: [' ', ' ', '-'],
      blocks: [2, 5, 4],
      numericOnly: true
    });
  });
  document.querySelector("form").addEventListener("submit", function () {
    input.value = iti.getNumber();
  });
  document.getElementById("copy_btn")?.addEventListener("click", function () {
    let text = document.getElementById("key").innerText;
    navigator.clipboard.writeText(text).then(() => alert("License copiada!"));
  });
  document.addEventListener("DOMContentLoaded", function () {
        const baseUrl = window.location.origin;
        const downloadLink = baseUrl + "/downloads/extension.zip";
        document.getElementById("downloadBtn").href = downloadLink;
    });
</script>
</body>
</html>

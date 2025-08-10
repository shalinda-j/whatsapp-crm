<?php
include("include/conn.php");
include("include/function.php");

$config = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM configurations WHERE id = 1"));
$emailPurchase = $config['email'] ?? '';

$message = '';
$licenseDate = null;
$readonlyLicense = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $license = trim($_POST['license'] ?? '');
    $newWhatsapp = preg_replace('/[^0-9]/', '', $_POST['whatsapp'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND license_key = ?");
    $stmt->bind_param("ss", $email, $license);
    $stmt->execute();
    $result = $stmt->get_result();
    $licenseDate = $result->fetch_assoc();
    $stmt->close();

    if (!$licenseDate) {
        $message = "<span class='error'>License ou email inválido.</span>";
    } elseif (!empty($newWhatsapp)) {
        $stmt = $conn->prepare("UPDATE users SET whatsapp_number = ? WHERE id = ?");
        $stmt->bind_param("si", $newWhatsapp, $licenseDate['id']);
        $stmt->execute();
        $stmt->close();

        $today = new DateTime();
        $end = new DateTime($licenseDate['end_date']);
        $validity = $today->diff($end)->days;

        $payload = [
            "email_purchase" => $emailPurchase,
            "license_key"    => $licenseDate['license_key'],
            "cname"          => $licenseDate['customer_name'],
            "wnumber"        => $newWhatsapp,
            "validity"       => $validity,
            "status"         => $licenseDate['status']
        ];

        $ch = curl_init(api_url("/editar"));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $result = curl_exec($ch);
        curl_close($ch);

        $message = "<span class='success'>Telefone atualizado com sucesso!</span>";
        $licenseDate['whatsapp_number'] = $newWhatsapp;
    }

    if ($licenseDate) {
        $readonlyLicense = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Telefone - WhatsApp CRM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/intlTelInput.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
  <style>
    /* mesmo CSS da tela original que você passou */
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
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
          </div>
          <div>
            <label>License</label>
            <input type="text" name="license" value="<?= htmlspecialchars($_POST['license'] ?? '') ?>" required <?= $readonlyLicense ? 'readonly style="background:#eee;cursor:not-allowed;"' : '' ?>>
        </div>
        </div>

        <?php if ($licenseDate) : ?>
        <div class="row">
          <div>
            <label>New WhatsApp</label>
            <input type="tel" name="whatsapp" id="whatsapp" value="<?= htmlspecialchars($licenseDate['whatsapp_number']) ?>" required>
          </div>
        </div>
        <?php endif; ?>

        <div class="row">
          <button type="submit" class="btn btn-warning"><i class="fa fa-edit"></i> Update telefone</button>
        </div>

        <div style="margin-top: 10px; font-size: 14px; background: #1daa61; padding: 12px; border-left: 4px solid #148040; border-radius: 4px; color: #fff;">
            <i class="fa fa-exclamation-circle" style="margin-right: 5px;"></i>
            <strong>Atenção:</strong> o número de telefone deve ser inserido exatamente como aparece no seu WhatsApp.<br>
            Se o seu número no WhatsApp possui o dígito <strong>9</strong>, ele deve ser incluído aqui. Caso não tenha, não inclua.
        </div>
      </form>

      <?php if (!empty($message)) : ?>
        <div id="response-message"><?= $message; ?></div>
      <?php endif; ?>
    </div>
    <div class="tutorial-section">
  <h3><i class="fa fa-edit"></i> Como atualizar seu número</h3>
  <ol>
    <li>Informe o <strong>email</strong> utilizado no cadastro.</li>
    <li>Digite a sua <strong>chave de licença</strong>.</li>
    <li>Clique em <strong>"Update telefone"</strong>.</li>
    <li>Se os dados estiverem corretos, será exibido o campo com seu número atual de WhatsApp.</li>
    <li>Atualize o número conforme necessário e clique novamente em <strong>"Update telefone"</strong>.</li>
  </ol>
  <div class="note">
    <i class="fa fa-info-circle"></i>
    Se você tiver perdido sua licença, entre em contato com o suporte via WhatsApp.
  </div>
</div>

  </div>
  <div class="footer-dev">
    <p>Desenvolvido por <strong>DROPE</strong></p>
  </div>
</div>

<script>
const input = document.querySelector("#whatsapp");
if (input) {
  const iti = window.intlTelInput(input, {
    initialCountry: "br",
    preferredCountries: ["br", "us"],
    separateDialCode: true,
    utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.js"
  });
  let cleave = new Cleave(input, {
    delimiters: [' ', ' ', '-'],
    blocks: [2, 5, 4],
    numericOnly: true
  });
  input.closest("form").addEventListener("submit", function () {
    input.value = iti.getNumber();
  });
}
</script>
</body>
</html>

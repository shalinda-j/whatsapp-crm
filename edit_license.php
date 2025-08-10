<?php
include("include/conn.php");
include("include/function.php");

$login = cekSession();
if ($login != 1) {
    redirect("login.php");
}
check_license_if_needed($conn);
if (!is_license_active($conn)) {
    header("Location: license.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT `id`, `customer_name`, `whatsapp_number`, `email`, `license_key`, `end_date`, `status` FROM `users` WHERE `id` = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $license = $result->fetch_assoc();
    $stmt->close();

    if (!$license) {
        echo "License not found!";
        exit;
    }

    $end_date = new DateTime($license['end_date']);
    $today = new DateTime();
    $validity = $today->diff($end_date)->days;
} else {
    echo "No license ID provided!";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = $_POST['customer_name'];
    $whatsapp_number = $_POST['whatsapp_number'];
    $end_date_input = $_POST['end_date'];
    $user_input_date = date('Y-m-d H:i:s', strtotime($_POST['end_date']));
    $now = new DateTime();
    $exp_date = new DateTime($user_input_date);

    if ($exp_date < $now) {
        $status = 'false';
    } else {
        $status = $_POST['status'] === 'true' ? 'true' : 'false';
    }

    $user_input_date = date('Y-m-d H:i:s', strtotime($end_date_input));
    $original_end_date = $license['end_date'];

    if ($user_input_date !== $original_end_date) {
        $datetime = $user_input_date;
        $data_alterada = true;
    } else {
        $datetime = $original_end_date;
        $data_alterada = false;
    }

    $stmt = $conn->prepare("UPDATE `users` SET `customer_name` = ?, `whatsapp_number` = ?, `end_date` = ?, `status` = ? WHERE `id` = ?");
    $stmt->bind_param("ssssi", $customer_name, $whatsapp_number, $datetime, $status, $id);

    if ($stmt->execute()) {
        $config = mysqli_fetch_assoc(mysqli_query($conn, "SELECT email FROM configurations WHERE id = 1"));
        $emailPurchase = $config['email'] ?? '';

        // Local model: no external API call needed.

        header("Location: all-licenses.php");
        exit;
    } else {
        echo "Errorr updating license: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-textdirection="ltr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="Chameleon Admin - Edit licença">
    <meta name="keywords"
        content="admin template, Chameleon admin template, dashboard template, gradient admin template, responsive admin template, webapp, eCommerce dashboard, analytic dashboard">
    <meta name="author" content="ThemeSelect">
    <title>Edit licença</title>
    <link rel="apple-touch-icon" href="assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/ico/favicon.ico">
    <link
        href="https://fonts.googleapis.com/css?family=Muli:300,300i,400,400i,600,600i,700,700i%7CComfortaa:300,400,700"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="assets/css/vendors.css">
    <link rel="stylesheet" type="text/css" href="assets/css/app-lite.css">
    <link rel="stylesheet" type="text/css" href="assets/css/core/menu/menu-types/vertical-menu.css">
    <link rel="stylesheet" type="text/css" href="assets/css/core/colors/palette-gradient.css">
    <style>
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input, select { width: 100%; padding: 8px; box-sizing: border-box; }
        .btn-submit { background-color: #007bff; color: #fff; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-submit:hover { background-color: #0056b3; }
    </style>
</head>
<body>
<?php include("include/header.php"); include("include/sidebar.php"); ?>
<div class="app-content content">
    <div class="content-wrapper">
        <div  <?= $style; ?> class="content-wrapper-before"></div>
        <div class="content-header row">
            <div class="content-header-left col-md-4 col-12 mb-2">
                <h3 class="content-header-title">Edit licença</h3>
            </div>
            <div class="content-header-right col-md-8 col-12">
                <div class="breadcrumbs-top float-md-right">
                    <div class="breadcrumb-wrapper mr-1">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="index.php">Licenses</a></li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-content collapse show">
                            <div class="card-body">
                                <form method="post" action="">
                                    <div class="form-group">
                                        <label for="customer_name">Name</label>
                                        <input type="text" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($license['customer_name']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="whatsapp_number">WhatsApp</label>
                                        <input type="text" id="whatsapp_number" name="whatsapp_number" value="<?php echo htmlspecialchars($license['whatsapp_number']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="end_date">Expiration Date</label>
                                        <input type="datetime-local" id="end_date" name="end_date" value="<?php echo date('Y-m-d\TH:i', strtotime($license['end_date'])); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select name="status" id="status" required>
                                            <option value="true" <?php if ($license['status'] === 'true') echo 'selected'; ?>>Ativa</option>
                                            <option value="false" <?php if ($license['status'] === 'false') echo 'selected'; ?>>Inativa</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn-submit">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Edit licença end -->
        </div>
    </div>
</div>
<?php include("include/footer.php"); ?>
<script src="assets/vendors/js/vendors.min.js"></script>
<script src="assets/js/core/app-menu-lite.js"></script>
<script src="assets/js/core/app-lite.js"></script>
</body>
</html>

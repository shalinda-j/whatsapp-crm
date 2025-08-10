    <?php
    include("include/conn.php");
    include("include/function.php");
    $login = cekSession();

    $config = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM configurations where id = 1"));

    $emailPurchase = $config['email'];
    $supportPhoneNumber = $config['support_phone_number'];
    $trialKeyValidity = $config['trial_key_validity'];
    $color_background = $config['color_background'];
    $colorText = $config['color_text'];

    if ($login != 1) {
        redirect("login.php");
    }
    check_license_if_needed($conn);
    if (!is_license_active($conn)) {
        header("Location: license.php");
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html class="loading" lang="en" data-textdirection="ltr">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <meta name="description" content="Chameleon Admin is a modern Bootstrap 4 webapp &amp; admin dashboard html template with a large number of components, elegant design, clean and organized code.">
        <meta name="keywords" content="admin template, Chameleon admin template, dashboard template, gradient admin template, responsive admin template, webapp, eCommerce dashboard, analytic dashboard">
        <meta name="author" content="ThemeSelect">
        <title id="title">Settings</title>
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
    <?php
    include("include/header.php");
    include("include/sidebar.php");
    ?>
    <style>
        #response-message {
            text-align: center;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 18px;
            font-weight: bold;
        }

        .success {
            background-color: #4CAF50;
            color: #fff;
            border: 2px solid #45A049;
        }

        .error {
            background-color: #FF5733;
            color: #fff;
            border: 2px solid #D73925;
        }
    </style>

    <div class="app-content content">
        <div class="content-wrapper">
            <div  <?= $style; ?> class="content-wrapper-before"></div>
            <div class="content-header row">
                <div class="content-header-left col-md-4 col-12 mb-2">
                    <h3 class="content-header-title">Settings</h3>
                </div>
                <div class="content-header-right col-md-8 col-12">
                    <div class="breadcrumbs-top float-md-right">
                        <div class="breadcrumb-wrapper mr-1">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                                <li class="breadcrumb-item active">Settings</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body"><!-- Basic Inputs start -->

                <div class="col-xl-12 col-lg-12 col-md-12">
                    <div class="card">
                        <div class="card-block">
                            <form id="reseller">
                                <div class="card-body">
                                    <fieldset class="form-group">
                                        <h6 class="card-title">Cor de estilo</h6>
                                        <input type="color"  id="bgColor" name="bgColor" style='height:30px;width:50px;' value='<?= $color_background; ?>'>
                                    </fieldset>
                                    <fieldset class="form-group">
                                        <h6 class="card-title">Cor do texto</h6>
                                        <input type="color"  id="txtColor" name="txtColor" style='height:30px;width:50px;' value='<?= $colorText; ?>'>
                                    </fieldset>
                                    <fieldset class="form-group">
                                        <h6 class="card-title">Email de compra</h6>
                                        <input type="email" class="form-control" id="emailPurchase" name="emailPurchase" placeholder='Email utilizado na DROPE' value='<?= $emailPurchase; ?>'>
                                    </fieldset>
                                    <fieldset class="form-group">
                                        <h6 class="card-title">WhatsApp de suporte</h6>
                                        <input type="number" class="form-control" id="supportMobileNumber" name="supportMobileNumber" placeholder='with country code...' value='<?= $supportPhoneNumber; ?>'>
                                    </fieldset>
                                    <fieldset class="form-group">
                                        <h6 class="card-title">Dias de teste (trial)</h6>
                                        <input type="number" class="form-control" id="trialValidity" name="trialValidity" value='<?= $trialKeyValidity; ?>'>
                                    </fieldset>
                                    <br>
                                    <fieldset class="form-group">
                                        <button  <?= $style; ?> type="button" id="add-reseller" class="btn btn-info btn-min-width mr-1 mb-1">Save</button>
                                    </fieldset>
                                </div>
                            </form>
                            <div id="response-message"></div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <!-- ////////////////////////////////////////////////////////////////////////////-->

    <?php include("include/footer.php"); ?>
    <script src="assets/vendors/js/vendors.min.js" type="text/javascript"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="assets/js/core/app-menu-lite.js" type="text/javascript"></script>
    <script src="assets/js/core/app-lite.js" type="text/javascript"></script>
    <script src="assets/vendors/js/forms/tags/form-field.js" type="text/javascript"></script>

    <script>
    $(document).ready(function() {
        $('.datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            minDate: 0
        });

        $('#add-reseller').click(function() {
            var bgColor = $('#bgColor').val();
            var txtColor = $('#txtColor').val();
            var emailPurchase = $('#emailPurchase').val();
            var supportNumber = $('#supportMobileNumber').val();
            var trialValidity = $('#trialValidity').val();
            
            $.ajax({
                url: 'function/configuration.php',
                type: 'POST',
                data: {
                    bgColor: bgColor,
                    txtColor: txtColor,
                    emailPurchase: emailPurchase,
                    supportNumber: supportNumber,
                    trialValidity: trialValidity,
                    status: true
                },
                success: function(response) {
                    if (response.status) {
                        $('#response-message').html('<div class="success">' + response.message + '</div>');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        $('#response-message').html('<div class="error">' + response.message + '</div>');
                    }
                },
                error: function() {
                    $('#response-message').html('<div class="error">An error occurred.</div>');
                }
            });
        });
    });
    </script>

    </body>

    </html>

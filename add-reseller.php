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
    <title id="title"></title>
    <link rel="apple-touch-icon" href="assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/ico/favicon.ico">
    <link href="https://fonts.googleapis.com/css?family=Muli:300,300i,400,400i,600,600i,700,700i%7CComfortaa:300,400,700" rel="stylesheet">
    <link href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css" rel="stylesheet">
    <!-- BEGIN VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/vendors.css">
    <!-- END VENDOR CSS-->
    <!-- BEGIN CHAMELEON  CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/app-lite.css">
    <!-- END CHAMELEON  CSS-->
    <!-- BEGIN Page Level CSS-->
    <link rel="stylesheet" type="text/css" href="assets/css/core/menu/menu-types/vertical-menu.css">
    <link rel="stylesheet" type="text/css" href="assets/css/core/colors/palette-gradient.css">
    <!-- END Page Level CSS-->
    <!-- BEGIN Custom CSS-->
    <link rel="stylesheet" type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- END Custom CSS-->
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
                <h3 class="content-header-title">Add revendedor</h3>
            </div>
            <div class="content-header-right col-md-8 col-12">
                <div class="breadcrumbs-top float-md-right">
                    <div class="breadcrumb-wrapper mr-1">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item">Add revendedor</li>
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
                                    <h6 class="card-title">Name</h6>
                                    <input type="text" class="form-control" id="cname" name="cname" required>
                                </fieldset>
                                <fieldset class="form-group">
                                    <h6 class="card-title">WhatsApp (com código do país)</h6>
                                    <input type="number" class="form-control" id="wnumber" name="wnumber" required>
                                </fieldset>
                                <fieldset class="form-group">
                                    <h6 class="card-title">User</h6>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </fieldset>
                                <fieldset class="form-group">
                                    <h6 class="card-title">Password</h6>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </fieldset>
                                <fieldset class="form-group">
                                    <h6 class="card-title">Date de início</h6>
                                    <input type="text" class="form-control datepicker" id="start_date" name="start_date" required>
                                </fieldset>
                                <fieldset class="form-group">
                                    <h6 class="card-title">Date de expiração</h6>
                                    <input type="text" class="form-control datepicker" id="expired_date" name="expired_date" required>
                                </fieldset>
                                <br>
                                <fieldset class="form-group">
                                    <button  <?= $style; ?> type="button" id="add-reseller" class="btn btn-info btn-min-width mr-1 mb-1">Add</button>
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
<!-- BEGIN VENDOR JS-->
<script src="assets/vendors/js/vendors.min.js" type="text/javascript"></script>
<!-- BEGIN PAGE VENDOR JS-->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<!-- BEGIN CHAMELEON  JS-->
<script src="assets/js/core/app-menu-lite.js" type="text/javascript"></script>
<script src="assets/js/core/app-lite.js" type="text/javascript"></script>
<!-- END CHAMELEON  JS-->
<!-- BEGIN PAGE LEVEL JS-->
<script src="assets/vendors/js/forms/tags/form-field.js" type="text/javascript"></script>
<!-- END PAGE LEVEL JS-->

<script>
$(document).ready(function() {
    // Initialize datepickers
    $('.datepicker').datepicker({
        dateFormat: 'yy-mm-dd', // Format for the date
        minDate: 0 // Prevents selection of past dates
    });

    // Handle form submission
    $('#add-reseller').click(function() {
        var cname = $('#cname').val();
        var wnumber = $('#wnumber').val();
        var username = $('#username').val();
        var password = $('#password').val();
        var startDate = $('#start_date').val();
        var expiredDate = $('#expired_date').val();

        $.ajax({
            url: 'function/add-reseller.php',
            type: 'POST',
            data: {
                cname: cname,
                wnumber: wnumber,
                username: username,
                password: password,
                start_date: startDate,
                expired_date: expiredDate,
                user_type: 'reseller',
                status: true
            },
            success: function(response) {
                if (response.status) {
                    $('#response-message').html('<div class="success">' + response.message + '</div>');
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

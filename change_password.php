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
    <title id="title">Change Password</title>
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
    <!-- END Custom CSS-->
</head>
<?php
include("include/header.php");
include("include/sidebar.php");
?>
<style>
    #response-message {
        text-align: center;
        /* Center-align the text horizontally */
        padding: 20px;
        /* Add padding for spacing */
        border: 1px solid #ddd;
        /* Add a border */
        border-radius: 5px;
        /* Round the corners */
        margin-top: 20px;
        /* Add some margin to separate it from other elements */
        font-size: 18px;
        /* Set the font size */
        font-weight: bold;
        /* Make the text bold */
    }

    /* Style for success messages */
    .success {
        background-color: #4CAF50;
        /* Green background color */
        color: #fff;
        /* White text color */
        border: 2px solid #45A049;
        /* Green border */
    }

    /* Style for error messages */
    .error {
        background-color: #FF5733;
        /* Red background color */
        color: #fff;
        /* White text color */
        border: 2px solid #D73925;
        /* Red border */
    }
</style>

<div class="app-content content">
    <div class="content-wrapper">
        <div <?= $style; ?> class="content-wrapper-before"></div>
        <div class="content-header row">
            <div class="content-header-left col-md-4 col-12 mb-2">
                <h3 class="content-header-title">Change Password</h3>
            </div>
            <div class="content-header-right col-md-8 col-12">
                <div class="breadcrumbs-top float-md-right">
                    <div class="breadcrumb-wrapper mr-1">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Home</a>
                            </li>
                            <li class="breadcrumb-item active">Change Password
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body"><!-- Basic Inputs start -->

            <div class="col-xl-12 col-lg-12 col-md-12">
                <div class="card">
                    <div class="card-block">
                        <form id="changePasswordForm">
                            <div class="card-body">

                                <fieldset class="form-group">
                                    <h6 class="card-title">Password atual</h6>
                                    <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                                </fieldset>

                                <fieldset class="form-group">
                                    <h6 class="card-title">Nova senha</h6>
                                    <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                                </fieldset>

                                <fieldset class="form-group">
                                    <h6 class="card-title">Confirm nova senha</h6>
                                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                                </fieldset>

                                <button type="button" id="submitChangePassword" class="btn btn-secondary btn-glow">Save</button>
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
<!-- BEGIN VENDOR JS-->
<!-- BEGIN PAGE VENDOR JS-->
<!-- END PAGE VENDOR JS-->
<!-- BEGIN CHAMELEON  JS-->
<script src="assets/js/core/app-menu-lite.js" type="text/javascript"></script>
<script src="assets/js/core/app-lite.js" type="text/javascript"></script>
<!-- END CHAMELEON  JS-->
<!-- BEGIN PAGE LEVEL JS-->
<script src="assets/vendors/js/forms/tags/form-field.js" type="text/javascript"></script>
<!-- END PAGE LEVEL JS-->

<script>
    $(document).ready(function() {
        $('#title').html('Change Password')
    });
    document.getElementById("sid-add").classList.add("active");
</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#submitChangePassword').click(function () {
            var currentPassword = $('#currentPassword').val();
            var newPassword = $('#newPassword').val();
            var confirmPassword = $('#confirmPassword').val();

            // Validação básica
            if (!currentPassword || !newPassword || !confirmPassword) {
                $('#response-message').html('<div class="error">Preencha todos os campos.</div>');
                return;
            }

            if (newPassword !== confirmPassword) {
                $('#response-message').html('<div class="error">As novas senhas não coincidem.</div>');
                return;
            }

            // Requisição AJAX
            $.ajax({
                type: 'POST',
                url: 'function/change_password.php',
                dataType: 'json',
                data: {
                    current_password: currentPassword,
                    new_password: newPassword
                },
                success: function (response) {
                    if (response.status === true) {
                        $('#response-message').html('<div class="success">' + response.message + '</div>');
                        $('#changePasswordForm')[0].reset();
                    } else {
                        $('#response-message').html('<div class="error">' + response.message + '</div>');
                    }
                },
                error: function () {
                    $('#response-message').html('<div class="error">Error ao processar a requisição.</div>');
                }
            });
        });
    });
</script>

</body>

</html>
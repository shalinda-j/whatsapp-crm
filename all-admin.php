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
  <meta name="description"
    content="Chameleon Admin is a modern Bootstrap 4 webapp &amp; admin dashboard html template with a large number of components, elegant design, clean and organized code.">
  <meta name="keywords"
    content="admin template, Chameleon admin template, dashboard template, gradient admin template, responsive admin template, webapp, eCommerce dashboard, analytic dashboard">
  <meta name="author" content="ThemeSelect">
  <title id="title">Administrators</title>
  <link rel="apple-touch-icon" href="assets/images/ico/apple-icon-120.png">
  <link rel="shortcut icon" type="image/x-icon" href="assets/images/ico/favicon.ico">
  <link href="https://fonts.googleapis.com/css?family=Muli:300,300i,400,400i,600,600i,700,700i%7CComfortaa:300,400,700"
    rel="stylesheet">
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
  <link rel="stylesheet" type="text/css" href="assets/css/core/colors/palette-gradient.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
  <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
  <!-- END Custom CSS-->
</head>
<?php
include("include/header.php");
include("include/sidebar.php");
?>
<div class="app-content content">
  <div class="content-wrapper">
    <div  <?= $style; ?> class="content-wrapper-before"></div>
    <div class="content-header row">
      <div class="content-header-left col-md-4 col-12 mb-2">
        <h3 class="content-header-title">Administrators</h3>
      </div>
      <div class="content-header-right col-md-8 col-12">
        <div class="breadcrumbs-top float-md-right">
          <div class="breadcrumb-wrapper mr-1">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="index.php">Home</a>
              </li>
              <li class="breadcrumb-item active">Administrators
              </li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <div class="content-body"><!-- Basic All Licenses start -->


      <!-- Bordered table start -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <div>
                <h4 class="card-title mb-0">Administrators</h4>
                <p class="mb-0">Abaixo está a lista com todos os administradores adicionados</p>
              </div>
              <a href="add-admin.php" class="btn btn-secondary btn-glow">
                <i class="la la-user-plus mr-1"></i> Add
              </a>
            </div>
            <div class="card-content collapse show">
              <div class="table-responsive" style="padding:20px;">
                <table class="table table-bordered mb-0" id="resellerTable">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>User</th>
                      <th>Name</th>
                      <th>WhatsApp</th>
                      <th>Status</th>
                      <th>Home</th>
                      <th>Expiração</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>

                    <?php
                    $iduser = 0;
                    if ($_SESSION['user_type'] == 'super_admin') {
                      // Super Admin: Show Administratorss
                      $q = mysqli_query($conn, "SELECT `id`,`username`, `name`, `contact_number`, `password`, `user_type`,`start_date`, `expired_date`, `status` FROM `admin` WHERE `user_type` = 'admin' AND `deleted` != 'yes'");

                    } else {
                      // Admin: Show only associated admins
                      $u_id = $_SESSION['id'];
                      $q = mysqli_query($conn, "SELECT `id`,`username`, `name`, `contact_number`, `password`, `user_type`,`start_date`, `expired_date`, `status` FROM `admin` WHERE `user_type` = 'user' AND `deleted` != 'yes' AND `admin_id` = '$u_id'");

                    }

                    while ($row = mysqli_fetch_assoc($q)) {
                      $iduser++;
                      $id = $row['id'];
                      echo '<tr>';
                      echo '<td scope="row">' . $iduser . '</td>';
                      echo '<td>' . $row['username'] . '</td>';
                      echo '<td>' . $row['name'] . '</td>';
                      echo '<td>' . $row['contact_number'] . '</td>';
                      echo '<td>';
                      if ($row['status'] == 'true') {
                        echo '<span class="license-status">Live</span>';
                      } else {
                        echo '<span class="license-status">Expired</span>';
                      }
                      echo '</td>';

                      echo '<td>' . $row['start_date'] . '</td>';
                      echo '<td>' . $row['expired_date'] . '</td>';
                      echo '<td>
            <div class="btn-group mr-1 mb-1">
                <button type="button" class="btn btn-secondary btn-min-width dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Actions</button>
                <div class="dropdown-menu">
                    <a class="dropdown-item activate-btn" href="#" data-id="' . $id . '">Ativar</a>
                    <a class="dropdown-item deactivate-btn" href="#" data-id="' . $id . '">Desativar</a>
                    <div class="dropdown-divider"></div>
                     <a class="dropdown-item change-password-btn" href="#" data-id="' . $id . '">Change Password</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item delete-btn" href="#" data-id="' . $id . '">Delete</a>
                </div>
            </div>
          </td>';

                      echo '</tr>';
                    }

                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Bordered table end -->
    </div>
  </div>
</div>
<!-- ////////////////////////////////////////////////////////////////////////////-->
<?php include("include/footer.php"); ?>

<!-- BEGIN VENDOR JS-->
<!-- <script src="assets/vendors/js/vendors.min.js" type="text/javascript"></script> -->
<!-- BEGIN VENDOR JS-->
<!-- BEGIN PAGE VENDOR JS-->
<!-- END PAGE VENDOR JS-->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<!-- BEGIN CHAMELEON  JS-->
<!-- <script src="assets/js/core/app-menu-lite.js" type="text/javascript"></script> -->
<!-- <script src="assets/js/core/app-lite.js" type="text/javascript"></script> -->
<!-- END CHAMELEON  JS-->
<!-- BEGIN PAGE LEVEL JS-->
<!-- END PAGE LEVEL JS-->
<script>
  $(document).ready(function () {
    $('#title').html('Administrators');
    $('#resellerTable').DateTable({
      "paging": true, // Enables pagination
      "ordering": true, // Enables sorting
      "searching": true, // Enables searching
      "order": [
        [4, "desc"]
      ], // Default sort by Activation Date in descending order
      "columnDefs": [{
        "targets": [0, 1], // Make the first column and the second column not sortable
        "orderable": false
      }]
    });
  });
</script>

<script>
  // Activate admin
  $(document).on('click', '.activate-btn', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    Swal.fire({
      title: 'Are you sure?',
      text: "You want to activate this admin?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, activate!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location = 'admin_activate.php?id=' + id;
      }
    });
  });

  // Deactivate admin
  $(document).on('click', '.deactivate-btn', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    Swal.fire({
      title: 'Are you sure?',
      text: "You want to deactivate this admin?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, deactivate!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location = 'admin_deactivate.php?id=' + id;
      }
    });
  });

  // Change password for admin
  $(document).on('click', '.change-password-btn', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    window.location = 'admin_change_password.php?id=' + id;
  });

  // Delete admin
  $(document).on('click', '.delete-btn', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    Swal.fire({
      title: 'Are you sure?',
      text: "You want to delete this admin?",
      icon: 'error',
      showCancelButton: true,
      confirmButtonText: 'Yes, delete!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        window.location = 'admin_delete.php?id=' + id;
      }
    });
  });
</script>

</html>

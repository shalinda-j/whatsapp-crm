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
    <title id="title"></title>
    <link rel="apple-touch-icon" href="assets/images/ico/apple-icon-120.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/images/ico/favicon.ico">
    <link
        href="https://fonts.googleapis.com/css?family=Muli:300,300i,400,400i,600,600i,700,700i%7CComfortaa:300,400,700"
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
    <!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css"> -->
    <!-- <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"> -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script> -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" />
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <style>
        /* .hidden {
            display: none;
        } */
        .hidden {
            display: none !important;
        }

        .calendar-icon {
            background: none;
            border: none;
            cursor: pointer;
        }

        .copy-icon {
            cursor: pointer;
            font-size: 16px;
            color: #007bff;
            margin-left: 10px;
            transition: color 0.3s ease;
        }

        .copy-icon:hover {
            color: #0056b3;
        }

        .copy-text {
            display: flex;
            align-items: center;
        }

        .copy-text input {
            border: none;
            background: none;
            cursor: default;
            padding: 0;
            margin: 0;
            width: 100%;
        }
    </style>



    <!-- END Page Level CSS-->
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
                <h3 class="content-header-title">Licenses ativas</h3>
            </div>
            <div class="content-header-right col-md-8 col-12">
                <div class="breadcrumbs-top float-md-right">
                    <div class="breadcrumb-wrapper mr-1">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Home</a>
                            </li>
                            <li class="breadcrumb-item active">Licenses ativas
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <!-- Basic All Licenses start -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h4 class="card-title mb-0">Licenses ativas</h4>
                                <p class="mb-0">Abaixo está a lista com todas as licenças ativas</p>
                            </div>
                        </div>
                        <div class="card-content collapse show">
                            <div class="table-responsive" style="padding:20px;">
                                <table class="table table-bordered mb-0" id="customerTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>WhatsApp</th>
                                            <th>License</th>
                                            <th>Ativação</th>
                                            <th>Expiração</th>
                                            <th>Oridgem</th>
                                            <th>Dias restantes</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        $iduser = 0;
                                        
                                        if ($_SESSION['user_type'] == 'admin') {
                                            $q = mysqli_query($conn, "SELECT `id`, `customer_name`, `whatsapp_number`, `license_key`, `act_date`, `end_date`, `user_id`, `status` FROM `users` WHERE`status` = 'true'AND `deleted_key` != 'yes'");
                                        } elseif ($_SESSION['user_type'] == 'user') {
                                            $u_id = $_SESSION['id'];
                                            $q = mysqli_query($conn, "
                                                SELECT `id`, `customer_name`, `whatsapp_number`, `license_key`, `act_date`, `end_date`, `user_id`, `status` 
                                                FROM `users` 
                                                WHERE `deleted_key` != 'yes' AND `status` = 'true'
                                                AND (user_id = '$u_id' OR user_id IN (SELECT id FROM admin WHERE admin_id = '$u_id' AND user_type != 'user'))
                                            ");
                                        } else {
                                            $u_id = $_SESSION['id'];
                                            $q = mysqli_query($conn, "SELECT `id`, `customer_name`, `whatsapp_number`, `license_key`, `act_date`, `end_date`, `user_id`, `status` FROM `users` WHERE `user_id` = '$u_id' AND `status` = 'true' AND `deleted_key` != 'yes'");
                                        }

                                        while ($row = mysqli_fetch_assoc($q)) {
                                            $iduser++;
                                            echo '<tr>';
                                            echo '<td scope="row">' . $iduser . '</td>';
                                            echo '<td>' . $row['customer_name'] . '</td>';
                                            echo '<td>' . $row['whatsapp_number'] . '</td>';
                                            // License key with copy icon
                                            echo '<td class="copy-text">';
                                            echo '<input type="text" value="' . htmlspecialchars($row['license_key']) . '" id="license-key-' . $row['id'] . '" readonly>';
                                            echo '<i class="fas fa-copy copy-icon" onclick="copyToClipboard(\'license-key-' . $row['id'] . '\')" title="Copy"></i>';
                                            echo '</td>';


                                            // Activation Date with calendar icon
                                            echo '<td>';
                                            echo '<span class="display-date" id="display-act_date-' . $row['id'] . '">' . $row['act_date'] . '</span>';
                                            echo '<input type="date" class="date-picker hidden" id="input-act_date-' . $row['id'] . '" data-id="' . $row['id'] . '" data-field="act_date" value="' . $row['act_date'] . '">';
                                            echo '</td>';

                                            // End Date with calendar icon
                                            echo '<td>';
                                            echo '<span class="display-date" id="display-end_date-' . $row['id'] . '">' . $row['end_date'] . '</span>';
                                            echo '<input type="date" class="date-picker hidden" id="input-end_date-' . $row['id'] . '" data-id="' . $row['id'] . '" data-field="end_date" value="' . $row['end_date'] . '">';
                                            echo '</td>';
                                            $user_id = $row['user_id'];
                                            $admin_query = mysqli_query($conn, "SELECT username FROM admin WHERE id = '$user_id'");
                                            $admin_row = mysqli_fetch_assoc($admin_query);
                                            $username = $admin_row['username'] ?? 'N/A';

                                            echo '<td>' . $username . '</td>'; // Display the fetched username
                                        
                                            $endDate = new DateTime($row['end_date']);
                                            $today = new DateTime();
                                            $remainingDays = $today->diff($endDate)->days;
                                            echo '<td>' . $remainingDays . '</td>'; // Display remaining days
                                            echo '<td>';
                                            if ($row['status'] == 'true') {
                                                echo '<span class="license-status">Live</span>';
                                            } else {
                                                echo '<span class="license-status">Expired</span>';
                                            }
                                            echo '</td>';

                                            echo '<td>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    Actions
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item activate-btn" href="#" data-id="' . $row['id'] . '">Ativar</a>
                                                    <a class="dropdown-item deactivate-btn" href="#" data-id="' . $row['id'] . '">Desativar</a>
                                                    <div class="dropdown-divider"></div>
                                                     <a class="dropdown-item edit-btn" href="edit_license.php?id=' . htmlspecialchars($row['id']) . '"" data-id="' . $row['id'] . '">Edit</a>
                                                    <a class="dropdown-item delete-btn" href="#" data-id="' . $row['id'] . '">Delete</a>
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
<!-- END VENDOR JS-->
<!-- BEGIN PAGE VENDOR JS-->
<!-- END PAGE VENDOR JS-->
<!-- BEGIN CHAMELEON  JS-->
<!-- <script src="assets/js/core/app-menu-lite.js" type="text/javascript"></script> -->
<!-- <script src="assets/js/core/app-lite.js" type="text/javascript"></script> -->
<!-- END CHAMELEON  JS-->
<!-- BEGIN PAGE LEVEL JS-->
<!-- END PAGE LEVEL JS-->
<!-- jQuery and Bootstrap JS -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<!-- Bootstrap JS -->
<!-- <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->
<script>
    $(document).ready(function () {
        $('#title').html('All License');
        $('#customerTable').DateTable({
            "paging": true, // Enables pagination
            "ordering": true, // Enables sorting
            "searching": true, // Enables searching
            "order": [
                [4, "desc"]
            ], // Default sort by Activation Date in descending order
            "columnDefs": [{
                "orderable": false,
                "targets": 9
            } // Disable sorting on the "Actions" column

            ]
        });

    });
</script>

<script>
    function copyToClipboard(elementId) {
        var copyText = document.getElementById(elementId);
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices

        try {
            var successful = document.execCommand('copy');
            if (successful) {
                Swal.fire({
                    title: 'Copied!',
                    text: "Copied the text: " + copyText.value,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    title: 'Failed!',
                    text: "Failed to copy the text.",
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        } catch (err) {
            console.error('Oops, unable to copy', err);
            Swal.fire({
                title: 'Error!',
                text: "An error occurred while copying.",
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    }
</script>

<script>
    $(document).ready(function () {
        // Activate license
        $(document).on('click', '.activate-btn', function (e) {
            e.preventDefault();
            var licenseId = $(this).data('id');
            updateLicenseStatus(licenseId, 'true');
        });

        // Deactivate license
        $(document).on('click', '.deactivate-btn', function (e) {
            e.preventDefault();
            var licenseId = $(this).data('id');
            updateLicenseStatus(licenseId, 'false');
        });

        // Function to update license status
        function updateLicenseStatus(id, status) {
            $.ajax({
                url: 'update-license-status.php',
                type: 'POST',
                data: { id: id, status: status },
                success: function (response) {
                    console.log("Response from server:", response); // Debugging line

                    if (response.trim() === 'success') {
                        Swal.fire({
                            title: 'Updated!',
                            text: 'License status updated successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload(); // Reload only after alert is closed
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to update license status.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while updating status.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        }
    });

    // deleted function
    $(document).ready(function () {
        // Delete license by marking it as deleted in the database
        $(document).on('click', '.delete-btn', function () {
            var licenseId = $(this).data('id');

            if (confirm('Você tem certeza que deseja excluir essa licença?')) {
                $.ajax({
                    url: 'delete-license.php',
                    type: 'POST',
                    data: { id: licenseId },
                    success: function (response) {
                        if (response.trim() === 'success') {
                            Swal.fire({
                                title: 'Excluído!',
                                text: 'License excluída com sucesso.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload(); // Reload the page to reflect changes
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Falha ao excluir licença.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Ocorreu um erro ao excluir a licença.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });


</script>

</body>

</html>
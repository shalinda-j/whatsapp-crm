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

    // Funções de contagem com comparativo mensal
    function getDashboardData($conn, $userId, $userType) {
        $data = [];

        // Dates para comparação (mantidas em UTC)
        $firstDayLastMonth = date('Y-m-01', strtotime("-1 month"));
        $lastDayLastMonth = date('Y-m-t', strtotime("-1 month"));

        if ($userType === 'reseller') {
            // Date do revendedor (user_id fixo)
            $data['total_licenses'] = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT COUNT(*) as total 
                FROM users 
                WHERE deleted_key != 'yes' AND user_id = $userId"
            ))['total'];

            $data['active_licenses'] = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT COUNT(*) as total 
                FROM users 
                WHERE deleted_key != 'yes' AND user_id = $userId 
                AND status = 'true' AND end_date >= DATE_SUB(NOW(), INTERVAL 3 HOUR)"
            ))['total'];

            $data['inactive_licenses'] = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT COUNT(*) as total 
                FROM users 
                WHERE deleted_key != 'yes' AND user_id = $userId 
                AND (status = 'false' OR end_date < DATE_SUB(NOW(), INTERVAL 3 HOUR))"
            ))['total'];

        } else {
            // Super admin ou user com subordinados
            $uid = intval($userId);
            $where = "deleted_key != 'yes'";
            if ($userType === 'user') {
                $where .= " AND (user_id = $uid OR user_id IN (SELECT id FROM admin WHERE admin_id = $uid AND user_type != 'user'))";
            }

            $data['total_licenses'] = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT COUNT(*) as total FROM users WHERE $where"
            ))['total'];

            $data['total_licenses_last_month'] = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT COUNT(*) as total 
                FROM users 
                WHERE $where 
                AND act_date BETWEEN '$firstDayLastMonth' AND '$lastDayLastMonth'"
            ))['total'] ?? 0;

            $data['active_licenses'] = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT COUNT(*) as total 
                FROM users 
                WHERE $where 
                AND status = 'true' 
                AND end_date >= DATE_SUB(NOW(), INTERVAL 3 HOUR)"
            ))['total'];

            $data['inactive_licenses'] = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT COUNT(*) as total 
                FROM users 
                WHERE $where 
                AND (status = 'false' OR end_date < DATE_SUB(NOW(), INTERVAL 3 HOUR))"
            ))['total'];

            // Somente super_admin vê estatísticas globais
            if ($userType === 'super_admin') {
                $data['total_admins'] = mysqli_fetch_assoc(mysqli_query($conn,
                    "SELECT COUNT(*) as total 
                    FROM admin 
                    WHERE user_type = 'admin' AND deleted = 'no'"
                ))['total'];

                $data['total_resellers'] = mysqli_fetch_assoc(mysqli_query($conn,
                    "SELECT COUNT(*) as total 
                    FROM admin 
                    WHERE user_type = 'reseller' AND deleted = 'no'"
                ))['total'];

                $data['total_reseller_licenses'] = mysqli_fetch_assoc(mysqli_query($conn,
                    "SELECT COUNT(*) as total 
                    FROM users 
                    WHERE deleted_key != 'yes' 
                    AND user_id IN (SELECT id FROM admin WHERE user_type = 'reseller' AND deleted = 'no')"
                ))['total'];

                $resellersQuery = mysqli_query($conn, "
                    SELECT a.id, a.username, a.name, a.contact_number, a.expired_date,
                        COUNT(u.id) AS license_count,
                        SUM(CASE WHEN u.status = 'true' AND u.end_date >= DATE_SUB(NOW(), INTERVAL 3 HOUR) THEN 1 ELSE 0 END) AS active_licenses,
                        SUM(CASE WHEN u.status = 'false' OR u.end_date < DATE_SUB(NOW(), INTERVAL 3 HOUR) THEN 1 ELSE 0 END) AS inactive_licenses
                    FROM admin a
                    LEFT JOIN users u ON a.id = u.user_id AND u.deleted_key != 'yes'
                    WHERE a.user_type = 'reseller' AND a.deleted = 'no'
                    GROUP BY a.id
                ");
                $data['resellers'] = mysqli_fetch_all($resellersQuery, MYSQLI_ASSOC);
            }
        }

        return $data;
    }


    $userId = $_SESSION['id'];
    $userType = $_SESSION['user_type'];
    $data = getDashboardData($conn, $userId, $userType);

    function formatDateTime($datetime) {
        return date('d-m-Y H:i', strtotime($datetime));
    }
    ?>
    <!DOCTYPE html>
    <html class="loading" lang="en" data-textdirection="ltr">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <meta name="description" content="Dashboard">
        <meta name="keywords" content="admin template, dashboard, metrics">
        <meta name="author" content="ThemeSelect">
        <title>Dashboard</title>
        <link rel="apple-touch-icon" href="assets/images/ico/apple-icon-120.png">
        <link rel="shortcut icon" type="image/x-icon" href="assets/images/ico/favicon.ico">
        <link href="https://fonts.googleapis.com/css?family=Muli:300,300i,400,400i,600,600i,700,700i%7CComfortaa:300,400,700" rel="stylesheet">
        <link href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="assets/css/vendors.css">
        <link rel="stylesheet" type="text/css" href="assets/css/app-lite.css">
        <link rel="stylesheet" type="text/css" href="assets/css/core/menu/menu-types/vertical-menu.css">
        <link rel="stylesheet" type="text/css" href="assets/css/core/colors/palette-gradient.css">
        <style>
            .tooltip-container {
                position: relative;
                display: inline-block;
                float: right;
            }
            .tooltip-icon {
                cursor: pointer;
                color: #555;
            }
            .tooltip-text {
                font-size: 11px;
                visibility: hidden;
                width: 220px;
                background-color: #333;
                color: #fff;
                text-align: left;
                border-radius: 4px;
                padding: 6px;
                position: absolute;
                z-index: 1;
                top: -5px;
                right: 125%;
                opacity: 0;
                transition: opacity 0.3s;
            }
            .tooltip-container:hover .tooltip-text {
                visibility: visible;
                opacity: 1;
            }
        </style>
    </head>

    <?php include("include/header.php"); include("include/sidebar.php"); ?>

    <div class="app-content content">
        <div class="content-wrapper">
            <div  <?= $style; ?> class="content-wrapper-before"></div>
            <div class="content-header row">
                <div class="content-header-left col-md-4 col-12 mb-2">
                    <h3 class="content-header-title">Dashboard</h3>
                </div>
            </div>
            <div class="content-body">
                <div class="row">
                    <?php
                    function card($title, $count, $diff = null) {
                        $tooltip = $diff !== null ? "<div class='tooltip-container'><i class='fa fa-info-circle tooltip-icon'></i><span class='tooltip-text'>" . ($diff >= 0 ? "+$diff" : $diff) . " compared to previous month</span></div>" : "";
                        echo "<div class='col-md-4'><div class='card'><div class='card-content'><div class='card-body'><h4 class='card-title d-flex justify-content-between align-items-center'>$title $tooltip</h4><h3>$count</h3></div></div></div></div>";
                    }

                    if ($userType === 'reseller') {
                        card("My Licenses", $data['total_licenses']);
                        card("Active Licenses", $data['active_licenses']);
                        card("Inactive Licenses", $data['inactive_licenses']);
                    } else {
                        card("Total Licenses", $data['total_licenses'], $data['total_licenses'] - $data['total_licenses_last_month']);
                        card("Active Licenses", $data['active_licenses']);
                        card("Inactive Licenses", $data['inactive_licenses']);
                        
                        // Only show these cards for super_admin
                        if ($userType === 'super_admin') {
                            card("Resellers Licenses", $data['total_reseller_licenses'] ?? 0);
                            card("Total Administrators", $data['total_admins'] ?? 0);
                            card("Total Resellers", $data['total_resellers'] ?? 0);
                        }
                    }
                    ?>
                </div>
                <?php if ($userType === 'super_admin' && isset($data['resellers']) && is_array($data['resellers'])): ?>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Resellers</h4>
                        </div>
                        <div class="card-content collapse show">
                            <div class="table-responsive p-2">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>WhatsApp</th>
                                            <th>Expiration Date</th>
                                            <th>Generated Licenses</th>
                                            <th>Active Licenses</th>
                                            <th>Inactive Licenses</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $index = 1;
                                        foreach ($data['resellers'] as $reseller) {
                                            $formattedDate = $reseller['expired_date'] ? formatDateTime($reseller['expired_date']) : '-';
                                            echo "<tr>
                                                <td>{$index}</td>
                                                <td>{$reseller['name']}</td>
                                                <td>{$reseller['contact_number']}</td>
                                                <td>{$formattedDate}</td>
                                                <td>{$reseller['license_count']}</td>
                                                <td>{$reseller['active_licenses']}</td>
                                                <td>{$reseller['inactive_licenses']}</td>
                                            </tr>";
                                            $index++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include("include/footer.php"); ?>

    <script src="assets/vendors/js/vendors.min.js" type="text/javascript"></script>
    <script src="assets/js/core/app-menu-lite.js" type="text/javascript"></script>
    <script src="assets/js/core/app-lite.js" type="text/javascript"></script>
    </body>
    </html>
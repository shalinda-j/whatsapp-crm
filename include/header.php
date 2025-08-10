<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<body class="vertical-layout vertical-menu 2-columns menu-expanded fixed-navbar" data-open="click" data-menu="vertical-menu" data-color="bg-gradient-x-purple-blue" data-col="2-columns">

    <!-- fixed-top-->
    <nav  class="header-navbar navbar-expand-md navbar navbar-with-menu navbar-without-dd-arrow fixed-top navbar-semi-light">
      <div  class="navbar-wrapper">
        <div <?= $style; ?> class="navbar-container content">
          <div class="collapse navbar-collapse show" id="navbar-mobile">
            <ul class="nav navbar-nav mr-auto float-left">
            </ul>
            <ul class="nav navbar-nav float-right">
              <li  class="dropdown dropdown-user nav-item"><a class="dropdown-toggle nav-link dropdown-user-link" href="#" data-toggle="dropdown"><span class="avatar avatar-online"><img src="assets/images/<?= $adminMainLogoName; ?>" alt="avatar"><i></i></span></a>
                <div class="dropdown-menu dropdown-menu-right">
                  <div class="arrow_box_right">
                    <a class="dropdown-item" href="#"> <span class="user-name text-bold-400 ml-1" style='font-weight:bold;'><?= $adminName; ?></span></span></a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="change_password.php"><i class="fa fa-key" aria-hidden="true"></i> Change Password</a>
                    <a class="dropdown-item" href="logout.php"><i class="fa fa-power-off" aria-hidden="true"></i> Logout</a>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>

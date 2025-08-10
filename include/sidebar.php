<div  class="main-menu menu-fixed menu-light menu-accordion menu-shadow " data-scroll-to-active="true" data-img="assets/images/backgrounds/02.jpg">
    <div class="navbar-header">
        <ul class="nav navbar-nav flex-row" style="justify-content: center;">
            <img style="margin-top:20px;margin-bottom:20px;" src="assets/images/<?= $adminMainLogoName; ?>"/>
        </ul>
    </div>
    <div class="main-menu-content">
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
            <li id="sid-main" ><a href="index.php"><i <?= $style; ?> class="ft-home"></i><span class=" menu-title" data-i18n="">Dashboard</span></a></li>
            <li id="sid-all" class=" nav-item"><a href="all-licenses.php"><i <?= $style; ?> class="fa fa-database" aria-hidden="true"></i><span class="menu-title" data-i18n="">Licenses</span></a></li>
            <?php if ($_SESSION['user_type'] == 'super_admin') { ?>
                <li id="sid-rall" class="nav-item"><a href="all-reseller.php"><i <?= $style; ?> class="ft-list"></i><span class="menu-title" data-i18n="">Resellers</span></a></li>
                <li id="sid-rall-admin" class="nav-item"><a href="all-admin.php"><i <?= $style; ?>  class="fa fa-server" aria-hidden="true"></i><span class="menu-title" data-i18n="">Administrators</span></a></li>
                <li id="sid-configuration" class="nav-item"><a href="configuration.php"><i <?= $style; ?> class="fa fa-cogs" aria-hidden="true"></i><span class="menu-title" data-i18n="">Settings</span></a></li>
            <?php } elseif ($_SESSION['user_type'] == 'admin') { ?>
                <li id="sid-rall" class="nav-item"><a href="all-reseller.php"><i <?= $style; ?> class="ft-list"></i><span class="menu-title" data-i18n="">Resellers</span></a></li>
            <?php } ?>
            <?php if ($_SESSION['user_type'] == 'super_admin') { ?>
                <li id="sid-rall-download_ex" class="nav-item"><a href="license.php"><i <?= $style; ?> class="ft-award"></i><span class="menu-title" data-i18n="">License</span></a></li>
                <li id="sid-rall-download_ex" class="nav-item"><a href="extension.php"><i <?= $style; ?> class="ft-download"></i><span class="menu-title" data-i18n="">Extension</span></a></li>
            <?php } ?>
                <li id="sid-change-pass" class="nav-item"><a href="change_password.php"><i <?= $style; ?> class="fa fa-lock"></i><span class="menu-title" data-i18n="">Change Password</span></a></li>
                <li id="sid-logout" class="nav-item"><a href="logout.php"><i <?= $style; ?> class="fa fa-sign-out"></i><span class="menu-title" data-i18n="">Logout</span></a></li>
        </ul>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.querySelector(".main-menu");
  if (sidebar) {
    const versionDiv = document.createElement("div");
    versionDiv.textContent = "Version 2.2.8";
    versionDiv.style.position = "absolute";
    versionDiv.style.bottom = "10px";
    versionDiv.style.width = "100%";
    versionDiv.style.textAlign = "center";
    versionDiv.style.fontSize = "12px";
    versionDiv.style.color = "#999";
    sidebar.appendChild(versionDiv);
  }
});
</script>
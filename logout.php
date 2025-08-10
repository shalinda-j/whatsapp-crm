<?php
include("include/conn.php");
include("include/function.php");

session_destroy();
redirect("login.php");

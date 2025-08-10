<?php
date_default_timezone_set('America/Sao_Paulo'); 
$host = "localhost";
$username = "root";
$password = "";
$db = "whatsappcrm";
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Set database connection options
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($host, $username, $password, $db);
    if (!$conn) {
        throw new mysqli_sql_exception("Failed to connect to database: " . mysqli_connect_error());
    }
    // Set charset to utf8mb4 for better character support
    mysqli_set_charset($conn, "utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log("Datebase connection error: " . $e->getMessage());
    die("Datebase connection failed. Please check your configuration.");
}
$app_name = "DROPE CRM";
$c_name = "DROPE";

//Get configuration
$configQuery = mysqli_query($conn, "SELECT * FROM configurations WHERE id = 1");
if (!$configQuery || mysqli_num_rows($configQuery) === 0) {
    $config = [
        'support_phone_number' => '5582994229991',
        'trial_key_validity' => 3,
        'color_background' => '#ffffff',
        'color_text' => '#000000',
    ];
} else {
    $config = mysqli_fetch_assoc($configQuery);
}

$supportPhoneNumber = $config['support_phone_number'];
$trialKeyValidity = $config['trial_key_validity'];
$color_background = $config['color_background'];
$colorText = $config['color_text'];

$bgColor = $color_background;
$txtColor = $colorText;

$style = "style='background:".$bgColor.";color:".$txtColor.";border:none;'";

//Configurations
$adminName = "DROPE";
$adminMainLogoName = "logo.png";
$adminFaviconLogoName = "favicon.ico";
$adminMobile = "558294229991";
$moreProgramsLink = "https://dropestore.com";
$officialWebsiteLink = "https://dropestore.com";
?>



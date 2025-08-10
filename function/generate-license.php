<?php
include("../include/conn.php");
include("../include/function.php");

$response = array();
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wnumber  = $_POST['wnumber'];
    $validity = $_POST['validity'];
    $cname    = $_POST['cname'];
    $email    = isset($_POST['email']) ? $_POST['email'] : null;

    // Check if required fields are filled
    if (empty($wnumber) || empty($validity) || empty($cname)) {
        $response['status'] = false;
        $response['message'] = "Please fill in all required fields.";
    } elseif (check_number($wnumber) === true) {
        $response['status'] = false;
        $response['message'] = "Phone number already registered.";
    } else {
        $data_expiration = date("Y-m-d H:i:s", strtotime("+$validity days"));

        // Generate a local license key instead of calling external API
        $licenseKey = generate_license(16);
        $today = date("Y-m-d H:i:s");

        $stmt = $conn->prepare("INSERT INTO `users` 
            (`user_id`, `customer_name`, `whatsapp_number`, `email`, `license_key`, `act_date`, `end_date`, `life_time`, `plan_type`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'false', 'Premium')");

        $userId = $_SESSION['id'];
        $stmt->bind_param("issssss", $userId, $cname, $wnumber, $email, $licenseKey, $today, $data_expiration);

        if ($stmt->execute()) {
            $response['status'] = true;
            $response['message'] = "License created and saved successfully!";
            $response['license'] = $licenseKey;
        } else {
            $response['status'] = false;
            $response['message'] = "Error saving to local database: " . $stmt->error;
        }

        $stmt->close();
    }
} else {
    $response['status'] = false;
    $response['message'] = "Invalid request method.";
}

header('Content-Type: application/json');
echo json_encode($response);

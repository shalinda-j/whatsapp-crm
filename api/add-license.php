<?php
// adddevice.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, x-requested-with");
header('Content-Type: application/json');

include("../include/conn.php");
include("../include/function.php");

$sub_key = $_POST['sub_key'];
$unique_id = $_POST['unique_id'];
$mo_no = $_POST['mo_no'];
$r_id = $_POST['r_id'];

if (empty($sub_key) || empty($unique_id) || empty($mo_no) || empty($r_id)) {
    $response = [
        'status' => 400,
        'message' => 'Missing Parameters'
    ];
    echo json_encode($response);
    exit;
}

$query = "SELECT * FROM `users` WHERE `whatsapp_number` =  '$mo_no'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $row = mysqli_fetch_assoc($result);
    $q = mysqli_query($conn, "UPDATE `users` SET `license_key`='$sub_key' WHERE  `whatsapp_number` = '$mo_no'");

    $response = [
        "status" => 200,
        "message" => "OK",
        "data" => "License key is valid. Device added successfully."
    ];
} else {
    $response = [
        'status' => 400,
        "message" => "License Key Not Found"
    ];
}

echo json_encode($response);

<?php

$url = 'http://localhost/whatsapp-crm/api/verify-license.php';

$data = array(
    'sub_key' => 'TEST-LICENSE-KEY',
    'unique_id' => 'TEST-DEVICE-ID',
    'mo_no' => '5582994229991'
);

$options = array(
    'http' => array(
        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
        'method'  => 'POST',
        'content' => http_build_query($data)
    )
);

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

if ($result === FALSE) { 
    echo "Error: Could not connect to the API.";
} else {
    echo $result;
}

?>
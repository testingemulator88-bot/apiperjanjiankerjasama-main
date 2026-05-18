<?php
include '../library/config.php';
error_reporting(0);
check_injection();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$header = apache_request_headers();
$Bearer = $header['authorization'];
if ($Bearer == '') {
    $Bearer = $header['Authorization'];
}

if ($Bearer == 'Bearer GPMop8LQ06S0rZXcJyEH3wk8jVrINbHwn7tBq2') {

    $ip = base64_encode($_SERVER['REMOTE_ADDR']);    
    mysqli_close($link);
    echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'randkey' => $ip), JSON_PRETTY_PRINT);
    die;
} else {
    mysqli_close($link);
    echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
    die;
}

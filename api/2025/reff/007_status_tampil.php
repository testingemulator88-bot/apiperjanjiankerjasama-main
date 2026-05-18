<?php
include '../library/config.php';
check_injection();
error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, authorization");
header("Content-type: application/json");
$header = apache_request_headers();
//var_dump($header);
$Bearer = $header['authorization'];
if ($Bearer == '') {
    $Bearer = $header['Authorization'];
}
$ip = base64_encode($_SERVER['REMOTE_ADDR']);

if (($Bearer == 'Bearer GPMop8LQ06S0rZXcJyEH3wk8jVrINbHwn7tBq2' . $ip) && $ip<>'') {
    $myArray = array();
    $sql = "select id,nama from simfast_master_tampil where deleted='0' order by id";
    $result = mysqli_query($link, $sql);
    $num = mysqli_num_rows($result);
    if ($num > 0) {
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $myArray[] = (object)
                [
                    'id' => $row['id'],
                    'nama' => $row['nama'],
                ];
            }
            $response         = [];
            $response['data'] =  $myArray;
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
            die;
        }
    } else {
        mysqli_close($link);
        echo json_encode(array('response' => 'success', 'message' => 'data kosong'), JSON_PRETTY_PRINT);
        die;
    }
} else {
    mysqli_close($link);
    echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
    die;
}

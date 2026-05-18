<?php
include '../../library/config.php';
error_reporting(0);
//check_injection();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-type: application/json");
$header = apache_request_headers();
//var_dump($header);
$Bearer = $header['authorization'];
if ($Bearer == '') {
    $Bearer = $header['Authorization'];
}
$tempBearer = explode(" ", $Bearer);

$sqlcek = "select id as jumlah from tb_user where token='" . str_replace('"','',$tempBearer[1]) . "' and deleted='0'";
$resultsqlcek = mysqli_query($link, $sqlcek);
$numcek = mysqli_num_rows($resultsqlcek);
if ($numcek > 0) {
    $kduser = fixup(str_replace('"','',$_GET['kduser']));
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($kduser <> '')) {
        $myArray = array();
        $sql = "select a.username
        from tb_user a 
        where a.deleted='0' 
        and md5(a.id)='" . $kduser . "'
        order by a.id asc  ";
        $result = mysqli_query($link, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $myArray[] = $row;
            }
            
            $response['data'] =  $myArray;
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'content' => $response), JSON_PRETTY_PRINT);
            die;
        }
    } else {
        mysqli_close($link);
        echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
        die;
    }
} else {
    mysqli_close($link);
    echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid2'));
    die;
}

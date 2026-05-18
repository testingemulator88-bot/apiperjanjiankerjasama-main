<?php
include '../../../library/config.php';
error_reporting(0);
// check_injection();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-type: application/json");

$header = apache_request_headers();
//var_dump($header);
$Bearer = $header['authorization'];

if ($Bearer == '') {
    $Bearer = $header['Authorization'];
}

$tempBearer = explode(" ", $Bearer);

$sqlcek         = "select id as jumlah from tb_user where token='" . str_replace('"', '', $tempBearer[1]) . "' and deleted='0'";
$resultsqlcek   = mysqli_query($link, $sqlcek);
$numcek         = mysqli_num_rows($resultsqlcek);

if ($numcek > 0) {
    
    $id_lke                 = fixup($_POST['id_lke']);
    $upload_dok_satker      = fixup($_POST['upload_dok_satker']);
    $nama_file_dok          = fixup($_POST['nama_file_dok']);

    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($id_lke <> '') ) {
        $sql = "UPDATE hasil_lke SET upload_dok_satker = '" . $upload_dok_satker . "', nama_file_dok = '" . $nama_file_dok . "' WHERE id_hasil_lke = '" . $id_lke . "'";
        
        // echo $sql;
        // die;

        $result = mysqli_query($link, $sql);
        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah Diedit'));
            die;
        }
        else {
            mysqli_close($link);
            echo json_encode(array('response' => 'error', 'message' => 'Gagal valid'));
            die;
        }
    }
    else {
        mysqli_close($link);
        echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
        die;
    }
}
else {
    mysqli_close($link);
    echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
    die;
}
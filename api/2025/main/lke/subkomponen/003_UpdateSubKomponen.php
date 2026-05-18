<?php
include '../../../library/config.php';
error_reporting(0);
check_injection();
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
    $id_komponen        = fixup($_POST['id_komponen']);
    $kode_subkomponen   = fixup($_POST['kode_subkomponen']);
    $nama_subkomponen   = fixup($_POST['nama_subkomponen']);
    $bobot_subkomponen  = fixup($_POST['bobot_subkomponen']);
    $updatedby          = fixup($_POST['updatedby']);
    $id                 = fixup($_POST['id']);

    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($id_komponen <> '') && ($nama_subkomponen <> '') && ($bobot_subkomponen <> '') && ($id <> '')) {

        $sql = "UPDATE subkomponen SET id_komponen = '" . $id_komponen . "', kode_subkomponen = '" . $kode_subkomponen . "', nama_subkomponen = '" . $nama_subkomponen . "'
                    , bobot_subkomponen = '" . $bobot_subkomponen . "' WHERE id_subkomponen = '" . $id . "'";
        //echo $sql;
        //die;
        $result = mysqli_query($link, $sql);
        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah Diedit'));
            die;
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
}
else {
    mysqli_close($link);
    echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
    die;
}
<?php
include '../../library/config.php';
error_reporting(0);
//check_injection();
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

$sqlcek = "select id as jumlah from tb_user where token='" . str_replace('"', '', $tempBearer[1]) . "' and deleted='0'";
$resultsqlcek = mysqli_query($link, $sqlcek);
$numcek = mysqli_num_rows($resultsqlcek);
if ($numcek > 0) {
    $kolom = ($_POST['kolom']);
    $id = ($_POST['id']);
    $isi = ($_POST['isi']);
    $updatedby = fixup($_POST['updatedby']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($kolom <> '') && ($id <> '')) {
        if ($kolom == 'radio_aktive') {
            $sql = " update master_pk set is_active = '0'
            , updatedby = '" . $updatedby . "' , updatedate = SYSDATE()";
            $result = mysqli_query($link, $sql);

            $sql = " update master_pk set is_active = '1'
            , updatedby = '" . $updatedby . "' , updatedate = SYSDATE()
            where id = '" . $id . "'";
            $result = mysqli_query($link, $sql);
        } else {
            $sql = " update master_pk set " . $kolom . " = '" . $isi . "'
            , updatedby = '" . $updatedby . "' , updatedate = SYSDATE()
            where id = '" . $id . "'";
            $result = mysqli_query($link, $sql);
        }
        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah Diedit'));
            die;
        } else {
            mysqli_close($link);
            echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
            die;
        }
    } else {
        mysqli_close($link);
        echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
        die;
    }
} else {
    mysqli_close($link);
    echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
    die;
}

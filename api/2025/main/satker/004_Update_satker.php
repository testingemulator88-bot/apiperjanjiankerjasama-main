<?php
include '../../library/config.php';
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

$sqlcek = "select id as jumlah from tb_user where token='" . str_replace('"', '', $tempBearer[1]) . "' and deleted='0'";
$resultsqlcek = mysqli_query($link, $sqlcek);
$numcek = mysqli_num_rows($resultsqlcek);
if ($numcek > 0) {
    $kdunor = fixup($_POST['kdunor']);
    $kode_satker = fixup($_POST['kode_satker']);
    $kode_satker_pendek = fixup($_POST['kode_satker_pendek']);
    $kode_satker_old = fixup($_POST['kode_satker_old']);
    $kode_satker_old_pendek = fixup($_POST['kode_satker_old_pendek']);
    $kdbalai = fixup($_POST['kdbalai']);
    $nama_satker = fixup($_POST['nama_satker']);
    $status_piu = fixup($_POST['status_piu']);
    $level_piu = fixup($_POST['level_piu']);
    $aktif = fixup($_POST['aktif']);
    $updatedby = fixup($_POST['updatedby']);
    $id = fixup($_POST['id']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($kdunor <> '') && ($kdbalai <> '') && ($nama_satker <> '') && ($id <> '')) {

        $sql = " update master_satker set kdunor = '" . $kdunor . "',kdbalai = '" . $kdbalai . "'
        , kode_satker = '" . $kode_satker . "', kode_satker_old = '" . $kode_satker_old . "'
        , kode_satker_pendek = '" . $kode_satker_pendek . "', kode_satker_old_pendek = '" . $kode_satker_old_pendek . "'
        , nama_satker = '" . $nama_satker . "', status_piu = '" . $status_piu . "', level_piu = '" . $level_piu . "'
        , aktif = '" . $aktif . "', updateby = '" . $updatedby . "' , updatedate = SYSDATE()
        where id = '" . $id . "'";
        //echo $sql;
        //die;
        $result = mysqli_query($link, $sql);
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

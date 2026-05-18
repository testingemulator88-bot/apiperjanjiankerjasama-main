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
    $createdby = fixup($_POST['createdby']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($kdunor <> '') && ($kdbalai <> '') && ($kode_satker <> '') && ($nama_satker <> '')) {

        $sql = "insert into master_satker (id,kdunor,kdbalai,kode_satker,kode_satker_pendek,kode_satker_old
        ,kode_satker_old_pendek,nama_satker,status_piu,level_piu,aktif
        ,createddate,createdby,deleted) values 
        (null,'" . $kdunor . "','" . $kdbalai . "','" . $kode_satker . "'
        ,'" . $kode_satker_pendek . "','" . $kode_satker_old . "','" . $kode_satker_old_pendek . "','" . $nama_satker . "'
        ,'" . $status_piu . "','" . $level_piu . "','" . $aktif . "',SYSDATE(),'" . $createdby . "','0')";
        //echo $sql;
        //die;
        $result = mysqli_query($link, $sql);
        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah Ditambah'));
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

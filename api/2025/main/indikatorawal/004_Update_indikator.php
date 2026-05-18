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
    $updateby = fixup($_POST['updateby']);
    $id = fixup($_POST['kode']);
    $isi = fixup($_POST['isi']);
    $kode_satker = fixup($_POST['kode_satker']);
    $id_satker = fixup($_POST['id_satker']);
    $kode_tahun = fixup($_POST['kode_tahun']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($id <> '')) {
        if ($isi == '0') {
            $sql = " insert into tb_data_pk_tidak_cetak_awal (id, id_indikator, kode_tahun, id_satker, kode_satker, createddate
            , createdby, deleted) VALUES (null, '" . $id . "', '" . $kode_tahun . "', '" . $id_satker . "', '" . $kode_satker . "'
            , SYSDATE(), '" . $updateby . "', '0')";
            //echo $sql;
            //die;
        } else {
            $sqlcekeksist = "select id from tb_data_pk_tidak_cetak_awal where deleted='0'
            and id_indikator = '" . $id . "' and kode_tahun = '" . $kode_tahun . "'
            and id_satker = '" . $id_satker . "' and kode_satker = '" . $kode_satker . "'";
            $resulteksist  = mysqli_query($link, $sqlcekeksist);
            $numcekeksist = mysqli_num_rows($resulteksist);
            if ($numcekeksist > 0) {
                while ($roweksist = mysqli_fetch_assoc($resulteksist)) {
                    $idnya = $roweksist['id'];
                }
            }
            $sql = "update tb_data_pk_tidak_cetak_awal set deleted='1' where id = '" . $idnya . "'";
        }

        //echo $sql;
        //die;
        $result = mysqli_query($link, $sql);
        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah Diupdate'));
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

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
    $level_pejabat = fixup($_POST['level_pejabat']);
    $kode_satker_pejabat = fixup($_POST['kode_satker_pejabat']);
    $tanggal_pejabat = fixup($_POST['tanggal_pejabat']);
    $lokasi_pejabat = fixup($_POST['lokasi_pejabat']);
    $nama_pejabat = fixup($_POST['nama_pejabat']);
    $pangkat_pejabat = fixup($_POST['pangkat_pejabat']);
    $jabatan_pejabat = fixup($_POST['jabatan_pejabat']);
    $nip_pejabat = fixup($_POST['nip_pejabat']);
    $filenya_ttd = fixup($_POST['filenya_ttd']);
    $createdby = fixup($_POST['createdby']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($kode_satker_pejabat <> '')) {
        $sqlcekpejabat = "select id from tb_data_ttd where deleted='0' and level_pejabat = '" . $level_pejabat . "' and kode_satker_pejabat = '" . $kode_satker_pejabat . "'";
        $resultcekpejabat = mysqli_query($link, $sqlcekpejabat);
        $numcekpejabat = mysqli_num_rows($resultcekpejabat);

        if ($numcekpejabat > 0) {
            if ($resultcekpejabat) {
                while ($rowpejabat = mysqli_fetch_assoc($resultcekpejabat)) {
                    $id = $rowpejabat['id'];
                }
            }
            $sql = " update tb_data_ttd set level_pejabat='" . $level_pejabat . "'
            ,kode_satker_pejabat='" . $kode_satker_pejabat . "'
            ,tanggal_pejabat='" . $tanggal_pejabat . "'
            ,lokasi_pejabat='" . $lokasi_pejabat . "'
            ,nama_pejabat='" . $nama_pejabat . "'
            ,pangkat_pejabat='" . $pangkat_pejabat . "'
            ,jabatan_pejabat='" . $jabatan_pejabat . "'
            ,nip_pejabat='" . $nip_pejabat . "'
            ,filenya_ttd='" . $filenya_ttd . "'
            , updateby = '" . $createdby . "' , updatedate = SYSDATE()
            where id = '" . $id . "'";
        } else {
            $sql = " insert into tb_data_ttd (id, level_pejabat, kode_satker_pejabat, tanggal_pejabat
            , lokasi_pejabat, nama_pejabat, pangkat_pejabat, jabatan_pejabat, nip_pejabat, filenya_ttd
            , createddate, createdby, deleted) VALUES (null, '" . $level_pejabat . "', '" . $kode_satker_pejabat . "'
            , '" . $tanggal_pejabat . "', '" . $lokasi_pejabat . "', '" . $nama_pejabat . "'
            , '" . $pangkat_pejabat . "', '" . $jabatan_pejabat . "', '" . $nip_pejabat . "', '" . $filenya_ttd . "'
            ,  SYSDATE(), '" . $createdby . "','0')";
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

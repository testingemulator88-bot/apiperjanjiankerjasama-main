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
    $id = fixup($_POST['id']);
    $kode_tahun = fixup($_POST['kode_tahun']);
    $tahun = fixup($_POST['tahun']);
    $level_verif = fixup($_POST['level_verif']);
    $id_pelaksana = fixup($_POST['id_pelaksana']);
    $kode_pelaksana = fixup($_POST['kode_pelaksana']);
    $hasil_verif = fixup($_POST['hasil_verif']);
    $tanggal_batas = fixup($_POST['tanggal_batas']);
    $dari = fixup($_POST['dari']);
    $kepada = fixup($_POST['kepada']);
    $catatan = fixup($_POST['catatan']);
    $verifikasi_ke = fixup($_POST['verifikasi_ke']);
    $updateby = fixup($_POST['updateby']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($kode_pelaksana <> '')) {
        $sqlpkaktif = "select id from master_pk where deleted = '0' and is_active = '1'";
        $resultpkaktif  = mysqli_query($link, $sqlpkaktif);
        while ($rowpkaktif  = mysqli_fetch_assoc($resultpkaktif )) {
            $pk_aktif = $rowpkaktif ['id'];
        }
        $pk_aktif = '1';
        if ($hasil_verif == '2') {
            $status_ajuan = 'Pengajuan Revisi PK ke-' . $verifikasi_ke;
            $status_verifikasi  = 'Revisi Pengajuan PK ke-' . $verifikasi_ke;
        }
        if ($hasil_verif == '1') {
            $status_ajuan = 'Verifikasi PK ke-' . $verifikasi_ke . ' Selesai';
            $status_verifikasi = 'Pengajuan PK ke-' . $verifikasi_ke . ' Disetujui';
        }

        $sql = " insert into tb_data_pk_verifikasi (id, dari, kepada, kode_tahun, tahun, level_verif
        , id_pelaksana, kode_pelaksana, hasil_verif, tanggal_batas, catatan, verifikasi_ke, readed, status_ajuan, status_verifikasi
        , jenis_pk, createddate, createdby, deleted) VALUES (null, '" . $dari . "', '" . $kepada . "', '" . $kode_tahun . "', '" . $tahun . "'
        , '" . $level_verif . "', '" . $id_pelaksana . "', '" . $kode_pelaksana . "'
        , '" . $hasil_verif . "', '" . $tanggal_batas . "', '" . $catatan . "', '" . $verifikasi_ke . "'
        ,'0', '" . $status_ajuan . "', '" . $status_verifikasi . "', '" . $pk_aktif . "',  SYSDATE(), '" . $updateby . "','0')";

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

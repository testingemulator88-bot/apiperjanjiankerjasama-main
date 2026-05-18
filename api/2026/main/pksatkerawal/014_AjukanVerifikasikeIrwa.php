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
    $sqlpkaktif = "select id from master_pk where deleted = '0' and is_active = '1'";
    $resultpkaktif  = mysqli_query($link, $sqlpkaktif);
    while ($rowpkaktif  = mysqli_fetch_assoc($resultpkaktif)) {
        $pk_aktif = $rowpkaktif['id'];
    }
    $pk_aktif = '1';
    $dari = fixup($_POST['dari']);
    $kepada = fixup($_POST['kepada']);
    $kepada2 = fixup($_POST['kepada2']);
    $evaluasi = fixup($_POST['evaluasi']);

    $kode_tahun = fixup($_POST['kode_tahun']);
    $tahun = get_Isi_Field1('tahun','master_pk','id','1');
    $id_pelaksana = fixup($_POST['id_pelaksana']);
    $kode_pelaksana = fixup($_POST['kode_pelaksana']);
    $verifikasi_ke = fixup($_POST['verifikasi_ke']);
    $updateby = fixup($_POST['updateby']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($kode_pelaksana <> '')) {
        $sql = " insert into tb_data_pk_verifikasi (id, dari, kepada, kepada2, evaluasi, kode_tahun, tahun
        , id_pelaksana, kode_pelaksana, verifikasi_ke, readed, status_ajuan, status_verifikasi, jenis_pk
        , createddate, createdby, deleted) VALUES (null, '" . $dari . "', '" . $kepada . "', '0', '" . $evaluasi . "'
        , '" . $kode_tahun . "', '" . $tahun . "', '" . $id_pelaksana . "', '" . $kode_pelaksana . "', '" . $verifikasi_ke . "'
        ,'0', 'Pengajuan PK ke-2', 'Proses Verifikasi PK ke-2', '" . $pk_aktif . "',  SYSDATE(), '" . $updateby . "','0')";
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

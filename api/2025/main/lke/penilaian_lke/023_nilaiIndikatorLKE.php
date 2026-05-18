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
    $id_kriteria            = fixup($_POST['id_kriteria']);
    $tahun                  = fixup($_POST['tahun']);
    $id_user                = fixup($_POST['id_user']);
    $kode_satker            = fixup($_POST['kode_satker']);
    $level                  = fixup($_POST['level']);
    $kriteria_penilaian1    = 0;
    $kriteria_penilaian2    = 0;
    $kriteria_penilaian3    = 0;
    $kriteria_penilaian4    = 0;
    $catatan_evaluator      = '-';
    $tanggapan_evaluator    = '-';
    $deskripsi_rekomendasi  = '-';
    $status_tindak_lanjut   = '-';
    $status_lke             = '4';

    

    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($id_kriteria <> '') && ($tahun <> '') && ($id_user <> '')) {

        $sql = "INSERT INTO hasil_lke (id_kriteria, id_user, kode_satker, level, tahun, kriteria_penilaian1, kriteria_penilaian2, kriteria_penilaian3, kriteria_penilaian4, catatan_evaluator, tanggapan_evaluator, deskripsi_rekomendasi, status_tindak_lanjut, status_lke) 
                    VALUES 
                    ('" . $id_kriteria . "','" . $id_user . "','" . $kode_satker . "','" . $level . "','" . $tahun . "','" . $kriteria_penilaian1 . "','" . $kriteria_penilaian2 . "','" . $kriteria_penilaian3 . "','" . $kriteria_penilaian4 . "','" . $catatan_evaluator . "','" . $tanggapan_evaluator . "','" . $deskripsi_rekomendasi . "','" . $status_tindak_lanjut . "','" . $status_lke . "'  )";
            // echo $sql;
            // die;

        $result = mysqli_query($link, $sql);
        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah di nilai'));
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
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
    $id         = fixup($_POST['kode']);

    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($id <> '')) {

        $sql = "UPDATE hasil_lke SET kriteria_penilaian1 = '0', kriteria_penilaian2 = '0', kriteria_penilaian3 = '0', kriteria_penilaian4 = '0', 
                catatan_evaluator = NULL, jawaban_satker = NULL, tanggapan_evaluator = NULL, deskripsi_rekomendasi = NULL, status_tindak_lanjut = NULL, 
                status_lke = '1' WHERE id_hasil_lke = '" . $id . "'";
        //echo $sql;
        //die;
        $result = mysqli_query($link, $sql);
        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah direset'));
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

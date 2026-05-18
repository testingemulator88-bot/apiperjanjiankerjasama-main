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
    $id_kriteria        = fixup($_POST['id_kriteria']);
    $tahun              = fixup($_POST['tahun']);
    $id_user            = fixup($_POST['id_user']);
    $kode_satker        = fixup($_POST['kode_satker']);
    $upload_dok_satker  = fixup($_POST['upload_dok_satker']);
    $nama_file_dok  = fixup($_POST['nama_file_dok']);
    $level              = fixup($_POST['level']);

    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($id_kriteria <> '') && ($tahun <> '') && ($id_user <> '') && ($upload_dok_satker <> '')) {

        $sql = "INSERT INTO hasil_lke (id_kriteria, id_user, kode_satker, level, tahun, upload_dok_satker, nama_file_dok) 
                    VALUES 
                    ('" . $id_kriteria . "','" . $id_user . "','" . $kode_satker . "','" . $level . "','" . $tahun . "','" . $upload_dok_satker . "', '" . $nama_file_dok . "'  )";
            // echo $sql;
            // die;

        $result = mysqli_query($link, $sql);
        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah Ditambah'));
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
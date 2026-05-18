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
    $id_user        = fixup($_POST['id_user']);
    $tim            = fixup($_POST['tim']);
    $nip            = fixup($_POST['nip']);
    $jabatan        = fixup($_POST['jabatan']);
    $tahun_evaluasi = fixup($_POST['tahun_evaluasi']);

    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($id_user <> '') && ($tahun_evaluasi <> '')) {

        $sql_kategori_satker    = "SELECT kategori_satker FROM tb_user WHERE kode_satker='" . $id_user . "' and deleted='0' LIMIT 1";
        $result_kategori_satker = mysqli_query($link, $sql_kategori_satker);
        $row_kategori_satker    = mysqli_fetch_assoc($result_kategori_satker);
        $kategori_satker        = $row_kategori_satker['kategori_satker'];

        $sql = "INSERT INTO evaluator (kode_satker, tim, nip, jabatan, wilayah, tahun_evaluasi) 
                    VALUES 
                    ('" . $id_user . "','" . $tim . "','" . $nip . "','" . $jabatan . "','" . $kategori_satker . "','" . $tahun_evaluasi . "')";
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
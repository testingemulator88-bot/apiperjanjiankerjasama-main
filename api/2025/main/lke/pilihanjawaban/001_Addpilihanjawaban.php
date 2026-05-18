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
    $pilihan_jawaban    = fixup($_POST['pilihan_jawaban']);
    $nilai              = fixup($_POST['nilai']);
    $penjelasan_jawaban = fixup($_POST['penjelasan_jawaban']);
    $kategori_jawaban   = fixup($_POST['kategori_jawaban']);

    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($pilihan_jawaban <> '') && ($nilai <> '')) {

        $sqlcekdata     = "SELECT pilihan_jawaban FROM pilihan_jawaban WHERE pilihan_jawaban='" . $pilihan_jawaban . "' and is_active='1'";
        $resultcekuser  = mysqli_query($link, $sqlcekdata);
        $numcekuser     = mysqli_num_rows($resultcekuser);

        if ($numcekuser > 0) {
            mysqli_close($link);
            echo json_encode(array('response' => 'error', 'message' => 'Sub Komponen sudah ada di sistem'));
            die;
        }
        else {
            $sql = "INSERT INTO pilihan_jawaban (pilihan_jawaban, nilai, penjelasan_jawaban, kategori_jawaban) 
                    VALUES 
                    ('" . $pilihan_jawaban . "','" . $nilai . "','" . $penjelasan_jawaban . "','" . $kategori_jawaban . "')";
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
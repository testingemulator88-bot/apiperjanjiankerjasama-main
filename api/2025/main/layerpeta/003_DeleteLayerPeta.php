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
    $kode = fixup($_POST['kode']);
    $deletedby = fixup($_POST['deletedby']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($kode <> '') && ($deletedby <> '')) {
        $sqlcek = "select id from simfast_peta where id_parent='" . $kode . "' and deleted='0'";
        $resultcek = mysqli_query($link, $sqlcek);
        $jumlahcek = mysqli_num_rows($resultcek);
        if ($jumlahcek == 0) {
            $sql = "update simfast_peta set deleted='1',deleteddate=SYSDATE(),deletedby='" . $deletedby . "' where id ='" . $kode . "' ";
            $result = mysqli_query($link, $sql);
            if ($result) {
                mysqli_close($link);
                echo json_encode(array('response' => 'success', 'message' => 'Data Telah Dihapus'));
                die;
            } else {
                mysqli_close($link);
                echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
                die;
            }
        } else {
            mysqli_close($link);
            echo json_encode(array('response' => 'error', 'message' => 'Silahkan hapus level dibawahnya terlebih dahulu'));
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

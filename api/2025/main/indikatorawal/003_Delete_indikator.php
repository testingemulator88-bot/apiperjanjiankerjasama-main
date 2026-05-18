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
    $deletedby = fixup($_POST['deletedby']);
    $id = fixup($_POST['id']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($id <> '')) {
        $sqlcek = "select id from tb_indikator_awal where id_parent = '" . $id . "' and deleted='0'";
        $resultcek = mysqli_query($link, $sqlcek);
        $numcek = mysqli_num_rows($resultcek);
        if ($numcek > 0) {
            mysqli_close($link);
            echo json_encode(array('response' => 'error', 'message' => 'Hapus Level Bawah Dari Data Terlebih Dahulu'));
            die;
        } else {
            $sql = " update tb_indikator_awal set deleted = '1'
            , deletedby = '" . $deletedby . "' , deleteddate = SYSDATE()
            where id = '" . $id . "'";
            //echo $sql;
            //die;
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

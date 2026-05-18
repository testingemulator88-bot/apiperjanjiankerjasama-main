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
    $kdunor = fixup($_POST['kdunor']);
    $kdbalai = fixup($_POST['kdbalai']);
    $kdbalai_pendek = fixup($_POST['kdbalai_pendek']);
    $kdbalai_old = fixup($_POST['kdbalai_old']);
    $kdbalai_old_pendek = fixup($_POST['kdbalai_old_pendek']);
    $nama_kategori = fixup($_POST['nama_kategori']);
    $alamat = fixup($_POST['alamat']);
    $latitude = fixup($_POST['latitude']);
    $longitude = fixup($_POST['longitude']);
    $urut = fixup($_POST['urut']);
    $createdby = fixup($_POST['createdby']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($kdbalai <> '') && ($nama_kategori <> '') && ($kdunor <> '')) {

        $sql = "insert into master_kategori_satker (id,kdunor,kdbalai,kdbalai_pendek,kdbalai_old,kdbalai_old_pendek
        ,nama_kategori,alamat,latitude,longitude,urut
        ,createddate,createdby,deleted) values 
        (null,'" . $kdunor . "','" . $kdbalai . "','" . $kdbalai_pendek . "','" . $kdbalai_old . "','" . $kdbalai_old_pendek . "','" . $nama_kategori . "','" . $alamat . "','" . $latitude . "'
        ,'" . $longitude . "','" . $urut . "',SYSDATE(),'" . $createdby . "','0')";
        //echo $sql;
        //die;
        $result = mysqli_query($link, $sql);
        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah Ditambah'));
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

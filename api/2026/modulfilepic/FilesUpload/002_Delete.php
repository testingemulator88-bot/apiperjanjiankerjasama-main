<?php
include '../../library/config.php';
error_reporting(0);
check_injection();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, authorization");
header("Content-type: application/json");
$header = apache_request_headers();
//var_dump($header);
$Bearer = $header['authorization'];
if ($Bearer == '') {
    $Bearer = $header['Authorization'];
}
$project_id = fixup($_POST['project_id']);
$tempBearer = explode(" ", $Bearer);

if (($Bearer <> '') && ($tempBearer[1] <> '')) {
    $res = [];
    $ekstensiYangDibolehkan = [
        'png',
        'jpg',
        'jpeg',
        'gif',
        'pdf'
    ];
    $ekstensipanjangYangDibolehkan = [
        'image/png',
        'image/jpg',
        'image/jpeg',
        'application/pdf'
    ];
    $target_dir = $docRootLKE . $_POST['folderToUpload'] . "/";
    $filenya = $_POST['filenya'];
    $filegeojson = substr($filenya, -8);
    $fileexel = substr($filenya, -5);
    if (strtoupper($filegeojson) == '.GEOJSON') {
        if (file_exists($target_dir . $filenya)) {
            unlink($target_dir . $filenya);
            $res["message"] = "hapus file berhasil";
        } else {
            $res["message"] = "Terjadi kesalahan waktu hapus file.";
        }
        echo json_encode($res);
        die();
    } else if (strtoupper($fileexel) == '.XLSX') {
        if (file_exists($target_dir . $filenya)) {
            unlink($target_dir . $filenya);
            $res["message"] = "hapus file berhasil";
        } else {
            $res["message"] = "Terjadi kesalahan waktu hapus file.";
        }
        echo json_encode($res);
        die();
    } else {
        $temp = explode(".", $filenya);
        if (!in_array($temp[1], $ekstensiYangDibolehkan)) {
            mysqli_close($link);
            echo json_encode(array('response' => 'error', 'message' => 'File Tidak Valid'));
            die();
        }
        if (file_exists($target_dir . $filenya)) {
            unlink($target_dir . $filenya);
            $res["message"] = "hapus file berhasil";
        } else {
            $res["message"] = "Terjadi kesalahan waktu hapus file.";
        }
        echo json_encode($res);
        die();
    }
} else {
    mysqli_close($link);
    echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
}

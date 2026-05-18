<?php
include '../../library/config.php';
error_reporting(0);
//check_injection();
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
        'pdf',
        'mp4'
    ];
    $ekstensipanjangYangDibolehkan = [
        'image/png',
        'image/jpg',
        'image/jpeg',
        'application/pdf',
        'video/mp4'
    ];
    $target_dir = $docRootLKE . $_POST['folderToUpload'] . "/";
    $filegeojson = substr($_FILES["fileToUpload"]["name"], -8);
    $fileexel = substr($_FILES["fileToUpload"]["name"], -5);
    if (strtoupper($filegeojson) == '.GEOJSON') {
        $newname = date('YmdHis') . "_" . preg_replace('/\s+/', '', md5(basename($_FILES["fileToUpload"]["name"])) . ".geojson");
    }
    else if (strtoupper($fileexel) == '.XLSX') {
        $newname = date('YmdHis') . "_" . preg_replace('/\s+/', '', md5(basename($_FILES["fileToUpload"]["name"])) . ".xlsx");
    } else {
        $newname = date('YmdHis') . "_" . preg_replace('/\s+/', '', md5(basename($_FILES["fileToUpload"]["name"])) . "." . basename($_FILES["fileToUpload"]["type"]));
    }
    $target_files = $target_dir . $newname;
    $ext = basename($_FILES["fileToUpload"]["type"]);
    //echo json_encode(array('response' => 'error', 'message' => $filegeojson));


    if (strtoupper($filegeojson) == '.GEOJSON') {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_files)) {
            $res["message"] = "File " . basename($_FILES["fileToUpload"]["name"]) . " telah terupload";
            $res["uploadedUrl"] = $target_files;
            $res["filename"] = $newname;
        } else {
            $res["message"] = "Terjadi kesalahan waktu upload file.";
            $res["uploadedUrl"] = $target_files;
            $res["filename"] = $newname;
        }
        echo json_encode($res);
        die();
    } else if (strtoupper($fileexel) == '.XLSX') {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_files)) {
            $res["message"] = "File " . basename($_FILES["fileToUpload"]["name"]) . " telah terupload";
            $res["uploadedUrl"] = $target_files;
            $res["filename"] = $newname;
        } else {
            $res["message"] = "Terjadi kesalahan waktu upload file.";
            $res["uploadedUrl"] = $target_files;
            $res["filename"] = $newname;
        }
        echo json_encode($res);
        die();
    } else {
        if (!in_array($ext, $ekstensiYangDibolehkan)) {
            mysqli_close($link);
            echo json_encode(array('response' => 'error', 'message' => 'File Tidak Valid'));
            die();
        }
        if (!in_array(mime_content_type($_FILES["fileToUpload"]["tmp_name"]), $ekstensipanjangYangDibolehkan)) {
            mysqli_close($link);
            echo json_encode(array('response' => 'error', 'message' => 'File Tidak Valid'));
            die();
        }

        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_files)) {
            $res["message"] = "File " . basename($_FILES["fileToUpload"]["name"]) . " telah terupload";
            $res["uploadedUrl"] = $target_files;
            $res["filename"] = $newname;
        } else {
            $res["message"] = "Terjadi kesalahan waktu upload file 1.";
        }
        echo json_encode($res);
        die();
    }
} else {
    mysqli_close($link);
    echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
}

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
    $id_komponen        = fixup($_POST['id_komponen']);
    $kode_subkomponen   = fixup($_POST['kode_subkomponen']);
    $nama_subkomponen   = fixup($_POST['nama_subkomponen']);
    $bobot_subkomponen  = fixup($_POST['bobot_subkomponen']);

    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($nama_subkomponen <> '') && ($bobot_subkomponen <> '')) {

        $sqlcekdata     = "SELECT nama_subkomponen FROM subkomponen WHERE nama_subkomponen='" . $nama_subkomponen . "' and is_active='1'";
        $resultcekuser  = mysqli_query($link, $sqlcekdata);
        $numcekuser     = mysqli_num_rows($resultcekuser);

        if ($numcekuser > 0) {
            mysqli_close($link);
            echo json_encode(array('response' => 'error', 'message' => 'Sub Komponen sudah ada di sistem'));
            die;
        }
        else {
            // $bobot_komponen = 0;
            // $sqlKomponen    = "SELECT bobot_komponen FROM komponen WHERE id_komponen='" . $id_komponen . "'";
            // $resultKomponen = mysqli_query($link, $sqlKomponen);
            // while($rowKomponen    = mysqli_fetch_assoc($resultKomponen)){
            //     $bobot_komponen = (float) $rowKomponen['bobot_komponen'] * (float) $bobot_subkomponen;
            // }

            $sql = "INSERT INTO subkomponen (id_komponen, kode_subkomponen, nama_subkomponen, bobot_subkomponen) 
                    VALUES 
                    ('" . $id_komponen . "','" . $kode_subkomponen . "','" . $nama_subkomponen . "','" . $bobot_subkomponen . "')";
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
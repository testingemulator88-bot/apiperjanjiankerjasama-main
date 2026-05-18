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
    $link_bukti_dukung  = fixup($_POST['link_bukti_dukung']);
    $tanggapan_satker   = fixup($_POST['tanggapan_satker']);

    $id_lke              = fixup($_POST['id_lke']);
    $kode_satker         = fixup($_POST['kode_satker']);
    $level               = fixup($_POST['level']);

    // if(strstr($level, '1') || strstr($level, '2') || strstr($level, '5')){
    //     $kode_satker    = fixup($_POST['id_user']);

    //     if(strstr($level, '1') || strstr($level, '2')){
    //         $sql_cek_user   = "SELECT id FROM tb_user WHERE kode_satker = '". $kode_satker ."' AND level in(5,8) AND deleted = '0'";
    //         // echo $sql_cek_user;
    //         // die;
    //     }
    //     elseif(strstr($level, '5')){
    //         $sql_cek_user   = "SELECT id FROM tb_user WHERE kode_satker = '". $kode_satker ."' AND level = '7' AND deleted = '0'";
    //     }
    //     else{
    //         $sql_cek_user   = "SELECT id FROM tb_user WHERE kode_satker = '". $kode_satker ."' AND deleted = '0'";
    //     }
        
    //     echo $sql_cek_user;
    //     die;
        
    //     $result_cek_user= mysqli_query($link, $sql_cek_user);
    //     $row_cek_user   = mysqli_fetch_assoc($result_cek_user);
    //     $id_user        = $row_cek_user['id'];
    // }
    // else{
    //     $id_user    = fixup($_POST['id_user']);
    // }

    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($id_lke <> '')) {

        $sql = "UPDATE hasil_lke SET jawaban_satker = '" . $tanggapan_satker . "', link_bukti_dukung = '" . $link_bukti_dukung . "', status_lke = '3' 
                WHERE id_hasil_lke = '" . $id_lke . "'";
        // echo $sql;
        // die;

        $result = mysqli_query($link, $sql);
        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah Diedit'));
            die;
        }
        else {
            mysqli_close($link);
            echo json_encode(array('response' => 'error', 'message' => 'Gagal valid'));
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
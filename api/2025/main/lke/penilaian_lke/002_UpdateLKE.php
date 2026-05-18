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
    $kriteria_penilaian1    = fixup($_POST['kriteria_penilaian1']);
    $kriteria_penilaian2    = fixup($_POST['kriteria_penilaian2']);
    $kriteria_penilaian3    = fixup($_POST['kriteria_penilaian3']);
    $kriteria_penilaian4    = fixup($_POST['kriteria_penilaian4']);
    $tanggal_evaluasi       = $_POST['tanggal_evaluasi'];
    $catatan_hasil_evaluasi = fixup($_POST['catatan_hasil_evaluasi']);
    $tanggapan_evaluator    = fixup($_POST['tanggapan_evaluator']);
    $rekomendasi_evaluator  = fixup($_POST['rekomendasi']);
    $tindak_lanjut          = fixup($_POST['tindak_lanjut']);

    $id_kriteria            = fixup($_POST['id_kriteria']);
    $id_lke                 = fixup($_POST['id_lke']);
    $tahun                  = fixup($_POST['tahun']);
    $id_evaluator           = fixup($_POST['id_evaluator']);
    $kode_satker            = fixup($_POST['kode_satker']);
    $level                  = fixup($_POST['level']);

    // $sql_cek_user           = "SELECT id FROM tb_user WHERE kode_satker = '". $kode_satker ."' AND deleted = '0'";
    // $result_cek_user        = mysqli_query($link, $sql_cek_user);
    // $row_cek_user           = mysqli_fetch_assoc($result_cek_user);
    // $id_user                = $row_cek_user['id'];

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
        
    //     // echo $sql_cek_user;
    //     // die;
        
    //     $result_cek_user= mysqli_query($link, $sql_cek_user);
    //     $row_cek_user   = mysqli_fetch_assoc($result_cek_user);
    //     $id_user        = $row_cek_user['id'];
    // }
    // else{
    //     $id_user    = fixup($_POST['id_user']);
    // }

    // echo $id_user;
    // die;

    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($kriteria_penilaian1 <> '') && ($kriteria_penilaian3 <> '') && ($kriteria_penilaian4 <> '') && ($id_kriteria <> '') && ($tahun <> '') && ($kode_satker <> '')) {
        if($tanggal_evaluasi != '' && !is_null($tanggal_evaluasi)){
            $sql = "UPDATE hasil_lke SET kriteria_penilaian1 = '" . $kriteria_penilaian1 . "', kriteria_penilaian2 = '" . $kriteria_penilaian2 . "', kriteria_penilaian3 = '" . $kriteria_penilaian3 . "', 
                kriteria_penilaian4 = '" . $kriteria_penilaian4 . "', tanggal_evaluasi = '" . $tanggal_evaluasi . "', catatan_evaluator = '" . $catatan_hasil_evaluasi . "', status_lke = '2' 
                WHERE id_hasil_lke = '" . $id_lke . "'";
            
        }
        else{
            $sql = "UPDATE hasil_lke SET kriteria_penilaian1 = '" . $kriteria_penilaian1 . "', kriteria_penilaian2 = '" . $kriteria_penilaian2 . "', kriteria_penilaian3 = '" . $kriteria_penilaian3 . "', 
                kriteria_penilaian4 = '" . $kriteria_penilaian4 . "', catatan_evaluator = '" . $catatan_hasil_evaluasi . "', tanggapan_evaluator = '" . $tanggapan_evaluator . "', deskripsi_rekomendasi = '" . $rekomendasi_evaluator . "', status_tindak_lanjut = '" . $tindak_lanjut . "', status_lke = '4' 
                WHERE  id_hasil_lke = '" . $id_lke . "'";
        }
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
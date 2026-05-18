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
    $updateby = fixup($_POST['updateby']);
    $tabel = fixup($_POST['tabel']);
    $id_indikator = fixup($_POST['id_indikator']);
    $kode_tahun = fixup($_POST['kode_tahun']);
    $kolom = fixup($_POST['kolom']);
    $isi = fixup($_POST['isi']);
    $tahun = get_Isi_Field1('tahun','master_pk','id','3');
    $id_balai = fixup($_POST['id_balai']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($id_indikator <> '')) {
        $sqltemp = " select id, " . $kolom . " from " . $tabel . " where deleted ='0' and id_indikator='" . $id_indikator . "'
        and kode_tahun = '" . $kode_tahun . "' and tahun = '" . $tahun . "' and id_balai = '" . $id_balai . "'";
        $resulttmp  = mysqli_query($link, $sqltemp);
        while ($rowtmp = mysqli_fetch_assoc($resulttmp)) {
            $idnya = $rowtmp['id'];
            $isian = replace_comma($isi);
        }
        $sql = "update " . $tabel . " set " . $kolom . "='" . $isian . "' where id = '" . $idnya . "'";

        //echo $sql;
        //die;
        $result = mysqli_query($link, $sql);
        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => $sql));
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

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
    $updateby = fixup($_POST['updateby']);
    $id = fixup($_POST['kode']);
    $kolom = fixup($_POST['kolom']);
    if ($kolom == 'longitude') {
        $isi = fixup($_POST['isi']);
    }
    else if ($kolom == 'latitude') {
        $isi = fixup($_POST['isi']);
    }
    else {
        $isi = fixup(replace_comma($_POST['isi']));
    }
    $kolom = fixup($_POST['kolom']);
    
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($id <> '')) {

        $sql = " update tb_data_pk_emon set " . $kolom . "='" . $isi . "'
        , updateby = '" . $updateby . "' , updatedate = SYSDATE()
        where id = '" . $id . "'";
        //echo $sql;
        //die;
        $result = mysqli_query($link, $sql);

        $sqlcekemon = "select id_pk,id_indikator,kode_satker,tahun from tb_data_pk_emon where id = '" . $id . "'";
        $resultcekemon = mysqli_query($link, $sqlcekemon);
        $numemon = mysqli_num_rows($resultcekemon);
        if ($numemon > 0) {
            while ($rowemon = mysqli_fetch_assoc($resultcekemon)) {
                $id_pk = $rowemon['id_pk'];
                $id_indikator = $rowemon['id_indikator'];
                $kode_satker = $rowemon['kode_satker'];
                $tahun = $rowemon['tahun'];
            }
            $sqljumlah = "select sum(" . $kolom . ") as " . $kolom . " from tb_data_pk_emon where deleted = '0'
            and id_pk = '" . $id_pk . "' and id_indikator = '" . $id_indikator . "' 
            and kode_satker = '" . $kode_satker . "' and tahun = '" . $tahun . "'";
            $resultjumlah = mysqli_query($link, $sqljumlah);
            $numejumlah = mysqli_num_rows($resultjumlah);
            if ($numejumlah > 0) {
                while ($rowjumlah = mysqli_fetch_assoc($resultjumlah)) {
                    $jumlahnya = replace_dot_koma_tiga($rowjumlah[$kolom]);
                }
            }
            else {
                $jumlahnya = 0;
            }
        } else {
            $jumlahnya = 0;
        }
        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah Diupdate', 'jumlahnya' => $jumlahnya));
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

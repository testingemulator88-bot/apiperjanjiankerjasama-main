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

$numcek = mysqli_num_rows($resultsqlcek);
if ($numcek > 0) {

    $kode_satker    = fixup($_POST['kode_satker']);
    $tahun          = fixup($_POST['tahun']);
    // $no_surat       = fixup($_POST['no_surat']);
    $tempat_ttd     = fixup($_POST['tempat_ttd']);
    $tgl_surat      = fixup($_POST['tgl_surat']);
    $nip_pejabat    = fixup($_POST['nip_pejabat']);
    $nama_pejabat   = fixup($_POST['nama_pejabat']);
    $jabatan        = fixup($_POST['jabatan']);
    
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($kode_satker <> '') && ($tahun <> '')) {
        $sqlcekttd      = "SELECT id FROM data_ttd_lke WHERE kode_satker = '" . $kode_satker . "' AND tahun = '" . $tahun . "' AND is_active = '1' LIMIT 1";
        $resultcekttd   = mysqli_query($link, $sqlcekttd);
        $numcekttd      = mysqli_num_rows($resultcekttd);

        if ($numcekttd > 0) {
            $rowttd     = mysqli_fetch_assoc($resultcekttd);
            $id         = $rowttd['id'];
            
            $sql = "UPDATE data_ttd_lke set kode_satker='" . $kode_satker . "', tahun = '". $tahun ."', 
                    tempat_ttd = '". $tempat_ttd ."', tgl_surat = '". $tgl_surat ."', nip_pejabat = '". $nip_pejabat ."', nama_pejabat = '". $nama_pejabat ."', jabatan = '". $jabatan ."'
                    WHERE id = '" . $id . "'";
        }
        else {
            $sql = "INSERT INTO data_ttd_lke (kode_satker, tahun, tempat_ttd, tgl_surat, nip_pejabat, nama_pejabat, jabatan) 
                    VALUES ('" . $kode_satker . "', '" . $tahun . "', '" . $tempat_ttd . "', '" . $tgl_surat . "', '" . $nip_pejabat . "', '" . $nama_pejabat . "', '" . $jabatan . "')";
        }

        //echo $sql;
        //die;
        $result = mysqli_query($link, $sql);
        
        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah Diupdate'));
            die;
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
}
else {
    mysqli_close($link);
    echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
    die;
}

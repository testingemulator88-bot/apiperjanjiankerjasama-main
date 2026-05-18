<?php
include '../../library/config.php';
error_reporting(0);
check_injection();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
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
//echo $sqlcek;
//die;
$resultsqlcek = mysqli_query($link, $sqlcek);
$numcek = mysqli_num_rows($resultsqlcek);
if ($numcek > 0) {
    if (($Bearer <> '') && ($tempBearer[1] <> '')) {
        $kode_satker = fixup($_GET['kode_satker']);
        $myArray = array();
        // IKSK
        if ($kode_satker <> '1') {
            $sql = "select a.id,a.level_pejabat,a.kode_satker_pejabat,a.tanggal_pejabat
            ,a.lokasi_pejabat,a.nama_pejabat,a.pangkat_pejabat
            ,a.jabatan_pejabat,a.nip_pejabat,a.filenya_ttd
            from tb_data_ttd_akhir a 
            where a.deleted='0' and a.level_pejabat = 'Balai'
            and a.kode_satker_pejabat = '" . $kode_satker . "'
            order by a.id ASC ";
        } else {
            $sql = "select a.id,a.level_pejabat,a.kode_satker_pejabat,a.tanggal_pejabat
            ,a.lokasi_pejabat,a.nama_pejabat,a.pangkat_pejabat
            ,a.jabatan_pejabat,a.nip_pejabat,a.filenya_ttd
            from tb_data_ttd_akhir a 
            where a.deleted='0' and a.level_pejabat = 'Dirjen'
            and a.kode_satker_pejabat = '" . $kode_satker . "'
            order by a.id ASC ";
        }

        //echo $sql;
        //die();
        $result = mysqli_query($link, $sql);
        $num = mysqli_num_rows($result);
        if ($num > 0) {
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $myArray[] = (object)
                    [
                        'id' => (float) $row['id'],
                        'level_pejabat' => $row['level_pejabat'],
                        'kode_satker_pejabat' => $row['kode_satker_pejabat'],
                        'tanggal_pejabat' => $row['tanggal_pejabat'],
                        'lokasi_pejabat' => $row['lokasi_pejabat'],
                        'nama_pejabat' => $row['nama_pejabat'],
                        'pangkat_pejabat' => $row['pangkat_pejabat'],
                        'jabatan_pejabat' => $row['jabatan_pejabat'],
                        'nip_pejabat' => $row['nip_pejabat'],
                        'filenya_ttd' => $row['filenya_ttd'],
                    ];
                }
                $response         = [];
                $response['data'] =  $myArray;
                mysqli_close($link);
                echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
                die;
            }
        } else {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'data kosong'), JSON_PRETTY_PRINT);
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

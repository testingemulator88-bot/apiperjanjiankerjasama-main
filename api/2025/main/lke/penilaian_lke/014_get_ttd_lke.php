<?php
include '../../../library/config.php';
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
$resultsqlcek   = mysqli_query($link, $sqlcek);
$numcek         = mysqli_num_rows($resultsqlcek);

if ($numcek > 0) {
    if (($Bearer <> '') && ($tempBearer[1] <> '')) {
        $kode_satker    = fixup($_GET['kode_satker']);
        $tahun          = fixup($_GET['tahun']);
        $myArray        = array();

        $sql = "SELECT * FROM data_ttd_lke WHERE kode_satker = '" . $kode_satker . "' AND tahun = '" . $tahun ."' AND is_active = '1' ORDER BY id ASC LIMIT 1";

        //echo $sql;
        //die();
        $result = mysqli_query($link, $sql);
        $num    = mysqli_num_rows($result);

        if ($num > 0) {
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $myArray[] = (object)
                    [
                        'id'            => (float) $row['id'],
                        'kode_satker'   => $row['kode_satker'],
                        'tahun'         => $row['tahun'],
                        'no_surat'      => $row['no_surat'],
                        'tempat_ttd'    => $row['tempat_ttd'],
                        'tgl_surat'     => $row['tgl_surat'],
                        'nip_pejabat'   => $row['nip_pejabat'],
                        'nama_pejabat'  => $row['nama_pejabat'],
                        'jabatan'       => $row['jabatan'],
                    ];
                }

                $response         = [];
                $response['data'] =  $myArray;
                
                mysqli_close($link);
                echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
                die;
            }
        }
        else {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'data kosong'), JSON_PRETTY_PRINT);
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

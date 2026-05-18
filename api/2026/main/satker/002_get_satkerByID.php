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
        $id=fixup($_GET['id']);
        $myArray = array();
        $sql = "select a.id,c.id as idunor,c.kdbalai as kdunor,c.nama_kategori as namaunor,a.kdbalai as idbalai
        ,b.kdbalai as kdbalai,b.nama_kategori as nama_kategori_satker,a.kode_satker,a.kode_satker_pendek
        ,a.kode_satker_old,a.kode_satker_old_pendek,a.nama_satker,a.aktif
        ,a.status_piu, d.nama as namastatus_piu,a.level_piu, e.nama as namalevel_piu
        from master_satker a 
        left join master_unor c on
        a.kdunor=c.id
        left join master_kategori_satker b on
        a.kdbalai=b.id
        left join master_status_piu d on
        a.status_piu=d.id
        left join master_level_piu e on
        a.level_piu=e.id
        where a.deleted='0' 
        and a.id = '" . $id . "' 
        order by a.id  ";
        //echo $sql;
        //die();
        $result = mysqli_query($link, $sql);
        $num = mysqli_num_rows($result);
        if ($num > 0) {
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $myArray[] = $row;
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

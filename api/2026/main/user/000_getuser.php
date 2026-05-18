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
    $level = fixup($_GET['level']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($level <> '')) {
        $myArray = array();
        $sql = "select a.id,a.username,md5(a.id) as md5nya, a.nama, a.level as level
        , a.levelcode, a.levellabel
        , b.nama as namaleveluser,a.unor,e.nama_kategori as nama_unor, a.kategori_satker, c.nama_kategori as nama_kategori_satker
        , a.satker,a.kode_satker, d.nama_satker
        ,a.jabatan,a.pangkat,a.nip, a.notelp, a.alamat, a.email
        ,ifnull(a.ikon,'3177440.png') as foto
        from tb_user a 
        left join master_level b
        on a.level=b.id
        left join master_unor e
        on a.unor=e.id
        left join master_kategori_satker c
        on a.kategori_satker=c.id
        left join master_satker d
        on a.satker = d.id
        where a.deleted='0' 
        and a.level >=" . $level . "
        order by a.kategori_satker,a.kode_satker, a.level asc  ";
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

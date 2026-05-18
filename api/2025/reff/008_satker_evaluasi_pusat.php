<?php
include '../library/config.php';
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
$filter_query = "";
$sqlcek = "select id as jumlah from tb_user where token='" . str_replace('"', '', $tempBearer[1]) . "' and deleted='0'";
//echo $sqlcek;
//die;
$resultsqlcek = mysqli_query($link, $sqlcek);
$numcek = mysqli_num_rows($resultsqlcek);

$id_user = fixup($_GET['iduser']);

if ($numcek > 0) {
    if (($Bearer <> '') && ($tempBearer[1] <> '')) {
        $cek_user       = "SELECT * FROM tb_user WHERE id='". $id_user ."' and deleted='0'";
        $result_user    = mysqli_query($link, $cek_user);
        $row_user       = mysqli_fetch_assoc($result_user);
        $hakaksesevaluatorlke = $row_user['hakaksesevaluatorlke'];

        $myArray = array();

        if($row_user['level'] == 1){
            $sql = "SELECT a.id, a.kode_satker, a.kdbalai, replace(a.nama_satker, 'SATKER', '') as nama FROM master_satker as a 
                    INNER JOIN tb_user as b ON b.kode_satker = a.kode_satker and b.deleted='0'
                    WHERE (b.level in (4,6,8,9) or a.level_piu = 21 or a.level_piu = 6 or a.level_piu = 9 or a.level_piu = 31) and a.deleted='0' group by a.id order by a.kdbalai, a.kode_satker, a.id ";
        }
        elseif($row_user['level'] == 2){
            $sql = "SELECT a.id, a.kode_satker, a.kdbalai, replace(a.nama_satker, 'SATKER', '') as nama FROM master_satker as a 
                    INNER JOIN tb_user as b ON b.kode_satker = a.kode_satker and b.deleted='0'
                    WHERE a.kode_satker in(". $hakaksesevaluatorlke .") and (b.level in (4,6,8,9) or a.level_piu = 21 or a.level_piu = 6 or a.level_piu = 9 or a.level_piu = 31) and a.deleted='0' group by a.id order by a.kdbalai, a.kode_satker, a.id ";
        }
        else{
            $sql = "SELECT a.id, a.kode_satker, a.kdbalai, replace(a.nama_satker, 'SATKER', '') as nama FROM master_satker as a 
                    INNER JOIN tb_user as b ON b.kode_satker = a.kode_satker and b.deleted='0'
                    WHERE (b.level in (4,6,8,9) or a.level_piu = 21 or a.level_piu = 6 or a.level_piu = 9 or a.level_piu = 31) and a.deleted='0' group by a.id order by a.kdbalai, a.kode_satker, a.id ";
        }
        
        // echo $sql;
        // die();
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

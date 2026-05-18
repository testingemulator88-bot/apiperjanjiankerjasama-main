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
if ($numcek > 0) {

    if (($Bearer <> '') && ($tempBearer[1] <> '')) {
        $myArray = array();
        $table = fixup($_GET['table']);
        if ($table <> '') {
            $param_table = "hasil_lke where is_active = '1'";
        }
        else{
            $param_table = "tb_indikator where deleted='0'";
        }

        $sqlcek = "select tahun from ". $param_table . " group by tahun order by tahun ";
        $resultcek = mysqli_query($link, $sqlcek);
        $numcek = mysqli_num_rows($resultcek);

        if ($numcek > 0) {
            $sql = "select min(tahun) as tahunmin,max(tahun) as tahunmax from ". $param_table ."
            group by tahun order by tahun ";
        } else {
            $sql = "SELECT YEAR(CURDATE()) AS tahunmin, YEAR(CURDATE()) AS tahunmax";
        }

        //echo $sql;
        //die();
        $result = mysqli_query($link, $sql);
        $num = mysqli_num_rows($result);
        if ($num > 0) {
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $tahunmin = (float) $row['tahunmin'];
                    $tahunmax = (float) $row['tahunmax'];
                }

                $n = 1;
                if ($tahunmin == $tahunmax) {
                    for ($x = $tahunmin - 3; $x <= $tahunmin + 3; $x++) {
                        $myArray[] = (object)
                        [
                            'id' => (float) $n,
                            'tahun' => (float) $x,
                        ];
                        $n++;
                    }
                } else {
                    for ($x = $tahunmin; $x <= $tahunmin + 3; $x++) {
                        $myArray[] = (object)
                        [
                            'id' => (float) $n,
                            'tahun' => (float) $x,
                        ];
                        $n++;
                    }
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

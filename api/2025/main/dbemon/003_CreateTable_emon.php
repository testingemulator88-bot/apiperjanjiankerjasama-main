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
    $namatabel = fixup($_POST['namatabel']);
    $kolomtabel = fixup($_POST['kolomtabel']);
    $typekolom = fixup($_POST['typekolom']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($namatabel <> '') && ($kolomtabel <> '')) {
        $finalquerykolom = "";
        $tempkolomtabel = explode(",", $kolomtabel);
        $temptypekolom = explode(",", $typekolom);
        $finalquerykolom = $finalquerykolom . " idnya BIGINT(40) NOT NULL AUTO_INCREMENT PRIMARY KEY, ";
        for ($x = 0; $x <= count($tempkolomtabel) - 1; $x++) {
            $tipetabel = "TEXT";
            if ($temptypekolom[$x] == 'REAL') {
                $tipetabel = "DOUBLE";
            }
            $finalquerykolom = $finalquerykolom . $tempkolomtabel[$x] . " " . $tipetabel . ", ";
        }
        $createdby = fixup($_POST['createdby']);
        $sql = "SHOW TABLES LIKE '" . $namatabel . "'";
        $result = mysqli_query($link, $sql);
        $num = mysqli_num_rows($result);
        if ($num == 0) {
            $sqlcreate = "CREATE TABLE " . $namatabel . " (
            " . $finalquerykolom . "
            tanggaldata DATETIME )";
            //echo $sql;
            //die;
            $resultcreate = mysqli_query($link, $sqlcreate);
            if ($resultcreate) {
                mysqli_close($link);
                echo json_encode(array('response' => 'success', 'message' => 'Data Tabel Telah Dibuat'));
                die;
            } else {
                mysqli_close($link);
                echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
                die;
            }
        } else {
            mysqli_close($link);
            echo json_encode(array('response' => 'error', 'message' => 'Tabel Sudah Ada'));
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

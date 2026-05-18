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
    $filenya_emon = fixup($_POST['filenya_emon']);
    $tempfilenya_emon = explode("_", $filenya_emon);
    $temptanggal = $tempfilenya_emon[3];
    $tahun = substr($temptanggal, 0, 4);
    $bulan = substr($temptanggal, 4, 2);
    $tanggal = substr($temptanggal, 6, 2);
    $jam = substr($temptanggal, 8, 2);
    $tabel = fixup($_POST['tabel']);

    $fixtanggal = $tahun . "-" . $bulan . "-" . $tanggal . " " . $jam . ":00";

    $createdby = fixup($_POST['createdby']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($filenya_emon <> '')) {

        $temptabel = explode(",", $tabel);
        for ($x = 0; $x <= count($temptabel) - 1; $x++) {
            $sqlcekdatabase = "SHOW TABLES FROM " . $dbbackup . " LIKE '" . $temptabel[$x] . "'";
            $resultcekdatabase = mysqli_query($link, $sqlcekdatabase);
            $numcekdatabase = mysqli_num_rows($resultcekdatabase);
            if ($numcekdatabase > 0) {
                $sqlcekbackup = "SELECT tanggaldata
                FROM " . $dbbackup . "." . $temptabel[$x] . "
                WHERE tanggaldata = '" . $fixtanggal . "' group by tanggaldata";
                $resultcekbackup = mysqli_query($link, $sqlcekbackup);
                $numcekbackup = mysqli_num_rows($resultcekbackup);
                if ($numcekbackup == 0) {
                    $namafield = "";
                    $sqlisidata = "SHOW COLUMNS FROM " . $temptabel[$x] . "";
                    $resultisidata = mysqli_query($link, $sqlisidata);
                    while ($rowisidata = mysqli_fetch_assoc($resultisidata)) {
                        if ($rowisidata['Field'] <> 'idnya') {
                            $namafield = $namafield . "," . $rowisidata['Field'];
                        }
                    }
                    $fixfieldtambahdata = "null" . $namafield;
                    $sqlinsertdata = " INSERT INTO " . $dbbackup . "." . $temptabel[$x] . " SELECT " . $fixfieldtambahdata . " FROM " . $db . "." . $temptabel[$x] . "";
                    $resultsqlinsertdata = mysqli_query($link, $sqlinsertdata);
                }
            } else {
                $namafield = "";
                $sqlisidata = "SHOW COLUMNS FROM " . $temptabel[$x] . "";
                $resultisidata = mysqli_query($link, $sqlisidata);
                while ($rowisidata = mysqli_fetch_assoc($resultisidata)) {
                    if ($rowisidata['Field'] <> 'idnya') {
                        $namafield = $namafield . "," . $rowisidata['Field'];
                    }
                }
                $fixfieldtambahdata = "null" . $namafield;
                $sqlcreatetabel = "CREATE TABLE " . $dbbackup . "." . $temptabel[$x] . " LIKE " . $db . "." . $temptabel[$x] . "";
                $resultsqlcreatetabel = mysqli_query($link, $sqlcreatetabel);
                $sqlinsertdata = " INSERT INTO " . $dbbackup . "." . $temptabel[$x] . " SELECT " . $fixfieldtambahdata . " FROM " . $db . "." . $temptabel[$x] . "";
                $resultsqlinsertdata = mysqli_query($link, $sqlinsertdata);
            }
        }
        $sqlupdate = "update tb_data_emon set status_backup = '1', tanggal_backup=SYSDATE()
        , updateby='" . $createdby . "', updatedate = SYSDATE() where filenya_emon='" . $filenya_emon . "' and deleted='0'";
        $resultupdate = mysqli_query($link, $sqlupdate);
        $response['data'] =  'tabel ' . $tabel . ' telah diimport';
        if ($resultupdate) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
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

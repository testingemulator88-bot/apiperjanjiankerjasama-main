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

        $kdbalai = fixup($_GET['kdbalai']);

        $kode_satkeremon_balai = "";
        $kode_satkeremon2_balai = "";
        $sqlsatkeremon_balai = "select GROUP_CONCAT(kode_satker_old_pendek) as kode_satker_old_pendek
        ,GROUP_CONCAT(kode_satker_pendek) as kode_satker_pendek 
        from master_satker where kdbalai = '" . $kdbalai . "' and kode_satker_old_pendek <> ''
        and kode_satker_pendek <> '' and deleted='0' order by urut,kode_satker";
        //echo $sqlsatkeremon;
        //die;
        $resultsatkeremon_balai = mysqli_query($link, $sqlsatkeremon_balai);
        while ($rowsatkeremon_balai = mysqli_fetch_assoc($resultsatkeremon_balai)) {
            $kode_satkeremon_balai = $rowsatkeremon_balai['kode_satker_old_pendek'];
            $kode_satkeremon2_balai = $rowsatkeremon_balai['kode_satker_pendek'];
        }
        if ($kode_satkeremon_balai <> '' || $kode_satkeremon2_balai <> '') {
            $filter_query = $filter_query . "  and a.kdoutput in (select x.kdoutput from paket x where 
            x.kdsatker in (" . $kode_satkeremon_balai . ") or x.kdsatker in (" . $kode_satkeremon2_balai . ") group by x.kdoutput)";
        }


        $kode_satker = fixup($_GET['kode_satker']);
        $kode_satkeremon = "";
        $kode_satkeremon2 = "";
        $sqlsatkeremon = "select kode_satker_old_pendek,kode_satker_pendek 
        from master_satker where kode_satker = '" . $kode_satker . "'";
        //echo $sqlsatkeremon;
        //die;
        $resultsatkeremon = mysqli_query($link, $sqlsatkeremon);
        while ($rowsatkeremon = mysqli_fetch_assoc($resultsatkeremon)) {
            $kode_satkeremon = $rowsatkeremon['kode_satker_old_pendek'];
            $kode_satkeremon2 = $rowsatkeremon['kode_satker_pendek'];
        }
        if ($kode_satkeremon <> '' || $kode_satkeremon2 <> '') {
            $filter_query = $filter_query . "  and a.kdoutput in (select x.kdoutput from paket x where 
            x.kdsatker = '" . $kode_satkeremon . "' or x.kdsatker = '" . $kode_satkeremon2 . "' group by x.kdoutput)";
        }

        $kdgiat = fixup($_GET['kdgiat']);
        if ($kdgiat <> '') {
            $filter_query = $filter_query . "  and a.kdgiat in (select x.kdgiat from paket x where 
            x.kdgiat = '" . $kdgiat . "' group by x.kdgiat)";
        }

        $kdoutput = fixup($_GET['kdoutput']);
        if ($kdoutput <> '') {
            $filter_query = $filter_query . "  and a.kdoutput in (select x.kdoutput from paket x where 
            x.kdoutput = '" . $kdoutput . "' group by x.kdoutput)";
        }

        $myArray = array();
        $sql = "select a.idnya as id,a.kdgiat as kdgiat,a.kdoutput,a.kdsoutput as kode
        ,a.ursoutput as nama from d_soutput a where a.ursoutput<>'' " . $filter_query . "
        group by a.kdsoutput,a.ursoutput order by a.kdsoutput";
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

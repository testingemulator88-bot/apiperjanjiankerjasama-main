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
    $id_parent = fixup($_POST['id_parent']);
    if (($id_parent == '') || ($id_parent == 'null') || ($id_parent == null)) {
        $id_parent = 'null';
    } else {
        $id_parent = "'" . $id_parent . "'";
    }
    $level = fixup($_POST['level']);
    $nama = fixup($_POST['nama']);
    $urut = fixup($_POST['urut']);
    $jenis = fixup($_POST['jenis']);
    $asal_peta = fixup($_POST['asal_peta']);
    if (($asal_peta == '') || ($asal_peta == 'null') || ($asal_peta == null)) {
        $asal_peta = '0';
    } else {
        $asal_peta = fixup($_POST['asal_peta']);
    }
    $api = fixup($_POST['api']);
    if (($api == '') || ($api == 'null') || ($api == null)) {
        $api = '0';
    } else {
        $api = fixup($_POST['api']);
    }
    $filenya_peta = fixup($_POST['filenya_peta']);
    if (($filenya_peta == '') || ($filenya_peta == 'null') || ($filenya_peta == null)) {
        $filenya_peta = 'null';
    } else {
        $filenya_peta = "'" . $filenya_peta . "'";
    }

    if ($asal_peta == '2') {
        $sqlapi = "select api from simfast_master_api_peta where id='" . $api . "'";
        $resultapi = mysqli_query($link, $sqlapi);
        while ($rowapi = mysqli_fetch_assoc($resultapi)) {
            $filenya_peta = "'" . $rowapi['api'] . "'";
        }
    }
    $tipe_peta = fixup($_POST['tipe_peta']);

    $fill = fixup($_POST['fill']);
    $stroke = fixup($_POST['stroke']);
    $fill_width = fixup($_POST['fill_width']);
    $stroke_width = fixup($_POST['stroke_width']);
    $dash_start = fixup($_POST['dash_start']);
    $dash_end = fixup($_POST['dash_end']);
    $filenya_ikon = fixup($_POST['filenya_ikon']);
    if (($filenya_ikon == '') || ($filenya_ikon == 'null') || ($filenya_ikon == null)) {
        $filenya_ikon = 'null';
    } else {
        $filenya_ikon = "'" . $filenya_ikon . "'";
    }

    $tipe_fill = fixup($_POST['tipe_fill']);
    $tipe_pattern = fixup($_POST['tipe_pattern']);

    $sudut = fixup($_POST['sudut']);
    $urutindex = fixup($_POST['urutindex']);
    $latitude = fixup($_POST['latitude']);
    $longitude = fixup($_POST['longitude']);
    $zoom = fixup($_POST['zoom']);

    $radius = fixup($_POST['radius']);
    $kolomfilter = fixup($_POST['kolomfilter']);
    $textfilter = fixup($_POST['textfilter']);
    $tampilpublik = fixup($_POST['tampilpublik']);

    $createdby = fixup($_POST['createdby']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($nama <> '')) {
        $sql = "INSERT INTO simfast_peta(id, level, id_parent, urut, nama, jenis
        ,asal_peta, api, filenya_peta, tipe_peta
        ,fill, stroke, fill_width,stroke_width, dash_start, dash_end, filenya_ikon, tipe_fill, tipe_pattern
        , sudut, urutindex, latitude, longitude, zoom, radius, kolomfilter, textfilter, tampilpublik
        , createddate, createdby, deleted) VALUES (null ,'" . $level . "',$id_parent
        ,'" . $urut . "','" . $nama . "','" . $jenis . "'
        ,'" . $asal_peta . "','" . $api . "'," . $filenya_peta . ",'" . $tipe_peta . "'
        ,'" . $fill . "','" . $stroke . "','" . $fill_width . "'
        ,'" . $stroke_width . "','" . $dash_start . "','" . $dash_end . "'," . $filenya_ikon . "
        ,'" . $tipe_fill . "','" . $tipe_pattern . "'
        ,'" . $sudut . "','" . $urutindex . "','" . $latitude . "','" . $longitude . "','" . $zoom . "'
        ,'" . $radius . "','" . $kolomfilter . "','" . $textfilter . "','" . $tampilpublik . "'
        ,SYSDATE(),'" . $createdby . "','0')";
        //echo $sql;
        //die;
        $result = mysqli_query($link, $sql);
        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah Ditambah'));
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

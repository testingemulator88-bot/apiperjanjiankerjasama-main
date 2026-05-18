<?php
include '../../library/config.php';
error_reporting(0);
//check_injection();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, authorization");
header("Content-type: application/json");
$header = apache_request_headers();
$ip = base64_encode($_SERVER['REMOTE_ADDR']);
//var_dump($header);
$Bearer = $header['authorization'];
if ($Bearer == '') {
    $Bearer = $header['Authorization'];
}

$tempBearer = explode(" ", $Bearer);
$myArray = array();
$filter_query = "";
$kdsatker = $_GET['kdsatker'];
if ($kdsatker <> '') {
    $filter_query = $filter_query . " and a.kdsatker = '" . $kdsatker . "'";
}

$sql = "select a.kode_satker,a.kode_emon, group_concat(b.nmpaket) as nmpaket
,concat('" . $webRoot . "maps_parastapainnovation-Ikon?f=','panah1.png') as images
,CAST(a.longitude AS DECIMAL(10,5)) as Longitude
,CAST(a.latitude AS DECIMAL(10,5)) as Latitude 
,ifnull((select b.nama_satker from master_satker b where b.deleted='0' and b.kode_satker = a.kode_satker group by b.kode_satker),'') as namasatker
from tb_data_pk_emon_akhir a 
inner join paket_pk_akhir b on a.kode_emon = b.kode
WHERE a.deleted='0' and a.longitude<>'' and a.latitude<>'' 
and a.longitude < 142 and a.longitude > 93 and a.latitude < 6 and a.latitude > -40
" . $filter_query . "
group by a.longitude, a.latitude";

//echo $sql;

$result = mysqli_query($link, $sql);
$num = mysqli_num_rows($result);
if ($num > 0) {
    if ($result) {
        $geojson = array(
            'type'    => 'FeatureCollection',
            'features'    => array()
        );
        while ($row = mysqli_fetch_assoc($result)) {
            //$myArray[] = $row;
            $properties = (object)
            [
                'Kode Satker' => $row['kode_satker'],
                'Nama Satker' => $row['namasatker'],
                'Nama Paket' => $row['nmpaket'],
                'gambar' => str_replace(' ', '',$row['images']),
                'Latitude' => $row['Latitude'],
                'Longitude' => $row['Longitude'],
                'TEXT_LABEL' => $row['kode_satker'],
            ];
            $feature = array(
                'type'    => 'Feature',
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => array(
                        (float) $row['Longitude'],
                        (float) $row['Latitude']
                    )
                ),
                'properties' => $properties
            );
            array_push($geojson['features'], $feature);
        }
        //$response         = [];
        //$response['data'] =  $myArray;
        mysqli_close($link);
        echo json_encode($geojson, JSON_PRETTY_PRINT);
        die;
    }
} else {
    echo json_encode(array('response' => 'success', 'message' => 'data kosong'), JSON_PRETTY_PRINT);
    mysqli_close($link);
    die;
}

<?php
include '../library/config.php';
check_injection();
error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, authorization");
header("Content-type: application/json");
$header = apache_request_headers();
//var_dump($header);
$Bearer = $header['authorization'];
if ($Bearer == '') {
    $Bearer = $header['Authorization'];
}
$ip = base64_encode($_SERVER['REMOTE_ADDR']);

if (($Bearer == 'Bearer GPMop8LQ06S0rZXcJyEH3wk8jVrINbHwn7tBq2' . $ip) && $ip <> '') {
    $nama = fixup($_GET['nama']);
    $filter_query = "";
    if ($nama <> "") {
        $filter_query = $filter_query . " and (a.nama like '%" . $nama . "%' 
        or a.id in (select id_parent from simfast_peta where nama like '%" . $nama . "%')
        or a.id in (select id from simfast_peta where nama like '%" . $nama . "%')
        or a.id_parent in (select id_parent from simfast_peta where nama like '%" . $nama . "%')
        or a.id_parent in (select id from simfast_peta where nama like '%" . $nama . "%'))";
    }
    $myArray = array();
    $sql = "select a.*,b.nama as namajenismenu 
        ,ifnull(c.nama,'Pilih Salah Satu') as namaasalpeta,ifnull(d.nama,'Tidak Ada') as namaapipeta
        ,ifnull(e.nama,'Pilih Salah Satu') as namatipepeta
        ,ifnull(f.nama,'Pilih Salah Satu') as namatipefill
        ,ifnull(g.nama,'Pilih Salah Satu') as namatipepattern
        ,ifnull(h.nama,'Pilih Salah Satu') as namastatustampil
        from simfast_peta a 
        left join simfast_master_jenis_menu_peta b on a.jenis=b.id
        left join simfast_master_asal_peta c on a.asal_peta=c.id
        left join simfast_master_api_peta d on a.api=d.id
        left join simfast_master_tipe_peta e on a.tipe_peta=e.id
        left join simfast_master_fill_polygon f on a.tipe_fill=f.id
        left join simfast_master_pattern_polygon g on a.tipe_pattern=g.id
        left join simfast_master_tampil h on a.tampilpublik=h.id
        where a.deleted='0' " . $filter_query . " order by a.level ASC, a.id_parent ASC, a.urut ASC";
    //echo $sql;
    //die;
    $result = mysqli_query($link, $sql);
    $num = mysqli_num_rows($result);
    if ($num > 0) {
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $myArray[] = (object)
                [
                    'id' => $row['id'],
                    'level' => $row['level'],
                    'id_parent' => $row['id_parent'],
                    'urut' => $row['urut'],
                    'jenis' => $row['jenis'],
                    'namajenismenu' => $row['namajenismenu'],
                    'nama' => $row['nama'],
                    'asal_peta' => $row['asal_peta'],
                    'namaasalpeta' => $row['namaasalpeta'],
                    'api' => $row['api'],
                    'namaapipeta' => $row['namaapipeta'],
                    'filenya_peta' => $row['filenya_peta'],
                    'tipe_peta' => $row['tipe_peta'],
                    'namatipepeta' => $row['namatipepeta'],
                    'fill' => $row['fill'],
                    'stroke' => $row['stroke'],
                    'fill_width' => $row['fill_width'],
                    'stroke_width' => $row['stroke_width'],
                    'dash_start' => $row['dash_start'],
                    'dash_end' => $row['dash_end'],
                    'filenya_ikon' => $row['filenya_ikon'],
                    'tipe_fill' => $row['tipe_fill'],
                    'namatipefill' => $row['namatipefill'],
                    'tipe_pattern' => $row['tipe_pattern'],
                    'namatipepattern' => $row['namatipepattern'],
                    'sudut' => $row['sudut'],
                    'urutindex' => $row['urutindex'],
                    'latitude' => $row['latitude'],
                    'longitude' => $row['longitude'],
                    'zoom' => $row['zoom'],
                    'radius' => $row['radius'],
                    'kolomfilter' => $row['kolomfilter'],
                    'textfilter' => $row['textfilter'],
                    'tampilpublik' => $row['tampilpublik'],
                    'namastatustampil' => $row['namastatustampil'],
                    'legenda' => base64_encode($row['legendapeta']),
                ];
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

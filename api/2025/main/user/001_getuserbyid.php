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
//echo $header;
//die;

$sqlcek = "select id as jumlah from tb_user where token='" . str_replace('"', '', $tempBearer[1]) . "' and deleted='0'";
//echo $sqlcek;
//die();
$resultsqlcek = mysqli_query($link, $sqlcek);
$numcek = mysqli_num_rows($resultsqlcek);
if ($numcek > 0) {
    $kduser = fixup($_GET['kduser']);
    $level = fixup($_GET['level']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($kduser <> '') && ($level <> '')) {
        $myArray = array();
        $sql = "select a.id,a.username,md5(a.id) as md5nya, a.nama, a.level as level,a.password
        , a.levelcode, a.levellabel, a.hakaksesevaluatorlke, a.hakaksesevaluatorlkecode, a.hakaksesevaluatorlkelabel
        , b.nama as namaleveluser,a.unor,e.nama_kategori as nama_unor, a.kategori_satker, c.nama_kategori as nama_kategori_satker
        , a.satker,a.kode_satker, d.nama_satker,d.status_piu,d.level_piu,c.level_piu as level_piu_balai
        , a.jabatan,a.pangkat,a.nip, a.notelp, a.alamat, a.email,a.jenis_piu, i.nama as namajenis_piu
        , a.verif,a.verifcode,a.veriflabel
        , a.verif2,a.verif2code,a.verif2label
        , a.verif3,a.verif3code,a.verif3label
        , null as evaluasi
        , ifnull(a.ikon,'3177440.png') as foto
        , (select x.tahun from master_pk x where x.id='1') as tahunpkawal
        , (select x.tahun from master_pk x where x.id='2') as tahunpkrevisi
        , (select x.tahun from master_pk x where x.id='3') as tahunpkakhir
        from tb_user a 
        left join master_level b
        on a.level=b.id
        left join master_unor e
        on a.unor=e.id
        left join master_kategori_satker c
        on a.kategori_satker=c.id
        left join master_satker d
        on a.satker = d.id
        left join master_level_piu i
        on a.jenis_piu=i.id
        where a.deleted='0' 
        and md5(a.id)='" . $kduser . "' and a.level='" . $level . "'
        order by a.id asc";
        //echo $sql;
        //die();
        $result = mysqli_query($link, $sql);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $myArray[] = $row;
            }
            $response         = [];
            $response['data'] =  $myArray;
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'content' => $response), JSON_PRETTY_PRINT);
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

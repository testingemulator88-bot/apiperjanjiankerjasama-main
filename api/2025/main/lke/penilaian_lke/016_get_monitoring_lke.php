<?php
include '../../../library/config.php';
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
$resultsqlcek   = mysqli_query($link, $sqlcek);
$numcek         = mysqli_num_rows($resultsqlcek);

if ($numcek > 0) {
    if (($Bearer <> '') && ($tempBearer[1] <> '')) {
        $id_user        = fixup($_GET['id_user']);
        $tahun          = fixup($_GET['tahun']);
        $level          = fixup($_GET['level']);
        $kategori_satker = fixup($_GET['kategori_satker']);

        if(strstr($level, '1')){
            $myArray        = array();
            $sql_user       = "SELECT a.id, a.kode_satker, a.kdbalai, replace(a.nama_satker, 'SATKER', '') as nama FROM master_satker as a 
                                INNER JOIN tb_user as b ON b.kode_satker = a.kode_satker and b.deleted='0'
                                WHERE (b.level in (4,6,8,9) or a.level_piu = 21 or a.level_piu = 6 or a.level_piu = 9 or a.level_piu = 31) 
                                and a.deleted='0' and a.kode_satker != 03694174
                                GROUP BY a.id ORDER BY a.kdbalai, a.kode_satker, a.id";

            $result_user    = mysqli_query($link, $sql_user);

            while ($row_user = mysqli_fetch_assoc($result_user)) {
                $id_user_lke = $row_user['kode_satker'];

                $sql_lke_diajukan           = "SELECT count(a.id_hasil_lke) as jumlah_status_diajukan FROM hasil_lke as a
                                                WHERE a.tahun = '". $tahun ."' AND a.kode_satker ='". $id_user_lke ."' AND a.status_lke = '1' AND a.is_active = '1'
                                                AND (
                                                        a.level in (4,6,8,9) 
                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                    )";
                $result_lke_diajukan        = mysqli_query($link, $sql_lke_diajukan);
                $num_result_lke_diajukan    = mysqli_num_rows($result_lke_diajukan);

                if($num_result_lke_diajukan > 0) {
                    $row_lke_diajukan       = mysqli_fetch_assoc($result_lke_diajukan);
                    $jumlah_status_diajukan = $row_lke_diajukan['jumlah_status_diajukan'];
                }
                else {
                    $jumlah_status_diajukan = 0;
                }

                $sql_lke_dievaluasi           = "SELECT count(a.id_hasil_lke) as jumlah_status_dievaluasi FROM hasil_lke as a
                                                WHERE a.tahun = '". $tahun ."' AND a.kode_satker ='". $id_user_lke ."' AND a.status_lke = '2' AND a.is_active = '1'
                                                AND (
                                                        a.level in (4,6,8,9) 
                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                    )";
                $result_lke_dievaluasi        = mysqli_query($link, $sql_lke_dievaluasi);
                $num_result_lke_dievaluasi    = mysqli_num_rows($result_lke_dievaluasi);

                if($num_result_lke_dievaluasi > 0) {
                    $row_lke_dievaluasi       = mysqli_fetch_assoc($result_lke_dievaluasi);
                    $jumlah_status_dievaluasi = $row_lke_dievaluasi['jumlah_status_dievaluasi'];
                }
                else {
                    $jumlah_status_dievaluasi = 0;
                }

                $sql_lke_direvisi           = "SELECT count(a.id_hasil_lke) as jumlah_status_direvisi FROM hasil_lke as a
                                                WHERE a.tahun = '". $tahun ."' AND a.kode_satker ='". $id_user_lke ."' AND a.status_lke = '3' AND a.is_active = '1'
                                                AND (
                                                        a.level in (4,6,8,9) 
                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                    )";
                $result_lke_direvisi        = mysqli_query($link, $sql_lke_direvisi);
                $num_result_lke_direvisi    = mysqli_num_rows($result_lke_direvisi);

                if($num_result_lke_direvisi > 0) {
                    $row_lke_direvisi       = mysqli_fetch_assoc($result_lke_direvisi);
                    $jumlah_status_direvisi = $row_lke_direvisi['jumlah_status_direvisi'];
                }
                else {
                    $jumlah_status_direvisi = 0;
                }

                $sql_lke_selesai           = "SELECT count(a.id_hasil_lke) as jumlah_status_selesai FROM hasil_lke as a
                                                WHERE a.tahun = '". $tahun ."' AND a.kode_satker ='". $id_user_lke ."' AND a.status_lke = '4' AND a.is_active = '1'
                                                AND (
                                                        a.level in (4,6,8,9) 
                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                    )";
                $result_lke_selesai        = mysqli_query($link, $sql_lke_selesai);
                $num_result_lke_selesai    = mysqli_num_rows($result_lke_selesai);

                if($num_result_lke_selesai > 0) {
                    $row_lke_selesai       = mysqli_fetch_assoc($result_lke_selesai);
                    $jumlah_status_selesai = $row_lke_selesai['jumlah_status_selesai'];
                }
                else {
                    $jumlah_status_selesai = 0;
                }

                $myArray[] = (object)[
                    'id_user'                   => $id_user_lke,
                    'nama'                      => $row_user['nama'],
                    'jumlah_status_diajukan'    => $jumlah_status_diajukan,
                    'jumlah_status_dievaluasi'  => $jumlah_status_dievaluasi,
                    'jumlah_status_direvisi'    => $jumlah_status_direvisi,
                    'jumlah_status_selesai'     => $jumlah_status_selesai
                ];
            }

            $response         = [];
            $response['data'] =  $myArray;
            
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
            die;
        }
        elseif(strstr($level, '2')){
            $myArray            = array();
            $sql_user_pilih     = "SELECT * FROM tb_user WHERE id = '" . $id_user . "' AND deleted = '0'";
            $result_user_pilih  = mysqli_query($link, $sql_user_pilih);
            $row_user_pilih     = mysqli_fetch_assoc($result_user_pilih);
            
            if(!is_null($row_user_pilih['hakaksesevaluatorlke']) && $row_user_pilih['hakaksesevaluatorlke'] != ''){
                $sql_user       = "SELECT a.id, a.kode_satker, a.kdbalai, replace(a.nama_satker, 'SATKER', '') as nama FROM master_satker as a 
                                    INNER JOIN tb_user as b ON b.kode_satker = a.kode_satker and b.deleted='0'
                                    WHERE b.kode_satker IN (" . $row_user_pilih['hakaksesevaluatorlke'] . ") AND a.kode_satker != 03694174
                                    AND (
                                            b.level in (4,6,8,9) 
                                            OR (b.level = 7 AND b.kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                            OR (b.level = 7 AND b.kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                            OR (b.level = 7 AND b.kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                            OR (b.level = 7 AND b.kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                        )
                                    AND a.deleted = '0' ORDER BY id ASC";
                $result_user    = mysqli_query($link, $sql_user);

                while ($row_user = mysqli_fetch_assoc($result_user)) {
                    $id_user_lke = $row_user['kode_satker'];

                    $sql_lke_diajukan           = "SELECT count(id_hasil_lke) as jumlah_status_diajukan FROM hasil_lke WHERE kode_satker = '" . $id_user_lke . "' AND tahun = '" . $tahun . "'
                                                    AND (
                                                            level in (4,6,8,9) 
                                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                        )
                                                    AND status_lke = '1' AND is_active = '1'";
                    $result_lke_diajukan        = mysqli_query($link, $sql_lke_diajukan);
                    $num_result_lke_diajukan    = mysqli_num_rows($result_lke_diajukan);

                    if($num_result_lke_diajukan > 0) {
                        $row_lke_diajukan       = mysqli_fetch_assoc($result_lke_diajukan);
                        $jumlah_status_diajukan = $row_lke_diajukan['jumlah_status_diajukan'];
                    }
                    else {
                        $jumlah_status_diajukan = 0;
                    }

                    $sql_lke_dievaluasi           = "SELECT count(id_hasil_lke) as jumlah_status_dievaluasi FROM hasil_lke WHERE kode_satker = '" . $id_user_lke . "' AND tahun = '" . $tahun . "'
                                                    AND (
                                                            level in (4,6,8,9) 
                                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                        )
                                                    AND status_lke = '2' AND is_active = '1'";
                    $result_lke_dievaluasi        = mysqli_query($link, $sql_lke_dievaluasi);
                    $num_result_lke_dievaluasi    = mysqli_num_rows($result_lke_dievaluasi);

                    if($num_result_lke_dievaluasi > 0) {
                        $row_lke_dievaluasi       = mysqli_fetch_assoc($result_lke_dievaluasi);
                        $jumlah_status_dievaluasi = $row_lke_dievaluasi['jumlah_status_dievaluasi'];
                    }
                    else {
                        $jumlah_status_dievaluasi = 0;
                    }

                    $sql_lke_direvisi           = "SELECT count(id_hasil_lke) as jumlah_status_direvisi FROM hasil_lke WHERE kode_satker = '" . $id_user_lke . "' AND tahun = '" . $tahun . "' 
                                                    AND (
                                                            level in (4,6,8,9) 
                                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                        )
                                                    AND status_lke = '3' AND is_active = '1'";
                    $result_lke_direvisi        = mysqli_query($link, $sql_lke_direvisi);
                    $num_result_lke_direvisi    = mysqli_num_rows($result_lke_direvisi);

                    if($num_result_lke_direvisi > 0) {
                        $row_lke_direvisi       = mysqli_fetch_assoc($result_lke_direvisi);
                        $jumlah_status_direvisi = $row_lke_direvisi['jumlah_status_direvisi'];
                    }
                    else {
                        $jumlah_status_direvisi = 0;
                    }

                    $sql_lke_selesai           = "SELECT count(id_hasil_lke) as jumlah_status_selesai FROM hasil_lke WHERE kode_satker = '" . $id_user_lke . "' AND tahun = '" . $tahun . "'
                                                 AND (
                                                        level in (4,6,8,9) 
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                    )
                                                AND status_lke = '4' AND is_active = '1'";
                    $result_lke_selesai        = mysqli_query($link, $sql_lke_selesai);
                    $num_result_lke_selesai    = mysqli_num_rows($result_lke_selesai);

                    if($num_result_lke_selesai > 0) {
                        $row_lke_selesai       = mysqli_fetch_assoc($result_lke_selesai);
                        $jumlah_status_selesai = $row_lke_selesai['jumlah_status_selesai'];
                    }
                    else {
                        $jumlah_status_selesai = 0;
                    }

                    $myArray[] = (object)[
                        'id_user'                   => $id_user_lke,
                        'nama'                      => $row_user['nama'],
                        'jumlah_status_diajukan'    => $jumlah_status_diajukan,
                        'jumlah_status_dievaluasi'  => $jumlah_status_dievaluasi,
                        'jumlah_status_direvisi'    => $jumlah_status_direvisi,
                        'jumlah_status_selesai'     => $jumlah_status_selesai
                    ];
                }

                $response         = [];
                $response['data'] =  $myArray;
                
                mysqli_close($link);
                echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
                die;
            }
            else{
                $myArray        = array();
                $sql_user       = "SELECT a.id, a.kode_satker, a.kdbalai, replace(a.nama_satker, 'SATKER', '') as nama FROM master_satker as a 
                                    INNER JOIN tb_user as b ON b.kode_satker = a.kode_satker and b.deleted='0'
                                    WHERE a.kode_satker != 03694174
                                    AND (
                                            b.level in (4,6,8,9) 
                                            OR (b.level = 7 AND b.kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                            OR (b.level = 7 AND b.kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                            OR (b.level = 7 AND b.kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                            OR (b.level = 7 AND b.kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                        )
                                    AND a.deleted = '0' 
                                    GROUP BY a.id
                                    ORDER BY id ASC";
                $result_user    = mysqli_query($link, $sql_user);

                while ($row_user = mysqli_fetch_assoc($result_user)) {
                    $id_user_lke = $row_user['kode_satker'];

                    $sql_lke_diajukan           = "SELECT count(id_hasil_lke) as jumlah_status_diajukan FROM hasil_lke WHERE kode_satker = '" . $id_user_lke . "' AND tahun = '" . $tahun . "' 
                                                    AND (
                                                        level in (4,6,8,9) 
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                    ) 
                                                    AND status_lke = '1' AND is_active = '1'";
                    $result_lke_diajukan        = mysqli_query($link, $sql_lke_diajukan);
                    $num_result_lke_diajukan    = mysqli_num_rows($result_lke_diajukan);

                    if($num_result_lke_diajukan > 0) {
                        $row_lke_diajukan       = mysqli_fetch_assoc($result_lke_diajukan);
                        $jumlah_status_diajukan = $row_lke_diajukan['jumlah_status_diajukan'];
                    }
                    else {
                        $jumlah_status_diajukan = 0;
                    }

                    $sql_lke_dievaluasi           = "SELECT count(id_hasil_lke) as jumlah_status_dievaluasi FROM hasil_lke WHERE kode_satker = '" . $id_user_lke . "' AND tahun = '" . $tahun . "' 
                                                    AND (
                                                        level in (4,6,8,9) 
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                    ) 
                                                    AND status_lke = '2' AND is_active = '1'";
                    $result_lke_dievaluasi        = mysqli_query($link, $sql_lke_dievaluasi);
                    $num_result_lke_dievaluasi    = mysqli_num_rows($result_lke_dievaluasi);

                    if($num_result_lke_dievaluasi > 0) {
                        $row_lke_dievaluasi       = mysqli_fetch_assoc($result_lke_dievaluasi);
                        $jumlah_status_dievaluasi = $row_lke_dievaluasi['jumlah_status_dievaluasi'];
                    }
                    else {
                        $jumlah_status_dievaluasi = 0;
                    }

                    $sql_lke_direvisi           = "SELECT count(id_hasil_lke) as jumlah_status_direvisi FROM hasil_lke WHERE kode_satker = '" . $id_user_lke . "' AND tahun = '" . $tahun . "' 
                                                    AND (
                                                        level in (4,6,8,9) 
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                    ) 
                                                    AND status_lke = '3' AND is_active = '1'";
                    $result_lke_direvisi        = mysqli_query($link, $sql_lke_direvisi);
                    $num_result_lke_direvisi    = mysqli_num_rows($result_lke_direvisi);

                    if($num_result_lke_direvisi > 0) {
                        $row_lke_direvisi       = mysqli_fetch_assoc($result_lke_direvisi);
                        $jumlah_status_direvisi = $row_lke_direvisi['jumlah_status_direvisi'];
                    }
                    else {
                        $jumlah_status_direvisi = 0;
                    }

                    $sql_lke_selesai           = "SELECT count(id_hasil_lke) as jumlah_status_selesai FROM hasil_lke WHERE kode_satker = '" . $id_user_lke . "' AND tahun = '" . $tahun . "' 
                                                    AND (
                                                        level in (4,6,8,9) 
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                        OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                    ) 
                                                    AND status_lke = '4' AND is_active = '1'";
                    $result_lke_selesai        = mysqli_query($link, $sql_lke_selesai);
                    $num_result_lke_selesai    = mysqli_num_rows($result_lke_selesai);

                    if($num_result_lke_selesai > 0) {
                        $row_lke_selesai       = mysqli_fetch_assoc($result_lke_selesai);
                        $jumlah_status_selesai = $row_lke_selesai['jumlah_status_selesai'];
                    }
                    else {
                        $jumlah_status_selesai = 0;
                    }

                    $myArray[] = (object)[
                        'id_user'                   => $id_user_lke,
                        'nama'                      => $row_user['nama'],
                        'jumlah_status_diajukan'    => $jumlah_status_diajukan,
                        'jumlah_status_dievaluasi'  => $jumlah_status_dievaluasi,
                        'jumlah_status_direvisi'    => $jumlah_status_direvisi,
                        'jumlah_status_selesai'     => $jumlah_status_selesai
                    ];
                }

                $response         = [];
                $response['data'] =  $myArray;
                
                mysqli_close($link);
                echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
                die;
            }
        }
        elseif(strstr($level, '6') || strstr($level, '5')){
            $myArray        = array();
            $sql_user       = "SELECT kode_satker, nama_satker FROM master_satker WHERE kdbalai = '" . $kategori_satker . "' AND deleted = '0' ORDER BY kode_satker ASC";
            $result_user    = mysqli_query($link, $sql_user);

            while ($row_user = mysqli_fetch_assoc($result_user)) {
                $id_user_lke = $row_user['kode_satker'];
                $sql_lke_diajukan           = "SELECT count(id_hasil_lke) as jumlah_status_diajukan FROM hasil_lke WHERE kode_satker = '" . $id_user_lke . "' AND tahun = '" . $tahun . "' AND level in (7) AND status_lke = '1' AND is_active = '1'";
                $result_lke_diajukan        = mysqli_query($link, $sql_lke_diajukan);
                $num_result_lke_diajukan    = mysqli_num_rows($result_lke_diajukan);

                if($num_result_lke_diajukan > 0) {
                    $row_lke_diajukan       = mysqli_fetch_assoc($result_lke_diajukan);
                    $jumlah_status_diajukan = $row_lke_diajukan['jumlah_status_diajukan'];
                }
                else {
                    $jumlah_status_diajukan = 0;
                }

                $sql_lke_dievaluasi           = "SELECT count(id_hasil_lke) as jumlah_status_dievaluasi FROM hasil_lke WHERE kode_satker = '" . $id_user_lke . "' AND tahun = '" . $tahun . "' AND level in (7) AND status_lke = '2' AND is_active = '1'";
                $result_lke_dievaluasi        = mysqli_query($link, $sql_lke_dievaluasi);
                $num_result_lke_dievaluasi    = mysqli_num_rows($result_lke_dievaluasi);

                if($num_result_lke_dievaluasi > 0) {
                    $row_lke_dievaluasi       = mysqli_fetch_assoc($result_lke_dievaluasi);
                    $jumlah_status_dievaluasi = $row_lke_dievaluasi['jumlah_status_dievaluasi'];
                }
                else {
                    $jumlah_status_dievaluasi = 0;
                }

                $sql_lke_direvisi           = "SELECT count(id_hasil_lke) as jumlah_status_direvisi FROM hasil_lke WHERE kode_satker = '" . $id_user_lke . "' AND tahun = '" . $tahun . "' AND level in (7) AND status_lke = '3' AND is_active = '1'";
                $result_lke_direvisi        = mysqli_query($link, $sql_lke_direvisi);
                $num_result_lke_direvisi    = mysqli_num_rows($result_lke_direvisi);

                if($num_result_lke_direvisi > 0) {
                    $row_lke_direvisi       = mysqli_fetch_assoc($result_lke_direvisi);
                    $jumlah_status_direvisi = $row_lke_direvisi['jumlah_status_direvisi'];
                }
                else {
                    $jumlah_status_direvisi = 0;
                }

                $sql_lke_selesai           = "SELECT count(id_hasil_lke) as jumlah_status_selesai FROM hasil_lke WHERE kode_satker = '" . $id_user_lke . "' AND tahun = '" . $tahun . "' AND level in (7) AND status_lke = '4' AND is_active = '1'";
                $result_lke_selesai        = mysqli_query($link, $sql_lke_selesai);
                $num_result_lke_selesai    = mysqli_num_rows($result_lke_selesai);

                if($num_result_lke_selesai > 0) {
                    $row_lke_selesai       = mysqli_fetch_assoc($result_lke_selesai);
                    $jumlah_status_selesai = $row_lke_selesai['jumlah_status_selesai'];
                }
                else {
                    $jumlah_status_selesai = 0;
                }

                $myArray[] = (object)[
                    'id_user'                   => $id_user_lke,
                    'nama'                      => $row_user['nama_satker'],
                    'jumlah_status_diajukan'    => $jumlah_status_diajukan,
                    'jumlah_status_dievaluasi'  => $jumlah_status_dievaluasi,
                    'jumlah_status_direvisi'    => $jumlah_status_direvisi,
                    'jumlah_status_selesai'     => $jumlah_status_selesai
                ];
            }

            $response         = [];
            $response['data'] =  $myArray;
            
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
            die;
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

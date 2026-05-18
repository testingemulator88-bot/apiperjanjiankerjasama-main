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

        if(strstr($level, '7')){
            $myArray        = array();
            $sql_user       = "SELECT * FROM tb_user WHERE kode_satker ='".$id_user."' AND level in (7) AND deleted = '0' ORDER BY id ASC LIMIT 1";
            $result_user    = mysqli_query($link, $sql_user);
            $result_num_row = mysqli_num_rows($result_user);

            if($result_num_row > 0) {
                $row_user   = mysqli_fetch_assoc($result_user);
                $user_id    = $row_user['kode_satker'];

                $sql_lke_diajukan           = "SELECT count(a.id_hasil_lke) as jumlah_status_diajukan FROM hasil_lke as a
                                                WHERE a.tahun = '". $tahun ."' AND a.kode_satker ='". $id_user ."' AND a.status_lke = '1' AND a.is_active = '1'
                                                AND a.level in (7)
                                                ";
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
                                                WHERE a.tahun = '". $tahun ."' AND a.kode_satker ='". $id_user ."' AND a.status_lke = '2' AND a.is_active = '1'
                                                AND a.level in (7)
                                                ";
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
                                                WHERE a.tahun = '". $tahun ."' AND a.kode_satker ='". $id_user ."' AND a.status_lke = '3' AND a.is_active = '1'
                                                AND a.level in (7)
                                                ";
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
                                                WHERE a.tahun = '". $tahun ."' AND a.kode_satker ='". $id_user ."' AND a.status_lke = '4' AND a.is_active = '1'
                                                AND a.level in (7)
                                                ";
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
                    'total_lke_diajukan'    => $jumlah_status_diajukan,
                    'total_lke_dievaluasi'  => $jumlah_status_dievaluasi,
                    'total_lke_revisi'      => $jumlah_status_direvisi,
                    'total_lke_diterima'    => $jumlah_status_selesai
                ];

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

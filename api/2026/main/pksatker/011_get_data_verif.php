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

$sqlcek = "select id as jumlah from tb_user where token='" . str_replace('"', '', $tempBearer[1]) . "' and deleted='0'";
//echo $sqlcek;
//die;
$resultsqlcek = mysqli_query($link, $sqlcek);
$numcek = mysqli_num_rows($resultsqlcek);
$filter_query = "";
if ($numcek > 0) {
    $id_pelaksana = fixup($_GET['pelaksana']);
    $kode_satker = fixup($_GET['kode_satker']);
    $kode_tahun = fixup($_GET['tahun']);
    $tahun = date('Y');
    if (($Bearer <> '') && ($tempBearer[1] <> '')) {
        $sqlpkaktif = "select id from master_pk where deleted = '0' and is_active = '1'";
        $resultpkaktif  = mysqli_query($link, $sqlpkaktif);
        while ($rowpkaktif  = mysqli_fetch_assoc($resultpkaktif )) {
            $pk_aktif = $rowpkaktif ['id'];
        }
        $pk_aktif = '2';
        $myArray = array();
        // IKSK
        $sqlnya = "select max(id) as idnya
        from tb_data_pk_verifikasi a 
        where a.deleted='0' and a.id_pelaksana = '" . $id_pelaksana . "'
        and a.kode_pelaksana = '" . $kode_satker . "' and a.kode_tahun = '" . $kode_tahun . "'
        and a.jenis_pk = '" . $pk_aktif . "'
        order by a.id ASC ";
        $resultnya = mysqli_query($link, $sqlnya);
        while ($rownya = mysqli_fetch_assoc($resultnya)) {
            $idnya = $rownya['idnya'];
            if ($idnya <> "") {
                $filter_query = $filter_query . " and id='" . $idnya . "'";
            }
        }

        $sql = "select a.*
        from tb_data_pk_verifikasi a 
        where a.deleted='0' and a.id_pelaksana = '" . $id_pelaksana . "'
        and a.kode_pelaksana = '" . $kode_satker . "' and a.kode_tahun = '" . $kode_tahun . "'
        and a.jenis_pk = '" . $pk_aktif . "'
        " . $filter_query . "
        order by a.id ASC ";

        //echo $sql;
        //die();
        $result = mysqli_query($link, $sql);
        $num = mysqli_num_rows($result);
        if ($num > 0) {
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $sqlcekjumlhaverif = "select id_parent from master_level_piu where deleted='0'
                    and id = '" . $id_pelaksana . "'";
                    $resultjumlhaverif = mysqli_query($link, $sqlcekjumlhaverif);
                    while ($rowjumlhaverif = mysqli_fetch_assoc($resultjumlhaverif)) {
                        $acuanjumlahverifikasi = (float) $rowjumlhaverif['id_parent'];
                        //echo $acuanjumlahverifikasi;
                        //die;
                    }
                    if ($row['hasil_verif'] == '1') {
                        //echo (float) $row['verifikasi_ke']."_".$acuanjumlahverifikasi;
                        //die;
                        if ((float) $row['verifikasi_ke'] < $acuanjumlahverifikasi) {
                            $akan_verif_ke = (float) $row['verifikasi_ke'] + 1;
                            $myArray[] = (object)
                            [
                                'id' => null,
                                'tahun' => $tahun,
                                'level_verif' => '',
                                'id_pelaksana' => $id_pelaksana,
                                'kode_satker' => $kode_satker,
                                'jumlah_verif' => (float) $row['verifikasi_ke'],
                                'hasil_verif' => '',
                                'tanggal_batas' => '',
                                'catatan' => '',
                                'akan_verif_ke' => $akan_verif_ke,
                            ];
                        } else {
                            $akan_verif_ke = (float) $row['verifikasi_ke'];
                            $myArray[] = (object)
                            [
                                'id' => (float) $row['id'],
                                'tahun' => $row['tahun'],
                                'level_verif' => $row['level_verif'],
                                'id_pelaksana' => $row['id_pelaksana'],
                                'kode_satker' => $row['kode_pelaksana'],
                                'jumlah_verif' => $row['verifikasi_ke'],
                                'hasil_verif' => $row['hasil_verif'],
                                'tanggal_batas' => $row['tanggal_batas'],
                                'catatan' => base64_encode($row['catatan']),
                                'akan_verif_ke' => $akan_verif_ke,
                            ];
                        }
                    } else {
                        $akan_verif_ke = (float) $row['verifikasi_ke'];
                        $myArray[] = (object)
                        [
                            'id' => (float) $row['id'],
                            'tahun' => $row['tahun'],
                            'level_verif' => $row['level_verif'],
                            'id_pelaksana' => $row['id_pelaksana'],
                            'kode_satker' => $row['kode_pelaksana'],
                            'jumlah_verif' => $row['verifikasi_ke'],
                            'hasil_verif' => $row['hasil_verif'],
                            'tanggal_batas' => $row['tanggal_batas'],
                            'catatan' => base64_encode($row['catatan']),
                            'akan_verif_ke' => $akan_verif_ke,
                        ];
                    }
                    //echo $akan_verif_ke;
                    //die;

                }
                $response         = [];
                $response['data'] =  $myArray;
                mysqli_close($link);
                echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
                die;
            }
        } else {
            $myArray[] = (object)
            [
                'id' => null,
                'tahun' => $tahun,
                'level_verif' => '',
                'id_pelaksana' => $id_pelaksana,
                'kode_satker' => $kode_satker,
                'jumlah_verif' => 0,
                'hasil_verif' => '',
                'tanggal_batas' => '',
                'catatan' => '',
                'akan_verif_ke' => 1,
            ];
            $response         = [];
            $response['data'] =  $myArray;
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
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

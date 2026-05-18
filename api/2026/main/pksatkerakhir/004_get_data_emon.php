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
if ($numcek > 0) {
    if (($Bearer <> '') && ($tempBearer[1] <> '')) {
        $filterquery = "";
        $filterquery2 = "";
        $kode_satkeremon = "";
        $kode_satkeremon2 = "";
        $kode_satker = fixup($_GET['kodesatker']);
        $sqlsatkeremon = "select kode_satker_old_pendek,kode_satker_pendek 
        from master_satker where kode_satker = '" . $kode_satker . "'";
        //echo $sqlsatkeremon;
        //die;
        $resultsatkeremon = mysqli_query($link, $sqlsatkeremon);
        while ($rowsatkeremon = mysqli_fetch_assoc($resultsatkeremon)) {
            $kode_satkeremon = $rowsatkeremon['kode_satker_old_pendek'];
            $kode_satkeremon2 = $rowsatkeremon['kode_satker_pendek'];
        }
        $tahun = fixup($_GET['tahun']);
        $id = fixup($_GET['id']);
        $kodekegiatan = fixup($_GET['kodekegiatan']);
        if ($kode_satkeremon <> '') {
            $filterquery = $filterquery . " and (a.kdsatker = '" . $kode_satkeremon . "' or a.kdsatker = '" . $kode_satkeremon2 . "')";
            $filterquery2 = $filterquery2 . " and (a.kdsatker = '" . $kode_satkeremon . "' or a.kdsatker = '" . $kode_satkeremon2 . "')";
        }
        if ($kodekegiatan <> '') {
            //$filterquery = $filterquery . " and a.kdgiat = '" . $kodekegiatan . "'";
        }

        $sqlcekemonterpilih = "select GROUP_CONCAT(kode_emon) as cekemon from tb_data_pk_emon_akhir where deleted='0' and id_pk='" . $id . "'";
        $resultemonterpilih = mysqli_query($link, $sqlcekemonterpilih);
        $numemonterpilih = mysqli_num_rows($resultemonterpilih);
        if ($numemonterpilih == 0) {
            $cekemon = "xxxxxxxxxx";
        } else {
            while ($rowemonterpilih = mysqli_fetch_assoc($resultemonterpilih)) {
                $cekemon = $rowemonterpilih['cekemon'];
            }
        }
        //echo $sqlcekemonterpilih;
        //die;

        $myArray = array();
        $myArraydetailemon = array();
        $sql = "select a.idnya,a.kdsatker,a.kdprogram,a.kdgiat
        ,a.kdoutput,a.kdsoutput,a.kdkmpnen,a.kdskmpnen,a.kode,a.nmpaket,a.vol,a.sat
        ,a.pgrupiah,a.pgpln,a.pgsbsn,a.rtot,a.ufis,a.pfis,a.pg57,a.tanggaldata
        from paket_pk_akhir a where a.nmpaket <>''
        " . $filterquery . " and YEAR(a.tanggaldata) = '" . $tahun . "'
        and a.tanggaldata = (select MAX(tanggaldata) from paket_pk_akhir where YEAR(tanggaldata)= '" . $tahun . "')
        order by a.kode";
        //echo $sql;
        //die();
        $result = mysqli_query($link, $sql);
        $num = mysqli_num_rows($result);
        if ($num > 0) {
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $pagu = 0;
                    if ((float) $row['pgrupiah'] > 0) {
                        $sumber_dana = "RPM";
                        $pagu = (float) $row['pgrupiah'];
                    } else if ((float) $row['pgpln'] > 0) {
                        $sumber_dana = "PHLN";
                        $pagu = (float) $row['pgpln'];
                    } else {
                        $sumber_dana = "SBSN";
                        $pagu = (float) $row['pgsbsn'];
                    }

                    $persenfisik = 0;
                    if ((float) $row['pfis'] == 0) {
                        $persenfisik = 0;
                    } else {
                        $persenfisik = (((float) $row['ufis'] / 1000) / ((float) $row['pfis'] / 1000)) * 100;
                    }

                    $keu_efisiensi = 0;
                    $fisik_efisiensi = 0;

                    $pagu_efisiensi = (float) $row['pg57'];
                    if ($pagu_efisiensi == 0) {
                        $pagu_efisiensi = $pagu;
                    }

                    if ($pagu_efisiensi == 0) {
                        $keu_efisiensi = 0;
                        $fisik_efisiensi = 0;
                    } else {
                        $keu_efisiensi = (((float) $row['rtot'] / 1000) / ($pagu_efisiensi / 1000)) * 100;
                        $fisik_efisiensi = (((float) $row['ufis'] / 1000) / ($pagu_efisiensi / 1000)) * 100;
                    }
                    //echo $pagu."<br>";
                    if (strstr($cekemon, $row['kode'])) {
                        $checked = true;
                        //echo $pagu."<br>";
                        array_push($myArraydetailemon, $row['kode'] . '_' . $pagu . '_' . (float) $row['rtot'] . '_' . number_format((((float) $row['rtot'] / 1000) / ($pagu / 1000)) * 100, 2, ",", ".") . '_' . number_format($persenfisik, 2, ",", ".") . '_' . $row['tanggaldata']);
                    } else {
                        $checked = false;
                    }
                    //print_r($myArraydetailemon);
                    //die;
                    $myArray[] = (object)
                    [
                        'idnya' => (float) $row['idnya'],
                        'kode' => $row['kode'],
                        'nmpaket' => $row['nmpaket'],
                        'pgrupiah' => $pagu,
                        'realisasi' => (float) $row['rtot'],
                        'persenkeu' => number_format((((float) $row['rtot'] / 1000) / ($pagu / 1000)) * 100, 2, ",", "."),
                        'persenfisik' => number_format($persenfisik, 2, ",", "."),
                        'vol' => $row['vol'],
                        'sat' => $row['sat'],
                        'tanggaldata' => $row['tanggaldata'],
                        'gabung' => $row['kode'] . '_' . $pagu . '_' . (float) $row['rtot'] . '_' . number_format((((float) $row['rtot'] / 1000) / ($pagu / 1000)) * 100, 2, ",", ".") . '_' . number_format($persenfisik, 2, ",", ".") . '_' . $row['tanggaldata'],
                        'checked' => $checked,
                    ];
                }
                //die;
                $response         = [];
                $response['data'] =  $myArray;
                $response['detail'] =  $myArraydetailemon;
                mysqli_close($link);
                echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
                die;
            }
        } else {
            $sql2 = "select a.idnya,a.kdsatker,a.kdprogram,a.kdgiat
            ,a.kdoutput,a.kdsoutput,a.kdkmpnen,a.kdskmpnen,a.kode,a.nmpaket,a.vol,a.sat
            ,a.pgrupiah,a.pgpln,a.pgsbsn,a.rtot,a.ufis,a.pfis,a.pg57,a.tanggaldata
            from paket_pk_akhir a where a.nmpaket <>''
            " . $filterquery2 . " and YEAR(a.tanggaldata) = '" . $tahun . "'
            and a.tanggaldata = (select MAX(tanggaldata) from paket_pk_akhir where YEAR(tanggaldata)= '" . $tahun . "')
            order by a.kode";
            //echo $sql;
            //die();
            $result2 = mysqli_query($link, $sql2);
            $num2 = mysqli_num_rows($result2);
            if ($num2 > 0) {
                if ($result2) {
                    while ($row2 = mysqli_fetch_assoc($result2)) {
                        $pagu = 0;
                        if ((float) $row2['pgrupiah'] > 0) {
                            $sumber_dana = "RPM";
                            $pagu = (float) $row2['pgrupiah'];
                        } else if ((float) $row2['pgpln'] > 0) {
                            $sumber_dana = "PHLN";
                            $pagu = (float) $row2['pgpln'];
                        } else {
                            $sumber_dana = "SBSN";
                            $pagu = (float) $row2['pgsbsn'];
                        }

                        $persenfisik = 0;
                        if ((float) $row2['pfis'] == 0) {
                            $persenfisik = 0;
                        } else {
                            $persenfisik = (((float) $row2['ufis'] / 1000) / ((float) $row2['pfis'] / 1000)) * 100;
                        }

                        $keu_efisiensi = 0;
                        $fisik_efisiensi = 0;

                        $pagu_efisiensi = (float) $row2['pg57'];
                        if ($pagu_efisiensi == 0) {
                            $pagu_efisiensi = $pagu;
                        }

                        if ($pagu_efisiensi == 0) {
                            $keu_efisiensi = 0;
                            $fisik_efisiensi = 0;
                        } else {
                            $keu_efisiensi = (((float) $row2['rtot'] / 1000) / ($pagu_efisiensi / 1000)) * 100;
                            $fisik_efisiensi = (((float) $row2['ufis'] / 1000) / ($pagu_efisiensi / 1000)) * 100;
                        }

                        if (strstr($cekemon, $row2['kode'])) {
                            $checked = true;
                            array_push($myArraydetailemon, $row2['kode'] . '_' . $pagu . '_' . (float) $row2['rtot'] . '_' . number_format((((float) $row2['rtot'] / 1000) / ($pagu / 1000)) * 100, 2, ",", ".") . '_' . number_format($persenfisik, 2, ",", ".") . '_' . $row2['tanggaldata']);
                        } else {
                            $checked = false;
                        }

                        $myArray[] = (object)
                        [
                            'idnya' => (float) $row2['idnya'],
                            'kode' => $row2['kode'],
                            'nmpaket' => $row2['nmpaket'],
                            'pgrupiah' => $pagu,
                            'realisasi' => (float) $row2['rtot'],
                            'persenkeu' => number_format((((float) $row2['rtot'] / 1000) / ($pagu / 1000)) * 100, 2, ",", "."),
                            'persenfisik' => number_format($persenfisik, 2, ",", "."),
                            'vol' => $row2['vol'],
                            'sat' => $row2['sat'],
                            'tanggaldata' => $row2['tanggaldata'],
                            'gabung' => $row2['kode'] . '_' . $pagu . '_' . (float) $row2['rtot'] . '_' . number_format((((float) $row2['rtot'] / 1000) / ($pagu / 1000)) * 100, 2, ",", ".") . '_' . number_format($persenfisik, 2, ",", ".") . '_' . $row2['tanggaldata'],
                            'checked' => $checked,
                        ];
                    }
                    $response         = [];
                    $response['data'] =  $myArray;
                    $response['detail'] =  $myArraydetailemon;
                    mysqli_close($link);
                    echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
                    die;
                }
            } else {
                mysqli_close($link);
                echo json_encode(array('response' => 'success', 'message' => 'data kosong'), JSON_PRETTY_PRINT);
                die;
            }
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

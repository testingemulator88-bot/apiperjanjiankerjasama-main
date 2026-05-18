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
    $filterquery = "";


    $tahun = fixup($_GET['tahun']);
    $phln = fixup($_GET['phln']);
    $filterquery = $filterquery . " and a.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "') and YEAR(a.tanggaldata) ='" . $tahun . "'";

    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($tahun <> '')) {
        $pgrupiahgrandtotal = 0;
        $pgplngrandtotal = 0;
        $pgsbsngrandtotal = 0;
        $rtotgrandtotal = 0;
        $ufisgrandtotal = 0;
        $pfisgrandtotal = 0;
        $pg57grandtotal = 0;
        $pagurpmgrandtotal = 0;
        $rrmpgrandtotal = 0;
        $rplngrandtotal = 0;
        $rr_sbsngrandtotal = 0;
        $rtotgrandtotal = 0;
        $persenkeugrandtotal = 0;
        $persenfisikgrandtotal = 0;
        $ufisgrandtotal = 0;

        $myArray = array();
        $myArraytotal = array();
        $tanggalemon = date("Y-m-d H:i:s");
        $sqltanggalemon = "select MAX(nama) as tanggalemon from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "'";
        //echo $sqltanggalemon;
        //die;

        $resulttanggalemon = mysqli_query($link, $sqltanggalemon);
        while ($rowtanggalemon = mysqli_fetch_assoc($resulttanggalemon)) {
            $tanggalemon = $rowtanggalemon['tanggalemon'];
        }

        $pgrupiah = 0;
        $pgpln = 0;
        $pgsbsn = 0;
        $rtot = 0;
        $ufis = 0;
        $pfis = 0;
        $pg57 = 0;
        $pagurpm = 0;
        $rrmp = 0;
        $rpln = 0;
        $rr_sbsn = 0;
        $rtot = 0;
        $persenkeu = 0;
        $persenfisik = 0;
        $ufis = 0;
        $urut = 1;
        $sql = "select a.id
        ,a.kdunor,a.kdbalai,a.kdbalai_pendek,a.kdbalai_old,a.kdbalai_old_pendek,a.nama_kategori as nama,a.alamat
        ,a.latitude,a.longitude,a.urut from master_kategori_satker a 
        where a.deleted='0' 
        order by a.urut";
        //echo $sql;
        //die();
        $result = mysqli_query($link, $sql);
        $num = mysqli_num_rows($result);
        if ($num > 0) {
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $kode_satkeremon = "";
                    $kode_satkeremon2 = "";
                    $sqlsatkeremon = "select GROUP_CONCAT(kode_satker_old_pendek) as kode_satker_old_pendek
                    ,GROUP_CONCAT(kode_satker_pendek) as kode_satker_pendek 
                    from master_satker where kdbalai = '" . $row['id'] . "' and kode_satker_old_pendek <> ''
                    and kode_satker_pendek <> '' and deleted='0'";
                    //echo $sqlsatkeremon;
                    //die;
                    $resultsatkeremon = mysqli_query($link, $sqlsatkeremon);
                    $x = 1;
                    while ($rowsatkeremon = mysqli_fetch_assoc($resultsatkeremon)) {
                        $kode_satkeremon = $rowsatkeremon['kode_satker_old_pendek'];
                        $kode_satkeremon2 = $rowsatkeremon['kode_satker_pendek'];
                        $x++;
                    }

                    if ($kode_satkeremon <> '' && $kode_satkeremon2 <> '') {
                        $sqldata = "select sum(a.pgrupiah) as pgrupiah,sum(a.pgpln) as pgpln,sum(a.pgsbsn) as pgsbsn
                        ,sum(a.rtot) as rtot,sum(a.ufis) as ufis,sum(a.pfis) as pfis,sum(a.pg57) as pg57
                        ,sum(a.rrmp - a.rr_sbsn) as rrmp,sum(a.rpln) as rpln,sum(a.rr_sbsn) as rr_sbsn
                        ,sum(a.blokir) as blokir
                        from paket a 
                        where a.nmpaket <> '' and (a.kdsatker in (" . $kode_satkeremon . ") 
                        or a.kdsatker in (" . $kode_satkeremon2 . ")) " . $filterquery . "
                        group by a.kdsatker";

                        //echo $sqldata."<br>";
                        //die;
                        $resultdata = mysqli_query($link, $sqldata);
                        $pgrupiahtotal = 0;
                        $pgplntotal = 0;
                        $pgsbsntotal = 0;
                        $rtottotal = 0;
                        $ufistotal = 0;
                        $pfistotal = 0;
                        $pg57total = 0;
                        $pagurpmtotal = 0;
                        $rrmptotal = 0;
                        $rplntotal = 0;
                        $rr_sbsntotal = 0;
                        $rtottotal = 0;
                        $persenkeutotal = 0;
                        $persenfisiktotal = 0;
                        $ufistotal = 0;
                        while ($rowdata = mysqli_fetch_assoc($resultdata)) {
                            $pgrupiah = (float) $rowdata['pgrupiah'] / 1000;
                            if ((float) $rowdata['pg57'] == 0) {
                                $pagurpm =  ((float) $rowdata['pgrupiah'] - (float) $rowdata['blokir']) / 1000;
                            } else {
                                $pagurpm =  ((float) $rowdata['pg57']) / 1000;
                            }

                            $pgpln =  (float) $rowdata['pgpln'] / 1000;
                            $pgsbsn =  (float) $rowdata['pgsbsn'] / 1000;
                            $rtot =  (float) $rowdata['rtot'] / 1000;
                            $ufis =  (float) $rowdata['ufis'] / 1000;
                            $pfis =  (float) $rowdata['pfis'] / 1000;
                            $pg57 =  (float) $rowdata['pg57'] / 1000;
                            $rrmp =  (float) $rowdata['rrmp'] / 1000;
                            $rpln =  (float) $rowdata['rpln'] / 1000;
                            $rr_sbsn =  (float) $rowdata['rr_sbsn'] / 1000;
                            $rtot =  (float) $rowdata['rtot'] / 1000;
                            $ufis =  (float) $rowdata['ufis'] / 1000;

                            $pagu = $pg57;
                            if ($pagu == 0) {
                                $pagu = $pagurpm + $pgpln;
                            } else {
                                $pagu = $pg57 + $pgpln;
                            }

                            if ($pagu == 0) {
                                $persenkeu = 0;
                                $persenfisik = 0;
                            } else {
                                $persenkeu = ($rtot / $pagu) * 100;
                                $persenfisik = ($ufis / $pagu) * 100;
                            }

                            $pgrupiahtotal =  $pgrupiahtotal + $pgrupiah;
                            $pagurpmtotal =  $pagurpmtotal + $pagurpm;
                            $pgplntotal =  $pgplntotal + $pgpln;
                            $pgsbsntotal =  $pgsbsntotal + $pgsbsn;
                            $ufistotal =  $ufistotal + $ufis;

                            $rrmptotal =  $rrmptotal + $rrmp;
                            $rplntotal =  $rplntotal + $rpln;
                            $rr_sbsntotal =  $rr_sbsntotal + $rr_sbsn;

                            if ($phln == 'true') {
                                $persenkeu = ((($rrmptotal + $rplntotal + $rr_sbsntotal) / 1000) / (($pagurpmtotal + $pgplntotal) / 1000)) * 100;
                                $persenfisik = (($ufistotal / 1000) / (($pagurpmtotal + $pgplntotal) / 1000)) * 100;
                            } else {
                                $persenkeu = ((($rrmptotal + $rplntotal + $rr_sbsntotal) / 1000) / (($pagurpmtotal) / 1000)) * 100;
                                $persenfisik = (($ufistotal / 1000) / (($pagurpmtotal) / 1000)) * 100;
                            }
                        }


                        $pgrupiahgrandtotal =  $pgrupiahgrandtotal + $pgrupiahtotal;
                        $pagurpmgrandtotal =  $pagurpmgrandtotal + $pagurpmtotal;
                        $pgplngrandtotal =  $pgplngrandtotal + $pgplntotal;
                        $pgsbsngrandtotal =  $pgsbsngrandtotal + $pgsbsntotal;
                        $ufisgrandtotal =  $ufisgrandtotal + $ufistotal;

                        $rrmpgrandtotal =  $rrmpgrandtotal + $rrmptotal;
                        $rplngrandtotal =  $rplngrandtotal + $rplntotal;
                        $rr_sbsngrandtotal =  $rr_sbsngrandtotal + $rr_sbsntotal;

                        $myArray[] = (object)
                        [
                            'urut' => $urut,
                            'idbalai' => $row['id'],
                            'kdbalai' => $row['kdbalai'],
                            'kdbalai_pendek' => $row['kdbalai_pendek'],
                            'kdbalai_old' => $row['kdbalai_old'],
                            'kdbalai_old_pendek' => $row['kdbalai_old_pendek'],
                            'nama' => $row['nama'],
                            'pgrupiah' => $pgrupiahtotal,
                            'pagurpm' => $pagurpmtotal,
                            'pgpln' => $pgplntotal,
                            'pgsbsn' => $pgsbsntotal,
                            'ufis' => 0,
                            'pfis' => 0,
                            'pg57' => 0,
                            'rrmp' => $rrmptotal,
                            'rpln' => $rplntotal,
                            'rr_sbsn' => $rr_sbsntotal,
                            'rtot' => $rrmptotal + $rplntotal + $rr_sbsntotal,
                            'persenkeu' => $persenkeu,
                            'persenfisik' => $persenfisik,
                            'class'  => '',
                        ];
                        $urut++;
                    }
                }

                if ($phln == 'true') {
                    $persenkeutotal = ((($rrmpgrandtotal + $rplngrandtotal + $rr_sbsngrandtotal) / 1000) / (($pagurpmgrandtotal + $pgplngrandtotal) / 1000)) * 100;
                    $persenfisiktotal = (($ufisgrandtotal / 1000) / (($pagurpmgrandtotal + $pgplngrandtotal) / 1000)) * 100;
                } else {
                    $persenkeutotal = ((($rrmpgrandtotal + $rplngrandtotal + $rr_sbsngrandtotal) / 1000) / (($pagurpmgrandtotal) / 1000)) * 100;
                    $persenfisiktotal = (($ufisgrandtotal / 1000) / (($pagurpmgrandtotal) / 1000)) * 100;
                }

                $myArray[] = (object)
                [
                    'urut' => 0,
                    'idbalai' => '',
                    'kdbalai' => '',
                    'kdbalai_pendek' => '',
                    'kdbalai_old' => '',
                    'kdbalai_old_pendek' => '',
                    'nama' => 'TOTAL',
                    'pgrupiah' => $pgrupiahgrandtotal,
                    'pagurpm' => $pagurpmgrandtotal,
                    'pgpln' => $pgplngrandtotal,
                    'pgsbsn' => $pgsbsngrandtotal,
                    'ufis' => 0,
                    'pfis' => 0,
                    'pg57' => 0,
                    'rrmp' => $rrmpgrandtotal,
                    'rpln' => $rplngrandtotal,
                    'rr_sbsn' => $rr_sbsngrandtotal,
                    'rtot' => $rrmpgrandtotal + $rplngrandtotal + $rr_sbsngrandtotal,
                    'persenkeu' => $persenkeutotal,
                    'persenfisik' => $persenfisiktotal,
                    'class'  => 'totalbaru',
                ];


                $getUrut = function ($urutanlevel) {
                    return $urutanlevel->urut;
                };

                $names = array_map($getUrut, $myArray);
                sort($names);
                $sortedData = [];
                foreach ($names as $name) {
                    foreach ($myArray as $urutanlevel) {
                        if ($urutanlevel->urut === $name) {
                            $sortedData[] = $urutanlevel;
                            break;
                        }
                    }
                }

                $response         = [];
                $response['tanggalemon'] =  $tanggalemon;
                $response['data'] =  $sortedData;
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

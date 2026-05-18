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
    if ($kode_satkeremon <> '') {
        $filterquery = $filterquery . " and (a.kdsatker = '" . $kode_satkeremon . "' or a.kdsatker = '" . $kode_satkeremon2 . "')";
    }

    $tahun = fixup($_GET['tahun']);
    $filterquery = $filterquery . " and a.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "') and YEAR(a.tanggaldata) ='" . $tahun . "'";

    $kdgiat = fixup($_GET['kdgiat']);
    if ($kdgiat <> '') {
        $filterquery = $filterquery . " and a.kdgiat = '" . $kdgiat . "'";
    }

    $kdoutput = fixup($_GET['kdoutput']);
    if ($kdoutput <> '') {
        $filterquery = $filterquery . " and a.kdoutput = '" . $kdoutput . "'";
    }

    $kdsoutput = fixup($_GET['kdsoutput']);
    if ($kdsoutput <> '') {
        $filterquery = $filterquery . " and a.kdsoutput = '" . $kdsoutput . "'";
    }

    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($tahun <> '')) {
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

        $sql = "select a.kdsatker,a.kdprogram,a.kdgiat,a.kdunit
        ,a.kdoutput,a.kdsoutput,a.kdkmpnen,a.kdskmpnen,a.kode,a.nmpaket,a.vol,a.sat
        ,a.pgrupiah,a.pg,a.pgpln,a.pgsbsn,a.rtot,a.ufis,a.pfis,a.pg57,a.blokir
        ,b.ursoutput,c.nmoutput,c.sat as sat_output,d.nmgiat as nama_kegiatan
        ,(select b.nmlokasi from tlokasi b where b.kdlokasi = a.kdlokasi
        and b.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "') 
        and YEAR(b.tanggaldata) ='" . $tahun . "') as nmlokasi
        ,(select c.nmkabkota from tkabkota c where c.kdkabkota = a.kdkabkota
        and c.kdlokasi = a.kdlokasi and c.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "') 
        and YEAR(c.tanggaldata) ='" . $tahun . "') as nmkabkota
        ,(select d.nmkategori from tkategori d where d.kdkategori = a.kdkategori 
        and d.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "') 
        and YEAR(d.tanggaldata) ='" . $tahun . "') as nmkategori
        ,(select e.urmetode from tmetode e where e.kdmetode = a.metode
        and e.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "') 
        and YEAR(e.tanggaldata) ='" . $tahun . "' ) as urmetode
        ,(select GROUP_CONCAT(f.nama_satker) from master_satker f where f.kode_satker_pendek = a.kdsatker
	    and f.deleted='0') as nama_satker
        from paket a 
        left join tgiat d
        on a.kdgiat = d.kdgiat
        left join toutput c
        on a.kdgiat = c.kdgiat 
        and a.kdoutput = c.kdoutput 
        left join d_soutput b on
        a.kdgiat = b.kdgiat and a.kdoutput = b.kdoutput and a.kdsoutput = b.kdsoutput
        and a.kdsatker = b.kdsatker
        where a.kdgiat<>'' 
        " . $filterquery . " 
        order by a.kdsatker, a.kdgiat, a.kdprogram";
        //echo $sql;
        //die();
        $result = mysqli_query($link, $sql);
        $num = mysqli_num_rows($result);
        if ($num > 0) {
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $sumber_dana = "";
                    $pagu = 0;
                    if ((float) $row['pgrupiah'] > 0) {
                        $sumber_dana = "RPM";
                    } else if ((float) $row['pgpln'] > 0) {
                        $sumber_dana = "PHLN";
                    } else {
                        $sumber_dana = "SBSN";
                    }
                    $pagu = (float) $row['pagu'];

                    if ($pagu == 0) {
                        $pagu = (float) $row['pg'];
                    }

                    $persenfisik = 0;
                    if ((float) $row['pfis'] == 0) {
                        $persenfisik = 0;
                    } else {
                        $persenfisik = (((float) $row['ufis']) / ((float) $row['pfis'])) * 100;
                    }

                    $keu_efisiensi = 0;
                    $fisik_efisiensi = 0;

                    $pagu_efisiensi = (float) $row['pg57'];
                    if ($pagu_efisiensi == 0) {
                        if ($pagu_efisiensi == 0) {
                            if ($sumber_dana == "RPM") {
                                $pagu_efisiensi = $pagu - (float) $row['blokir'];
                            } else {
                                $pagu_efisiensi = $pagu;
                            }
                        }
                    }

                    if ($pagu_efisiensi == 0) {
                        $keu_efisiensi = 0;
                        $fisik_efisiensi = 0;
                    } else {
                        $keu_efisiensi = (((float) $row['rtot']) / ($pagu_efisiensi)) * 100;
                        $fisik_efisiensi = (((float) $row['ufis']) / ($pagu_efisiensi)) * 100;
                    }

                    if ($fisik_efisiensi > 100) {
                        $fisik_efisiensi = 100;
                    }

                    $persenkeu = (((float) $row['rtot']) / ($pagu)) * 100;

                    $sqlinsert = "INSERT INTO tb_master_data_paket(id, kdsatker, nama_satker, kdprogram
                    , nama_kegiatan, kdoutput, nmoutput, sat_output, kdsoutput, ursoutput, kdkmpnen
                    , kdskmpnen, kdgiat, nmgiat, kode, vol, satuan, lokasi, kabkota, kategori, metode
                    , sumber_dana, pagu, blokir, realisasi, ufis, pfis, persenkeu, persenfisik, pagu_efisiensi
                    , persenkeu_efisiensi, persenfisik_efisiensi, tanggaldata, deleted) VALUES 
                    (null, '" . $row['kdsatker'] . "', '" . $row['nama_satker'] . "', '" . $row['kdprogram'] . "'
                    , '" . $row['nama_kegiatan'] . "', '" . $row['kdoutput'] . "', '" . $row['nmoutput'] . "'
                    , '" . str_replace("/", " / ", $row['sat_output']) . "', '" . $row['kdsoutput'] . "'
                    , '" . $row['ursoutput'] . "', '" . $row['kdkmpnen'] . "', '" . $row['kdskmpnen'] . "'
                    , '" . $row['kdgiat'] . "', '" . $row['nmpaket'] . "', '" . $row['kode'] . "'
                    , '" . (float) $row['vol'] . "', '" . $row['sat'] . "', '" . $row['nmlokasi'] . "'
                    , '" . $row['nmkabkota'] . "', '" . $row['nmkategori'] . "', '" . $row['urmetode'] . "'
                    , '" . $sumber_dana . "', '" . $pagu . "', '" . (float) $row['blokir'] . "'
                    , '" . (float) $row['rtot'] . "', '" . (float) $row['ufis'] . "'
                    , '" . (float) $row['pfis'] . "', '" . $persenkeu . "', '" . $persenfisik . "', '" . $pagu_efisiensi . "'
                    , '" . $keu_efisiensi . "', '" . $fisik_efisiensi . "', '" . $tanggalemon . "', '0')";

                    $resultinsert = mysqli_query($link, $sqlinsert);
                }
                if ($resultinsert) {
                    mysqli_close($link);
                    echo json_encode(array('response' => 'success', 'message' => 'Data Telah Diinsert'), JSON_PRETTY_PRINT);
                    die;
                } else {
                    mysqli_close($link);
                    echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'), JSON_PRETTY_PRINT);
                    die;
                }
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

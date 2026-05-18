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
    $tahun = fixup($_GET['tahun']);
    $filterquery = $filterquery . " and a.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "') and YEAR(a.tanggaldata) ='" . $tahun . "'";
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($tahun <> '') && ($kode_satker <> '')) {
        $pgtotal = 0;
        $jumlahtotal = 0;
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


        $kode_satkeremon = "";
        $kode_satkeremon2 = "";
        $sqlsatkeremon = "select nama_satker, kode_satker_old_pendek, kode_satker_old
        , kode_satker_pendek , kode_satker
        from master_satker where kode_satker = '" . $kode_satker . "' and kode_satker_old_pendek <> ''
        and kode_satker_pendek <> '' and deleted='0' order by urut,kode_satker";
        //echo $sqlsatkeremon;
        //die;
        $resultsatkeremon = mysqli_query($link, $sqlsatkeremon);
        $x = 1;
        while ($rowsatkeremon = mysqli_fetch_assoc($resultsatkeremon)) {
            $kode_satkeremon = $rowsatkeremon['kode_satker_old_pendek'];
            $kode_satkeremon2 = $rowsatkeremon['kode_satker_pendek'];

            $pg = 0;
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

            if ($kode_satkeremon <> '' && $kode_satkeremon2 <> '') {
                $sqldata = "select a.nmpaket,a.kode,a.tahapan_skrg,a.status,a.ket_lanjut
                ,a.rkn_nama,a.nkon,a.nkon_rev,a.nomor_kontrak,a.nomor_kontrak_rev
                ,d.vol,d.sat,d.pgrupiah,d.pgpln,d.pgsbsn,d.rtot,d.ufis,d.pfis,d.pg57,d.blokir,a.pgp
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
                from d_penyedia a 
                left join paket d
                on a.kode=d.kode
                where a.nmpaket <> '' and (a.metode = '1' or a.metode = '2' or a.metode = '6' or a.metode = '13')
                and (a.pg57 <= a.pg and a.pg57 > 0)
                and (a.kdsatker in (" . $kode_satkeremon . ") 
                or a.kdsatker in (" . $kode_satkeremon2 . ")) " . $filterquery . "";
                //echo $sqldata;
                //die;
                $resultdata = mysqli_query($link, $sqldata);
                $jumlahdata = mysqli_num_rows($resultdata);

                if ($jumlahdata > 0) {
                    $pgtotal =  0;
                    $pagurpmtotal =  0;
                    $pgplntotal =  0;
                    $pgsbsntotal =  0;
                    while ($rowdata = mysqli_fetch_assoc($resultdata)) {
                        $sumber_dana = "";
                        $pagu = 0;
                        if ((float) $rowdata['pgrupiah'] > 0) {
                            $sumber_dana = "RPM";
                            $pagu = (float) $rowdata['pgrupiah'];
                        } else if ((float) $rowdata['pgpln'] > 0) {
                            $sumber_dana = "PHLN";
                            $pagu = (float) $rowdata['pgpln'];
                        } else {
                            $sumber_dana = "SBSN";
                            $pagu = (float) $rowdata['pgsbsn'];
                        }

                        $persenfisik = 0;
                        if ((float) $rowdata['pfis'] == 0) {
                            $persenfisik = 0;
                        } else {
                            $persenfisik = (((float) $rowdata['ufis'] / 1000) / ((float) $rowdata['pfis'] / 1000)) * 100;
                        }

                        $keu_efisiensi = 0;
                        $fisik_efisiensi = 0;

                        $pagu_efisiensi = (float) $rowdata['pg57'];
                        if ($pagu_efisiensi == 0) {
                            if ($sumber_dana == "RPM") {
                                $pagu_efisiensi = $pagu - (float) $rowdata['blokir'];
                            } else {
                                $pagu_efisiensi = $pagu;
                            }
                        }

                        if ($pagu_efisiensi == 0) {
                            $keu_efisiensi = 0;
                            $fisik_efisiensi = 0;
                        } else {
                            $keu_efisiensi = (((float) $rowdata['rtot'] / 1000) / ($pagu_efisiensi / 1000)) * 100;
                            $fisik_efisiensi = (((float) $rowdata['ufis'] / 1000) / ($pagu_efisiensi / 1000)) * 100;
                        }

                        $nilaikontrak = 0;
                        if ((float) $rowdata['nkon_rev'] == 0) {
                            $nilaikontrak = (float) $rowdata['nkon'];
                        } else {
                            $nilaikontrak = (float) $rowdata['nkon_rev'];
                        }

                        $nomor_kontrak = '';

                        if ($rowdata['nomor_kontrak_rev'] == '') {
                            $nomor_kontrak = $rowdata['nomor_kontrak'];
                        } else {
                            $nomor_kontrak = $rowdata['nomor_kontrak_rev'];
                        }

                        $myArray[] = (object)
                        [
                            'urut' => $x,
                            'kode_satker' => $rowsatkeremon['kode_satker'],
                            'kode_satker_pendek' => $rowsatkeremon['kode_satker_pendek'],
                            'kode_satker_old' => $rowsatkeremon['kode_satker_old'],
                            'kode_satker_old_pendek' => $rowsatkeremon['kode_satker_old_pendek'],
                            'nama_satker' => $rowsatkeremon['nama_satker'],
                            'kode' => $rowdata['kode'],
                            'sumber_dana' => $sumber_dana,
                            'nmpaket' => $rowdata['nmpaket'],
                            'vol' => (float) $rowdata['vol'],
                            'satuan' => $rowdata['sat'],
                            'lokasi' => $rowdata['nmlokasi'],
                            'kabkota' => $rowdata['nmkabkota'],
                            'kategori' => $rowdata['nmkategori'],
                            'metode' => $rowdata['urmetode'],
                            'sumber_dana' => $sumber_dana,
                            'pagu' => ($pagu / 1000),
                            'realisasi' => (float) $rowdata['rtot'] / 1000,
                            'persenkeu' => (((float) $rowdata['rtot'] / 1000) / ($pagu / 1000)) * 100,
                            'persenfisik' => $persenfisik,
                            'pagu_efisiensi' => $pagu_efisiensi / 1000,
                            'persenkeu_efisiensi' => $keu_efisiensi,
                            'persenfisik_efisiensi' => $fisik_efisiensi,
                            'tahapan_skrg' => $rowdata['tahapan_skrg'],
                            'rkn_nama' => $rowdata['rkn_nama'],
                            'ket_lanjut' => $rowdata['ket_lanjut'],
                            'status' => $rowdata['status'],
                            'pagu_pengadaan' => ((float) $rowdata['pgp'] / 1000),
                            'nilaikontrak' => ($nilaikontrak / 1000),
                            'nomor_kontrak' => $nomor_kontrak,
                            'class'  => '',
                        ];

                        $jumlahtotal = $jumlahtotal + $jumlahdata;
                        $pgtotal =  $pgtotal + $pg;
                        $pagurpmtotal =  $pagurpmtotal + $pagurpm;
                        $pgplntotal =  $pgplntotal + $pgpln;
                        $pgsbsntotal =  $pgsbsntotal + $pgsbsn;
                        $x++;
                    }
                    $response         = [];
                    $response['tanggalemon'] =  $tanggalemon;
                    $response['data'] =  $myArray;
                    mysqli_close($link);
                    echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
                    die;
                } else {
                    mysqli_close($link);
                    echo json_encode(array('response' => 'success', 'message' => 'data kosong'), JSON_PRETTY_PRINT);
                    die;
                }
                //$jumlahtotal = 0;

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

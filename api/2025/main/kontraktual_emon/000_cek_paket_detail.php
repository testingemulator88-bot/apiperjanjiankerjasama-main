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
        $myArraysatker = array();
        $tanggalemon = date("Y-m-d H:i:s");
        $sqltanggalemon = "select MAX(nama) as tanggalemon from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "'";
        //echo $sqltanggalemon;
        //die;

        $resulttanggalemon = mysqli_query($link, $sqltanggalemon);
        while ($rowtanggalemon = mysqli_fetch_assoc($resulttanggalemon)) {
            $tanggalemon = $rowtanggalemon['tanggalemon'];
        }

        $urut = 1;
        $sqldata = "select x.nama_satker,a.kdsatker,a.tahapan_skrg,a.status_tender
        ,a.pgrupiah,a.pgpln, a.pgsbsn,a.pg57,a.ket_lanjut
        ,a.pg,a.pgp,a.blokir,a.pfis,a.nkon,a.nkon_rev,a.rkn_nama,a.kdspaket
        ,a.rtot,a.tanggaldata,a.status,a.kode,a.nmpaket
        ,a.nomor_kontrak,a.nomor_kontrak_rev,a.nomor_kontrak_rev,a.kontrak_mulai,a.kontrak_mulai_rev
        ,a.ren_ttdkontrak,a.ren_fho,a.jadwal_kontrak,a.tanggal_kontrak,a.jadwal_pengumuman,a.jadwal_pemenang
        ,a.tgl_lelang,a.tgl_mulai,a.tgl_selesai
        from d_penyedia a 
        left join master_satker x on a.kdsatker = x.kode_satker_pendek
        where a.nmpaket <> '' and a.kdunit <> '' and x.deleted='0' and (a.metode = '1' or a.metode = '2' or a.metode = '6' or a.metode = '13')
        and a.kdprogram ='FC' and a.tgl_lelang<>'' and a.tgl_mulai<>'' and a.tgl_selesai<>'' and a.pg>0 and a.rtot >0
        and a.tgl_lelang<>'--' and a.tgl_mulai<>'--' and a.tgl_selesai<>'--' and a.status <> ''
        and (a.kdoutput = 'ABF' or a.kdoutput = 'BMA' or a.kdoutput = 'CBG' or a.kdoutput = 'CBH' or a.kdoutput = 'CBR' or a.kdoutput = 'CBS'
        or a.kdoutput = 'CDH' or a.kdoutput = 'FAD' or a.kdoutput = 'QMA' or a.kdoutput = 'RBG'or a.kdoutput = 'RBH'or a.kdoutput = 'RBR'or a.kdoutput = 'RBS') 
        " . $filterquery . "

        UNION

        select x.nama_satker,a.kdsatker,a.tahapan_skrg,a.status_tender
        ,a.pgrupiah,a.pgpln, a.pgsbsn,a.pg57,a.ket_lanjut
        ,a.pg,a.pgp,a.blokir,a.pfis,a.nkon,a.nkon_rev,a.rkn_nama,a.kdspaket
        ,a.rtot,a.tanggaldata,a.status,a.kode,a.nmpaket
        ,a.nomor_kontrak,a.nomor_kontrak_rev,a.nomor_kontrak_rev,a.kontrak_mulai,a.kontrak_mulai_rev
        ,a.ren_ttdkontrak,a.ren_fho,a.jadwal_kontrak,a.tanggal_kontrak,a.jadwal_pengumuman,a.jadwal_pemenang
        ,a.tgl_lelang,a.tgl_mulai,a.tgl_selesai
        from d_penyedia a 
        left join master_satker x on a.kdsatker = x.kode_satker_pendek
        where a.nmpaket <> '' and a.kdunit <> '' and x.deleted='0' and (a.metode = '1' or a.metode = '2' or a.metode = '6' or a.metode = '13')
        and a.kdprogram ='FC' and a.tgl_lelang<>'' and a.tgl_mulai<>'' and a.tgl_selesai<>'' and a.pg>0 and a.rtot =0
        and a.tgl_lelang<>'--' and a.tgl_mulai<>'--' and a.tgl_selesai<>'--' and (a.status = 'Sudah Penetapan Pemenang' or a.status = 'Belum Penetapan')
        and a.pg > a.blokir
        and (a.kdoutput = 'ABF' or a.kdoutput = 'BMA' or a.kdoutput = 'CBG' or a.kdoutput = 'CBH' or a.kdoutput = 'CBR' or a.kdoutput = 'CBS'
        or a.kdoutput = 'CDH' or a.kdoutput = 'FAD' or a.kdoutput = 'QMA' or a.kdoutput = 'RBG'or a.kdoutput = 'RBH'or a.kdoutput = 'RBR'or a.kdoutput = 'RBS') 
        " . $filterquery . "

        UNION

        select x.nama_satker,a.kdsatker,a.tahapan_skrg,a.status_tender
        ,a.pgrupiah,a.pgpln, a.pgsbsn,a.pg57,a.ket_lanjut
        ,a.pg,a.pgp,a.blokir,a.pfis,a.nkon,a.nkon_rev,a.rkn_nama,a.kdspaket
        ,a.rtot,a.tanggaldata,a.status,a.kode,a.nmpaket
        ,a.nomor_kontrak,a.nomor_kontrak_rev,a.nomor_kontrak_rev,a.kontrak_mulai,a.kontrak_mulai_rev
        ,a.ren_ttdkontrak,a.ren_fho,a.jadwal_kontrak,a.tanggal_kontrak,a.jadwal_pengumuman,a.jadwal_pemenang
        ,a.tgl_lelang,a.tgl_mulai,a.tgl_selesai
        from d_penyedia a 
        left join master_satker x on a.kdsatker = x.kode_satker_pendek
        where a.nmpaket <> '' and a.kdunit <> '' and x.deleted='0' and (a.metode = '1' or a.metode = '2' or a.metode = '6' or a.metode = '13')
        and a.kdprogram ='FC' and a.tgl_lelang<>'' and a.tgl_mulai<>'' and a.tgl_selesai<>'' and a.pg>0 and a.rtot =0 and a.pg<>a.blokir and a.pg57 > 0 and a.pg>a.pfis
        and a.tgl_lelang<>'--' and a.tgl_mulai<>'--' and a.tgl_selesai<>'--' and a.status = '' and a.tgl_selesai > '" . $tanggalemon . "'
        and (a.kdoutput = 'ABF' or a.kdoutput = 'BMA' or a.kdoutput = 'CBG' or a.kdoutput = 'CBH' or a.kdoutput = 'CBR' or a.kdoutput = 'CBS'
        or a.kdoutput = 'CDH' or a.kdoutput = 'FAD' or a.kdoutput = 'QMA' or a.kdoutput = 'RBG'or a.kdoutput = 'RBH'or a.kdoutput = 'RBR'or a.kdoutput = 'RBS') 
        " . $filterquery . "

        UNION

        select x.nama_satker,a.kdsatker,a.tahapan_skrg,a.status_tender
        ,a.pgrupiah,a.pgpln, a.pgsbsn,a.pg57,a.ket_lanjut
        ,a.pg,a.pgp,a.blokir,a.pfis,a.nkon,a.nkon_rev,a.rkn_nama,a.kdspaket
        ,a.rtot,a.tanggaldata,a.status,a.kode,a.nmpaket
        ,a.nomor_kontrak,a.nomor_kontrak_rev,a.nomor_kontrak_rev,a.kontrak_mulai,a.kontrak_mulai_rev
        ,a.ren_ttdkontrak,a.ren_fho,a.jadwal_kontrak,a.tanggal_kontrak,a.jadwal_pengumuman,a.jadwal_pemenang
        ,a.tgl_lelang,a.tgl_mulai,a.tgl_selesai
        from d_penyedia a 
        left join master_satker x on a.kdsatker = x.kode_satker_pendek
        where a.nmpaket <> '' and a.kdunit <> '' and x.deleted='0' and (a.metode = '1' or a.metode = '2' or a.metode = '6' or a.metode = '13')
        and a.kdprogram ='FC' and a.tgl_lelang<>'' and a.tgl_mulai<>'' and a.tgl_selesai<>'' and a.pg>0 and a.rtot =0 and a.pg<>a.blokir and a.pg57 > 0 and a.pg>a.pfis
        and a.tgl_lelang<>'--' and a.tgl_mulai<>'--' and a.tgl_selesai<>'--' and a.status = 'Sudah Penetapan Pemenang' 
        and a.jadwal_kontrak <= '" . $tanggalemon . "' and (a.tahapan_skrg like '%Prakualifikasi%' or a.tahapan_skrg like '%Pelelangan%')
        and (a.kdoutput = 'ABF' or a.kdoutput = 'BMA' or a.kdoutput = 'CBG' or a.kdoutput = 'CBH' or a.kdoutput = 'CBR' or a.kdoutput = 'CBS'
        or a.kdoutput = 'CDH' or a.kdoutput = 'FAD' or a.kdoutput = 'QMA' or a.kdoutput = 'RBG'or a.kdoutput = 'RBH'or a.kdoutput = 'RBR'or a.kdoutput = 'RBS') 
        " . $filterquery . "

        UNION

        select x.nama_satker,a.kdsatker,a.tahapan_skrg,a.status_tender
        ,a.pgrupiah,a.pgpln, a.pgsbsn,a.pg57,a.ket_lanjut
        ,a.pg,a.pgp,a.blokir,a.pfis,a.nkon,a.nkon_rev,a.rkn_nama,a.kdspaket
        ,a.rtot,a.tanggaldata,a.status,a.kode,a.nmpaket
        ,a.nomor_kontrak,a.nomor_kontrak_rev,a.nomor_kontrak_rev,a.kontrak_mulai,a.kontrak_mulai_rev
        ,a.ren_ttdkontrak,a.ren_fho,a.jadwal_kontrak,a.tanggal_kontrak,a.jadwal_pengumuman,a.jadwal_pemenang
        ,a.tgl_lelang,a.tgl_mulai,a.tgl_selesai
        from d_penyedia a 
        left join master_satker x on a.kdsatker = x.kode_satker_pendek
        where a.nmpaket <> '' and a.kdunit <> '' and x.deleted='0' and (a.metode = '1' or a.metode = '2' or a.metode = '6' or a.metode = '13')
        and a.kdprogram ='FC' and a.tgl_lelang<>'' and a.tgl_mulai<>'' and a.tgl_selesai<>'' and a.pg>0 and a.rtot =0 and a.pg=a.blokir and a.pg57 = 0 and a.pg=a.pfis
        and a.tgl_lelang<>'--' and a.tgl_mulai<>'--' and a.tgl_selesai<>'--' and a.status = 'Sudah Penetapan Pemenang' and a.kdspaket<>'' 
        and a.jadwal_kontrak <= '" . $tanggalemon . "' and (a.tahapan_skrg like '%Prakualifikasi%' or a.tahapan_skrg like '%Pelelangan%')
        and (a.kdoutput = 'ABF' or a.kdoutput = 'BMA' or a.kdoutput = 'CBG' or a.kdoutput = 'CBH' or a.kdoutput = 'CBR' or a.kdoutput = 'CBS'
        or a.kdoutput = 'CDH' or a.kdoutput = 'FAD' or a.kdoutput = 'QMA' or a.kdoutput = 'RBG'or a.kdoutput = 'RBH'or a.kdoutput = 'RBR'or a.kdoutput = 'RBS') 
        " . $filterquery . "

        UNION

        select x.nama_satker,a.kdsatker,a.tahapan_skrg,a.status_tender
        ,a.pgrupiah,a.pgpln, a.pgsbsn,a.pg57,a.ket_lanjut
        ,a.pg,a.pgp,a.blokir,a.pfis,a.nkon,a.nkon_rev,a.rkn_nama,a.kdspaket
        ,a.rtot,a.tanggaldata,a.status,a.kode,a.nmpaket
        ,a.nomor_kontrak,a.nomor_kontrak_rev,a.nomor_kontrak_rev,a.kontrak_mulai,a.kontrak_mulai_rev
        ,a.ren_ttdkontrak,a.ren_fho,a.jadwal_kontrak,a.tanggal_kontrak,a.jadwal_pengumuman,a.jadwal_pemenang
        ,a.tgl_lelang,a.tgl_mulai,a.tgl_selesai
        from d_penyedia a 
        left join master_satker x on a.kdsatker = x.kode_satker_pendek
        where a.nmpaket <> '' and a.kdunit <> '' and x.deleted='0' and (a.metode = '1' or a.metode = '2' or a.metode = '6' or a.metode = '13')
        and a.kdprogram ='FC' and a.tgl_lelang='--' and a.tgl_mulai<>'' and a.tgl_selesai<>'' and a.pg>0 and a.pg57>0 and a.pg>a.blokir and a.rtot > 0 
        and a.tgl_mulai <= '" . $tanggalemon . "' and a.tgl_mulai<>'--' and a.tgl_selesai<>'--' and a.status = 'Sudah Penetapan Pemenang' 
        and a.jadwal_kontrak <= '" . $tanggalemon . "' and a.tanggal_kontrak <= '" . $tanggalemon . "' and (a.tahapan_skrg like '%Selesai%' or a.tahapan_skrg like '%Selesai%')
        and (a.kdoutput = 'ABF' or a.kdoutput = 'BMA' or a.kdoutput = 'CBG' or a.kdoutput = 'CBH' or a.kdoutput = 'CBR' or a.kdoutput = 'CBS'
        or a.kdoutput = 'CDH' or a.kdoutput = 'FAD' or a.kdoutput = 'QMA' or a.kdoutput = 'RBG'or a.kdoutput = 'RBH'or a.kdoutput = 'RBR'or a.kdoutput = 'RBS') 
        " . $filterquery . "

        UNION

        select x.nama_satker,a.kdsatker,a.tahapan_skrg,a.status_tender
        ,a.pgrupiah,a.pgpln, a.pgsbsn,a.pg57,a.ket_lanjut
        ,a.pg,a.pgp,a.blokir,a.pfis,a.nkon,a.nkon_rev,a.rkn_nama,a.kdspaket
        ,a.rtot,a.tanggaldata,a.status,a.kode,a.nmpaket
        ,a.nomor_kontrak,a.nomor_kontrak_rev,a.nomor_kontrak_rev,a.kontrak_mulai,a.kontrak_mulai_rev
        ,a.ren_ttdkontrak,a.ren_fho,a.jadwal_kontrak,a.tanggal_kontrak,a.jadwal_pengumuman,a.jadwal_pemenang
        ,a.tgl_lelang,a.tgl_mulai,a.tgl_selesai
        from d_penyedia a 
        left join master_satker x on a.kdsatker = x.kode_satker_pendek
        where a.nmpaket <> '' and a.kdunit <> '' and x.deleted='0' and (a.metode = '1' or a.metode = '2' or a.metode = '6' or a.metode = '13')
        and a.kdprogram ='FC' and a.tgl_lelang<>'' and a.tgl_mulai<>'' and a.tgl_selesai<>'' and a.pg>0
        and a.tgl_lelang<>'--' and a.tgl_mulai<>'--' and a.tgl_selesai<>'--' and a.status = '' and a.pg57>0
        and a.jadwal_kontrak <= '" . $tanggalemon . "' and a.tahapan_skrg = ''
        and (a.kdoutput = 'ABF' or a.kdoutput = 'BMA' or a.kdoutput = 'CBG' or a.kdoutput = 'CBH' or a.kdoutput = 'CBR' or a.kdoutput = 'CBS'
        or a.kdoutput = 'CDH' or a.kdoutput = 'FAD' or a.kdoutput = 'QMA' or a.kdoutput = 'RBG'or a.kdoutput = 'RBH'or a.kdoutput = 'RBR'or a.kdoutput = 'RBS') 
        " . $filterquery . "

        group by kode order by kdsatker,kode";

        //echo $sqldata;
        //die;
        $resultdata = mysqli_query($link, $sqldata);
        $jumlahtotal = 0;

        while ($rowdata = mysqli_fetch_assoc($resultdata)) {
            $status_kontrak = '';
            $pg_rm = 0;
            if ((float) $rowdata['pg57'] == 0) {
                $pagu_efektif = (float) $rowdata['pg'];
            } else {
                $pagu_efektif = (float) $rowdata['pg57'];
            }

            if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                $pg_rm = $pagu_efektif;
            }

            if ($rowdata['status_tender'] == '2') {
                $status_kontrak = "Terkontrak";
                if ((float) $rowdata['nkon_rev'] > 0) {
                    $nilai_kontrak = (float) $rowdata['nkon_rev'] / 1000;
                } else {
                    $nilai_kontrak = (float) $rowdata['nkon'] / 1000;
                }

                if ($nilai_kontrak > $pagu_efektif) {
                    $nilai_kontrak_anak = $pagu_efektif;
                } else {
                    $nilai_kontrak_anak = $nilai_kontrak;
                }
            } else if (($rowdata['status_tender'] == '') && (($rowdata['status'] == '') || ($rowdata['status'] == 'Belum Penetapan')) && (($rowdata['jadwal_kontrak'] <= $tanggalemon) || ($rowdata['jadwal_kontrak'] =='--')) && ($rowdata['tgl_mulai'] <= $tanggalemon) && ((float) $rowdata['rtot'] > 0)) {
                $status_kontrak = "Terkontrak";
                if ((float) $rowdata['nkon_rev'] > 0) {
                    $nilai_kontrak = (float) $rowdata['nkon_rev'] / 1000;
                } else {
                    $nilai_kontrak = (float) $rowdata['nkon'] / 1000;
                }

                if ($nilai_kontrak > $pagu_efektif) {
                    $nilai_kontrak_anak = $pagu_efektif;
                } else {
                    $nilai_kontrak_anak = $nilai_kontrak;
                }
            }
            else if (($rowdata['status_tender'] == '') && ($rowdata['status'] == '') && ($rowdata['tgl_lelang'] <= $tanggalemon) && (($rowdata['tgl_mulai'] <= $tanggalemon) || ($rowdata['tgl_mulai'] ='--')) && ($rowdata['ren_ttdkontrak'] <= $tanggalemon) && ((float) $rowdata['rtot'] == 0)) {
                $status_kontrak = "Terkontrak";
                if ((float) $rowdata['nkon_rev'] > 0) {
                    $nilai_kontrak = (float) $rowdata['nkon_rev'] / 1000;
                } else {
                    $nilai_kontrak = (float) $rowdata['nkon'] / 1000;
                }

                if ($nilai_kontrak > $pagu_efektif) {
                    $nilai_kontrak_anak = $pagu_efektif;
                } else {
                    $nilai_kontrak_anak = $nilai_kontrak;
                }
            }
            else if ($rowdata['status_tender'] == '4') {
                $status_kontrak = "Gagal Lelang";
                if ((float) $rowdata['nkon_rev'] > 0) {
                    $nilai_kontrak = (float) $rowdata['nkon_rev'] / 1000;
                } else {
                    $nilai_kontrak = (float) $rowdata['nkon'] / 1000;
                }

                if ($nilai_kontrak > $pagu_efektif) {
                    $nilai_kontrak_anak = $pagu_efektif;
                } else {
                    $nilai_kontrak_anak = $nilai_kontrak;
                }
            }
            else if ($rowdata['status_tender'] == '1') {
                $status_kontrak = "Proses Lelang";
                if ((float) $rowdata['nkon_rev'] > 0) {
                    $nilai_kontrak = (float) $rowdata['nkon_rev'] / 1000;
                } else {
                    $nilai_kontrak = (float) $rowdata['nkon'] / 1000;
                }

                if ($nilai_kontrak > $pagu_efektif) {
                    $nilai_kontrak_anak = $pagu_efektif;
                } else {
                    $nilai_kontrak_anak = $nilai_kontrak;
                }
            } else if (($rowdata['status_tender'] == '') && (($rowdata['status'] == '') || ($rowdata['status'] == 'Belum Penetapan')) && (($rowdata['jadwal_kontrak'] > $tanggalemon) || ($rowdata['jadwal_kontrak'] =='--')) && ($rowdata['tgl_mulai'] > $tanggalemon) && ((float) $rowdata['rtot'] == 0)) {
                $status_kontrak = "Belum Lelang";
                if ((float) $rowdata['nkon_rev'] > 0) {
                    $nilai_kontrak = (float) $rowdata['nkon_rev'] / 1000;
                } else {
                    $nilai_kontrak = (float) $rowdata['nkon'] / 1000;
                }

                if ($nilai_kontrak > $pagu_efektif) {
                    $nilai_kontrak_anak = $pagu_efektif;
                } else {
                    $nilai_kontrak_anak = $nilai_kontrak;
                }
            }

            $myArray[] = (object)
            [
                'urut' => $urut,
                'nama' => $rowdata['nama_satker'],
                'kdsatker' => $rowdata['kdsatker'],
                'kode' => $rowdata['kode'],
                'nmpaket' => $rowdata['nmpaket'],
                'pg' => (float) $rowdata['pg'] / 1000,
                'pgp' => (float) $rowdata['pgp'] / 1000,
                'pgrupiah' => (float) $rowdata['pgrupiah'] / 1000,
                'pg_rm' => $pg_rm / 1000,
                'pgpln' => (float) $rowdata['pgpln'] / 1000,
                'pgsbsn' => (float) $rowdata['pgsbsn'] / 1000,
                'pg57' => (float) $rowdata['pg57'] / 1000,
                'blokir' => (float) $rowdata['blokir'] / 1000,
                'pagu_efektif' => $pagu_efektif / 1000,
                'status_kontrak' => $status_kontrak,
                'nilai_kontrak' => $nilai_kontrak,
                'nilai_kontrak_anak' => $nilai_kontrak_anak,
                'class'  => '',
            ];
        }
        //die;

        $response         = [];
        $response['tanggalemon'] =  $tanggalemon;
        $response['data'] =  $myArray;
        mysqli_close($link);
        echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
        die;
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

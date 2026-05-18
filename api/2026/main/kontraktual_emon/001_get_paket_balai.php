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
    $jumlahtotalpaket = 0;
    $jumlahtotalpaket_terkontrak = 0;
    $jumlahtotalpagu = 0;
    $jumlahTotalpagu_efisiensi = 0;
    $jumlahTotalpagu_efisiensi_terkontrak = 0;
    $jumlahTotal_terkontrak = 0;
    $jumlahTotalpagu_efisiensi_terkontrak_rm = 0;
    $jumlahTotalpagu_efisiensi_terkontrak_phln = 0;
    $jumlahTotalpagu_efisiensi_terkontrak_sbsn = 0;
    $jumlahTotal_terkontrak_rm = 0;
    $jumlahTotal_terkontrak_phln = 0;
    $jumlahTotal_terkontrak_sbsn = 0;
    $jumlahTotalRealisasi = 0;
    $jumlahTotalFisik = 0;
    $tahun = fixup($_GET['tahun']);
    $filterquery = $filterquery . " and a.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "') and YEAR(a.tanggaldata) ='" . $tahun . "'";

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
                        $sqldata = "select x.nama_satker,a.kdsatker,a.tahapan_skrg, a.ufis
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
                        and (a.kdsatker in (" . $kode_satkeremon . ") 
                        or a.kdsatker in (" . $kode_satkeremon2 . ")) " . $filterquery . "
                        
                        UNION

                        select x.nama_satker,a.kdsatker,a.tahapan_skrg, a.ufis
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
                        and (a.kdsatker in (" . $kode_satkeremon . ") 
                        or a.kdsatker in (" . $kode_satkeremon2 . ")) " . $filterquery . "

                        UNION

                        select x.nama_satker,a.kdsatker,a.tahapan_skrg, a.ufis
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
                        and (a.kdsatker in (" . $kode_satkeremon . ") 
                        or a.kdsatker in (" . $kode_satkeremon2 . ")) " . $filterquery . "

                        UNION

                        select x.nama_satker,a.kdsatker,a.tahapan_skrg, a.ufis
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
                        and (a.kdsatker in (" . $kode_satkeremon . ") 
                        or a.kdsatker in (" . $kode_satkeremon2 . ")) " . $filterquery . "

                        UNION

                        select x.nama_satker,a.kdsatker,a.tahapan_skrg, a.ufis
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
                        and (a.kdsatker in (" . $kode_satkeremon . ") 
                        or a.kdsatker in (" . $kode_satkeremon2 . ")) " . $filterquery . "

                        UNION

                        select x.nama_satker,a.kdsatker,a.tahapan_skrg, a.ufis
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
                        and (a.kdsatker in (" . $kode_satkeremon . ") 
                        or a.kdsatker in (" . $kode_satkeremon2 . ")) " . $filterquery . "

                        UNION

                        select x.nama_satker,a.kdsatker,a.tahapan_skrg, a.ufis
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
                        and (a.kdsatker in (" . $kode_satkeremon . ") 
                        or a.kdsatker in (" . $kode_satkeremon2 . ")) " . $filterquery . "

                        group by kode";

                        //echo $sqldata;
                        //die;

                        $resultdata = mysqli_query($link, $sqldata);
                        $jumlahpaket = 0;
                        $jumlahpaket_terkontrak = 0;
                        $jumlahpagu_efisiensi = 0;
                        $jumlahpagu_efisiensi_terkontrak = 0;
                        $nilai_terkontrak = 0;
                        $jumlahpagu_efisiensi_terkontrak_rm = 0;
                        $jumlahpagu_efisiensi_terkontrak_phln = 0;
                        $jumlahpagu_efisiensi_terkontrak_sbsn = 0;
                        $nilai_terkontrak_rm = 0;
                        $nilai_terkontrak_phln = 0;
                        $nilai_terkontrak_sbsn = 0;
                        $jumlahrealisasi = 0;
                        $jumlahufis = 0;

                        $jumlahpaket_persiapan = 0;

                        while ($rowdata = mysqli_fetch_assoc($resultdata)) {
                            $jumlahpaket++;
                            if ((float) $rowdata['pg57'] == 0) {
                                $pagu_efisiensi = (float) $rowdata['pg'] / 1000;
                            } else {
                                $pagu_efisiensi = (float) $rowdata['pg57'] / 1000;
                            }
                            $jumlahpagu_efisiensi = $jumlahpagu_efisiensi + $pagu_efisiensi;

                            if (((float) $rowdata['rtot'] > 0)) {
                                $jumlahpaket_terkontrak++;
                                $jumlahpagu_efisiensi_terkontrak = $jumlahpagu_efisiensi_terkontrak + $pagu_efisiensi;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $jumlahpagu_efisiensi_terkontrak_rm = $jumlahpagu_efisiensi_terkontrak_rm + $pagu_efisiensi;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $jumlahpagu_efisiensi_terkontrak_phln = $jumlahpagu_efisiensi_terkontrak_phln + $pagu_efisiensi;
                                } else {
                                    $jumlahpagu_efisiensi_terkontrak_sbsn = $jumlahpagu_efisiensi_terkontrak_sbsn + $pagu_efisiensi;
                                }

                                if ((float) $rowdata['nkon_rev'] > 0) {
                                    $nilai_kontrak = (float) $rowdata['nkon_rev'] / 1000;
                                } else {
                                    $nilai_kontrak = (float) $rowdata['nkon'] / 1000;
                                }
                                if ($nilai_kontrak > $pagu_efisiensi) {
                                    $nilai_kontrak = $pagu_efisiensi;
                                }
                                $nilai_terkontrak = $nilai_terkontrak + $nilai_kontrak;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $nilai_terkontrak_rm = $nilai_terkontrak_rm + $nilai_kontrak;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $nilai_terkontrak_phln = $nilai_terkontrak_phln + $nilai_kontrak;
                                } else {
                                    $nilai_terkontrak_sbsn = $nilai_terkontrak_sbsn + $nilai_kontrak;
                                }

                                $realisasi = (float) $rowdata['rtot'] / 1000;
                                if ($realisasi > $nilai_kontrak) {
                                    $realisasi = $nilai_kontrak;
                                }
                                $jumlahrealisasi = $jumlahrealisasi + $realisasi;

                                $ufis = (float) $rowdata['ufis'] / 1000;
                                if ($ufis > $nilai_kontrak) {
                                    $ufis = $nilai_kontrak;
                                }
                                $jumlahufis = $jumlahufis + $ufis;
                            } else if (($rowdata['tgl_lelang'] <= $tanggalemon) && ($rowdata['tgl_mulai'] <= $tanggalemon)
                                && ($rowdata['ren_ttdkontrak'] <= $tanggalemon) && ($rowdata['tanggal_kontrak'] <= $tanggalemon)
                                && ($rowdata['status'] == 'Sudah Penetapan Pemenang') && ($rowdata['tahapan_skrg'] == 'Tender Sudah Selesai')
                                && (!(strstr(strtoupper($rowdata['ket_lanjut']), 'TIDAK EFEKTIF')))
                            ) {
                                $jumlahpaket_terkontrak++;
                                $jumlahpagu_efisiensi_terkontrak = $jumlahpagu_efisiensi_terkontrak + $pagu_efisiensi;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $jumlahpagu_efisiensi_terkontrak_rm = $jumlahpagu_efisiensi_terkontrak_rm + $pagu_efisiensi;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $jumlahpagu_efisiensi_terkontrak_phln = $jumlahpagu_efisiensi_terkontrak_phln + $pagu_efisiensi;
                                } else {
                                    $jumlahpagu_efisiensi_terkontrak_sbsn = $jumlahpagu_efisiensi_terkontrak_sbsn + $pagu_efisiensi;
                                }

                                if ((float) $rowdata['nkon_rev'] > 0) {
                                    $nilai_kontrak = (float) $rowdata['nkon_rev'] / 1000;
                                } else {
                                    $nilai_kontrak = (float) $rowdata['nkon'] / 1000;
                                }
                                if ($nilai_kontrak > $pagu_efisiensi) {
                                    $nilai_kontrak = $pagu_efisiensi;
                                }
                                $nilai_terkontrak = $nilai_terkontrak + $nilai_kontrak;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $nilai_terkontrak_rm = $nilai_terkontrak_rm + $nilai_kontrak;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $nilai_terkontrak_phln = $nilai_terkontrak_phln + $nilai_kontrak;
                                } else {
                                    $nilai_terkontrak_sbsn = $nilai_terkontrak_sbsn + $nilai_kontrak;
                                }

                                $realisasi = (float) $rowdata['rtot'] / 1000;
                                if ($realisasi > $nilai_kontrak) {
                                    $realisasi = $nilai_kontrak;
                                }
                                $jumlahrealisasi = $jumlahrealisasi + $realisasi;

                                $ufis = (float) $rowdata['ufis'] / 1000;
                                if ($ufis > $nilai_kontrak) {
                                    $ufis = $nilai_kontrak;
                                }
                                $jumlahufis = $jumlahufis + $ufis;
                            } else if (($rowdata['tgl_lelang'] <= $tanggalemon) && ($rowdata['tgl_mulai'] <= $tanggalemon)
                                && ($rowdata['ren_ttdkontrak'] <= $tanggalemon) && ($rowdata['tanggal_kontrak'] <= $tanggalemon)
                                && ($rowdata['status'] == 'Sudah Penetapan Pemenang') && ($rowdata['tahapan_skrg'] == 'Berita Acara Hasil Pelelangan')
                                && (!(strstr(strtoupper($rowdata['ket_lanjut']), 'TIDAK EFEKTIF')))
                            ) {
                                $jumlahpaket_terkontrak++;
                                $jumlahpagu_efisiensi_terkontrak = $jumlahpagu_efisiensi_terkontrak + $pagu_efisiensi;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $jumlahpagu_efisiensi_terkontrak_rm = $jumlahpagu_efisiensi_terkontrak_rm + $pagu_efisiensi;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $jumlahpagu_efisiensi_terkontrak_phln = $jumlahpagu_efisiensi_terkontrak_phln + $pagu_efisiensi;
                                } else {
                                    $jumlahpagu_efisiensi_terkontrak_sbsn = $jumlahpagu_efisiensi_terkontrak_sbsn + $pagu_efisiensi;
                                }

                                if ((float) $rowdata['nkon_rev'] > 0) {
                                    $nilai_kontrak = (float) $rowdata['nkon_rev'] / 1000;
                                } else {
                                    $nilai_kontrak = (float) $rowdata['nkon'] / 1000;
                                }
                                if ($nilai_kontrak > $pagu_efisiensi) {
                                    $nilai_kontrak = $pagu_efisiensi;
                                }
                                $nilai_terkontrak = $nilai_terkontrak + $nilai_kontrak;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $nilai_terkontrak_rm = $nilai_terkontrak_rm + $nilai_kontrak;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $nilai_terkontrak_phln = $nilai_terkontrak_phln + $nilai_kontrak;
                                } else {
                                    $nilai_terkontrak_sbsn = $nilai_terkontrak_sbsn + $nilai_kontrak;
                                }

                                $realisasi = (float) $rowdata['rtot'] / 1000;
                                if ($realisasi > $nilai_kontrak) {
                                    $realisasi = $nilai_kontrak;
                                }
                                $jumlahrealisasi = $jumlahrealisasi + $realisasi;

                                $ufis = (float) $rowdata['ufis'] / 1000;
                                if ($ufis > $nilai_kontrak) {
                                    $ufis = $nilai_kontrak;
                                }
                                $jumlahufis = $jumlahufis + $ufis;
                            } else if (($rowdata['tgl_lelang'] <= $tanggalemon) && ($rowdata['tgl_mulai'] <= $tanggalemon)
                                && ($rowdata['ren_ttdkontrak'] <= $tanggalemon) && ($rowdata['jadwal_pengumuman'] == '') && ($rowdata['jadwal_pemenang'] == '')
                                && ($rowdata['jadwal_kontrak'] == '')  && ($rowdata['status'] == '') && ($rowdata['tahapan_skrg'] == '')
                            ) {
                                $jumlahpaket_terkontrak++;
                                $jumlahpagu_efisiensi_terkontrak = $jumlahpagu_efisiensi_terkontrak + $pagu_efisiensi;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $jumlahpagu_efisiensi_terkontrak_rm = $jumlahpagu_efisiensi_terkontrak_rm + $pagu_efisiensi;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $jumlahpagu_efisiensi_terkontrak_phln = $jumlahpagu_efisiensi_terkontrak_phln + $pagu_efisiensi;
                                } else {
                                    $jumlahpagu_efisiensi_terkontrak_sbsn = $jumlahpagu_efisiensi_terkontrak_sbsn + $pagu_efisiensi;
                                }

                                if ((float) $rowdata['nkon_rev'] > 0) {
                                    $nilai_kontrak = (float) $rowdata['nkon_rev'] / 1000;
                                } else {
                                    $nilai_kontrak = (float) $rowdata['nkon'] / 1000;
                                }
                                if ($nilai_kontrak > $pagu_efisiensi) {
                                    $nilai_kontrak = $pagu_efisiensi;
                                }
                                $nilai_terkontrak = $nilai_terkontrak + $nilai_kontrak;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $nilai_terkontrak_rm = $nilai_terkontrak_rm + $nilai_kontrak;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $nilai_terkontrak_phln = $nilai_terkontrak_phln + $nilai_kontrak;
                                } else {
                                    $nilai_terkontrak_sbsn = $nilai_terkontrak_sbsn + $nilai_kontrak;
                                }

                                $realisasi = (float) $rowdata['rtot'] / 1000;
                                if ($realisasi > $nilai_kontrak) {
                                    $realisasi = $nilai_kontrak;
                                }
                                $jumlahrealisasi = $jumlahrealisasi + $realisasi;

                                $ufis = (float) $rowdata['ufis'] / 1000;
                                if ($ufis > $nilai_kontrak) {
                                    $ufis = $nilai_kontrak;
                                }
                                $jumlahufis = $jumlahufis + $ufis;
                            } else if (($rowdata['tgl_lelang'] <= $tanggalemon) && ($rowdata['tgl_mulai'] <= $tanggalemon) && ($rowdata['tgl_selesai'] <= $tanggalemon)
                                && ($rowdata['ren_ttdkontrak'] <= $tanggalemon) && ($rowdata['jadwal_pengumuman'] == '--') && ($rowdata['jadwal_pemenang'] <= $tanggalemon)
                                && ($rowdata['jadwal_kontrak'] == '--')  && ($rowdata['status'] == 'Sudah Penetapan Pemenang') && ($rowdata['tahapan_skrg'] == 'Tender Sudah Selesai')
                            ) {
                                $jumlahpaket_terkontrak++;
                                $jumlahpagu_efisiensi_terkontrak = $jumlahpagu_efisiensi_terkontrak + $pagu_efisiensi;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $jumlahpagu_efisiensi_terkontrak_rm = $jumlahpagu_efisiensi_terkontrak_rm + $pagu_efisiensi;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $jumlahpagu_efisiensi_terkontrak_phln = $jumlahpagu_efisiensi_terkontrak_phln + $pagu_efisiensi;
                                } else {
                                    $jumlahpagu_efisiensi_terkontrak_sbsn = $jumlahpagu_efisiensi_terkontrak_sbsn + $pagu_efisiensi;
                                }

                                if ((float) $rowdata['nkon_rev'] > 0) {
                                    $nilai_kontrak = (float) $rowdata['nkon_rev'] / 1000;
                                } else {
                                    $nilai_kontrak = (float) $rowdata['nkon'] / 1000;
                                }
                                if ($nilai_kontrak > $pagu_efisiensi) {
                                    $nilai_kontrak = $pagu_efisiensi;
                                }
                                $nilai_terkontrak = $nilai_terkontrak + $nilai_kontrak;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $nilai_terkontrak_rm = $nilai_terkontrak_rm + $nilai_kontrak;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $nilai_terkontrak_phln = $nilai_terkontrak_phln + $nilai_kontrak;
                                } else {
                                    $nilai_terkontrak_sbsn = $nilai_terkontrak_sbsn + $nilai_kontrak;
                                }

                                $realisasi = (float) $rowdata['rtot'] / 1000;
                                if ($realisasi > $nilai_kontrak) {
                                    $realisasi = $nilai_kontrak;
                                }
                                $jumlahrealisasi = $jumlahrealisasi + $realisasi;

                                $ufis = (float) $rowdata['ufis'] / 1000;
                                if ($ufis > $nilai_kontrak) {
                                    $ufis = $nilai_kontrak;
                                }
                                $jumlahufis = $jumlahufis + $ufis;
                            } else if (($rowdata['tgl_lelang'] <= $tanggalemon) && ($rowdata['tgl_mulai'] <= $tanggalemon)
                                && ($rowdata['ren_ttdkontrak'] <= $tanggalemon) && ($rowdata['jadwal_pengumuman'] <= $tanggalemon) && ($rowdata['jadwal_pemenang'] <= $tanggalemon)
                                && ($rowdata['jadwal_kontrak'] <= $tanggalemon)  && ($rowdata['status'] == 'Sudah Penetapan Pemenang')  && ($rowdata['jadwal_kontrak'] <> '--')
                                && ($rowdata['tahapan_skrg'] <> 'Surat Penunjukan Penyedia Barang/Jasa') && (!(strstr(strtoupper($rowdata['ket_lanjut']), 'TIDAK EFEKTIF')))
                            ) {
                                $jumlahpaket_terkontrak++;
                                $jumlahpagu_efisiensi_terkontrak = $jumlahpagu_efisiensi_terkontrak + $pagu_efisiensi;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $jumlahpagu_efisiensi_terkontrak_rm = $jumlahpagu_efisiensi_terkontrak_rm + $pagu_efisiensi;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $jumlahpagu_efisiensi_terkontrak_phln = $jumlahpagu_efisiensi_terkontrak_phln + $pagu_efisiensi;
                                } else {
                                    $jumlahpagu_efisiensi_terkontrak_sbsn = $jumlahpagu_efisiensi_terkontrak_sbsn + $pagu_efisiensi;
                                }

                                if ((float) $rowdata['nkon_rev'] > 0) {
                                    $nilai_kontrak = (float) $rowdata['nkon_rev'] / 1000;
                                } else {
                                    $nilai_kontrak = (float) $rowdata['nkon'] / 1000;
                                }
                                if ($nilai_kontrak > $pagu_efisiensi) {
                                    $nilai_kontrak = $pagu_efisiensi;
                                }
                                $nilai_terkontrak = $nilai_terkontrak + $nilai_kontrak;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $nilai_terkontrak_rm = $nilai_terkontrak_rm + $nilai_kontrak;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $nilai_terkontrak_phln = $nilai_terkontrak_phln + $nilai_kontrak;
                                } else {
                                    $nilai_terkontrak_sbsn = $nilai_terkontrak_sbsn + $nilai_kontrak;
                                }

                                $realisasi = (float) $rowdata['rtot'] / 1000;
                                if ($realisasi > $nilai_kontrak) {
                                    $realisasi = $nilai_kontrak;
                                }
                                $jumlahrealisasi = $jumlahrealisasi + $realisasi;

                                $ufis = (float) $rowdata['ufis'] / 1000;
                                if ($ufis > $nilai_kontrak) {
                                    $ufis = $nilai_kontrak;
                                }
                                $jumlahufis = $jumlahufis + $ufis;
                            } else if (($rowdata['tgl_lelang'] <= $tanggalemon) && ($rowdata['tgl_mulai'] <= $tanggalemon)
                                && ($rowdata['ren_ttdkontrak'] <= $tanggalemon) && ($rowdata['jadwal_pengumuman'] <= $tanggalemon) && ($rowdata['jadwal_pemenang'] <= $tanggalemon)
                                && ($rowdata['status'] == 'Sudah Penetapan Pemenang') && ($rowdata['tahapan_skrg'] == 'Masa Sanggah')
                            ) {
                                $jumlahpaket_terkontrak++;
                                $jumlahpagu_efisiensi_terkontrak = $jumlahpagu_efisiensi_terkontrak + $pagu_efisiensi;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $jumlahpagu_efisiensi_terkontrak_rm = $jumlahpagu_efisiensi_terkontrak_rm + $pagu_efisiensi;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $jumlahpagu_efisiensi_terkontrak_phln = $jumlahpagu_efisiensi_terkontrak_phln + $pagu_efisiensi;
                                } else {
                                    $jumlahpagu_efisiensi_terkontrak_sbsn = $jumlahpagu_efisiensi_terkontrak_sbsn + $pagu_efisiensi;
                                }

                                if ((float) $rowdata['nkon_rev'] > 0) {
                                    $nilai_kontrak = (float) $rowdata['nkon_rev'] / 1000;
                                } else {
                                    $nilai_kontrak = (float) $rowdata['nkon'] / 1000;
                                }
                                if ($nilai_kontrak > $pagu_efisiensi) {
                                    $nilai_kontrak = $pagu_efisiensi;
                                }
                                $nilai_terkontrak = $nilai_terkontrak + $nilai_kontrak;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $nilai_terkontrak_rm = $nilai_terkontrak_rm + $nilai_kontrak;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $nilai_terkontrak_phln = $nilai_terkontrak_phln + $nilai_kontrak;
                                } else {
                                    $nilai_terkontrak_sbsn = $nilai_terkontrak_sbsn + $nilai_kontrak;
                                }

                                $realisasi = (float) $rowdata['rtot'] / 1000;
                                if ($realisasi > $nilai_kontrak) {
                                    $realisasi = $nilai_kontrak;
                                }
                                $jumlahrealisasi = $jumlahrealisasi + $realisasi;

                                $ufis = (float) $rowdata['ufis'] / 1000;
                                if ($ufis > $nilai_kontrak) {
                                    $ufis = $nilai_kontrak;
                                }
                                $jumlahufis = $jumlahufis + $ufis;
                            } else if (($rowdata['tgl_lelang'] <= $tanggalemon) && ($rowdata['tgl_mulai'] <= $tanggalemon)
                                && ($rowdata['ren_ttdkontrak'] <= $tanggalemon) && ($rowdata['jadwal_pengumuman'] <= $tanggalemon) && ($rowdata['jadwal_pemenang'] <= $tanggalemon)
                                && ($rowdata['status'] == 'Sudah Penetapan Pemenang') && ($rowdata['jadwal_kontrak'] <> '--')
                                && ($rowdata['tanggal_kontrak'] <> '') && ($rowdata['tahapan_skrg'] == '')
                            ) {
                                $jumlahpaket_terkontrak++;
                                $jumlahpagu_efisiensi_terkontrak = $jumlahpagu_efisiensi_terkontrak + $pagu_efisiensi;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $jumlahpagu_efisiensi_terkontrak_rm = $jumlahpagu_efisiensi_terkontrak_rm + $pagu_efisiensi;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $jumlahpagu_efisiensi_terkontrak_phln = $jumlahpagu_efisiensi_terkontrak_phln + $pagu_efisiensi;
                                } else {
                                    $jumlahpagu_efisiensi_terkontrak_sbsn = $jumlahpagu_efisiensi_terkontrak_sbsn + $pagu_efisiensi;
                                }

                                if ((float) $rowdata['nkon_rev'] > 0) {
                                    $nilai_kontrak = (float) $rowdata['nkon_rev'] / 1000;
                                } else {
                                    $nilai_kontrak = (float) $rowdata['nkon'] / 1000;
                                }
                                if ($nilai_kontrak > $pagu_efisiensi) {
                                    $nilai_kontrak = $pagu_efisiensi;
                                }
                                $nilai_terkontrak = $nilai_terkontrak + $nilai_kontrak;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $nilai_terkontrak_rm = $nilai_terkontrak_rm + $nilai_kontrak;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $nilai_terkontrak_phln = $nilai_terkontrak_phln + $nilai_kontrak;
                                } else {
                                    $nilai_terkontrak_sbsn = $nilai_terkontrak_sbsn + $nilai_kontrak;
                                }

                                $realisasi = (float) $rowdata['rtot'] / 1000;
                                if ($realisasi > $nilai_kontrak) {
                                    $realisasi = $nilai_kontrak;
                                }
                                $jumlahrealisasi = $jumlahrealisasi + $realisasi;

                                $ufis = (float) $rowdata['ufis'] / 1000;
                                if ($ufis > $nilai_kontrak) {
                                    $ufis = $nilai_kontrak;
                                }
                                $jumlahufis = $jumlahufis + $ufis;
                            } else if (($rowdata['tgl_lelang'] <= $tanggalemon) && ($rowdata['tgl_mulai'] <= $tanggalemon)
                                && ($rowdata['ren_ttdkontrak'] <= $tanggalemon) && ($rowdata['jadwal_pengumuman'] <= $tanggalemon) && ($rowdata['jadwal_pemenang'] <= $tanggalemon)
                                && ($rowdata['status'] == 'Sudah Penetapan Pemenang') && ($rowdata['jadwal_kontrak'] == '--')
                                && ($rowdata['tanggal_kontrak'] <= $tanggalemon) && ($rowdata['tahapan_skrg'] == '') && ($rowdata['kdspaket'] <> '')
                            ) {
                                $jumlahpaket_terkontrak++;
                                $jumlahpagu_efisiensi_terkontrak = $jumlahpagu_efisiensi_terkontrak + $pagu_efisiensi;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $jumlahpagu_efisiensi_terkontrak_rm = $jumlahpagu_efisiensi_terkontrak_rm + $pagu_efisiensi;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $jumlahpagu_efisiensi_terkontrak_phln = $jumlahpagu_efisiensi_terkontrak_phln + $pagu_efisiensi;
                                } else {
                                    $jumlahpagu_efisiensi_terkontrak_sbsn = $jumlahpagu_efisiensi_terkontrak_sbsn + $pagu_efisiensi;
                                }

                                if ((float) $rowdata['nkon_rev'] > 0) {
                                    $nilai_kontrak = (float) $rowdata['nkon_rev'] / 1000;
                                } else {
                                    $nilai_kontrak = (float) $rowdata['nkon'] / 1000;
                                }
                                if ($nilai_kontrak > $pagu_efisiensi) {
                                    $nilai_kontrak = $pagu_efisiensi;
                                }
                                $nilai_terkontrak = $nilai_terkontrak + $nilai_kontrak;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $nilai_terkontrak_rm = $nilai_terkontrak_rm + $nilai_kontrak;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $nilai_terkontrak_phln = $nilai_terkontrak_phln + $nilai_kontrak;
                                } else {
                                    $nilai_terkontrak_sbsn = $nilai_terkontrak_sbsn + $nilai_kontrak;
                                }

                                $realisasi = (float) $rowdata['rtot'] / 1000;
                                if ($realisasi > $nilai_kontrak) {
                                    $realisasi = $nilai_kontrak;
                                }
                                $jumlahrealisasi = $jumlahrealisasi + $realisasi;

                                $ufis = (float) $rowdata['ufis'] / 1000;
                                if ($ufis > $nilai_kontrak) {
                                    $ufis = $nilai_kontrak;
                                }
                                $jumlahufis = $jumlahufis + $ufis;
                            } else if (($rowdata['tgl_lelang'] <= $tanggalemon) && ($rowdata['tgl_mulai'] <= $tanggalemon)
                                && ($rowdata['ren_ttdkontrak'] = '--') && ($rowdata['jadwal_pengumuman'] <= $tanggalemon)
                                && ($rowdata['status'] == 'Sudah Penetapan Pemenang') && ($rowdata['tahapan_skrg'] == 'Penandatanganan Kontrak')
                            ) {
                                $jumlahpaket_terkontrak++;
                                $jumlahpagu_efisiensi_terkontrak = $jumlahpagu_efisiensi_terkontrak + $pagu_efisiensi;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $jumlahpagu_efisiensi_terkontrak_rm = $jumlahpagu_efisiensi_terkontrak_rm + $pagu_efisiensi;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $jumlahpagu_efisiensi_terkontrak_phln = $jumlahpagu_efisiensi_terkontrak_phln + $pagu_efisiensi;
                                } else {
                                    $jumlahpagu_efisiensi_terkontrak_sbsn = $jumlahpagu_efisiensi_terkontrak_sbsn + $pagu_efisiensi;
                                }

                                if ((float) $rowdata['nkon_rev'] > 0) {
                                    $nilai_kontrak = (float) $rowdata['nkon_rev'] / 1000;
                                } else {
                                    $nilai_kontrak = (float) $rowdata['nkon'] / 1000;
                                }
                                if ($nilai_kontrak > $pagu_efisiensi) {
                                    $nilai_kontrak = $pagu_efisiensi;
                                }
                                $nilai_terkontrak = $nilai_terkontrak + $nilai_kontrak;

                                if ((float) $rowdata['pgpln'] == 0 && (float) $rowdata['pgsbsn'] == 0) {
                                    $nilai_terkontrak_rm = $nilai_terkontrak_rm + $nilai_kontrak;
                                } else if ((float) $rowdata['pgpln'] > 0) {
                                    $nilai_terkontrak_phln = $nilai_terkontrak_phln + $nilai_kontrak;
                                } else {
                                    $nilai_terkontrak_sbsn = $nilai_terkontrak_sbsn + $nilai_kontrak;
                                }

                                $realisasi = (float) $rowdata['rtot'] / 1000;
                                if ($realisasi > $nilai_kontrak) {
                                    $realisasi = $nilai_kontrak;
                                }
                                $jumlahrealisasi = $jumlahrealisasi + $realisasi;

                                $ufis = (float) $rowdata['ufis'] / 1000;
                                if ($ufis > $nilai_kontrak) {
                                    $ufis = $nilai_kontrak;
                                }
                                $jumlahufis = $jumlahufis + $ufis;
                            }
                        }

                        $sisa_lelang_total = $jumlahpagu_efisiensi_terkontrak - $nilai_terkontrak;
                        $sisa_rm = $jumlahpagu_efisiensi_terkontrak_rm - $nilai_terkontrak_rm;
                        $sisa_phln = $jumlahpagu_efisiensi_terkontrak_phln - $nilai_terkontrak_phln;
                        $sisa_sbsn = $jumlahpagu_efisiensi_terkontrak_sbsn - $nilai_terkontrak_sbsn;
                        if ($nilai_terkontrak == 0) {
                            $persenkeu = 0;
                        } else {
                            $persenkeu = ($jumlahrealisasi / $nilai_terkontrak) * 100;
                        }
                        if ($nilai_terkontrak == 0) {
                            $persenfisik = 0;
                        } else {
                            $persenfisik = ($jumlahufis / $nilai_terkontrak) * 100;
                        }
                        $myArray[] = (object)
                        [
                            'urut' => $urut,
                            'idbalai' => $row['id'],
                            'kdbalai' => $row['kdbalai'],
                            'kdbalai_pendek' => $row['kdbalai_pendek'],
                            'kdbalai_old' => $row['kdbalai_old'],
                            'kdbalai_old_pendek' => $row['kdbalai_old_pendek'],
                            'nama' => $row['nama'],
                            'jumlah' => $jumlahpaket,
                            'jumlah_terkontrak' => $jumlahpaket_terkontrak,
                            'pagu' => $jumlahpagu_efisiensi,
                            'pagu_terkontrak' => $jumlahpagu_efisiensi_terkontrak,
                            'nilai_kontrak' => $nilai_terkontrak,
                            'sisa_lelang_total' => $sisa_lelang_total,
                            'sisa_rm' => $sisa_rm,
                            'sisa_phln' => $sisa_phln,
                            'sisa_sbsn' => $sisa_sbsn,
                            'realisasi' => $jumlahrealisasi,
                            'persenkeu' => $persenkeu,
                            'persenfisik' => $persenfisik,
                            'class'  => '',
                        ];
                        $jumlahtotalpaket = $jumlahtotalpaket + $jumlahpaket;
                        $jumlahtotalpaket_terkontrak = $jumlahtotalpaket_terkontrak + $jumlahpaket_terkontrak;
                        $jumlahTotalpagu_efisiensi = $jumlahTotalpagu_efisiensi + $jumlahpagu_efisiensi;
                        $jumlahTotalpagu_efisiensi_terkontrak = $jumlahTotalpagu_efisiensi_terkontrak + $jumlahpagu_efisiensi_terkontrak;
                        $jumlahTotalpagu_efisiensi_terkontrak_rm = $jumlahTotalpagu_efisiensi_terkontrak_rm + $jumlahpagu_efisiensi_terkontrak_rm;
                        $jumlahTotalpagu_efisiensi_terkontrak_phln = $jumlahTotalpagu_efisiensi_terkontrak_phln + $jumlahpagu_efisiensi_terkontrak_phln;
                        $jumlahTotalpagu_efisiensi_terkontrak_sbsn = $jumlahTotalpagu_efisiensi_terkontrak_sbsn + $jumlahpagu_efisiensi_terkontrak_sbsn;
                        $jumlahTotal_terkontrak = $jumlahTotal_terkontrak + $nilai_terkontrak;
                        $jumlahTotal_terkontrak_rm = $jumlahTotal_terkontrak_rm + $nilai_terkontrak_rm;
                        $jumlahTotal_terkontrak_phln = $jumlahTotal_terkontrak_phln + $nilai_terkontrak_phln;
                        $jumlahTotal_terkontrak_sbsn = $jumlahTotal_terkontrak_sbsn + $nilai_terkontrak_sbsn;
                        $jumlahTotal_pagu_rm = $jumlahTotal_pagu_rm + $pagu_rm;
                        $jumlahTotal_pagu_phln = $jumlahTotal_pagu_phln + $pagu_phln;
                        $jumlahTotal_pagu_sbsn = $jumlahTotal_pagu_sbsn + $pagu_sbsn;
                        $jumlahTotalRealisasi = $jumlahTotalRealisasi + $jumlahrealisasi;
                        $jumlahTotalFisik = $jumlahTotalFisik + $jumlahufis;
                        $urut++;
                    }
                }

                $JumlahTotalsisa_lelang_total = $jumlahTotalpagu_efisiensi_terkontrak - $jumlahTotal_terkontrak;
                $jumlahTotal_sisa_rm = $jumlahTotalpagu_efisiensi_terkontrak_rm - $jumlahTotal_terkontrak_rm;
                $jumlahTotal_sisa_phln = $jumlahTotalpagu_efisiensi_terkontrak_phln - $jumlahTotal_terkontrak_phln;
                $jumlahTotal_sisa_sbsn = $jumlahTotalpagu_efisiensi_terkontrak_sbsn - $jumlahTotal_terkontrak_sbsn;
                if ($jumlahTotal_terkontrak == 0) {
                    $persenkeuTotal = 0;
                } else {
                    $persenkeuTotal = ($jumlahTotalRealisasi / $jumlahTotal_terkontrak) * 100;
                }
                if ($jumlahTotal_terkontrak == 0) {
                    $persenfisikTotal = 0;
                } else {
                    $persenfisikTotal = ($jumlahTotalFisik / $jumlahTotal_terkontrak) * 100;
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
                    'jumlah' => $jumlahtotalpaket,
                    'jumlah_terkontrak' => $jumlahtotalpaket_terkontrak,
                    'pagu' => $jumlahTotalpagu_efisiensi,
                    'pagu_terkontrak' => $jumlahTotalpagu_efisiensi_terkontrak,
                    'nilai_kontrak' => $jumlahTotal_terkontrak,
                    'sisa_lelang_total' => $JumlahTotalsisa_lelang_total,
                    'sisa_rm' => $jumlahTotal_sisa_rm,
                    'sisa_phln' => $jumlahTotal_sisa_phln,
                    'sisa_sbsn' => $jumlahTotal_sisa_sbsn,
                    'realisasi' => $jumlahTotalRealisasi,
                    'persenkeu' => $persenkeuTotal,
                    'persenfisik' => $persenfisikTotal,
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

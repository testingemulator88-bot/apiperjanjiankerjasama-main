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
        $sqlpkaktif = "select id from master_pk where deleted = '0' and is_active = '1'";
        $resultpkaktif  = mysqli_query($link, $sqlpkaktif);
        while ($rowpkaktif  = mysqli_fetch_assoc($resultpkaktif )) {
            $pk_aktif = $rowpkaktif ['id'];
        }

        $pk_aktif = '3';
        $shortcutinput = '0';
        $sqlshortcut = "select shortcutinput from master_pk where deleted = '0' and id = '" . $pk_aktif . "'";
        $resultshortcut  = mysqli_query($link, $sqlshortcut);
        while ($rowshortcut  = mysqli_fetch_assoc($resultshortcut)) {
            $shortcutinput = $rowshortcut['shortcutinput'];
        }

        $sqlttd = "select ttd from master_pk where deleted = '0' and id = '" . $pk_aktif . "'";
        $resultttd  = mysqli_query($link, $sqlttd);
        while ($rowttd  = mysqli_fetch_assoc($resultttd)) {
            $ttdinput = $rowttd['ttd'];
        }
        $tahunsekarang = get_Isi_Field1('tahun','master_pk','id','3');
        $filterquery = "";
        $filterqueryverif = "";
        $tahun = fixup($_GET['tahun']);
        if ($tahun <> '') {
            $filterquery = $filterquery . " and a.tahun = '" . $tahun . "'";
            $filterqueryverif = $filterqueryverif . " and a.kode_tahun = '" . $tahun . "'";
        }

        if ($pk_aktif <> '') {
            $filterqueryverif = $filterqueryverif . " and a.jenis_pk = '" . $pk_aktif . "'";
        }

        $pelaksana = fixup($_GET['pelaksana']);
        if ($pelaksana <> '') {
            $filterquery = $filterquery . " and (pelaksana in ('" . $pelaksana . "') or pelaksana in (" . $pelaksana . ")) or pelaksana like ('%," . $pelaksana . ",%') or pelaksana like ('" . $pelaksana . ",%') or pelaksana like ('%," . $pelaksana . "') or pelaksana like ('%," . $pelaksana . ",%')";
        } else {
            $filterquery = $filterquery . " and a.pelaksana in ('---')";
            $filterqueryverif = $filterqueryverif . " and a.id_pelaksana in ('---')";
        }
        $kode_satker = fixup($_GET['kode_satker']);
        $myArray = array();

        $sqljumlahverif = "select id_parent,verif,verif2,verif3 from master_level_piu where id = '" . $pelaksana . "'";
        $resultjumlahverif = mysqli_query($link, $sqljumlahverif);
        while ($rowjumlahverif = mysqli_fetch_assoc($resultjumlahverif)) {
            $jumlahtotalverifikasi = (float) $rowjumlahverif['id_parent'];
            $verif = $rowjumlahverif['verif'];
            $verif2 = $rowjumlahverif['verif2'];
            $verif3 = $rowjumlahverif['verif3'];
        }
        //echo $sqljumlahverif;
        //die;

        $sqlverif = "select a.dari, a.kepada, a.kepada2, a.evaluasi, a.verifikasi_ke, a.hasil_verif,b.nama ,a.readed
        , a.status_ajuan, a.status_verifikasi,a.catatan,a.createddate,a.createdby,c.nama
        from tb_data_pk_verifikasi a
        left join master_verifikasi b
        on a.hasil_verif=b.id
        left join tb_user c
        on a.createdby=md5(c.id)
        where a.deleted='0'
        " . $filterqueryverif . " and a.kode_pelaksana = '" . $kode_satker . "' 
        and a.id_pelaksana = '" . $pelaksana . "' order by a.id DESC limit 1";

        //echo $sqlverif;
        //die;
        $myArrayverif = array();
        $resultverif = mysqli_query($link, $sqlverif);
        $numcekverif = mysqli_num_rows($resultverif);
        $disabled = false;
        if ($numcekverif > 0) {
            while ($rowverif = mysqli_fetch_assoc($resultverif)) {
                $myArrayverif = array();

                //echo $rowverif['status_verifikasi'];
                //die;
                if ($rowverif['status_verifikasi'] == 'Proses Verifikasi PK ke-1') {
                    $verifikasi_ke = 1;
                    $status_ajuan = $rowverif['status_ajuan'];
                    $disabled = true;
                } else if (strstr($rowverif['status_verifikasi'], 'Disetujui')) {
                    //echo $jumlahtotalverifikasi;
                    if ($jumlahtotalverifikasi > (float) $rowverif['verifikasi_ke']) {
                        $verifikasi_ke = (float) $rowverif['verifikasi_ke'] + 1;
                        $status_ajuan = "Ajukan Verifikasi PK ke-" . $verifikasi_ke;
                        $disabled = false;
                    } else {
                        $verifikasi_ke = (float) $rowverif['verifikasi_ke'];
                        $status_ajuan = "Verifikasi Selesai";
                        $disabled = true;
                    }
                } else if (strstr($rowverif['status_verifikasi'], 'Proses Verifikasi PK ke-2')) {
                    if ($jumlahtotalverifikasi > (float) $rowverif['verifikasi_ke']) {
                        $verifikasi_ke = (float) $rowverif['verifikasi_ke'] + 1;
                        $status_ajuan = "Ajukan Verifikasi PK ke-" . $verifikasi_ke;
                        $disabled = false;
                    } else {
                        $verifikasi_ke = (float) $rowverif['verifikasi_ke'];
                        $status_ajuan = "Tahapan Verifikasi PK-2";
                        $disabled = true;
                    }
                } else if (strstr($rowverif['status_verifikasi'], 'Revisi Pengajuan PK ke-')) {
                    if ($jumlahtotalverifikasi > (float) $rowverif['verifikasi_ke']) {
                        $verifikasi_ke = (float) $rowverif['verifikasi_ke'];
                        $status_ajuan = "Ajukan Verifikasi PK ke-" . $verifikasi_ke;
                        $disabled = false;
                    } else {
                        $verifikasi_ke = (float) $rowverif['verifikasi_ke'];
                        $status_ajuan = "Ajukan Verifikasi PK ke-" . $verifikasi_ke;
                        $disabled = false;
                    }
                } else {
                    $disabled = false;
                }


                $myArrayverif[] = (object)
                [
                    'verifikasi_ke' => $verifikasi_ke,
                    'hasil_verif' => (float) $rowverif['hasil_verif'],
                    'status_ajuan' => ' ' . $status_ajuan,
                    'status_verifikasi' => ' ' . $rowverif['status_verifikasi'],
                    'dari' => (float) $rowverif['dari'],
                    'kepada' => (float) $rowverif['kepada'],
                    'kepada2' => (float) $rowverif['kepada2'],
                    'evaluasi' => (float) $rowverif['evaluasi'],
                    'catatan' => base64_encode($rowverif['catatan']),
                    'createddate' => $rowverif['createddate'],
                    'verif' => $verif,
                    'verif2' => $verif2,
                    'verif3' => $verif3,
                    'nama' => $rowverif['nama'],
                    'jumlahtotalverifikasi' => (float) $jumlahtotalverifikasi,
                    'kode_satker' => $kode_satker,
                    'disabled' => $disabled,
                ];
            }
        } else {
            $myArrayverif = array();
            $myArrayverif[] = (object)
            [
                'verifikasi_ke' => 1,
                'hasil_verif' => 0,
                'status_ajuan' => ' ' . 'Belum Verifikasi',
                'status_verifikasi' => ' ' . 'Ajukan Verifikasi PK Ke 1',
                'dari' => (float) $verif,
                'kepada' => (float) $pelaksana,
                'kepada2' => '',
                'verif' => $verif,
                'verif2' => $verif2,
                'verif3' => $verif3,
                'jumlahtotalverifikasi' => (float) $jumlahtotalverifikasi,
                'kode_satker' => $kode_satker,
                'disabled' => false,
            ];
        }


        // IKSK
        $sql = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
        ,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
        ,IF(a.satuan = '0', null, a.satuan) as satuan
        ,IF(a.output = '0', null, a.output) as output
        ,IF(a.outcome = '0', null, a.outcome) as outcome
        , (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
        , (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
        , (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
        , (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
        ,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
        , a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
        ,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
        and zz.kode_satker='" . $kode_satker . "') as terpilih
        ,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
        IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
        IF(a.level = 'ISP', 'levelsubsubkegiatan', 
        IF(a.level = 'KEGIATAN', 'levelpaket', 
        IF(a.level = 'SK', 'levelpekerjaan', 
        IF(a.level = 'IKSK', 'levelakhir', 
        IF(a.level = 'KRO', 'levelakhir', 
        IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
        from tb_indikator_akhir a 
        left join master_unor b
        on a.kdunor = b.id
        where a.deleted='0' and a.level = 'IKSK'
        " . $filterquery . "
        order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";

        //echo $sql;
        //die();
        $result = mysqli_query($link, $sql);
        $num = mysqli_num_rows($result);
        if ($num > 0) {
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $belakangkoma = (float) $row['belakangkoma'];
                    $baseline = 0;
                    $target = 0;
                    $target_komponen = 0;
                    $target_tahun_berjalan = 0;
                    $target_komponen_tahun_berjalan = 0;
                    $skor_komponen_a = 0;
                    $skor_komponen_b = 0;
                    $skor_komponen_c = 0;
                    $output_tahun_berjalan = 0;
                    $output_komponen_tahun_berjalan = 0;
                    $skor_komponen_a_tahun_berjalan = 0;
                    $skor_komponen_b_tahun_berjalan = 0;
                    $skor_komponen_c_tahun_berjalan = 0;
                    $capaian = 0;
                    $capaian_outcome = 0;
                    $sqlbaseline = "select baseline
                    from tb_data_baseline_akhir
                    where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
                    and id_indikator = '" . $row['id'] . "'";
                    //echo $sqlbaseline;
                    //die;
                    $resultbaseline = mysqli_query($link, $sqlbaseline);
                    $numbaseline = mysqli_num_rows($resultbaseline);
                    if ($numbaseline > 0) {
                        while ($rowbaseline = mysqli_fetch_assoc($resultbaseline)) {
                            $baseline = (float) $rowbaseline['baseline'];
                        }
                    }
                    $sqlskor = "select sum(target) as target
                    from tb_data_pk_akhir
                    where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
                    and id_indikator = '" . $row['id'] . "' and tahun = '" . $tahunsekarang . "'";
                    //echo $sqlskor;
                    $resultskor = mysqli_query($link, $sqlskor);
                    $numskor = mysqli_num_rows($resultskor);
                    if ($numskor > 0) {
                        while ($rowskor = mysqli_fetch_assoc($resultskor)) {
                            $target = (float) $rowskor['target'] + $baseline;
                        }
                    }

                    $sqlskortahunsekarang = "select sum(target) as target
                    from tb_data_pk_akhir
                    where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
                    and id_indikator = '" . $row['id'] . "' and tahun = '" . $tahunsekarang . "'";
                    //echo $sqlskortahunsekarang;
                    $resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
                    $numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
                    if ($numskortahunsekarang > 0) {
                        while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
                            $target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
                        }
                    }

                    $sqloutputtahunsekarang = "select sum(volume) as volume
                    from tb_data_pk_akhir
                    where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
                    and id_indikator = '" . $row['id'] . "' and tahun = '" . $tahunsekarang . "'";
                    //echo $sqloutputtahunsekarang;
                    $resultoutputtahunsekarang = mysqli_query($link, $sqloutputtahunsekarang);
                    $numoutputtahunsekarang = mysqli_num_rows($resultoutputtahunsekarang);
                    if ($numoutputtahunsekarang > 0) {
                        while ($rowoutputtahunsekarang = mysqli_fetch_assoc($resultoutputtahunsekarang)) {
                            $output_tahun_berjalan = (float) $rowoutputtahunsekarang['volume'];
                        }
                    }


                    $sqlcapaiantahunsekarang = "select sum(capaian) as capaian
                    from tb_data_pk_akhir
                    where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
                    and id_indikator = '" . $row['id'] . "' and tahun = '" . $tahunsekarang . "'";
                    //echo $sqlcapaiantahunsekarang;
                    $resultcapaiantahunsekarang = mysqli_query($link, $sqlcapaiantahunsekarang);
                    $numcapaiantahunsekarang = mysqli_num_rows($resultcapaiantahunsekarang);
                    if ($numcapaiantahunsekarang > 0) {
                        while ($rowcapaiantahunsekarang = mysqli_fetch_assoc($resultcapaiantahunsekarang)) {
                            $capaian = (float) $rowcapaiantahunsekarang['capaian'];
                        }
                    }

                    $sqlcapaian_outcometahunsekarang = "select sum(capaian_outcome) as capaian_outcome
                    from tb_data_pk_akhir
                    where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
                    and id_indikator = '" . $row['id'] . "' and tahun = '" . $tahunsekarang . "'";
                    //echo $sqlcapaian_outcometahunsekarang;
                    $resultcapaian_outcometahunsekarang = mysqli_query($link, $sqlcapaian_outcometahunsekarang);
                    $numcapaian_outcometahunsekarang = mysqli_num_rows($resultcapaian_outcometahunsekarang);
                    if ($numcapaian_outcometahunsekarang > 0) {
                        while ($rowcapaian_outcometahunsekarang = mysqli_fetch_assoc($resultcapaian_outcometahunsekarang)) {
                            $capaian_outcome = (float) $rowcapaian_outcometahunsekarang['capaian_outcome'];
                        }
                    }

                    //echo $row['hitungan_pk'];
                    //die;

                    if ($row['hitungan_pk'] == '2' || $row['hitungan_pk'] == '3') {
                        $sqlskor = "select nilai,rumus
                        from tb_data_pk_akhir
                        where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
                        and tahun = '" . $tahunsekarang . "'
                        and id_indikator = '" . $row['id'] . "'";
                        //echo $sqlskor;
                        //die;
                        $resultskor = mysqli_query($link, $sqlskor);
                        $numskor = mysqli_num_rows($resultskor);
                        if ($numskor > 0) {
                            while ($rowskor = mysqli_fetch_assoc($resultskor)) {
                                $nilai = $rowskor['nilai'];
                                $rumus = $rowskor['rumus'];
                                $tempnilai = explode("|", $nilai);

                                for ($s = 0; $s < 10; $s++) {
                                    $rumus = str_replace("input" . $s, (float) ($tempnilai[$s]), $rumus);
                                }
                                //echo $rumus;
                                for ($s = 0; $s < 10; $s++) {
                                    $rumus = str_replace("nilai" . $s, (float) ($tempnilai[$s]), $rumus);
                                }

                                for ($s = 10; $s < 21; $s++) {
                                    $rumus = str_replace("entry" . $s, (float) ($tempnilai[$s]), $rumus);
                                }

                                for ($s = 10; $s < 21; $s++) {
                                    $rumus = str_replace("rumus" . $s, (float) ($tempnilai[$s]), $rumus);
                                }


                                //echo $rumus."<br>";
                                $rumus = str_replace("||", "|0|", $rumus);
                                $tempnilairumus = explode("|", $rumus);
                                $finalrumus = $tempnilairumus[count($tempnilairumus) - 1];

                                //$finalrumus = str_replace("/0", "*0", $finalrumus);
                                $tempformula_string5 = explode("/", $finalrumus);
                                for ($s = 0; $s < count($tempformula_string5); $s++) {
                                    if ((float) $tempformula_string5[$s] == 0) {
                                        $finalrumus = str_replace("0/0", "0", $finalrumus);
                                        $finalrumus = str_replace("0/(0+0)", "0", $finalrumus);
                                        $finalrumus = str_replace("/0", "*0", $finalrumus);
                                    }
                                }

                                //echo $finalrumus . "<br>" . $row['id'] . "<aa>" . $rumus . "<bb>";
                                //die;
                                try {
                                    eval('$nilaisekarang = ' . $finalrumus . ';');
                                } catch (DivisionByZeroError $e) {
                                    eval('$nilaisekarang = 0;');
                                } catch (ParseError $e) {
                                    eval('$nilaisekarang = 0;');
                                }
                                //die;
                            }
                            $target = $nilaisekarang;
                            $output_tahun_berjalan = $nilaisekarang;
                            $target_tahun_berjalan = $nilaisekarang;
                        } else {
                            $target = 0;
                            $output_tahun_berjalan = 0;
                            $target_tahun_berjalan = 0;
                        }
                    } else {
                        $target = $target;
                        $output_tahun_berjalan = $output_tahun_berjalan;
                        $target_tahun_berjalan = $target_tahun_berjalan;
                    }
                    //ISPSK
                    $sqllevelISPSK = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
                    ,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
                    ,IF(a.satuan = '0', null, a.satuan) as satuan
                    ,IF(a.output = '0', null, a.output) as output
                    ,IF(a.outcome = '0', null, a.outcome) as outcome
                    , (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
                    , (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
                    , (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
                    , (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
                    ,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
                    , a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
                    ,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
                    and zz.kode_satker='" . $kode_satker . "') as terpilih
                    ,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
                    IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
                    IF(a.level = 'ISP', 'levelsubsubkegiatan', 
                    IF(a.level = 'KEGIATAN', 'levelpaket', 
                    IF(a.level = 'SK', 'levelpekerjaan', 
                    IF(a.level = 'IKSK', 'levelakhir', 
                    IF(a.level = 'KRO', 'levelakhir', 
                    IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
                    from tb_indikator_akhir a 
                    left join master_unor b
                    on a.kdunor = b.id
                    where a.deleted='0' and a.id = '" . $row['id_parent'] . "'
                    order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
                    //echo $sqllevelISPSK;
                    //die;
                    $resultlevelISPSK = mysqli_query($link, $sqllevelISPSK);
                    while ($rowlevelISPSK = mysqli_fetch_assoc($resultlevelISPSK)) {
                        // KEGIATAN
                        $sqllevelKEGIATAN = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
                        ,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
                        ,IF(a.satuan = '0', null, a.satuan) as satuan
                        ,IF(a.output = '0', null, a.output) as output
                        ,IF(a.outcome = '0', null, a.outcome) as outcome
                        , (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
                        , (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
                        , (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
                        , (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
                        ,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
                        , a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
                        ,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
                        and zz.kode_satker='" . $kode_satker . "') as terpilih
                        ,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
                        IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
                        IF(a.level = 'ISP', 'levelsubsubkegiatan', 
                        IF(a.level = 'KEGIATAN', 'levelpaket', 
                        IF(a.level = 'SK', 'levelpekerjaan', 
                        IF(a.level = 'IKSK', 'levelakhir', 
                        IF(a.level = 'KRO', 'levelakhir', 
                        IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
                        from tb_indikator_akhir a 
                        left join master_unor b
                        on a.kdunor = b.id
                        where a.deleted='0' and a.id = '" . $rowlevelISPSK['id_parent'] . "'
                        order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
                        //echo $sqllevelKEGIATAN;
                        //die;
                        $resultlevelKEGIATAN = mysqli_query($link, $sqllevelKEGIATAN);
                        while ($rowlevelKEGIATAN = mysqli_fetch_assoc($resultlevelKEGIATAN)) {
                            // PROGRAM
                            $sqllevelSASARANPROGRAM = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
                            ,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
                            ,IF(a.satuan = '0', null, a.satuan) as satuan
                            ,IF(a.output = '0', null, a.output) as output
                            ,IF(a.outcome = '0', null, a.outcome) as outcome
                            , (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
                            , (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
                            , (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
                            , (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
                            ,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
                            , a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
                            ,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
                            and zz.kode_satker='" . $kode_satker . "') as terpilih
                            ,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
                            IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
                            IF(a.level = 'ISP', 'levelsubsubkegiatan', 
                            IF(a.level = 'KEGIATAN', 'levelpaket', 
                            IF(a.level = 'SK', 'levelpekerjaan', 
                            IF(a.level = 'IKSK', 'levelakhir', 
                            IF(a.level = 'KRO', 'levelakhir', 
                            IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
                            from tb_indikator_akhir a 
                            left join master_unor b
                            on a.kdunor = b.id
                            where a.deleted='0' and a.id = '" . $rowlevelKEGIATAN['id_parent'] . "'
                            order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
                            //\\\\\echo $sqllevelPROGRAM;
                            //die;
                            $resultlevelSASARANPROGRAM = mysqli_query($link, $sqllevelSASARANPROGRAM);
                            while ($rowlevelSASARANPROGRAM = mysqli_fetch_assoc($resultlevelSASARANPROGRAM)) {

                                $sqllevelPROGRAM = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
                                ,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
                                ,IF(a.satuan = '0', null, a.satuan) as satuan
                                ,IF(a.output = '0', null, a.output) as output
                                ,IF(a.outcome = '0', null, a.outcome) as outcome
                                , (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
                                , (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
                                , (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
                                , (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
                                ,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
                                , a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
                                ,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
                                and zz.kode_satker='" . $kode_satker . "') as terpilih
                                ,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
                                IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
                                IF(a.level = 'ISP', 'levelsubsubkegiatan', 
                                IF(a.level = 'KEGIATAN', 'levelpaket', 
                                IF(a.level = 'SK', 'levelpekerjaan', 
                                IF(a.level = 'IKSK', 'levelakhir', 
                                IF(a.level = 'KRO', 'levelakhir', 
                                IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
                                from tb_indikator_akhir a 
                                left join master_unor b
                                on a.kdunor = b.id
                                where a.deleted='0' and a.id = '" . $rowlevelSASARANPROGRAM['id_parent'] . "'
                                order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
                                $resultlevelPROGRAM = mysqli_query($link, $sqllevelPROGRAM);
                                while ($rowlevelPROGRAM = mysqli_fetch_assoc($resultlevelPROGRAM)) {
                                    $searchValue = (float) $rowlevelPROGRAM['id'];
                                    $found = false;
                                    foreach ($myArray as $obj) {
                                        if (isset($obj->id) && $obj->id === $searchValue) {
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if (!$found) {
                                        $myArray[] = (object)
                                        [
                                            'id' => (float) $rowlevelPROGRAM['id'],
                                            'tahun' => (float) $rowlevelPROGRAM['tahun'],
                                            'id_parent' => $rowlevelPROGRAM['id_parent'],
                                            'kdunor' => $rowlevelPROGRAM['kdunor'],
                                            'nama_unor' => $rowlevelPROGRAM['nama_unor'],
                                            'urutlevel' => (float) $rowlevelPROGRAM['urutlevel'],
                                            'level' => $rowlevelPROGRAM['level'],
                                            'kode' => $rowlevelPROGRAM['kode'],
                                            'kode_unique' => $rowlevelPROGRAM['kode_unique'],
                                            'textindikator' => $rowlevelPROGRAM['textindikator'],
                                            'satuan' => $rowlevelPROGRAM['satuan'],
                                            'namasatuan' => $rowlevelPROGRAM['namasatuan'],
                                            'output' => $rowlevelPROGRAM['output'],
                                            'namaoutput' => $rowlevelPROGRAM['namaoutput'],
                                            'outcome' => $rowlevelPROGRAM['outcome'],
                                            'namaoutcome' => $rowlevelPROGRAM['namaoutcome'],
                                            'penanggungjawab' => $rowlevelPROGRAM['penanggungjawab'],
                                            'pelaksana' => $rowlevelPROGRAM['pelaksana'],
                                            'pelaksanacode' => $rowlevelPROGRAM['pelaksanacode'],
                                            'pelaksanalabel' => $rowlevelPROGRAM['pelaksanalabel'],
                                            'target' => $rowlevelPROGRAM['target'],
                                            'urut' => (float) $rowlevelPROGRAM['urut'],
                                            'hitungan_pk' => $rowlevelPROGRAM['hitungan_pk'],
                                            'namahitungan_pk' => $rowlevelPROGRAM['namahitungan_pk'],
                                            'jumlahkomponen' => $rowlevelPROGRAM['jumlahkomponen'],
                                            'kolomkomponen' => $rowlevelPROGRAM['kolomkomponen'],
                                            'bobotkomponen' => $rowlevelPROGRAM['bobotkomponen'],
                                            'rumuskomponen' => $rowlevelPROGRAM['rumuskomponen'],
                                            'terpilih' => $rowlevelPROGRAM['terpilih'],
                                            'targetkumulatif' => 0,
                                            'output_tahun_berjalan' => 0,
                                            'target_tahun_berjalan' => 0,
                                            'class' => $rowlevelPROGRAM['class'],
                                        ];
                                    }
                                }

                                $searchValue = (float) $rowlevelSASARANPROGRAM['id'];
                                $found = false;
                                foreach ($myArray as $obj) {
                                    if (isset($obj->id) && $obj->id === $searchValue) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $myArray[] = (object)
                                    [
                                        'id' => (float) $rowlevelSASARANPROGRAM['id'],
                                        'tahun' => (float) $rowlevelSASARANPROGRAM['tahun'],
                                        'id_parent' => $rowlevelSASARANPROGRAM['id_parent'],
                                        'kdunor' => $rowlevelSASARANPROGRAM['kdunor'],
                                        'nama_unor' => $rowlevelSASARANPROGRAM['nama_unor'],
                                        'urutlevel' => (float) $rowlevelSASARANPROGRAM['urutlevel'],
                                        'level' => $rowlevelSASARANPROGRAM['level'],
                                        'kode' => $rowlevelSASARANPROGRAM['kode'],
                                        'kode_unique' => $rowlevelSASARANPROGRAM['kode_unique'],
                                        'textindikator' => $rowlevelSASARANPROGRAM['textindikator'],
                                        'satuan' => $rowlevelSASARANPROGRAM['satuan'],
                                        'namasatuan' => $rowlevelSASARANPROGRAM['namasatuan'],
                                        'output' => $rowlevelSASARANPROGRAM['output'],
                                        'namaoutput' => $rowlevelSASARANPROGRAM['namaoutput'],
                                        'outcome' => $rowlevelSASARANPROGRAM['outcome'],
                                        'namaoutcome' => $rowlevelSASARANPROGRAM['namaoutcome'],
                                        'penanggungjawab' => $rowlevelSASARANPROGRAM['penanggungjawab'],
                                        'pelaksana' => $rowlevelSASARANPROGRAM['pelaksana'],
                                        'pelaksanacode' => $rowlevelSASARANPROGRAM['pelaksanacode'],
                                        'pelaksanalabel' => $rowlevelSASARANPROGRAM['pelaksanalabel'],
                                        'target' => $rowlevelSASARANPROGRAM['target'],
                                        'urut' => (float) $rowlevelSASARANPROGRAM['urut'],
                                        'hitungan_pk' => $rowlevelSASARANPROGRAM['hitungan_pk'],
                                        'namahitungan_pk' => $rowlevelSASARANPROGRAM['namahitungan_pk'],
                                        'jumlahkomponen' => $rowlevelSASARANPROGRAM['jumlahkomponen'],
                                        'kolomkomponen' => $rowlevelSASARANPROGRAM['kolomkomponen'],
                                        'bobotkomponen' => $rowlevelSASARANPROGRAM['bobotkomponen'],
                                        'rumuskomponen' => $rowlevelSASARANPROGRAM['rumuskomponen'],
                                        'terpilih' => $rowlevelSASARANPROGRAM['terpilih'],
                                        'targetkumulatif' => 0,
                                        'output_tahun_berjalan' => 0,
                                        'target_tahun_berjalan' => 0,
                                        'class' => $rowlevelSASARANPROGRAM['class'],
                                    ];
                                }
                            }
                            // PROGRAM
                            $searchValue = (float) $rowlevelKEGIATAN['id'];
                            $found = false;
                            foreach ($myArray as $obj) {
                                if (isset($obj->id) && $obj->id === $searchValue) {
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) {
                                $myArray[] = (object)
                                [
                                    'id' => (float) $rowlevelKEGIATAN['id'],
                                    'tahun' => (float) $rowlevelKEGIATAN['tahun'],
                                    'id_parent' => $rowlevelKEGIATAN['id_parent'],
                                    'kdunor' => $rowlevelKEGIATAN['kdunor'],
                                    'nama_unor' => $rowlevelKEGIATAN['nama_unor'],
                                    'urutlevel' => (float) $rowlevelKEGIATAN['urutlevel'],
                                    'level' => $rowlevelKEGIATAN['level'],
                                    'kode' => $rowlevelKEGIATAN['kode'],
                                    'kode_unique' => $rowlevelKEGIATAN['kode_unique'],
                                    'textindikator' => $rowlevelKEGIATAN['textindikator'],
                                    'satuan' => $rowlevelKEGIATAN['satuan'],
                                    'namasatuan' => $rowlevelKEGIATAN['namasatuan'],
                                    'output' => $rowlevelKEGIATAN['output'],
                                    'namaoutput' => $rowlevelKEGIATAN['namaoutput'],
                                    'outcome' => $rowlevelKEGIATAN['outcome'],
                                    'namaoutcome' => $rowlevelKEGIATAN['namaoutcome'],
                                    'penanggungjawab' => $rowlevelKEGIATAN['penanggungjawab'],
                                    'pelaksana' => $rowlevelKEGIATAN['pelaksana'],
                                    'pelaksanacode' => $rowlevelKEGIATAN['pelaksanacode'],
                                    'pelaksanalabel' => $rowlevelKEGIATAN['pelaksanalabel'],
                                    'target' => $rowlevelKEGIATAN['target'],
                                    'urut' => (float) $rowlevelKEGIATAN['urut'],
                                    'hitungan_pk' => $rowlevelKEGIATAN['hitungan_pk'],
                                    'namahitungan_pk' => $rowlevelKEGIATAN['namahitungan_pk'],
                                    'jumlahkomponen' => $rowlevelKEGIATAN['jumlahkomponen'],
                                    'kolomkomponen' => $rowlevelKEGIATAN['kolomkomponen'],
                                    'bobotkomponen' => $rowlevelKEGIATAN['bobotkomponen'],
                                    'rumuskomponen' => $rowlevelKEGIATAN['rumuskomponen'],
                                    'terpilih' => $rowlevelKEGIATAN['terpilih'],
                                    'targetkumulatif' => 0,
                                    'output_tahun_berjalan' => 0,
                                    'target_tahun_berjalan' => 0,
                                    'class' => $rowlevelKEGIATAN['class'],
                                ];
                            }
                        }
                        // KEGIATAN

                        $searchValue = (float) $rowlevelISPSK['id'];
                        $found = false;
                        foreach ($myArray as $obj) {
                            if (isset($obj->id) && $obj->id === $searchValue) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $myArray[] = (object)
                            [
                                'id' => (float) $rowlevelISPSK['id'],
                                'tahun' => (float) $rowlevelISPSK['tahun'],
                                'id_parent' => $rowlevelISPSK['id_parent'],
                                'kdunor' => $rowlevelISPSK['kdunor'],
                                'nama_unor' => $rowlevelISPSK['nama_unor'],
                                'urutlevel' => (float) $rowlevelISPSK['urutlevel'],
                                'level' => $rowlevelISPSK['level'],
                                'kode' => $rowlevelISPSK['kode'],
                                'kode_unique' => $rowlevelISPSK['kode_unique'],
                                'textindikator' => $rowlevelISPSK['textindikator'],
                                'satuan' => $rowlevelISPSK['satuan'],
                                'namasatuan' => $rowlevelISPSK['namasatuan'],
                                'output' => $rowlevelISPSK['output'],
                                'namaoutput' => $rowlevelISPSK['namaoutput'],
                                'outcome' => $rowlevelISPSK['outcome'],
                                'namaoutcome' => $rowlevelISPSK['namaoutcome'],
                                'penanggungjawab' => $rowlevelISPSK['penanggungjawab'],
                                'pelaksana' => $rowlevelISPSK['pelaksana'],
                                'pelaksanacode' => $rowlevelISPSK['pelaksanacode'],
                                'pelaksanalabel' => $rowlevelISPSK['pelaksanalabel'],
                                'target' => $rowlevelISPSK['target'],
                                'urut' => (float) $rowlevelISPSK['urut'],
                                'hitungan_pk' => $rowlevelISPSK['hitungan_pk'],
                                'namahitungan_pk' => $rowlevelISPSK['namahitungan_pk'],
                                'jumlahkomponen' => $rowlevelISPSK['jumlahkomponen'],
                                'kolomkomponen' => $rowlevelISPSK['kolomkomponen'],
                                'bobotkomponen' => $rowlevelISPSK['bobotkomponen'],
                                'rumuskomponen' => $rowlevelISPSK['rumuskomponen'],
                                'terpilih' => $rowlevelISPSK['terpilih'],
                                'targetkumulatif' => 0,
                                'output_tahun_berjalan' => 0,
                                'target_tahun_berjalan' => 0,
                                'class' => $rowlevelISPSK['class'],
                            ];
                        }
                    }
                    //ISPSK
                    if ($row['hitungan_pk'] == '1') {
                        $target = number_format($target, $belakangkoma, ",", ".");
                        $output_tahun_berjalan = number_format($output_tahun_berjalan, $belakangkoma, ",", ".");
                        $target_tahun_berjalan = number_format($target_tahun_berjalan, $belakangkoma, ",", ".");
                    } else {
                        $target = number_format($target, $belakangkoma, ",", ".");
                        $output_tahun_berjalan = number_format($output_tahun_berjalan, $belakangkoma, ",", ".");
                        $target_tahun_berjalan = number_format($target_tahun_berjalan, $belakangkoma, ",", ".");
                    }

                    $capaian = number_format($capaian, $belakangkoma, ",", ".");
                    $capaian_outcome = number_format($capaian_outcome, $belakangkoma, ",", ".");

                    $myArray[] = (object)
                    [
                        'id' => (float) $row['id'],
                        'tahun' => (float) $row['tahun'],
                        'id_parent' => $row['id_parent'],
                        'kdunor' => $row['kdunor'],
                        'nama_unor' => $row['nama_unor'],
                        'urutlevel' => (float) $row['urutlevel'],
                        'level' => $row['level'],
                        'kode' => $row['kode'],
                        'kode_unique' => $row['kode_unique'],
                        'textindikator' => $row['textindikator'],
                        'satuan' => $row['satuan'],
                        'namasatuan' => $row['namasatuan'],
                        'output' => $row['output'],
                        'namaoutput' => $row['namaoutput'],
                        'outcome' => $row['outcome'],
                        'namaoutcome' => $row['namaoutcome'],
                        'penanggungjawab' => $row['penanggungjawab'],
                        'pelaksana' => $row['pelaksana'],
                        'pelaksanacode' => $row['pelaksanacode'],
                        'pelaksanalabel' => $row['pelaksanalabel'],
                        'target' => $row['target'],
                        'urut' => (float) $row['urut'],
                        'hitungan_pk' => $row['hitungan_pk'],
                        'namahitungan_pk' => $row['namahitungan_pk'],
                        'jumlahkomponen' => $row['jumlahkomponen'],
                        'kolomkomponen' => $row['kolomkomponen'],
                        'bobotkomponen' => $row['bobotkomponen'],
                        'rumuskomponen' => $row['rumuskomponen'],
                        'terpilih' => $row['terpilih'],
                        'targetkumulatif' => $target,
                        'output_tahun_berjalan' => $output_tahun_berjalan,
                        'target_tahun_berjalan' => $target_tahun_berjalan,
                        'capaian' => $capaian,
                        'capaian_outcome' => $capaian_outcome,
                        'class' => $row['class'],
                    ];
                }
                $response         = [];
                $response['shortcutinput'] = $shortcutinput;
                $response['ttd'] = $ttdinput;
                $response['pk_aktif'] = $pk_aktif;
                $response['verif'] = $myArrayverif;
                $response['data'] =  $myArray;
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

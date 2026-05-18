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
        $filterquerybalai = "";
        $filterquerysatker = "";
        $id_indikator = fixup($_GET['id_indikator']);
        $kode_satker = fixup($_GET['kode_satker']);
        $kategorisatker = fixup($_GET['kategorisatker']);
        $kode_satkeremon = "";
        $id_satker = "";
        $sqlsatkeremon = "select kode_satker_pendek,kode_satker_old_pendek,level_piu from master_satker where kode_satker = '" . $kode_satker . "'";
        $resultsatkeremon = mysqli_query($link, $sqlsatkeremon);
        while ($rowsatkeremon = mysqli_fetch_assoc($resultsatkeremon)) {
            $kode_satkeremon = $rowsatkeremon['kode_satker_pendek'];
            $kode_satkeremon_old = $rowsatkeremon['kode_satker_old_pendek'];
            $id_satker = $rowsatkeremon['level_piu'];
        }

        if ($id_indikator <> '') {
            $filterquery = $filterquery . " and a.id = '" . $id_indikator . "'";
        }

        if ($kategorisatker <> '') {
            $filterquerybalai = $filterquerybalai . " and a.kode_emon in (select z.kode from paket_pk_akhir z where YEAR(z.tanggaldata)=(select tahun
            from master_pk where id='3')
            and (z.kdsatker in (select s.kode_satker_pendek from master_satker s where kdbalai = '" . $kategorisatker . "')
            or z.kdsatker in (select s.kode_satker_old_pendek from master_satker s where kdbalai = '" . $kategorisatker . "')))
            and a.kode_satker in (select s.kode_satker from master_satker s where kdbalai = '" . $kategorisatker . "')";
        }

        if ($kode_satker <> '' && $kode_satker <> 'Semua Data') {
            $filterquerysatker = $filterquerysatker . " and a.kode_emon in (select z.kode from paket_pk_akhir z where YEAR(z.tanggaldata)=(select tahun
            from master_pk where id='3')
            and (z.kdsatker = '" . $kode_satkeremon . "' or z.kdsatker = '" . $kode_satkeremon_old . "'))
            and a.kode_satker = '" . $kode_satker . "'";
        }

        $myArray = array();
        $sql = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
        ,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
        ,IF(a.level = 'UNOR', b.nama_kategori, a.nama_awal) as textindikator_awal
        ,IF(a.level = 'UNOR', b.nama_kategori, a.nama_akhir) as textindikator_akhir
        ,IF(a.satuan = '0', null, a.satuan) as satuan
        ,IF(a.output = '0', null, a.output) as output
        ,IF(a.outcome = '0', null, a.outcome) as outcome
        ,IF(a.satuan_awal = '0', null, a.satuan_awal) as satuan_awal
        ,IF(a.output_awal = '0', null, a.output_awal) as output_awal
        ,IF(a.outcome_awal = '0', null, a.outcome_awal) as outcome_awal
        ,IF(a.satuan_akhir = '0', null, a.satuan_akhir) as satuan_akhir
        ,IF(a.output_akhir = '0', null, a.output_akhir) as output_akhir
        ,IF(a.outcome_akhir = '0', null, a.outcome_akhir) as outcome_akhir
        , (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
        , (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
        , (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
        , (select x.singkatan from master_satuan x where x.id=a.satuan_awal) as namasatuan_awal
        , (select x.singkatan from master_satuan x where x.id=a.output_awal) as namaoutput_awal
        , (select x.singkatan from master_satuan x where x.id=a.outcome_awal) as namaoutcome_awal
        , (select x.singkatan from master_satuan x where x.id=a.satuan_akhir) as namasatuan_akhir
        , (select x.singkatan from master_satuan x where x.id=a.output_akhir) as namaoutput_akhir
        , (select x.singkatan from master_satuan x where x.id=a.outcome_akhir) as namaoutcome_akhir
        , (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
        ,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
        ,a.kolom1,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.rumuskolom1,a.rumuskolom2,a.rumuskolom3,a.rumuskolom4,a.rumuskolom5,a.isian_kolom
        ,a.cetak,a.cetakcode,a.cetaklabel,a.verif,a.verifcode,a.veriflabel,a.verif2,a.verif2code,a.verif2label
        , a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
        ,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
        IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
        IF(a.level = 'ISP', 'levelsubsubkegiatan', 
        IF(a.level = 'KEGIATAN', 'levelpaket', 
        IF(a.level = 'SK', 'levelpekerjaan', 
        IF(a.level = 'IKSK', 'levelakhir', 
        IF(a.level = 'KRO', 'levelakhir2', 
        IF(a.level = 'RO', 'levelakhir3', 'levelakhir3'))))))))))) as class
        from tb_indikator_akhir a 
        left join master_unor b
        on a.kdunor = b.id
        where a.deleted='0'
        " . $filterquery . "
        order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
        //echo $sql;
        //die();
        $result = mysqli_query($link, $sql);
        $num = mysqli_num_rows($result);
        if ($num > 0) {
            if ($result) {
                $myArrayheader = array();
                while ($row = mysqli_fetch_assoc($result)) {
                    $tempkolomkomponenparent = explode("|", $row['kolomkomponen']);
                    for ($z = 0; $z < count($tempkolomkomponenparent); $z++) {
                        $myArrayheader[] = (object)[
                            'header' => $tempkolomkomponenparent[$z],
                        ];
                    }

                    $myArrayemon = array();
                    $sqlemon = "select a.id,a.tahun,a.kode_satker,a.kode_emon,a.nilai, a.rumus
                    ,x.kode,x.nmpaket,x.pgrupiah,x.vol,x.sat,a.latitude,a.longitude,a.outcome,a.target
                    ,a.skor_komponen_a,a.skor_komponen_b,a.skor_komponen_c,a.capaian,a.capaian_outcome
                    from tb_data_pk_emon_akhir a 
                    left join paket_pk_akhir x
                    on a.kode_emon = x.kode
                    where a.deleted='0' and a.tahun=(select tahun
                    from master_pk where id='3') and a.id_indikator = '" . $id_indikator . "'
                    and YEAR(x.tanggaldata)=(select tahun
                    from master_pk where id='3')
                    " . $filterquerybalai . "
                    " . $filterquerysatker . "
                    order by a.kode_satker";
                    //echo $sqlemon;
                    //die;
                    $resultemon = mysqli_query($link, $sqlemon);
                    while ($rowemon = mysqli_fetch_assoc($resultemon)) {
                        $myArraynilaiemon = array();
                        $capaiannya = number_format($rowemon['capaian'], $row['belakangkoma'], ",", ".");
                        $tempnilai = explode("|", $rowemon['nilai']);
                        $temprumus = explode("|", $rowemon['rumus']);
                        $tempkolomkomponen = explode("|", $row['kolomkomponen']);
                        for ($z = 0; $z < count($tempnilai); $z++) {
                            if (strstr($temprumus[$z], "input")) {
                                $nilai = (float) $tempnilai[$z];
                            } else if (strstr($temprumus[$z], "entry")) {
                                $nilai = (float) $tempnilai[$z];
                            } else {
                                $formula_string5 = $temprumus[$z];
                                for ($s = 0; $s < 10; $s++) {
                                    $formula_string5 = str_replace("input" . $s, (float) ($tempnilai[$s]), $formula_string5);
                                }
                                //echo $rumus;
                                for ($s = 0; $s < 10; $s++) {
                                    $formula_string5 = str_replace("nilai" . $s, (float) ($tempnilai[$s]), $formula_string5);
                                }

                                for ($s = 10; $s < 21; $s++) {
                                    $formula_string5 = str_replace("entry" . $s, (float) ($tempnilai[$s]), $formula_string5);
                                }

                                for ($s = 10; $s < 21; $s++) {
                                    $formula_string5 = str_replace("rumus" . $s, (float) ($tempnilai[$s]), $formula_string5);
                                }

                                //echo $formula_string5 . "<br>";
                                $formula_string5 = str_replace("0/0", "0", $formula_string5);
                                $formula_string5 = str_replace("0/(0+0)", "0", $formula_string5);
                                //$formula_string5 = str_replace("00", "0", $formula_string5);
                                $tempformula_string5 = explode("/", $formula_string5);
                                for ($s = 0; $s < count($tempformula_string5); $s++) {
                                    if (strlen($tempformula_string5[$s] == 1) && $tempformula_string5[$s] == '0') {
                                        $formula_string5 = str_replace("0/0", "0", $formula_string5);
                                        $formula_string5 = str_replace("0/(0+0)", "0", $formula_string5);
                                        $formula_string5 = str_replace("/0", "*0", $formula_string5);
                                    }
                                }


                                //echo $formula_string5 . "<br>";
                                //die;
                                try {
                                    eval('$nilai = ' . $formula_string5 . ';');
                                } catch (DivisionByZeroError $e) {
                                    eval('$nilai = 0;');
                                } catch (ParseError $e) {
                                    eval('$nilai = 0;');
                                }

                                //$nilai = 1222;
                                //$disabled = true;
                            }
                            $myArraynilaiemon[] = (object)[
                                'header' => $tempkolomkomponen[$z],
                                'nilai' => number_format($nilai, $row['belakangkoma'], ",", "."),
                                'rumus' => $temprumus[$z],
                            ];
                        }
                        $myArrayemon[] = (object)[
                            'id' => (float) $rowemon['id'],
                            'tahun' => (float) $rowemon['tahun'],
                            'kode_satker' => $rowemon['kode_satker'],
                            'kode_emon' => $rowemon['kode_emon'],
                            'kode' => $rowemon['kode'],
                            'nmpaket' => $rowemon['nmpaket'],
                            'pgrupiah' => (float) $rowemon['pgrupiah'],
                            'vol' => $rowemon['vol'],
                            'sat' => $rowemon['sat'],
                            'latitude' => $rowemon['latitude'],
                            'longitude' => $rowemon['longitude'],
                            'nilai' => $myArraynilaiemon,
                            'capaian' => $capaiannya,
                        ];
                    }
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
                        'textindikator_awal' => $row['textindikator_awal'],
                        'satuan_awal' => $row['satuan_awal'],
                        'namasatuan_awal' => $row['namasatuan_awal'],
                        'output_awal' => $row['output_awal'],
                        'namaoutput_awal' => $row['namaoutput_awal'],
                        'outcome_awal' => $row['outcome_awal'],
                        'namaoutcome_awal' => $row['namaoutcome_awal'],
                        'textindikator_akhir' => $row['textindikator_akhir'],
                        'satuan_akhir' => $row['satuan_akhir'],
                        'namasatuan_akhir' => $row['namasatuan_akhir'],
                        'output_akhir' => $row['output_akhir'],
                        'namaoutput_akhir' => $row['namaoutput_akhir'],
                        'outcome_akhir' => $row['outcome_akhir'],
                        'namaoutcome_akhir' => $row['namaoutcome_akhir'],
                        'penanggungjawab' => $row['penanggungjawab'],
                        'pelaksana' => $row['pelaksana'],
                        'pelaksanacode' => $row['pelaksanacode'],
                        'pelaksanalabel' => $row['pelaksanalabel'],
                        'cetak' => $row['cetak'],
                        'cetakcode' => $row['cetakcode'],
                        'cetaklabel' => $row['cetaklabel'],
                        'verif' => $row['verif'],
                        'verifcode' => $row['verifcode'],
                        'veriflabel' => $row['veriflabel'],
                        'verif2' => $row['verif2'],
                        'verif2code' => $row['verif2code'],
                        'verif2label' => $row['verif2label'],
                        'target' => $row['target'],
                        'urut' => (float) $row['urut'],
                        'hitungan_pk' => $row['hitungan_pk'],
                        'namahitungan_pk' => $row['namahitungan_pk'],
                        'kolom1' => $row['kolom1'],
                        'kolom2' => $row['kolom2'],
                        'kolom3' => $row['kolom3'],
                        'kolom4' => $row['kolom4'],
                        'kolom5' => $row['kolom5'],
                        'rumuskolom1' => $row['rumuskolom1'],
                        'rumuskolom2' => $row['rumuskolom2'],
                        'rumuskolom3' => $row['rumuskolom3'],
                        'rumuskolom4' => $row['rumuskolom4'],
                        'rumuskolom5' => $row['rumuskolom5'],
                        'isian_kolom' => $row['isian_kolom'],
                        'jumlahkomponen' => $row['jumlahkomponen'],
                        'kolomkomponen' => $row['kolomkomponen'],
                        'bobotkomponen' => $row['bobotkomponen'],
                        'rumuskomponen' => $row['rumuskomponen'],
                        'belakangkoma' => $row['belakangkoma'],
                        'class' => $row['class'],
                        'volume' => number_format($volume, $row['belakangkoma'], ",", "."),
                        'target' => number_format($target, $row['belakangkoma'], ",", "."),
                        'capaian' => number_format($capaian, $row['belakangkoma'], ",", "."),
                        'capaian_outcome' => number_format($capaian, $row['belakangkoma'], ",", "."),
                        'nilai' => number_format($nilai, $row['belakangkoma'], ",", "."),
                        'kinerja' => number_format($kinerja, 2, ",", "."),
                        'header' => $myArrayheader,
                        'emon' => $myArrayemon,
                    ];
                }
                $response         = [];
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

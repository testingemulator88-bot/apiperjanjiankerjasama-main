<?php
include '../../library/config.php';
error_reporting(0);
//check_injection();
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
        $filterquerydata = "";
        $id = fixup($_GET['id']);
        $kode_satker = fixup($_GET['kode_satker']);
        $hitungan_pk = fixup($_GET['hitungan_pk']);
        //echo $hitungan_pk;
        //die;
        $textindikator = fixup($_GET['textindikator']);
        $tahun = fixup($_GET['tahun']);
        if ($id <> '') {
            $filterquery = $filterquery . " and a.id_indikator = '" . $id . "'";
        }
        if ($kode_satker <> '') {
            $filterquery = $filterquery . " and a.kode_satker = '" . $kode_satker . "'";
        }
        if ($tahun <> '') {
            $filterquery = $filterquery . " and a.kode_tahun = '" . $tahun . "'";
            $filterquerydata = $filterquerydata . " and a.id = '" . $tahun . "'";
        }

        $myArray = array();
        $myArraydata = array();
        $kode_satkeremon = "";
        $id_satker = "";
        $sqlsatkeremon = "select kode_satker_pendek,kode_satker_old_pendek,level_piu from master_satker where kode_satker = '" . $kode_satker . "'";
        $resultsatkeremon = mysqli_query($link, $sqlsatkeremon);
        while ($rowsatkeremon = mysqli_fetch_assoc($resultsatkeremon)) {
            $kode_satkeremon = $rowsatkeremon['kode_satker_pendek'];
            $kode_satkeremon_old = $rowsatkeremon['kode_satker_old_pendek'];
            $id_satker = $rowsatkeremon['level_piu'];
        }

        $kodekegiatan = "";
        $sqlindikator = "  select kode from tb_indikator where id = 
        (select id_parent from tb_indikator where id = (select id_parent from tb_indikator where id = '" . $id . "'))";
        $resultindikator = mysqli_query($link, $sqlindikator);
        while ($rowindikator = mysqli_fetch_assoc($resultindikator)) {
            $kodekegiatan = $rowindikator['kode'];
        }

        $sqltahuncek = "select a.tahun_awal,a.tahun_akhir from master_tahun a where a.deleted='0' " . $filterquerydata . "";
        $resulttahuncek = mysqli_query($link, $sqltahuncek);
        while ($rowtahuncek = mysqli_fetch_assoc($resulttahuncek)) {
            $tahun_awalcek = (float) $rowtahuncek['tahun_awal'];
            $tahun_akhircek = (float) $rowtahuncek['tahun_akhir'];
        }

        $sqlrumus = "select rumuskomponen from tb_indikator where id = '" . $id . "'";
        $resultrumus = mysqli_query($link, $sqlrumus);
        while ($rowrumus = mysqli_fetch_assoc($resultrumus)) {
            $rumus = $rowrumus['rumuskomponen'];
        }
        $isinilai = "";
        $isinilaifinal = "";
        $temprumus = explode("#", $rumus);
        for ($x = 0; $x < count($temprumus); $x++) {
            $tempnilai = explode("|", $temprumus[$x]);
            for ($y = 0; $y < count($tempnilai); $y++) {
                if ($y == 0) {
                    $isinilai = $isinilai . "0";
                } else {
                    $isinilai = $isinilai . "|" . "0";
                }
            }
            if ($x == 0) {
                $isinilaifinal = $isinilaifinal . $isinilai;
            } else {
                $isinilaifinal = $isinilaifinal . "#" . $isinilai;
            }
        }
        //echo $isinilaifinal;
        //die;

        for ($x = $tahun_awalcek; $x <= $tahun_akhircek; $x++) {
            $sqldatacek = "select a.id
            from tb_data_pk a where a.deleted='0' and a.tahun='" . $x . "' " . $filterquery . "";
            $resultdatacek = mysqli_query($link, $sqldatacek);
            $numdatacek = mysqli_num_rows($resultdatacek);
            if ($numdatacek == 0) {
                $sqlinsert = "insert into tb_data_pk (id,id_indikator,kode_tahun,tahun,id_satker,kode_satker
                ,proyek,nilai,rumus,deleted) values 
                (null,'" . $id . "','" . $tahun . "','" . $x . "','" . $id_satker . "','" . $kode_satker . "'
                ,'" . $textindikator . "','" . $isinilaifinal . "','" . $rumus . "','0')";
                $resultinsert = mysqli_query($link, $sqlinsert);
            }
        }

        $sql = "select a.id,a.kode_tahun,a.id_indikator,a.kode_satker,a.baseline ,a.nilai
        ,a.baseline_komponen_a,a.baseline_komponen_b,a.baseline_komponen_c, b.nama as tahun
        from tb_data_baseline a 
        left join master_tahun b
        on a.kode_tahun = b.id
        where a.deleted='0' 
        " . $filterquery . "
        order by a.id ";
        //echo $sql;
        //die();
        $result = mysqli_query($link, $sql);
        $num = mysqli_num_rows($result);
        if ($num > 0) {
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $baseline = (float) $row['baseline'];
                    $sqltahun = "select a.tahun_awal,a.tahun_akhir from master_tahun a where a.deleted='0' " . $filterquerydata . "";
                    $resulttahun = mysqli_query($link, $sqltahun);
                    while ($rowtahun = mysqli_fetch_assoc($resulttahun)) {
                        $tahun_awal = (float) $rowtahun['tahun_awal'];
                        $tahun_akhir = (float) $rowtahun['tahun_akhir'];
                    }

                    for ($x = $tahun_awal; $x <= $tahun_akhir; $x++) {
                        $myArraydetail = array();
                        $sqldata = "select a.id,a.kode_satker,a.proyek,a.volume,a.target,a.nilai,a.rumus
                        ,a.skor_komponen_a,a.skor_komponen_b,a.skor_komponen_c
                        from tb_data_pk a where a.deleted='0' and a.tahun='" . $x . "' " . $filterquery . "";
                        //echo $sqldata;
                        //die;
                        $resultdata = mysqli_query($link, $sqldata);
                        $numdata = mysqli_num_rows($resultdata);
                        if ($numdata > 0) {
                            if ($resultdata) {
                                while ($rowdata = mysqli_fetch_assoc($resultdata)) {
                                    $baseline = $baseline + $rowdata['target'];
                                    $volume = 0;
                                    $target = 0;
                                    $myArrayemon = array();
                                    $sqlemon = "select a.id,a.tahun,a.kode_satker,a.kode_emon,a.nilai, a.rumus, a.nmpaket as nmpaketdata
                                    ,x.kode,x.nmpaket,x.pgrupiah,x.vol,x.sat,a.latitude,a.longitude,a.outcome,a.target
                                    ,a.skor_komponen_a,a.skor_komponen_b,a.skor_komponen_c
                                    from tb_data_pk_emon a 
                                    left join paket_pk x
                                    on a.kode_emon = x.kode
                                    and YEAR(x.tanggaldata)='" . $x . "'
                                    and a.kode_emon in (select z.kode from paket_pk z where YEAR(z.tanggaldata)='" . $x . "'
                                    and (z.kdsatker = '".$kode_satkeremon."' or z.kdsatker = '".$kode_satkeremon_old."'))
                                    where a.deleted='0' and a.tahun='" . $x . "' " . $filterquery . "";
                                    //echo $sqlemon;
                                    //die;
                                    $resultemon = mysqli_query($link, $sqlemon);
                                    $numemon = mysqli_num_rows($resultemon);
                                    while ($rowemon = mysqli_fetch_assoc($resultemon)) {
                                        $volume = $volume + (float) $rowemon['outcome'];
                                        $target = $target + (float) $rowemon['target'];
                                        $myArraynilaiemon = array();
                                        $tempnilai = explode("|", $rowemon['nilai']);
                                        $temprumus = explode("|", $rowemon['rumus']);
                                        if ($hitungan_pk <> '1') {
                                            for ($z = 0; $z < count($tempnilai); $z++) {
                                                if (strstr($temprumus[$z], "input")) {
                                                    $nilai = (float) $tempnilai[$z];
                                                    $disabled = false;
                                                } else if (strstr($temprumus[$z], "entry")) {
                                                    $nilai = (float) $tempnilai[$z];
                                                    if (strstr($temprumus[$z], "*")) {
                                                        $disabled = true;
                                                    } else {
                                                        $disabled = false;
                                                    }
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
                                                    $disabled = true;
                                                }
                                                $myArraynilaiemon[] = (object)[
                                                    'nilai' => $nilai,
                                                    'rumus' => $temprumus[$z],
                                                    'disabled' => $disabled,
                                                ];
                                            }
                                        }

                                        //die;
                                        $myArrayemon[] = (object)
                                        [
                                            'id' => (float) $rowemon['id'],
                                            'tahun' => (float) $rowemon['tahun'],
                                            'kode_satker' => $rowemon['kode_satker'],
                                            'kode_emon' => $rowemon['kode_emon'],
                                            'kode' => $rowemon['kode'],
                                            'nmpaket' => $rowemon['nmpaket'],
                                            'pgrupiah' => (float) $rowemon['pgrupiah'],
                                            'vol' => $rowemon['vol'],
                                            'sat' => $rowemon['sat'],
                                            'nmpaketdata' => $rowemon['nmpaketdata'],
                                            'outcome' => replace_dot_koma_tiga($rowemon['outcome']),
                                            'target' => replace_dot_koma_tiga($rowemon['target']),
                                            'skor_komponen_a' => $rowemon['skor_komponen_a'],
                                            'skor_komponen_b' => $rowemon['skor_komponen_b'],
                                            'skor_komponen_c' => $rowemon['skor_komponen_c'],
                                            'latitude' => $rowemon['latitude'],
                                            'longitude' => $rowemon['longitude'],
                                            'nilai' => $myArraynilaiemon,
                                        ];
                                    }

                                    $myArraynilaiproyek = array();
                                    $tempnilaiproyekawal = explode("#", $rowdata['nilai']);
                                    $temprumusproyekawal = explode("#", $rowdata['rumus']);
                                    for ($y = 0; $y < count($tempnilaiproyekawal); $y++) {
                                        $tempnilaiproyek = explode("|", $tempnilaiproyekawal[$y]);
                                        $temprumusproyek = explode("|", $temprumusproyekawal[$y]);
                                        for ($z = 0; $z < count($tempnilaiproyek); $z++) {
                                            if ($hitungan_pk <> '1') {
                                                for ($z = 0; $z < count($tempnilaiproyek); $z++) {
                                                    if (strstr($temprumusproyek[$z], "input")) {
                                                        $nilai = (float) $tempnilaiproyek[$z];
                                                    } else if (strstr($temprumusproyek[$z], "entry")) {
                                                        $nilai = (float) $tempnilaiproyek[$z];
                                                    } else {

                                                        $formula_string5 = $temprumusproyek[$z];

                                                        for ($s = 0; $s < 10; $s++) {
                                                            $formula_string5 = str_replace("input" . $s, (float) ($tempnilaiproyek[$s]), $formula_string5);
                                                        }
                                                        //echo $rumus;
                                                        for ($s = 0; $s < 10; $s++) {
                                                            $formula_string5 = str_replace("nilai" . $s, (float) ($tempnilaiproyek[$s]), $formula_string5);
                                                        }

                                                        for ($s = 10; $s < 21; $s++) {
                                                            $formula_string5 = str_replace("entry" . $s, (float) ($tempnilaiproyek[$s]), $formula_string5);
                                                        }

                                                        for ($s = 10; $s < 21; $s++) {
                                                            $formula_string5 = str_replace("rumus" . $s, (float) ($tempnilaiproyek[$s]), $formula_string5);
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
                                                        //$formula_string5 = str_replace("/0", "*0", $formula_string5);

                                                        //echo $formula_string5 . "<br>";
                                                        //die;
                                                        try {
                                                            eval('$nilai = ' . $formula_string5 . ';');
                                                        } catch (DivisionByZeroError $e) {
                                                            eval('$nilai = 0;');
                                                        } catch (ParseError $e) {
                                                            eval('$nilai = 0;');
                                                        }
                                                    }
                                                    $myArraynilaiproyek[] = (object)[
                                                        'data_ke' => (float) $y,
                                                        'nilai' => $nilai,
                                                    ];
                                                }
                                            }
                                        }
                                    }

                                    $myArraydetail[] = (object)
                                    [
                                        'id' => (float) $rowdata['id'],
                                        'kode_satker' => $kode_satker,
                                        'kode_satkeremon' => $kode_satkeremon,
                                        'proyek' => $rowdata['proyek'],
                                        'volume' => replace_dot_koma_tiga($volume),
                                        'target' => replace_dot_koma_tiga($target),
                                        'nilai' => $myArraynilaiproyek,
                                        'skor_komponen_a' => (float) ($rowdata['skor_komponen_a']),
                                        'skor_komponen_b' => (float) ($rowdata['skor_komponen_b']),
                                        'skor_komponen_c' => (float) ($rowdata['skor_komponen_c']),
                                        'kodekegiatan' => $kodekegiatan,
                                        'tambahbaseline' => replace_dot_koma_tiga($target),
                                        'nilaikomponen' => ((float) $rowdata['skor_komponen_a'] * 0.4) + ((float) $rowdata['skor_komponen_b'] * 0.4) + ((float) $rowdata['skor_komponen_c'] * 0.2),
                                        'jumlahdataemon' => $numemon,
                                    ];
                                }
                            }
                        } else {
                            $myArrayemon = array();
                            $myArraydetail[] = (object)
                            [
                                'id' => null,
                                'kode_satker' => $kode_satker,
                                'kode_satkeremon' => $kode_satkeremon,
                                'proyek' => 0,
                                'volume' => 0,
                                'target' => 0,
                                'nilai' => '',
                                'skor_komponen_a' => 0,
                                'skor_komponen_b' => 0,
                                'skor_komponen_c' => 0,
                                'kodekegiatan' => $kodekegiatan,
                                'tambahbaseline' => replace_dot_koma_tiga(0),
                                'nilaikomponen' => 0,
                                'jumlahdataemon' => 0,
                            ];
                        }
                        $myArraydata[] = (object)
                        [
                            'tahun' => $x,
                            'detail' => $myArraydetail,
                            'dataemon' => $myArrayemon,
                        ];
                    }
                    $myArraynilaibaseline = array();
                    $tempnilaibaselineawal = explode("#", $row['nilai']);
                    for ($y = 0; $y < count($tempnilaibaselineawal); $y++) {
                        $tempnilaibaseline = explode("|", $tempnilaibaselineawal[$y]);
                        for ($z = 0; $z < count($tempnilaibaseline); $z++) {
                            $myArraynilaibaseline[] = (object)[
                                'data_ke' => (float) $y,
                                'nilai' => (float) $tempnilaibaseline[$z],
                            ];
                        }
                    }

                    $myArray[] = (object)
                    [
                        'id' => $id,
                        'kode_tahun' => (float) $row['kode_tahun'],
                        'tahun' => (float) $row['tahun'],
                        'id_indikator' => (float) $row['id_indikator'],
                        'kode_satker' => $row['kode_satker'],
                        'kode_satkeremon' => $kode_satkeremon,
                        'kodekegiatan' => $kodekegiatan,
                        'baseline' => replace_dot_koma_tiga($row['baseline']),
                        'baseline_komponen_a' => (float) ($row['baseline_komponen_a']),
                        'baseline_komponen_b' => (float) ($row['baseline_komponen_b']),
                        'baseline_komponen_c' => (float) ($row['baseline_komponen_c']),
                        'nilai' => $myArraynilaibaseline,
                        'nilaibaseline' => ((float) $row['baseline_komponen_a'] * 0.4) + ((float) $row['baseline_komponen_b'] * 0.4) + ((float) $row['baseline_komponen_c'] * 0.2),
                    ];
                }
                $response         = [];
                $response['baseline'] =  $myArray;
                $response['data'] =  $myArraydata;
                $response['tahun_awal'] =  $tahun_awal;
                $response['tahun_akhir'] =  $tahun_akhir;
                mysqli_close($link);
                echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
                die;
            }
        } else {
            $sqltahun = "select a.tahun_awal,a.tahun_akhir from master_tahun a where a.deleted='0' " . $filterquerydata . "";
            $resulttahun = mysqli_query($link, $sqltahun);
            while ($rowtahun = mysqli_fetch_assoc($resulttahun)) {
                $tahun_awal = (float) $rowtahun['tahun_awal'];
                $tahun_akhir = (float) $rowtahun['tahun_akhir'];
            }

            for ($x = $tahun_awal; $x <= $tahun_akhir; $x++) {
                $myArraydetail = array();
                $sqldata = "select a.id,a.proyek,a.volume,a.target,a.nilai,a.rumus
                ,a.skor_komponen_a,a.skor_komponen_b,a.skor_komponen_c
                from tb_data_pk a where a.deleted='0' and a.tahun='" . $x . "'" . $filterquery . "";
                //echo $sqldata;
                //die;
                $resultdata = mysqli_query($link, $sqldata);
                $numdata = mysqli_num_rows($resultdata);
                if ($numdata > 0) {
                    if ($resultdata) {
                        while ($rowdata = mysqli_fetch_assoc($resultdata)) {
                            $myArrayemon = array();
                            $sqlemon = "select a.id,a.tahun,a.kode_satker,a.kode_emon,a.outcome,a.target,a.nilai, a.rumus, a.nmpaket as nmpaketdata
                            ,x.kode,x.nmpaket,x.pgrupiah,x.vol,x.sat,a.latitude,a.longitude
                            ,a.skor_komponen_a,a.skor_komponen_b,a.skor_komponen_c
                            from tb_data_pk_emon a 
                            left join paket_pk x
                            on a.kode_emon = x.kode
                            and YEAR(x.tanggaldata)='" . $x . "'
                            where a.deleted='0' and a.tahun='" . $x . "' " . $filterquery . "";
                            //echo $sqlemon;
                            //die;
                            $resultemon = mysqli_query($link, $sqlemon);
                            $numemon = mysqli_num_rows($resultemon);
                            if ($numemov > 0) {
                                while ($rowemon = mysqli_fetch_assoc($resultemon)) {
                                    $myArraynilaiemon = array();
                                    $tempnilai = explode("|", $rowemon['nilai']);
                                    $temprumus = explode("|", $rowemon['rumus']);
                                    if ($hitungan_pk <> '1') {
                                        for ($z = 0; $z < count($tempnilai); $z++) {
                                            if (strstr($temprumus[$z], "input")) {
                                                $nilai = (float) $tempnilai[$z];
                                                $disabled = false;
                                            } else if (strstr($temprumus[$z], "entry")) {
                                                $nilai = (float) $tempnilai[$z];
                                                if (strstr($temprumus[$z], "*")) {
                                                    $disabled = true;
                                                } else {
                                                    $disabled = false;
                                                }
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
                                                //$formula_string5 = str_replace("/0", "*0", $formula_string5);
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
                                                $disabled = true;
                                            }
                                            $myArraynilaiemon[] = (object)[
                                                'nilai' => $nilai,
                                                'rumus' => $temprumus[$z],
                                                'disabled' => $disabled,
                                            ];
                                        }
                                    }

                                    $myArrayemon[] = (object)
                                    [
                                        'id' => (float) $rowemon['id'],
                                        'tahun' => (float) $rowemon['tahun'],
                                        'kode_satker' => $rowemon['kode_satker'],
                                        'kode_emon' => $rowemon['kode_emon'],
                                        'kode' => $rowemon['kode'],
                                        'nmpaket' => $rowemon['nmpaket'],
                                        'pgrupiah' => (float) $rowemon['pgrupiah'],
                                        'vol' => $rowemon['vol'],
                                        'sat' => $rowemon['sat'],
                                        'nmpaketdata' => $rowemon['nmpaketdata'],
                                        'outcome' => replace_dot_koma_tiga($rowemon['outcome']),
                                        'target' => replace_dot_koma_tiga($rowemon['target']),
                                        'skor_komponen_a' => $rowemon['skor_komponen_a'],
                                        'skor_komponen_b' => $rowemon['skor_komponen_b'],
                                        'skor_komponen_c' => $rowemon['skor_komponen_c'],
                                        'latitude' => $rowemon['latitude'],
                                        'longitude' => $rowemon['longitude'],
                                        'nilai' => $myArraynilaiemon,
                                    ];
                                }
                            }

                            //echo $rowdata['nilai'];
                            $myArraynilaiproyek = array();
                            $tempnilaiproyekawal = explode("#", $rowdata['nilai']);
                            $temprumusproyekawal = explode("#", $rowdata['rumus']);
                            for ($y = 0; $y < count($tempnilaiproyekawal); $y++) {
                                $tempnilaiproyek = explode("|", $tempnilaiproyekawal[$y]);
                                $temprumusproyek = explode("|", $temprumusproyekawal[$y]);
                                for ($z = 0; $z < count($tempnilaiproyek); $z++) {
                                    if ($hitungan_pk <> '1') {
                                        for ($z = 0; $z < count($tempnilaiproyek); $z++) {
                                            if (strstr($temprumusproyek[$z], "input")) {
                                                $nilai = (float) $tempnilaiproyek[$z];
                                            } else if (strstr($temprumusproyek[$z], "entry")) {
                                                $nilai = (float) $tempnilaiproyek[$z];
                                            } else {
                                                $formula_string5 = $temprumusproyek[$z];

                                                for ($s = 0; $s < 10; $s++) {
                                                    $formula_string5 = str_replace("input" . $s, (float) ($tempnilaiproyek[$s]), $formula_string5);
                                                }
                                                //echo $rumus;
                                                for ($s = 0; $s < 10; $s++) {
                                                    $formula_string5 = str_replace("nilai" . $s, (float) ($tempnilaiproyek[$s]), $formula_string5);
                                                }

                                                for ($s = 10; $s < 21; $s++) {
                                                    $formula_string5 = str_replace("entry" . $s, (float) ($tempnilaiproyek[$s]), $formula_string5);
                                                }

                                                for ($s = 10; $s < 21; $s++) {
                                                    $formula_string5 = str_replace("rumus" . $s, (float) ($tempnilaiproyek[$s]), $formula_string5);
                                                }


                                                $formula_string5 = str_replace("0/0", "0", $formula_string5);
                                                $formula_string5 = str_replace("0/(0+0)", "0", $formula_string5);
                                                //$formula_string5 = str_replace("00", "0", $formula_string5);
                                                //$formula_string5 = str_replace("/0", "*0", $formula_string5);

                                                $tempformula_string5 = explode("/", $formula_string5);
                                                for ($s = 0; $s < count($tempformula_string5); $s++) {
                                                    if (strlen($tempformula_string5[$s] == 1) && $tempformula_string5[$s] == '0') {
                                                        $formula_string5 = str_replace("0/0", "0", $formula_string5);
                                                        $formula_string5 = str_replace("0/(0+0)", "0", $formula_string5);
                                                        $formula_string5 = str_replace("/0", "*0", $formula_string5);
                                                    }
                                                }

                                                //echo $formula_string5 . "<br>";

                                            }
                                            $myArraynilaiproyek[] = (object)[
                                                'data_ke' => (float) $y,
                                                'nilai' => (float) $nilai,
                                            ];
                                        }
                                    }
                                }
                            }

                            $myArraydetail[] = (object)
                            [
                                'id' => (float) $rowdata['id'],
                                'kode_satker' => $kode_satker,
                                'kode_satkeremon' => $kode_satkeremon,
                                'proyek' => $rowdata['proyek'],
                                'volume' => replace_dot_koma_tiga($rowdata['volume']),
                                'target' => replace_dot_koma_tiga($rowdata['target']),
                                'nilai' => $myArraynilaiproyek,
                                'skor_komponen_a' => (float) ($rowdata['skor_komponen_a']),
                                'skor_komponen_b' => (float) ($rowdata['skor_komponen_b']),
                                'skor_komponen_c' => (float) ($rowdata['skor_komponen_c']),
                                'kodekegiatan' => $kodekegiatan,
                                'tambahbaseline' => replace_dot_koma_tiga($baseline),
                                'nilaikomponen' => ((float) $rowdata['skor_komponen_a'] * 0.4) + ((float) $rowdata['skor_komponen_b'] * 0.4) + ((float) $rowdata['skor_komponen_c'] * 0.2),
                                'jumlahdataemon' => $numemon,
                            ];
                        }
                    }
                } else {
                    $myArrayemon = array();
                    $myArraydetail[] = (object)
                    [
                        'id' => null,
                        'kode_satker' => $kode_satker,
                        'kode_satkeremon' => $kode_satkeremon,
                        'proyek' => '',
                        'volume' => 0,
                        'target' => 0,
                        'nilai' => '',
                        'skor_komponen_a' => 0,
                        'skor_komponen_b' => 0,
                        'skor_komponen_c' => 0,
                        'kodekegiatan' => $kodekegiatan,
                        'tambahbaseline' => replace_dot_koma_tiga(0),
                        'nilaikomponen' => 0,
                        'jumlahdataemon' => 0,
                    ];
                }
                $myArrayemon = array();
                $sqlemon = "select a.id,a.tahun,a.kode_satker,a.kode_emon,a.outcome,a.target,a.nilai, a.rumus, a.nmpaket as nmpaketdata
                ,x.kode,x.nmpaket,x.pgrupiah,x.vol,x.sat,a.latitude,a.longitude
                ,a.skor_komponen_a,a.skor_komponen_b,a.skor_komponen_c
                from tb_data_pk_emon a 
                left join paket_pk x
                on a.kode_emon = x.kode
                and YEAR(x.tanggaldata)='" . $x . "'
                and a.kode_emon in (select z.kode from paket_pk z where YEAR(z.tanggaldata)='" . $x . "'
                and (z.kdsatker = '".$kode_satkeremon."' or z.kdsatker = '".$kode_satkeremon_old."'))
                where a.deleted='0' and a.tahun='" . $x . "' " . $filterquery . "";
                //echo $sqlemon;
                $resultemon = mysqli_query($link, $sqlemon);
                $numemon = mysqli_num_rows($resultemon);
                while ($rowemon = mysqli_fetch_assoc($resultemon)) {
                    $myArraynilaiemon = array();
                    $tempnilai = explode("|", $rowemon['nilai']);
                    $temprumus = explode("|", $rowemon['rumus']);
                    if ($hitungan_pk <> '1') {
                        for ($z = 0; $z < count($tempnilai); $z++) {
                            if (strstr($temprumus[$z], "input")) {
                                $nilai = (float) $tempnilai[$z];
                                $disabled = false;
                            } else if (strstr($temprumus[$z], "entry")) {
                                $nilai = (float) $tempnilai[$z];
                                if (strstr($temprumus[$z], "*")) {
                                    $disabled = true;
                                } else {
                                    $disabled = false;
                                }
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
                                //$formula_string5 = str_replace("/0", "*0", $formula_string5);
                                $tempformula_string5 = explode("/", $formula_string5);
                                for ($s = 0; $s < count($tempformula_string5); $s++) {
                                    if (substr($tempformula_string5[$s], 0, 1) == '0') {
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
                                $disabled = true;
                            }
                            $myArraynilaiemon[] = (object)[
                                'nilai' => $nilai,
                                'rumus' => $temprumus[$z],
                                'disabled' => $disabled,
                            ];
                        }
                    }
                    $myArrayemon[] = (object)
                    [
                        'id' => (float) $rowemon['id'],
                        'tahun' => (float) $rowemon['tahun'],
                        'kode_satker' => $rowemon['kode_satker'],
                        'kode_emon' => $rowemon['kode_emon'],
                        'kode' => $rowemon['kode'],
                        'nmpaket' => $rowemon['nmpaket'],
                        'pgrupiah' => (float) $rowemon['pgrupiah'],
                        'vol' => $rowemon['vol'],
                        'sat' => $rowemon['sat'],
                        'nmpaketdata' => $rowemon['nmpaketdata'],
                        'outcome' => replace_dot_koma_tiga($rowemon['outcome']),
                        'target' => replace_dot_koma_tiga($rowemon['target']),
                        'skor_komponen_a' => $rowemon['skor_komponen_a'],
                        'skor_komponen_b' => $rowemon['skor_komponen_b'],
                        'skor_komponen_c' => $rowemon['skor_komponen_c'],
                        'latitude' => $rowemon['latitude'],
                        'longitude' => $rowemon['longitude'],
                        'nilai' => $myArraynilaiemon,
                    ];
                }
                $myArraydata[] = (object)
                [
                    'tahun' => $x,
                    'detail' => $myArraydetail,
                    'dataemon' => $myArrayemon,
                ];
            }

            $sqlmastertahun = "select nama from master_tahun where id='" . $tahun . "'";
            $resultmastertahun = mysqli_query($link, $sqlmastertahun);
            while ($rowmastertahun = mysqli_fetch_assoc($resultmastertahun)) {
                $namatahun = $rowmastertahun['nama'];
            }
            $myArray[] = (object)
            [
                'id' => null,
                'kode_tahun' => (float) $tahun,
                'tahun' => $namatahun,
                'id_indikator' => $id,
                'kode_satker' => $kode_satker,
                'kode_satkeremon' => $kode_satkeremon,
                'baseline' => 0,
                'baseline_komponen_a' => 0,
                'baseline_komponen_b' => 0,
                'baseline_komponen_c' => 0,
                'nilaibaseline' => 0,
                'nilai' => '',
                'nilaibaseline' => 0,
            ];
            $response['baseline'] =  $myArray;
            $response['data'] =  $myArraydata;
            $response['tahun_awal'] =  $tahun_awal;
            $response['tahun_akhir'] =  $tahun_akhir;
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

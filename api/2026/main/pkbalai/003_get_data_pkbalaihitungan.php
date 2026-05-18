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
        $filterquerydata = "";
        $id = fixup($_GET['id']);
        $kdbalai = fixup($_GET['kdbalai']);
        $hitungan_pk = fixup($_GET['hitungan_pk']);
        $tahun = fixup($_GET['tahun']);
        if ($id <> '') {
            $filterquery = $filterquery . " and a.id_indikator = '" . $id . "'";
        }
        if ($kdbalai <> '') {
            $filterquery = $filterquery . " and a.id_balai = '" . $kdbalai . "'";
        }
        if ($tahun <> '') {
            $filterquery = $filterquery . " and a.kode_tahun = '" . $tahun . "'";
            $filterquerydata = $filterquerydata . " and a.id = '" . $tahun . "'";
        }

        $myArray = array();
        $myArraydata = array();
        $kode_balaiemon = "";
        $id_balai = "";
        $sqlbalaiemon = "select kdbalai_pendek,kdbalai from master_kategori_satker where id = '" . $kdbalai . "'";
        $resultbalaiemon = mysqli_query($link, $sqlbalaiemon);
        while ($rowbalaiemon = mysqli_fetch_assoc($resultbalaiemon)) {
            $kode_balaiemon = $rowbalaiemon['kdbalai_pendek'];
            $id_balai = $rowbalaiemon['kdbalai'];
        }
        $myArraykolomkomponen = array();
        $myArraybobotkomponen = array();
        $myArrayrumuskomponen = array();
        $sqlindikator = "select * from tb_indikator where deleted='0' and id='" . $id . "' ";
        //echo $sqlindikator;
        //die;
        $resultindikator = mysqli_query($link, $sqlindikator);
        while ($rowindikator = mysqli_fetch_assoc($resultindikator)) {
            $kolom1 = $rowindikator['kolom1'];
            $kolom2 = $rowindikator['kolom2'];
            $kolom3 = $rowindikator['kolom3'];
            $kolom4 = $rowindikator['kolom4'];
            $kolom5 = $rowindikator['kolom5'];
            $namakomponen = $rowindikator['nama'];
            $rumuskolom1 = $rowindikator['rumuskolom1'];
            $rumuskolom2 = $rowindikator['rumuskolom2'];
            $rumuskolom3 = $rowindikator['rumuskolom3'];
            $rumuskolom4 = $rowindikator['rumuskolom4'];
            $rumuskolom5 = $rowindikator['rumuskolom5'];
            $isian_kolom = $rowindikator['isian_kolom'];
            $masterrumus = $rowindikator['rumuskomponen'];
            $kolomkomponen = $rowindikator['kolomkomponen'];
        }


        $tempkolomkomponen = explode("|", $kolomkomponen);
        $temprumuskomponen = explode("|", $masterrumus);

        for ($ceknol = 0; $ceknol < count($tempkolomkomponen); $ceknol++) {
            $myArraykolomkomponen[] = (object)
            [
                "nama" => $tempkolomkomponen[$ceknol],
            ];
            $myArrayrumuskomponen[] = (object)
            [
                "nama" => $temprumuskomponen[$ceknol],
            ];
        }

        $arraymasterrumus = array();
        $arraymasterkomponen = array();
        $tempmasterrumus = explode("|", $masterrumus);
        $tempkolomkomponen = explode("|", $kolomkomponen);
        $nilai_komponen = "";
        for ($x = 0; $x < count($tempmasterrumus); $x++) {
            if ($x == 0) {
                $nilai_komponen = $nilai_komponen . "0";
            } else {
                $nilai_komponen = $nilai_komponen . "|0";
            }
            $arraymasterrumus[] = (object)
            [
                'urut' => $x,
                'rumus' => $tempmasterrumus[$x],
            ];
            $arraymasterkomponen[] = (object)
            [
                'urut' => $x,
                'rumus' => $tempkolomkomponen[$x],
            ];
        }

        $sqlcekdatabaseline = "select a.id,a.kode_tahun,a.id_indikator,a.id_balai,a.kode_balai,a.kolom1 
        ,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.isian_kolom, b.nama as tahun
        from tb_data_baseline_pk_balai a 
        left join master_tahun b
        on a.kode_tahun = b.id
        where a.deleted='0' 
        " . $filterquery . "
        order by a.id ";
        $resultcekdatabaseline = mysqli_query($link, $sqlcekdatabaseline);
        $numcekdatabaseline = mysqli_num_rows($resultcekdatabaseline);
        if ($numcekdatabaseline == 0) {
            $sqlinsert = "insert into tb_data_baseline_pk_balai (id, kode_tahun, id_indikator, id_balai, kode_balai
            , kolom1, kolom2, kolom3, kolom4, kolom5, isian_kolom, nilai, rumus, createddate, createdby, deleted)
            VALUES (null, '" . $tahun . "', '" . $id . "', '" . $kdbalai . "', '" . $id_balai . "', '0'
            , '0', '0', '0', '0','','" . $nilai_komponen . "','" . $masterrumus . "', SYSDATE(), null,'0')";
            $resultinsert = mysqli_query($link, $sqlinsert);
        }

        $sqltahuncek = "select a.tahun_awal,a.tahun_akhir from master_tahun a where a.deleted='0' " . $filterquerydata . "";
        $resulttahuncek = mysqli_query($link, $sqltahuncek);
        while ($rowtahuncek = mysqli_fetch_assoc($resulttahuncek)) {
            $tahun_awalcek = (float) $rowtahuncek['tahun_awal'];
            $tahun_akhircek = (float) $rowtahuncek['tahun_akhir'];
        }
        for ($x = $tahun_awalcek; $x <= $tahun_akhircek; $x++) {
            $sqldatacek = "select a.id,a.id_balai,a.kode_balai,a.kolom1,a.kolom2,a.kolom3
            ,a.kolom4,a.kolom5,a.isian_kolom
            from tb_data_pk_balai a where a.deleted='0' and a.tahun='" . $x . "' " . $filterquery . "";
            //echo $sqldata;
            $resultdatacek = mysqli_query($link, $sqldatacek);
            $numdatacek = mysqli_num_rows($resultdatacek);
            if ($numdatacek == 0) {
                $sqlinsert = "insert into tb_data_pk_balai (id, kode_tahun, id_indikator, tahun, id_balai, kode_balai, nama
                , kolom1, kolom2, kolom3, kolom4, kolom5, isian_kolom, nilai, rumus, createddate, createdby, deleted)
                VALUES (null, '" . $tahun . "', '" . $id . "', '" . $x . "', '" . $kdbalai . "', '" . $id_balai . "', '" . $namakomponen . "', '0'
                , '0', '0', '0', '0','','" . $nilai_komponen . "','" . $masterrumus . "', SYSDATE(), null,'0')";
                $resultinsert = mysqli_query($link, $sqlinsert);
            }
        }

        sleep(1);

        $sql = "select a.id,a.kode_tahun,a.id_indikator,a.id_balai,a.kode_balai,a.kolom1 
        ,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.isian_kolom,a.nilai,a.rumus, b.nama as tahun
        from tb_data_baseline_pk_balai a 
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
                    $sqltahun = "select a.tahun_awal,a.tahun_akhir from master_tahun a where a.deleted='0' " . $filterquerydata . "";
                    $resulttahun = mysqli_query($link, $sqltahun);
                    while ($rowtahun = mysqli_fetch_assoc($resulttahun)) {
                        $tahun_awal = (float) $rowtahun['tahun_awal'];
                        $tahun_akhir = (float) $rowtahun['tahun_akhir'];
                    }

                    for ($x = $tahun_awal; $x <= $tahun_akhir; $x++) {
                        $myArraydetail = array();
                        $sqldata = "select a.id,a.id_balai,a.kode_balai,a.kolom1,a.kolom2,a.kolom3
                        ,a.kolom4,a.kolom5,a.isian_kolom,a.nilai,a.rumus
                        from tb_data_pk_balai a where a.deleted='0' and a.tahun='" . $x . "' " . $filterquery . "";
                        //echo $sqldata;
                        //die;
                        $resultdata = mysqli_query($link, $sqldata);
                        $numdata = mysqli_num_rows($resultdata);
                        if ($numdata > 0) {
                            if ($resultdata) {
                                while ($rowdata = mysqli_fetch_assoc($resultdata)) {
                                    $myArraynilai = array();
                                    //echo $rowdata['nilai']."<br>";
                                    $tempnilai = explode("|", $rowdata['nilai']);
                                    $temprumus = explode("|", $rowdata['rumus']);
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
                                            }

                                            //$nilai = 1222;
                                            $disabled = true;
                                        }
                                        $myArraynilai[] = (object)[
                                            'nilai' => $nilai,
                                            'rumus' => $temprumus[$z],
                                            'disabled' => $disabled,
                                        ];
                                    }
                                    $myArraydetail[] = (object)
                                    [
                                        'id' => (float) $rowdata['id'],
                                        'kode_balai' => $kdbalai,
                                        'm_kolom1' => $kolom1,
                                        'm_kolom2' => $kolom2,
                                        'm_kolom3' => $kolom3,
                                        'm_kolom4' => $kolom4,
                                        'm_kolom5' => $kolom5,
                                        'm_rumuskolom1' => $rumuskolom1,
                                        'm_rumuskolom2' => $rumuskolom2,
                                        'm_rumuskolom3' => $rumuskolom3,
                                        'm_rumuskolom4' => $rumuskolom4,
                                        'm_rumuskolom5' => $rumuskolom5,
                                        'm_isian_kolom' => $isian_kolom,
                                        'kolom1' => replace_dot_koma($isikolom1),
                                        'kolom2' => replace_dot_koma($isikolom2),
                                        'kolom3' => replace_dot_koma($isikolom3),
                                        'kolom4' => replace_dot_koma($isikolom4),
                                        'kolom5' => replace_dot_koma($isikolom5),
                                        'isian_kolom' => $rowdata['isian_kolom'],
                                        'rumus' => $rowdata['rumus'],
                                        'masterrumus' => $arraymasterrumus,
                                        'kolomkomponen' => $arraymasterkomponen,
                                        'nilai' => $myArraynilai,
                                    ];
                                }
                            }
                        }
                        $myArraydata[] = (object)
                        [
                            'tahun' => $x,
                            'detail' => $myArraydetail,
                        ];
                    }

                    $myArray[] = (object)
                    [
                        'id' => $id,
                        'kode_tahun' => (float) $row['kode_tahun'],
                        'tahun' => (float) $row['tahun'],
                        'id_indikator' => (float) $row['id_indikator'],
                        'kode_balai' => $row['kode_balai'],
                        'm_kolom1' => $kolom1,
                        'm_kolom2' => $kolom2,
                        'm_kolom3' => $kolom3,
                        'm_kolom4' => $kolom4,
                        'm_kolom5' => $kolom5,
                        'm_rumuskolom1' => $rumuskolom1,
                        'm_rumuskolom2' => $rumuskolom2,
                        'm_rumuskolom3' => $rumuskolom3,
                        'm_rumuskolom4' => $rumuskolom4,
                        'm_rumuskolom5' => $rumuskolom5,
                        'm_isian_kolom' => $isian_kolom,
                        'kolom1' => replace_dot_koma($row['kolom1']),
                        'kolom2' => replace_dot_koma($row['kolom2']),
                        'kolom3' => replace_dot_koma($row['kolom3']),
                        'kolom4' => replace_dot_koma($row['kolom4']),
                        'kolom5' => replace_dot_koma($row['kolom5']),
                        'isian_kolom' => $row['isian_kolom'],
                        'nilai' => $arraynilaifinal,
                        'rumus' => $row['rumus'],
                        'masterrumus' => $arraymasterrumus,
                        'kolomkomponen' => $arraymasterkomponen,
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
                $sqldata = "select a.id,a.id_balai,a.kode_balai,a.kolom1,a.kolom2,a.kolom3
                ,a.kolom4,a.kolom5,a.isian_kolom,a.nilai,a.rumus
                from tb_data_pk_balai a where a.deleted='0' and a.tahun='" . $x . "' " . $filterquery . "";
                $resultdata = mysqli_query($link, $sqldata);
                $numdata = mysqli_num_rows($resultdata);
                if ($numdata > 0) {
                    if ($resultdata) {
                        $sqlrumus = "select rumuskomponen, kolomkomponen from tb_indikator where id = '" . $id . "'";
                        $resultrumus = mysqli_query($link, $sqlrumus);
                        while ($rowrumus = mysqli_fetch_assoc($resultrumus)) {
                            $rumus = $rowrumus['rumuskomponen'];
                            $kolomkomponen = $rowrumus['kolomkomponen'];
                        }

                        $tempnilai = explode("|", $rowdata['nilai']);
                        $arraynilaifinal = array();
                        for ($zz = 0; $zz < count($tempnilai); $zz++) {
                            $textnilai = number_format($tempnilai[$zz], 2, ",", ".");
                            $arraynilaifinal[] = (object)
                            [
                                "nilai" => $textnilai,
                            ];
                        }

                        while ($rowdata = mysqli_fetch_assoc($resultdata)) {
                            $myArraydetail[] = (object)
                            [
                                'id' => (float) $rowdata['id'],
                                'kode_balai' => $kdbalai,
                                'm_kolom1' => $kolom1,
                                'm_kolom2' => $kolom2,
                                'm_kolom3' => $kolom3,
                                'm_kolom4' => $kolom4,
                                'm_kolom5' => $kolom5,
                                'm_rumuskolom1' => $rumuskolom1,
                                'm_rumuskolom2' => $rumuskolom2,
                                'm_rumuskolom3' => $rumuskolom3,
                                'm_rumuskolom4' => $rumuskolom4,
                                'm_rumuskolom5' => $rumuskolom5,
                                'm_isian_kolom' => $isian_kolom,
                                'kolom1' => $rowdata['kolom1'],
                                'kolom2' => $rowdata['kolom2'],
                                'kolom3' => $rowdata['kolom3'],
                                'kolom4' => $rowdata['kolom4'],
                                'kolom5' => $rowdata['kolom5'],
                                'isian_kolom' => $rowdata['isian_kolom'],
                                'nilai' => $arraynilaifinal,
                                'rumus' => $rowdata['rumus'],
                                'masterrumus' => $arraymasterrumus,
                                'kolomkomponen' => $arraymasterkomponen,
                            ];
                        }
                    }
                } else {
                    $sqlrumus = "select rumuskomponen, kolomkomponen from tb_indikator where id = '" . $id . "'";
                    $resultrumus = mysqli_query($link, $sqlrumus);
                    while ($rowrumus = mysqli_fetch_assoc($resultrumus)) {
                        $rumus = $rowrumus['rumuskomponen'];
                        $kolomkomponen = $rowrumus['kolomkomponen'];
                    }

                    $tempnilai = explode("|", $masterrumus);
                    $arraynilaifinal = array();
                    for ($zz = 0; $zz < count($tempnilai); $zz++) {
                        $textnilai = number_format($tempnilai[$zz], 2, ",", ".");
                        $arraynilaifinal[] = (object)
                        [
                            "nilai" => 0,
                        ];
                    }
                    $myArraydetail[] = (object)
                    [
                        'id' => null,
                        'kode_balai' => $kdbalai,
                        'm_kolom1' => $kolom1,
                        'm_kolom2' => $kolom2,
                        'm_kolom3' => $kolom3,
                        'm_kolom4' => $kolom4,
                        'm_kolom5' => $kolom5,
                        'm_rumuskolom1' => $rumuskolom1,
                        'm_rumuskolom2' => $rumuskolom2,
                        'm_rumuskolom3' => $rumuskolom3,
                        'm_rumuskolom4' => $rumuskolom4,
                        'm_rumuskolom5' => $rumuskolom5,
                        'm_isian_kolom' => $isian_kolom,
                        'kolom1' => 0,
                        'kolom2' => 0,
                        'kolom3' => 0,
                        'kolom4' => 0,
                        'kolom5' => 0,
                        'isian_kolom' => '',
                        'nilai' => $arraynilaifinal,
                        'rumus' => '',
                        'masterrumus' => $arraymasterrumus,
                        'kolomkomponen' => $arraymasterkomponen,
                    ];
                }
                $myArraydata[] = (object)
                [
                    'tahun' => $x,
                    'detail' => $myArraydetail,
                ];
            }

            $tempnilai = explode("|", $row['nilai']);
            $arraynilaifinal = array();
            for ($zz = 0; $zz < count($tempnilai); $zz++) {
                $textnilai = number_format($tempnilai[$zz], 2, ",", ".");
                $arraynilaifinal[] = (object)
                [
                    "nilai" => $textnilai,
                ];
            }

            $myArray[] = (object)
            [
                'id' => $id,
                'kode_tahun' => (float) $row['kode_tahun'],
                'tahun' => (float) $row['tahun'],
                'id_indikator' => (float) $row['id_indikator'],
                'kode_balai' => $kdbalai,
                'm_kolom1' => $kolom1,
                'm_kolom2' => $kolom2,
                'm_kolom3' => $kolom3,
                'm_kolom4' => $kolom4,
                'm_kolom5' => $kolom5,
                'm_rumuskolom1' => $rumuskolom1,
                'm_rumuskolom2' => $rumuskolom2,
                'm_rumuskolom3' => $rumuskolom3,
                'm_rumuskolom4' => $rumuskolom4,
                'm_rumuskolom5' => $rumuskolom5,
                'm_isian_kolom' => $isian_kolom,
                'kolom1' => replace_dot($row['kolom1']),
                'kolom2' => replace_dot($row['kolom2']),
                'kolom3' => replace_dot($row['kolom3']),
                'kolom4' => replace_dot($row['kolom4']),
                'kolom5' => replace_dot($row['kolom5']),
                'isian_kolom' => $row['isian_kolom'],
                'nilai' => $arraynilaifinal,
                'rumus' => $row['rumus'],
                'masterrumus' => $arraymasterrumus,
                'kolomkomponen' => $arraymasterkomponen,
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

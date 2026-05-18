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

        $sqlindikator = "select * from tb_indikator_awal where deleted='0' and id='" . $id . "' ";
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
            $jumlahkomponen = $rowindikator['jumlahkomponen'];
            $kolomkomponen = $rowindikator['kolomkomponen'];
            $bobotkomponen = $rowindikator['bobotkomponen'];
            $rumuskomponen = $rowindikator['rumuskomponen'];
            $rumuskolom1 = $rowindikator['rumuskolom1'];
            $rumuskolom2 = $rowindikator['rumuskolom2'];
            $rumuskolom3 = $rowindikator['rumuskolom3'];
            $rumuskolom4 = $rowindikator['rumuskolom4'];
            $rumuskolom5 = $rowindikator['rumuskolom5'];
            $isian_kolom = $rowindikator['isian_kolom'];
            $belakangkoma = $rowindikator['belakangkoma'];
        }

        $tempkolomkomponen = explode("|", $kolomkomponen);
        $tempbobotkomponen = explode("|", $bobotkomponen);
        $temprumuskomponen = explode("|", $rumuskomponen);
        $nilai_komponen = "";
        for ($ceknol = 0; $ceknol < count($tempkolomkomponen); $ceknol++) {
            if ($ceknol == 0) {
                $nilai_komponen = $nilai_komponen . "0";
            } else {
                $nilai_komponen = $nilai_komponen . "|0";
            }
            $myArraykolomkomponen[] = (object)
            [
                "nama" => $tempkolomkomponen[$ceknol],
            ];
            $myArraybobotkomponen[] = (object)
            [
                "nama" => $tempbobotkomponen[$ceknol],
            ];
            $myArrayrumuskomponen[] = (object)
            [
                "nama" => $temprumuskomponen[$ceknol],
            ];
        }
        $baselinekolom1 = 0;
        $baselinekolom2 = 0;
        $baselinekolom3 = 0;
        $baselinekolom4 = 0;
        $baselinekolom5 = 0;

        $sqlcekdatabaseline = "select a.id,a.kode_tahun,a.id_indikator,a.id_balai,a.kode_balai,a.kolom1 
        ,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.isian_kolom, b.nama as tahun
        from tb_data_baseline_pk_balai_awal a 
        left join master_tahun b
        on a.kode_tahun = b.id
        where a.deleted='0' 
        " . $filterquery . "
        order by a.id ";
        $resultcekdatabaseline = mysqli_query($link, $sqlcekdatabaseline);
        $numcekdatabaseline = mysqli_num_rows($resultcekdatabaseline);
        if ($numcekdatabaseline == 0) {
            $sqlinsert = "insert into tb_data_baseline_pk_balai_awal (id, kode_tahun, id_indikator, id_balai, kode_balai
            , kolom1, kolom2, kolom3, kolom4, kolom5, isian_kolom, nilai, rumus, createddate, createdby, deleted)
            VALUES (null, '" . $tahun . "', '" . $id . "', '" . $kdbalai . "', '" . $id_balai . "', '0'
            , '0', '0', '0', '0','','" . $nilai_komponen . "','" . $rumuskomponen . "', SYSDATE(), null,'0')";
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
            from tb_data_pk_balai_awal a where a.deleted='0' and a.tahun='" . $x . "' " . $filterquery . "";
            //echo $sqldata;
            $resultdatacek = mysqli_query($link, $sqldatacek);
            $numdatacek = mysqli_num_rows($resultdatacek);
            if ($numdatacek == 0) {
                $sqlinsert = "insert into tb_data_pk_balai_awal (id, kode_tahun, id_indikator, tahun, id_balai, kode_balai, nama
                , kolom1, kolom2, kolom3, kolom4, kolom5, isian_kolom, nilai, rumus, createddate, createdby, deleted)
                VALUES (null, '" . $tahun . "', '" . $id . "', '" . $x . "', '" . $kdbalai . "', '" . $id_balai . "', '" . $namakomponen . "', '0'
                , '0', '0', '0', '0','','" . $nilai_komponen . "','" . $rumuskomponen . "', SYSDATE(), null,'0')";
                $resultinsert = mysqli_query($link, $sqlinsert);
            }
        }

        sleep(1);
        $sql = "select a.id,a.kode_tahun,a.id_indikator,a.id_balai,a.kode_balai,a.kolom1 
        ,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.isian_kolom, b.nama as tahun
        from tb_data_baseline_pk_balai_awal a 
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
                    $baselinekolom1 = (float) $row['kolom1'];
                    $baselinekolom2 = (float) $row['kolom2'];
                    $baselinekolom3 = (float) $row['kolom3'];
                    $baselinekolom4 = (float) $row['kolom4'];
                    $baselinekolom5 = (float) $row['kolom5'];
                    $sqltahun = "select a.tahun_awal,a.tahun_akhir from master_tahun a where a.deleted='0' " . $filterquerydata . "";
                    $resulttahun = mysqli_query($link, $sqltahun);
                    while ($rowtahun = mysqli_fetch_assoc($resulttahun)) {
                        $tahun_awal = (float) $rowtahun['tahun_awal'];
                        $tahun_akhir = (float) $rowtahun['tahun_akhir'];
                    }
                    $arraytahun = array();
                    $arraykolom1 = array();
                    $arraykolom2 = array();
                    $arraykolom3 = array();
                    $arraykolom4 = array();
                    $arraykolom5 = array();
                    $s = 0;
                    $totalkolom1 = 0;
                    $totalkolom2 = 0;
                    $totalkolom3 = 0;
                    $totalkolom4 = 0;
                    $totalkolom5 = 0;
                    for ($x = $tahun_awal; $x <= $tahun_akhir; $x++) {
                        array_push($arraytahun, $x);
                        $myArraydetail = array();
                        $sqldata = "select a.id,a.id_balai,a.kode_balai,a.kolom1,a.kolom2,a.kolom3
                        ,a.kolom4,a.kolom5,a.isian_kolom
                        from tb_data_pk_balai_awal a where a.deleted='0' and a.tahun='" . $x . "' " . $filterquery . "";
                        //echo $sqldata;
                        $resultdata = mysqli_query($link, $sqldata);
                        $numdata = mysqli_num_rows($resultdata);
                        if ($numdata > 0) {
                            if ($resultdata) {
                                while ($rowdata = mysqli_fetch_assoc($resultdata)) {
                                    if ($hitungan_pk == '1') {

                                        $isikolom1 = 0;
                                        $isikolom2 = 0;
                                        $isikolom3 = 0;
                                        $isikolom4 = 0;
                                        $isikolom5 = 0;
                                        #kolom1
                                        if (strstr($rumuskolom1, "IKSK")) {
                                            $temprumuskolom1 = explode("+", $rumuskolom1);
                                            for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom1); $jumlahtambah++) {
                                                $sqlcekhitungan = "select id,hitungan_pk from tb_indikator_awal where deleted = '0'
                                                and kode_unique = '" . $temprumuskolom1[$jumlahtambah] . "'";
                                                $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                                while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                    $hitungan_pkdetail = $rowcekhitungan['hitungan_pk'];
                                                }
                                                if ($hitungan_pkdetail == '1') {
                                                    $sqlskortahunsekarang = "select sum(target) as target
                                                    from tb_data_pk_awal
                                                    where deleted = '0'
                                                    and kode_tahun='" . $tahun . "'
                                                    and id_indikator = (select id from tb_indikator_awal where deleted='0' and kode_unique = '" . $temprumuskolom1[$jumlahtambah] . "')
                                                    and tahun = '" . $x . "'";
                                                    //echo $sqlskortahunsekarang;
                                                    //die;
                                                    $resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
                                                    $numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
                                                    if ($numskortahunsekarang > 0) {
                                                        while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
                                                            $target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
                                                        }
                                                    } else {
                                                        $target_tahun_berjalan = 0;
                                                    }
                                                }
                                                $isikolom1 = $isikolom1 + $target_tahun_berjalan;
                                            }
                                        } else if (strstr($rumuskolom1, "ambildatasebelum")) {
                                            $ambildatasebelum = 0;
                                            if ($x == $tahun_awal) {
                                                array_push($arraykolom1, (float) $baselinekolom1);
                                                $ambildatasebelum = ((float) $baselinekolom1);
                                            } else {
                                                array_push($arraykolom1, (float) $rowdata['kolom1'] + (float) $arraykolom1[$s - 1]);
                                                $ambildatasebelum = ((float) $rowdata['kolom1'] + (float) $arraykolom1[$s - 1]);
                                            }
                                            $cekrumuskolom1 = str_replace("ambildatasebelumbaseline1", $ambildatasebelum, $rumuskolom1);
                                            //echo $cekrumuskolom1."<br>";
                                            $tempformula_string5 = explode("/", $cekrumuskolom1);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom1 = str_replace("0/0", "0", $cekrumuskolom1);
                                                    $cekrumuskolom1 = str_replace("0/(0+0)", "0", $cekrumuskolom1);
                                                    $cekrumuskolom1 = str_replace("/0", "*0", $cekrumuskolom1);
                                                }
                                            }
                                            try {
                                                eval('$isikolom1 = ' . $cekrumuskolom1 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom1 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom1 = 0;');
                                            }
                                        } else if (strstr($rumuskolom1, "given")) {
                                            $cekrumuskolom1 = $rumuskolom1;
                                            for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                $cekrumuskolom1 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom1);
                                            }
                                            $tempformula_string5 = explode("/", $cekrumuskolom1);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom1 = str_replace("0/0", "0", $cekrumuskolom1);
                                                    $cekrumuskolom1 = str_replace("0/(0+0)", "0", $cekrumuskolom1);
                                                    $cekrumuskolom1 = str_replace("/0", "*0", $cekrumuskolom1);
                                                }
                                            }
                                            try {
                                                eval('$isikolom1 = ' . $cekrumuskolom1 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom1 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom1 = 0;');
                                            }
                                        } else if (strstr($rumuskolom1, "isikolom")) {
                                            $cekrumuskolom1 = $rumuskolom1;
                                            for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                $cekrumuskolom1 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom1);
                                            }
                                            $tempformula_string5 = explode("/", $cekrumuskolom1);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom1 = str_replace("0/0", "0", $cekrumuskolom1);
                                                    $cekrumuskolom1 = str_replace("0/(0+0)", "0", $cekrumuskolom1);
                                                    $cekrumuskolom1 = str_replace("/0", "*0", $cekrumuskolom1);
                                                }
                                            }
                                            try {
                                                eval('$isikolom1 = ' . $cekrumuskolom1 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom1 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom1 = 0;');
                                            }
                                            //echo $isikolom1."<br>";
                                        } else if (strstr($rumuskolom1, "totalkolom")) {
                                            $cekrumuskolom1 = $rumuskolom1;
                                            for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                $cekrumuskolom1 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom1);
                                            }
                                            $tempformula_string5 = explode("/", $cekrumuskolom1);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom1 = str_replace("0/0", "0", $cekrumuskolom1);
                                                    $cekrumuskolom1 = str_replace("0/(0+0)", "0", $cekrumuskolom1);
                                                    $cekrumuskolom1 = str_replace("/0", "*0", $cekrumuskolom1);
                                                }
                                            }
                                            try {
                                                eval('$isikolom1 = ' . $cekrumuskolom1 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom1 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom1 = 0;');
                                            }
                                            //echo $isikolom1."<br>";
                                        } else {
                                            $isikolom1 = (float) $rowdata['kolom1'];
                                        }
                                        $totalkolom1 = $totalkolom1 + (float) $isikolom1;

                                        #kolom1

                                        #kolom2
                                        if (strstr($rumuskolom2, "IKSK")) {
                                            $temprumuskolom2 = explode("+", $rumuskolom2);
                                            for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom2); $jumlahtambah++) {
                                                $sqlcekhitungan = "select id,hitungan_pk from tb_indikator_awal where deleted = '0'
                                                and kode_unique = '" . $temprumuskolom2[$jumlahtambah] . "'";
                                                $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                                while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                    $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                                }
                                                if ($hitungan_pk == '1') {
                                                    $sqlskortahunsekarang = "select sum(target) as target
                                                    from tb_data_pk_awal
                                                    where deleted = '0' 
                                                    and kode_tahun='" . $tahun . "'
                                                    and id_indikator = (select id from tb_indikator_awal where deleted='0' and kode_unique = '" . $temprumuskolom2[$jumlahtambah] . "')
                                                    and tahun = '" . $x . "'";
                                                    //echo $sqlskortahunsekarang;
                                                    $resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
                                                    $numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
                                                    if ($numskortahunsekarang > 0) {
                                                        while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
                                                            $target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
                                                        }
                                                    } else {
                                                        $target_tahun_berjalan = 0;
                                                    }
                                                }
                                                $isikolom2 = $isikolom2 + $target_tahun_berjalan;
                                            }
                                        } else if (strstr($rumuskolom2, "ambildatasebelum")) {
                                            $ambildatasebelum = 0;
                                            if ($x == $tahun_awal) {
                                                array_push($arraykolom2, (float) $baselinekolom2);
                                                $ambildatasebelum = ((float) $baselinekolom2);
                                            } else {
                                                array_push($arraykolom2, (float) $rowdata['kolom2'] + (float) $arraykolom2[$s - 1]);
                                                $ambildatasebelum = ((float) $rowdata['kolom2'] + (float) $arraykolom2[$s - 1]);
                                            }
                                            $cekrumuskolom2 = str_replace("ambildatasebelumbaseline2", $ambildatasebelum, $rumuskolom2);
                                            //echo $cekrumuskolom2."<br>";
                                            $tempformula_string5 = explode("/", $cekrumuskolom2);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom2 = str_replace("0/0", "0", $cekrumuskolom2);
                                                    $cekrumuskolom2 = str_replace("0/(0+0)", "0", $cekrumuskolom2);
                                                    $cekrumuskolom2 = str_replace("/0", "*0", $cekrumuskolom2);
                                                }
                                            }
                                            try {
                                                eval('$isikolom2 = ' . $cekrumuskolom2 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom2 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom2 = 0;');
                                            }
                                        } else if (strstr($rumuskolom2, "given")) {
                                            $cekrumuskolom2 = $rumuskolom2;
                                            for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                $cekrumuskolom2 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom2);
                                            }
                                            $tempformula_string5 = explode("/", $cekrumuskolom2);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom2 = str_replace("0/0", "0", $cekrumuskolom2);
                                                    $cekrumuskolom2 = str_replace("0/(0+0)", "0", $cekrumuskolom2);
                                                    $cekrumuskolom2 = str_replace("/0", "*0", $cekrumuskolom2);
                                                }
                                            }
                                            try {
                                                eval('$isikolom2 = ' . $cekrumuskolom2 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom2 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom2 = 0;');
                                            }
                                        } else if (strstr($rumuskolom2, "isikolom")) {
                                            $cekrumuskolom2 = $rumuskolom2;
                                            for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                $cekrumuskolom2 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom2);
                                            }
                                            $tempformula_string5 = explode("/", $cekrumuskolom2);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom2 = str_replace("0/0", "0", $cekrumuskolom2);
                                                    $cekrumuskolom2 = str_replace("0/(0+0)", "0", $cekrumuskolom2);
                                                    $cekrumuskolom2 = str_replace("/0", "*0", $cekrumuskolom2);
                                                }
                                            }
                                            try {
                                                eval('$isikolom2 = ' . $cekrumuskolom2 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom2 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom2 = 0;');
                                            }
                                            //echo $isikolom2."<br>";
                                        } else if (strstr($rumuskolom2, "totalkolom")) {
                                            $cekrumuskolom2 = $rumuskolom2;
                                            for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                $cekrumuskolom2 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom2);
                                            }
                                            $tempformula_string5 = explode("/", $cekrumuskolom2);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom2 = str_replace("0/0", "0", $cekrumuskolom2);
                                                    $cekrumuskolom2 = str_replace("0/(0+0)", "0", $cekrumuskolom2);
                                                    $cekrumuskolom2 = str_replace("/0", "*0", $cekrumuskolom2);
                                                }
                                            }
                                            try {
                                                eval('$isikolom2 = ' . $cekrumuskolom2 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom2 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom2 = 0;');
                                            }
                                            //echo $isikolom2."<br>";
                                        } else {
                                            $isikolom2 = (float) $rowdata['kolom2'];
                                        }
                                        $totalkolom2 = $totalkolom2 + (float) $isikolom2;

                                        #kolom2

                                        #kolom3
                                        if (strstr($rumuskolom3, "IKSK")) {
                                            $temprumuskolom3 = explode("+", $rumuskolom3);
                                            for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom3); $jumlahtambah++) {
                                                $sqlcekhitungan = "select id,hitungan_pk from tb_indikator_awal where deleted = '0'
                                                and kode_unique = '" . $temprumuskolom3[$jumlahtambah] . "'";
                                                $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                                while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                    $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                                }
                                                if ($hitungan_pk == '1') {
                                                    $sqlskortahunsekarang = "select sum(target) as target
                                                    from tb_data_pk_awal
                                                    where deleted = '0' 
                                                    and kode_tahun='" . $tahun . "'
                                                    and id_indikator = (select id from tb_indikator_awal where deleted='0' and kode_unique = '" . $temprumuskolom3[$jumlahtambah] . "')
                                                    and tahun = '" . $x . "'";
                                                    //echo $sqlskortahunsekarang;
                                                    $resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
                                                    $numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
                                                    if ($numskortahunsekarang > 0) {
                                                        while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
                                                            $target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
                                                        }
                                                    } else {
                                                        $target_tahun_berjalan = 0;
                                                    }
                                                }
                                                $isikolom3 = $isikolom3 + $target_tahun_berjalan;
                                            }
                                        } else if (strstr($rumuskolom3, "ambildatasebelum")) {
                                            $ambildatasebelum = 0;
                                            if ($x == $tahun_awal) {
                                                array_push($arraykolom3, (float) $baselinekolom3);
                                                $ambildatasebelum = ((float) $baselinekolom3);
                                            } else {
                                                array_push($arraykolom3, (float) $rowdata['kolom3'] + (float) $arraykolom3[$s - 1]);
                                                $ambildatasebelum = ((float) $rowdata['kolom3'] + (float) $arraykolom3[$s - 1]);
                                            }
                                            $cekrumuskolom3 = str_replace("ambildatasebelumbaseline3", $ambildatasebelum, $rumuskolom3);
                                            //echo $cekrumuskolom3."<br>";
                                            $tempformula_string5 = explode("/", $cekrumuskolom3);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom3 = str_replace("0/0", "0", $cekrumuskolom3);
                                                    $cekrumuskolom3 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
                                                    $cekrumuskolom3 = str_replace("/0", "*0", $cekrumuskolom3);
                                                }
                                            }
                                            try {
                                                eval('$isikolom3 = ' . $cekrumuskolom3 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom3 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom3 = 0;');
                                            }
                                        } else if (strstr($rumuskolom3, "given")) {
                                            $cekrumuskolom3 = $rumuskolom3;
                                            for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                $cekrumuskolom3 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom3);
                                            }
                                            $tempformula_string5 = explode("/", $cekrumuskolom3);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom3 = str_replace("0/0", "0", $cekrumuskolom3);
                                                    $cekrumuskolom3 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
                                                    $cekrumuskolom3 = str_replace("/0", "*0", $cekrumuskolom3);
                                                }
                                            }
                                            try {
                                                eval('$isikolom3 = ' . $cekrumuskolom3 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom3 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom3 = 0;');
                                            }
                                        } else if (strstr($rumuskolom3, "isikolom")) {
                                            $cekrumuskolom3 = $rumuskolom3;
                                            for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                $cekrumuskolom3 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom3);
                                            }
                                            $tempformula_string5 = explode("/", $cekrumuskolom3);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom3 = str_replace("0/0", "0", $cekrumuskolom3);
                                                    $cekrumuskolom3 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
                                                    $cekrumuskolom3 = str_replace("/0", "*0", $cekrumuskolom3);
                                                }
                                            }
                                            try {
                                                eval('$isikolom3 = ' . $cekrumuskolom3 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom3 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom3 = 0;');
                                            }
                                            //echo $isikolom3."<br>";
                                        } else if (strstr($rumuskolom3, "totalkolom")) {
                                            $cekrumuskolom3 = $rumuskolom3;
                                            for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                $cekrumuskolom3 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom3);
                                            }
                                            $tempformula_string5 = explode("/", $cekrumuskolom3);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom3 = str_replace("0/0", "0", $cekrumuskolom3);
                                                    $cekrumuskolom3 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
                                                    $cekrumuskolom3 = str_replace("/0", "*0", $cekrumuskolom3);
                                                }
                                            }
                                            try {
                                                eval('$isikolom3 = ' . $cekrumuskolom3 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom3 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom3 = 0;');
                                            }
                                            //echo $isikolom3."<br>";
                                        } else {
                                            $isikolom3 = (float) $rowdata['kolom3'];
                                        }
                                        $totalkolom3 = $totalkolom3 + (float) $isikolom3;

                                        #kolom3

                                        #kolom4
                                        if (strstr($rumuskolom4, "IKSK")) {
                                            $temprumuskolom4 = explode("+", $rumuskolom4);
                                            for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom4); $jumlahtambah++) {
                                                $sqlcekhitungan = "select id,hitungan_pk from tb_indikator_awal where deleted = '0'
                                                and kode_unique = '" . $temprumuskolom4[$jumlahtambah] . "'";
                                                $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                                while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                    $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                                }
                                                if ($hitungan_pk == '1') {
                                                    $sqlskortahunsekarang = "select sum(target) as target
                                                    from tb_data_pk_awal
                                                    where deleted = '0' 
                                                    and kode_tahun='" . $tahun . "'
                                                    and id_indikator = (select id from tb_indikator_awal where deleted='0' and kode_unique = '" . $temprumuskolom4[$jumlahtambah] . "')
                                                    and tahun = '" . $x . "'";
                                                    //echo $sqlskortahunsekarang;
                                                    $resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
                                                    $numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
                                                    if ($numskortahunsekarang > 0) {
                                                        while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
                                                            $target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
                                                        }
                                                    } else {
                                                        $target_tahun_berjalan = 0;
                                                    }
                                                }
                                                $isikolom4 = $isikolom4 + $target_tahun_berjalan;
                                            }
                                        } else if (strstr($rumuskolom4, "ambildatasebelum")) {
                                            $ambildatasebelum = 0;
                                            if ($x == $tahun_awal) {
                                                array_push($arraykolom4, (float) $baselinekolom4);
                                                $ambildatasebelum = ((float) $baselinekolom4);
                                            } else {
                                                array_push($arraykolom4, (float) $rowdata['kolom4'] + (float) $arraykolom4[$s - 1]);
                                                $ambildatasebelum = ((float) $rowdata['kolom4'] + (float) $arraykolom4[$s - 1]);
                                            }
                                            $cekrumuskolom4 = str_replace("ambildatasebelumbaseline4", $ambildatasebelum, $rumuskolom4);
                                            //echo $cekrumuskolom4."<br>";
                                            $tempformula_string5 = explode("/", $cekrumuskolom4);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom4 = str_replace("0/0", "0", $cekrumuskolom4);
                                                    $cekrumuskolom4 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
                                                    $cekrumuskolom4 = str_replace("/0", "*0", $cekrumuskolom4);
                                                }
                                            }
                                            try {
                                                eval('$isikolom4 = ' . $cekrumuskolom4 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom4 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom4 = 0;');
                                            }
                                        } else if (strstr($rumuskolom4, "given")) {
                                            $cekrumuskolom4 = $rumuskolom4;
                                            for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                $cekrumuskolom4 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom4);
                                            }
                                            try {
                                                eval('$isikolom4 = ' . $cekrumuskolom4 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom4 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom4 = 0;');
                                            }
                                        } else if (strstr($rumuskolom4, "isikolom")) {
                                            $cekrumuskolom4 = $rumuskolom4;
                                            for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                $cekrumuskolom4 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom4);
                                            }
                                            $tempformula_string5 = explode("/", $cekrumuskolom4);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom4 = str_replace("0/0", "0", $cekrumuskolom4);
                                                    $cekrumuskolom4 = str_replace("0/(0+0)", "0", $cekrumuskolom4);
                                                    $cekrumuskolom4 = str_replace("/0", "*0", $cekrumuskolom4);
                                                }
                                            }
                                            try {
                                                eval('$isikolom4 = ' . $cekrumuskolom4 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom4 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom4 = 0;');
                                            }
                                            //echo $isikolom4."<br>";
                                        } else if (strstr($rumuskolom4, "totalkolom")) {
                                            $cekrumuskolom4 = $rumuskolom4;
                                            for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                $cekrumuskolom4 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom4);
                                            }
                                            $tempformula_string5 = explode("/", $cekrumuskolom4);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom4 = str_replace("0/0", "0", $cekrumuskolom4);
                                                    $cekrumuskolom4 = str_replace("0/(0+0)", "0", $cekrumuskolom4);
                                                    $cekrumuskolom4 = str_replace("/0", "*0", $cekrumuskolom4);
                                                }
                                            }
                                            try {
                                                eval('$isikolom4 = ' . $cekrumuskolom4 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom4 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom4 = 0;');
                                            }
                                            //echo $isikolom4."<br>";
                                        } else {
                                            $isikolom4 = (float) $rowdata['kolom4'];
                                        }
                                        $totalkolom4 = $totalkolom4 + (float) $isikolom4;

                                        #kolom4

                                        #kolom5
                                        if (strstr($rumuskolom5, "IKSK")) {
                                            $temprumuskolom5 = explode("+", $rumuskolom5);
                                            for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom5); $jumlahtambah++) {
                                                $sqlcekhitungan = "select id,hitungan_pk from tb_indikator_awal where deleted = '0'
                                                and kode_unique = '" . $temprumuskolom5[$jumlahtambah] . "'";
                                                $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                                while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                    $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                                }
                                                if ($hitungan_pk == '1') {
                                                    $sqlskortahunsekarang = "select sum(target) as target
                                                    from tb_data_pk_awal
                                                    where deleted = '0'  
                                                    and kode_tahun='" . $tahun . "'
                                                    and id_indikator = (select id from tb_indikator_awal where deleted='0' and kode_unique = '" . $temprumuskolom5[$jumlahtambah] . "')
                                                    and tahun = '" . $x . "'";
                                                    //echo $sqlskortahunsekarang;
                                                    $resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
                                                    $numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
                                                    if ($numskortahunsekarang > 0) {
                                                        while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
                                                            $target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
                                                        }
                                                    } else {
                                                        $target_tahun_berjalan = 0;
                                                    }
                                                }
                                                $isikolom5 = $isikolom5 + $target_tahun_berjalan;
                                            }
                                        } else if (strstr($rumuskolom5, "ambildatasebelum")) {
                                            $ambildatasebelum = 0;
                                            if ($x == $tahun_awal) {
                                                array_push($arraykolom5, (float) $baselinekolom5);
                                                $ambildatasebelum = ((float) $baselinekolom5);
                                            } else {
                                                array_push($arraykolom5, (float) $rowdata['kolom5'] + (float) $arraykolom5[$s - 1]);
                                                $ambildatasebelum = ((float) $rowdata['kolom5'] + (float) $arraykolom5[$s - 1]);
                                            }
                                            $cekrumuskolom5 = str_replace("ambildatasebelumbaseline5", $ambildatasebelum, $rumuskolom5);
                                            //echo $cekrumuskolom5."<br>";
                                            $tempformula_string5 = explode("/", $cekrumuskolom5);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
                                                    $cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
                                                    $cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
                                                }
                                            }
                                            try {
                                                eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom5 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom5 = 0;');
                                            }
                                        } else if (strstr($rumuskolom5, "given")) {
                                            $cekrumuskolom5 = $rumuskolom5;
                                            for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                $cekrumuskolom5 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom5);
                                            }
                                            $tempformula_string5 = explode("/", $cekrumuskolom5);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
                                                    $cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
                                                    $cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
                                                }
                                            }
                                            try {
                                                eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom5 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom5 = 0;');
                                            }
                                        } else if (strstr($rumuskolom5, "isikolom")) {
                                            $cekrumuskolom5 = $rumuskolom5;
                                            for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                $cekrumuskolom5 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom5);
                                            }
                                            $tempformula_string5 = explode("/", $cekrumuskolom5);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                //echo $tempformula_string5[$ceknol]."<br>";
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
                                                    $cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
                                                    $cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
                                                }
                                            }
                                            //echo $cekrumuskolom5."<br>";
                                            try {
                                                eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom5 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom5 = 0;');
                                            }
                                            //echo $isikolom5."<br>";
                                        } else if (strstr($rumuskolom5, "totalkolom")) {
                                            $cekrumuskolom5 = $rumuskolom5;
                                            for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                $cekrumuskolom5 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom5);
                                            }
                                            $tempformula_string5 = explode("/", $cekrumuskolom5);
                                            for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                //echo $tempformula_string5[$ceknol]."<br>";
                                                if ((float) $tempformula_string5[$ceknol] == 0) {
                                                    $cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
                                                    $cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
                                                    $cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
                                                }
                                            }
                                            //echo $cekrumuskolom5."<br>";
                                            try {
                                                eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
                                            } catch (DivisionByZeroError $e) {
                                                eval('$isikolom5 = 0;');
                                            } catch (ParseError $e) {
                                                eval('$isikolom5 = 0;');
                                            }
                                            //echo $isikolom5."<br>";
                                        } else {
                                            $isikolom5 = (float) $rowdata['kolom5'];
                                        }
                                        $totalkolom5 = $totalkolom5 + (float) $isikolom5;

                                        #kolom5
                                        //echo $isikolom5."<br>";
                                        //echo $totalkolom1."<br>";
                                    }

                                    if ((float) $belakangkoma == 3) {
                                        $finalnyakolomisian4 = replace_dot_koma_tiga($isikolom4);
                                        $finalnyakolomisian5 = replace_dot_koma_tiga($isikolom5);
                                    } else {
                                        $finalnyakolomisian4 = replace_dot_koma($isikolom4);
                                        $finalnyakolomisian5 = replace_dot_koma($isikolom5);
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
                                        'kolom1' => replace_dot_koma((float) $isikolom1),
                                        'kolom2' => replace_dot_koma((float) $isikolom2),
                                        'kolom3' => replace_dot_koma((float) $isikolom3),
                                        'kolom4' => $finalnyakolomisian4,
                                        'kolom5' => $finalnyakolomisian5,
                                    ];
                                }
                            }
                        }
                        $s++;
                        $myArraydata[] = (object)
                        [
                            'tahun' => $x,
                            'detail' => $myArraydetail,
                        ];
                    }

                    if ((float) $belakangkoma == 3) {
                        $finalnyakolom4 = replace_dot_koma_tiga($row['kolom4']);
                        $finalnyakolom5 = replace_dot_koma_tiga($row['kolom5']);
                    } else {
                        $finalnyakolom4 = replace_dot_koma($row['kolom4']);
                        $finalnyakolom5 = replace_dot_koma($row['kolom5']);
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
                        'jumlahkomponen' => $jumlahkomponen,
                        'kolomkomponen' => $myArraykolomkomponen,
                        'bobotkomponen' => $myArraybobotkomponen,
                        'rumuskomponen' => $myArrayrumuskomponen,
                        'kolom1' => replace_dot_koma($row['kolom1']),
                        'kolom2' => replace_dot_koma($row['kolom2']),
                        'kolom3' => replace_dot_koma($row['kolom3']),
                        'kolom4' => $finalnyakolom4,
                        'kolom5' => $finalnyakolom5,
                        'isian_kolom' => $row['isian_kolom'],
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

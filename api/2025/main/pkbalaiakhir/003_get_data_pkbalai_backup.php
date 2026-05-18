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
            $rumuskolom1 = $rowindikator['rumuskolom1'];
            $rumuskolom2 = $rowindikator['rumuskolom2'];
            $rumuskolom3 = $rowindikator['rumuskolom3'];
            $rumuskolom4 = $rowindikator['rumuskolom4'];
            $rumuskolom5 = $rowindikator['rumuskolom5'];
            $isian_kolom = $rowindikator['isian_kolom'];
        }

        $sql = "select a.id,a.kode_tahun,a.id_indikator,a.id_balai,a.kode_balai,a.kolom1 
        ,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.isian_kolom, b.nama as tahun
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
                        ,a.kolom4,a.kolom5,a.isian_kolom
                        from tb_data_pk_balai a where a.deleted='0' and a.tahun='" . $x . "' " . $filterquery . "";
                        $resultdata = mysqli_query($link, $sqldata);
                        $numdata = mysqli_num_rows($resultdata);
                        if ($numdata > 0) {
                            if ($resultdata) {
                                while ($rowdata = mysqli_fetch_assoc($resultdata)) {
                                    #kolom1
                                    if (strstr($rumuskolom1, "IKSK")) {
                                        $countIKSK = substr_count($rumuskolom1, "IKSK");
                                        if ($countIKSK == 1) {
                                            $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                            and id = '" . $id . "'";
                                            $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                            while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                            }
                                            //echo $hitungan_pk;
                                            //die;

                                            $sqlskortahunsekarang = "select sum(target) as target
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom1 . "')
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


                                            $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                            ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom1 . "')
                                            and tahun = '" . $x . "'";
                                            //echo $sqlskor_komponen_tahun_berjalan;
                                            $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                            $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                            if ($numskor_komponen_tahun_berjalan  > 0) {
                                                while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                                    $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                                    $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                                    $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                                    $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                                }
                                            } else {
                                                $target_komponen_tahun_berjalan = 0;
                                            }

                                            if ($hitungan_pk == '2') {
                                                $isikolom1 = $target_komponen_tahun_berjalan;
                                            } else {
                                                $isikolom1 = $target_tahun_berjalan;
                                            }
                                        }
                                        if ($countIKSK > 1) {
                                            $tempexplode = "";
                                            if (strstr($rumuskolom1, "+")) {
                                                $tempexplode = "+";
                                            }
                                            if (strstr($rumuskolom1, "-")) {
                                                $tempexplode = "-";
                                            }
                                            if (strstr($rumuskolom1, "/")) {
                                                $tempexplode = "/";
                                            }
                                            if (strstr($rumuskolom1, "*")) {
                                                $tempexplode = "*";
                                            }
                                            $tempIKSK = explode($tempexplode, $rumuskolom1);
                                            $nilaiIKSK = array();
                                            //////////////
                                            for ($n = 0; $n < count($tempIKSK); $n++) {
                                                ///////////////////
                                                $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                                and id = '" . $id_indikator . "'";
                                                $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                                while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                    $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                                }
                                                //echo $hitungan_pk;

                                                $sqlskortahunsekarang = "select sum(target) as target
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
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

                                                $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                                ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
                                                and tahun = '" . $x . "'";
                                                //echo $sqlskor_komponen_tahun_berjalan;
                                                $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                                $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                                if ($numskor_komponen_tahun_berjalan  > 0) {
                                                    while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                                        $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                                        $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                                        $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                                        $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                                    }
                                                } else {
                                                    $target_komponen_tahun_berjalan = 0;
                                                }


                                                if ($hitungan_pk == '2') {
                                                    $target_tahun_berjalan = $target_komponen_tahun_berjalan;
                                                } else {
                                                    $target_tahun_berjalan = $target_tahun_berjalan;
                                                }

                                                //$tempkolom1[$adi] = $target_tahun_berjalan;
                                                array_push($nilaiIKSK, $target_tahun_berjalan);
                                                $isikolom1 = $target_tahun_berjalan;
                                                ///////////////////
                                            }

                                            $aditnilai = "";
                                            $urutiksk = 1;
                                            foreach ($nilaiIKSK as $iksk) {
                                                if ($urutiksk == 1) {
                                                    $aditnilai = $aditnilai . $iksk;
                                                } else {
                                                    if ($tempexplode == "/" && (float)$iksk == 0) {
                                                        $aditnilai = $aditnilai . "*" . "0";
                                                    } else {
                                                        $aditnilai = $aditnilai . $tempexplode . $iksk;
                                                    }
                                                }
                                                $urutiksk++;
                                            }
                                            $isikolom1 = $aditnilai;
                                            $formula_string1 = $isikolom1;
                                            //echo $formula_string1;
                                            //die;
                                            eval('$isikolom1 = ' . $formula_string1 . ';');
                                            if ($isikolom1 == '') {
                                                $isikolom1 = 0;
                                            }
                                            //////////////
                                        }
                                    } else {
                                        $isikolom1 = $rowdata['kolom1'];
                                    }

                                    #kolom1

                                    #kolom2
                                    if (strstr($rumuskolom2, "IKSK")) {
                                        $countIKSK = substr_count($rumuskolom2, "IKSK");
                                        //echo $countIKSK;
                                        //die;
                                        if ($countIKSK == 1) {
                                            $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                            and id = '" . $id . "'";
                                            $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                            while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                            }
                                            //echo $hitungan_pk;
                                            //die;

                                            $sqlskortahunsekarang = "select sum(target) as target
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom2 . "')
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


                                            $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                            ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom2 . "')
                                            and tahun = '" . $x . "'";
                                            //echo $sqlskor_komponen_tahun_berjalan;
                                            $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                            $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                            if ($numskor_komponen_tahun_berjalan  > 0) {
                                                while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                                    $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                                    $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                                    $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                                    $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                                }
                                            } else {
                                                $target_komponen_tahun_berjalan = 0;
                                            }

                                            if ($hitungan_pk == '2') {
                                                $isikolom2 = $target_komponen_tahun_berjalan;
                                            } else {
                                                $isikolom2 = $target_tahun_berjalan;
                                            }
                                        }
                                        if ($countIKSK > 1) {
                                            $tempexplode = "";
                                            if (strstr($rumuskolom2, "+")) {
                                                $tempexplode = "+";
                                            }
                                            if (strstr($rumuskolom2, "-")) {
                                                $tempexplode = "-";
                                            }
                                            if (strstr($rumuskolom2, "/")) {
                                                $tempexplode = "/";
                                            }
                                            if (strstr($rumuskolom2, "*")) {
                                                $tempexplode = "*";
                                            }
                                            $tempIKSK = explode($tempexplode, $rumuskolom2);
                                            $nilaiIKSK = array();
                                            //////////////
                                            for ($n = 0; $n < count($tempIKSK); $n++) {
                                                ///////////////////
                                                $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                                and id = '" . $id_indikator . "'";
                                                $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                                while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                    $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                                }
                                                //echo $hitungan_pk;

                                                $sqlskortahunsekarang = "select sum(target) as target
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
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

                                                $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                                ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
                                                and tahun = '" . $x . "'";
                                                //echo $sqlskor_komponen_tahun_berjalan;
                                                $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                                $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                                if ($numskor_komponen_tahun_berjalan  > 0) {
                                                    while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                                        $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                                        $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                                        $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                                        $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                                    }
                                                } else {
                                                    $target_komponen_tahun_berjalan = 0;
                                                }


                                                if ($hitungan_pk == '2') {
                                                    $target_tahun_berjalan = $target_komponen_tahun_berjalan;
                                                } else {
                                                    $target_tahun_berjalan = $target_tahun_berjalan;
                                                }

                                                //$tempkolom2[$adi] = $target_tahun_berjalan;
                                                array_push($nilaiIKSK, $target_tahun_berjalan);
                                                $isikolom2 = $target_tahun_berjalan;
                                                ///////////////////
                                            }

                                            $aditnilai = "";
                                            $urutiksk = 1;
                                            foreach ($nilaiIKSK as $iksk) {
                                                if ($urutiksk == 1) {
                                                    $aditnilai = $aditnilai . $iksk;
                                                } else {
                                                    if ($tempexplode == "/" && (float)$iksk == 0) {
                                                        $aditnilai = $aditnilai . "*" . "0";
                                                    } else {
                                                        $aditnilai = $aditnilai . $tempexplode . $iksk;
                                                    }
                                                }
                                                $urutiksk++;
                                            }
                                            $isikolom2 = $aditnilai;
                                            $formula_string2 = $isikolom2;
                                            //echo $formula_string1;
                                            //die;
                                            eval('$isikolom2 = ' . $formula_string2 . ';');
                                            if ($isikolom2 == '') {
                                                $isikolom2 = 0;
                                            }
                                            //////////////
                                        }
                                    } else {
                                        $isikolom2 = $rowdata['kolom2'];
                                    }

                                    #kolom2

                                    #kolom3
                                    if (strstr($rumuskolom3, "IKSK")) {
                                        $countIKSK = substr_count($rumuskolom3, "IKSK");
                                        if ($countIKSK == 1) {
                                            $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                            and id = '" . $id . "'";
                                            $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                            while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                            }
                                            //echo $hitungan_pk;
                                            //die;

                                            $sqlskortahunsekarang = "select sum(target) as target
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom3 . "')
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


                                            $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                            ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom3 . "')
                                            and tahun = '" . $x . "'";
                                            //echo $sqlskor_komponen_tahun_berjalan;
                                            $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                            $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                            if ($numskor_komponen_tahun_berjalan  > 0) {
                                                while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                                    $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                                    $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                                    $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                                    $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                                }
                                            } else {
                                                $target_komponen_tahun_berjalan = 0;
                                            }

                                            if ($hitungan_pk == '2') {
                                                $isikolom3 = $target_komponen_tahun_berjalan;
                                            } else {
                                                $isikolom3 = $target_tahun_berjalan;
                                            }
                                        }

                                        if ($countIKSK > 1) {

                                            $tempexplode = "";
                                            if (strstr($rumuskolom3, "+")) {
                                                $tempexplode = "+";
                                            }
                                            if (strstr($rumuskolom3, "-")) {
                                                $tempexplode = "-";
                                            }
                                            if (strstr($rumuskolom3, "/")) {
                                                $tempexplode = "/";
                                            }
                                            if (strstr($rumuskolom3, "*")) {
                                                $tempexplode = "*";
                                            }

                                            $tempIKSK = explode($tempexplode, $rumuskolom3);
                                            $nilaiIKSK = array();
                                            //////////////
                                            for ($n = 0; $n < count($tempIKSK); $n++) {
                                                ///////////////////
                                                $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                                and id = '" . $id_indikator . "'";
                                                $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                                while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                    $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                                }
                                                //echo $hitungan_pk;

                                                $sqlskortahunsekarang = "select sum(target) as target
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
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

                                                $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                                ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
                                                and tahun = '" . $x . "'";
                                                //echo $sqlskor_komponen_tahun_berjalan;
                                                $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                                $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                                if ($numskor_komponen_tahun_berjalan  > 0) {
                                                    while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                                        $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                                        $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                                        $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                                        $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                                    }
                                                } else {
                                                    $target_komponen_tahun_berjalan = 0;
                                                }


                                                if ($hitungan_pk == '2') {
                                                    $target_tahun_berjalan = $target_komponen_tahun_berjalan;
                                                } else {
                                                    $target_tahun_berjalan = $target_tahun_berjalan;
                                                }

                                                //$tempkolom3[$adi] = $target_tahun_berjalan;
                                                array_push($nilaiIKSK, $target_tahun_berjalan);
                                                $isikolom3 = $target_tahun_berjalan;
                                                ///////////////////
                                            }

                                            $aditnilai = "";
                                            $urutiksk = 1;
                                            foreach ($nilaiIKSK as $iksk) {
                                                if ($urutiksk == 1) {
                                                    $aditnilai = $aditnilai . $iksk;
                                                } else {
                                                    if ($tempexplode == "/" && (float)$iksk == 0) {
                                                        $aditnilai = $aditnilai . "*" . "0";
                                                    } else {
                                                        $aditnilai = $aditnilai . $tempexplode . $iksk;
                                                    }
                                                }
                                                $urutiksk++;
                                            }
                                            $isikolom3 = $aditnilai;
                                            $formula_string3 = $isikolom3;
                                            //echo $formula_string1;
                                            //die;
                                            eval('$isikolom3 = ' . $formula_string3 . ';');
                                            if ($isikolom3 == '') {
                                                $isikolom3 = 0;
                                            }
                                            //////////////
                                        }
                                    } else {
                                        $isikolom3 = $rowdata['kolom3'];
                                    }
                                    #kolom3

                                    #kolom4
                                    if (strstr($rumuskolom4, "IKSK")) {
                                        $countIKSK = substr_count($rumuskolom4, "IKSK");
                                        if ($countIKSK == 1) {
                                            $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                            and id = '" . $id . "'";
                                            $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                            while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                            }
                                            //echo $hitungan_pk;
                                            //die;

                                            $sqlskortahunsekarang = "select sum(target) as target
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom4 . "')
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


                                            $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                            ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom4 . "')
                                            and tahun = '" . $x . "'";
                                            //echo $sqlskor_komponen_tahun_berjalan;
                                            $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                            $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                            if ($numskor_komponen_tahun_berjalan  > 0) {
                                                while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                                    $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                                    $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                                    $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                                    $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                                }
                                            } else {
                                                $target_komponen_tahun_berjalan = 0;
                                            }

                                            if ($hitungan_pk == '2') {
                                                $isikolom4 = $target_komponen_tahun_berjalan;
                                            } else {
                                                $isikolom4 = $target_tahun_berjalan;
                                            }
                                        }

                                        if ($countIKSK > 1) {

                                            $tempexplode = "";
                                            if (strstr($rumuskolom4, "+")) {
                                                $tempexplode = "+";
                                            }
                                            if (strstr($rumuskolom4, "-")) {
                                                $tempexplode = "-";
                                            }
                                            if (strstr($rumuskolom4, "/")) {
                                                $tempexplode = "/";
                                            }
                                            if (strstr($rumuskolom4, "*")) {
                                                $tempexplode = "*";
                                            }

                                            $tempIKSK = explode($tempexplode, $rumuskolom4);
                                            $nilaiIKSK = array();
                                            //////////////
                                            for ($n = 0; $n < count($tempIKSK); $n++) {
                                                ///////////////////
                                                $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                                and id = '" . $id_indikator . "'";
                                                $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                                while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                    $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                                }
                                                //echo $hitungan_pk;

                                                $sqlskortahunsekarang = "select sum(target) as target
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
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

                                                $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                                ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
                                                and tahun = '" . $x . "'";
                                                //echo $sqlskor_komponen_tahun_berjalan;
                                                $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                                $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                                if ($numskor_komponen_tahun_berjalan  > 0) {
                                                    while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                                        $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                                        $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                                        $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                                        $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                                    }
                                                } else {
                                                    $target_komponen_tahun_berjalan = 0;
                                                }


                                                if ($hitungan_pk == '2') {
                                                    $target_tahun_berjalan = $target_komponen_tahun_berjalan;
                                                } else {
                                                    $target_tahun_berjalan = $target_tahun_berjalan;
                                                }

                                                //$tempkolom4[$adi] = $target_tahun_berjalan;
                                                array_push($nilaiIKSK, $target_tahun_berjalan);
                                                $isikolom4 = $target_tahun_berjalan;
                                                ///////////////////
                                            }

                                            $aditnilai = "";
                                            $urutiksk = 1;
                                            foreach ($nilaiIKSK as $iksk) {
                                                if ($urutiksk == 1) {
                                                    $aditnilai = $aditnilai . $iksk;
                                                } else {
                                                    if ($tempexplode == "/" && (float)$iksk == 0) {
                                                        $aditnilai = $aditnilai . "*" . "0";
                                                    } else {
                                                        $aditnilai = $aditnilai . $tempexplode . $iksk;
                                                    }
                                                }
                                                $urutiksk++;
                                            }
                                            $isikolom4 = $aditnilai;
                                            $formula_string4 = $isikolom4;
                                            //echo $formula_string1;
                                            //die;
                                            eval('$isikolom4 = ' . $formula_string4 . ';');
                                            if ($isikolom4 == '') {
                                                $isikolom4 = 0;
                                            }
                                            //////////////
                                        }
                                    } else {
                                        $isikolom4 = $rowdata['kolom4'];
                                    }
                                    #kolom4

                                    #kolom5
                                    if (strstr($rumuskolom5, "IKSK")) {
                                        $countIKSK = substr_count($rumuskolom5, "IKSK");
                                        if ($countIKSK == 1) {
                                            $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                            and id = '" . $id . "'";
                                            $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                            while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                            }
                                            //echo $hitungan_pk;
                                            //die;

                                            $sqlskortahunsekarang = "select sum(target) as target
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom5 . "')
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


                                            $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                            ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom5 . "')
                                            and tahun = '" . $x . "'";
                                            //echo $sqlskor_komponen_tahun_berjalan;
                                            $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                            $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                            if ($numskor_komponen_tahun_berjalan  > 0) {
                                                while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                                    $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                                    $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                                    $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                                    $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                                }
                                            } else {
                                                $target_komponen_tahun_berjalan = 0;
                                            }

                                            if ($hitungan_pk == '2') {
                                                $isikolom5 = $target_komponen_tahun_berjalan;
                                            } else {
                                                $isikolom5 = $target_tahun_berjalan;
                                            }
                                        }

                                        if ($countIKSK > 1) {

                                            $tempexplode = "";
                                            if (strstr($rumuskolom5, "+")) {
                                                $tempexplode = "+";
                                            }
                                            if (strstr($rumuskolom5, "-")) {
                                                $tempexplode = "-";
                                            }
                                            if (strstr($rumuskolom5, "/")) {
                                                $tempexplode = "/";
                                            }
                                            if (strstr($rumuskolom5, "*")) {
                                                $tempexplode = "*";
                                            }

                                            $tempIKSK = explode($tempexplode, $rumuskolom5);
                                            $nilaiIKSK = array();
                                            //////////////
                                            for ($n = 0; $n < count($tempIKSK); $n++) {
                                                ///////////////////
                                                $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                                and id = '" . $id_indikator . "'";
                                                $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                                while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                    $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                                }
                                                //echo $hitungan_pk;

                                                $sqlskortahunsekarang = "select sum(target) as target
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
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

                                                $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                                ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
                                                and tahun = '" . $x . "'";
                                                //echo $sqlskor_komponen_tahun_berjalan;
                                                $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                                $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                                if ($numskor_komponen_tahun_berjalan  > 0) {
                                                    while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                                        $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                                        $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                                        $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                                        $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                                    }
                                                } else {
                                                    $target_komponen_tahun_berjalan = 0;
                                                }


                                                if ($hitungan_pk == '2') {
                                                    $target_tahun_berjalan = $target_komponen_tahun_berjalan;
                                                } else {
                                                    $target_tahun_berjalan = $target_tahun_berjalan;
                                                }

                                                //$tempkolom5[$adi] = $target_tahun_berjalan;
                                                array_push($nilaiIKSK, $target_tahun_berjalan);
                                                $isikolom5 = $target_tahun_berjalan;
                                                ///////////////////
                                            }

                                            $aditnilai = "";
                                            $urutiksk = 1;
                                            foreach ($nilaiIKSK as $iksk) {
                                                if ($urutiksk == 1) {
                                                    $aditnilai = $aditnilai . $iksk;
                                                } else {
                                                    if ($tempexplode == "/" && (float)$iksk == 0) {
                                                        $aditnilai = $aditnilai . "*" . "0";
                                                    } else {
                                                        $aditnilai = $aditnilai . $tempexplode . $iksk;
                                                    }
                                                }
                                                $urutiksk++;
                                            }
                                            $isikolom5 = $aditnilai;
                                            $formula_string5 = $isikolom5;
                                            //echo $formula_string1;
                                            //die;
                                            eval('$isikolom5 = ' . $formula_string5 . ';');
                                            if ($isikolom5 == '') {
                                                $isikolom5 = 0;
                                            }
                                            //////////////
                                        }
                                    } else {
                                        $isikolom5 = $rowdata['kolom5'];
                                    }
                                    //echo "aaaa".$isikolom2;
                                    #kolom5

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
                                    ];
                                }
                            }
                        } else {
                            //echo $rumuskolom2;
                            //die;
                            #kolom1
                            if (strstr($rumuskolom1, "IKSK")) {
                                $countIKSK = substr_count($rumuskolom1, "IKSK");
                                if ($countIKSK == 1) {
                                    $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                            and id = '" . $id . "'";
                                    $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                    while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                        $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                    }
                                    //echo $hitungan_pk;
                                    //die;

                                    $sqlskortahunsekarang = "select sum(target) as target
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom1 . "')
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


                                    $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                            ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom1 . "')
                                            and tahun = '" . $x . "'";
                                    //echo $sqlskor_komponen_tahun_berjalan;
                                    $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                    $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                    if ($numskor_komponen_tahun_berjalan  > 0) {
                                        while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                            $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                            $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                            $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                            $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                        }
                                    } else {
                                        $target_komponen_tahun_berjalan = 0;
                                    }

                                    if ($hitungan_pk == '2') {
                                        $isikolom1 = $target_komponen_tahun_berjalan;
                                    } else {
                                        $isikolom1 = $target_tahun_berjalan;
                                    }
                                }
                                if ($countIKSK > 1) {
                                    $tempexplode = "";
                                    if (strstr($rumuskolom1, "+")) {
                                        $tempexplode = "+";
                                    }
                                    if (strstr($rumuskolom1, "-")) {
                                        $tempexplode = "-";
                                    }
                                    if (strstr($rumuskolom1, "/")) {
                                        $tempexplode = "/";
                                    }
                                    if (strstr($rumuskolom1, "*")) {
                                        $tempexplode = "*";
                                    }
                                    $tempIKSK = explode($tempexplode, $rumuskolom1);
                                    $nilaiIKSK = array();
                                    //////////////
                                    for ($n = 0; $n < count($tempIKSK); $n++) {
                                        ///////////////////
                                        $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                                and id = '" . $id_indikator . "'";
                                        $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                        while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                            $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                        }
                                        //echo $hitungan_pk;

                                        $sqlskortahunsekarang = "select sum(target) as target
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
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

                                        $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                                ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
                                                and tahun = '" . $x . "'";
                                        //echo $sqlskor_komponen_tahun_berjalan;
                                        $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                        $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                        if ($numskor_komponen_tahun_berjalan  > 0) {
                                            while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                                $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                                $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                                $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                                $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                            }
                                        } else {
                                            $target_komponen_tahun_berjalan = 0;
                                        }


                                        if ($hitungan_pk == '2') {
                                            $target_tahun_berjalan = $target_komponen_tahun_berjalan;
                                        } else {
                                            $target_tahun_berjalan = $target_tahun_berjalan;
                                        }

                                        //$tempkolom1[$adi] = $target_tahun_berjalan;
                                        array_push($nilaiIKSK, $target_tahun_berjalan);
                                        $isikolom1 = $target_tahun_berjalan;
                                        ///////////////////
                                    }

                                    $aditnilai = "";
                                    $urutiksk = 1;
                                    foreach ($nilaiIKSK as $iksk) {
                                        if ($urutiksk == 1) {
                                            $aditnilai = $aditnilai . $iksk;
                                        } else {
                                            if ($tempexplode == "/" && (float)$iksk == 0) {
                                                $aditnilai = $aditnilai . "*" . "0";
                                            } else {
                                                $aditnilai = $aditnilai . $tempexplode . $iksk;
                                            }
                                        }
                                        $urutiksk++;
                                    }
                                    $isikolom1 = $aditnilai;
                                    $formula_string1 = $isikolom1;
                                    //echo $formula_string1;
                                    //die;
                                    eval('$isikolom1 = ' . $formula_string1 . ';');
                                    if ($isikolom1 == '') {
                                        $isikolom1 = 0;
                                    }
                                    //////////////
                                }
                            } else {
                                $isikolom1 = $rowdata['kolom1'];
                            }

                            #kolom1

                            #kolom2
                            if (strstr($rumuskolom2, "IKSK")) {
                                $countIKSK = substr_count($rumuskolom2, "IKSK");
                                //echo $countIKSK;
                                //die;
                                if ($countIKSK == 1) {
                                    $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                            and id = '" . $id . "'";
                                    $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                    while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                        $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                    }
                                    //echo $hitungan_pk;
                                    //die;

                                    $sqlskortahunsekarang = "select sum(target) as target
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom2 . "')
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


                                    $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                            ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom2 . "')
                                            and tahun = '" . $x . "'";
                                    //echo $sqlskor_komponen_tahun_berjalan;
                                    $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                    $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                    if ($numskor_komponen_tahun_berjalan  > 0) {
                                        while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                            $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                            $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                            $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                            $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                        }
                                    } else {
                                        $target_komponen_tahun_berjalan = 0;
                                    }

                                    if ($hitungan_pk == '2') {
                                        $isikolom2 = $target_komponen_tahun_berjalan;
                                    } else {
                                        $isikolom2 = $target_tahun_berjalan;
                                    }
                                }
                                if ($countIKSK > 1) {
                                    $tempexplode = "";
                                    if (strstr($rumuskolom2, "+")) {
                                        $tempexplode = "+";
                                    }
                                    if (strstr($rumuskolom2, "-")) {
                                        $tempexplode = "-";
                                    }
                                    if (strstr($rumuskolom2, "/")) {
                                        $tempexplode = "/";
                                    }
                                    if (strstr($rumuskolom2, "*")) {
                                        $tempexplode = "*";
                                    }
                                    $tempIKSK = explode($tempexplode, $rumuskolom2);
                                    $nilaiIKSK = array();
                                    //////////////
                                    for ($n = 0; $n < count($tempIKSK); $n++) {
                                        ///////////////////
                                        $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                                and id = '" . $id_indikator . "'";
                                        $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                        while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                            $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                        }
                                        //echo $hitungan_pk;

                                        $sqlskortahunsekarang = "select sum(target) as target
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
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

                                        $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                                ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
                                                and tahun = '" . $x . "'";
                                        //echo $sqlskor_komponen_tahun_berjalan;
                                        $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                        $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                        if ($numskor_komponen_tahun_berjalan  > 0) {
                                            while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                                $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                                $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                                $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                                $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                            }
                                        } else {
                                            $target_komponen_tahun_berjalan = 0;
                                        }


                                        if ($hitungan_pk == '2') {
                                            $target_tahun_berjalan = $target_komponen_tahun_berjalan;
                                        } else {
                                            $target_tahun_berjalan = $target_tahun_berjalan;
                                        }

                                        //$tempkolom2[$adi] = $target_tahun_berjalan;
                                        array_push($nilaiIKSK, $target_tahun_berjalan);
                                        $isikolom2 = $target_tahun_berjalan;
                                        ///////////////////
                                    }

                                    $aditnilai = "";
                                    $urutiksk = 1;
                                    foreach ($nilaiIKSK as $iksk) {
                                        if ($urutiksk == 1) {
                                            $aditnilai = $aditnilai . $iksk;
                                        } else {
                                            if ($tempexplode == "/" && (float)$iksk == 0) {
                                                $aditnilai = $aditnilai . "*" . "0";
                                            } else {
                                                $aditnilai = $aditnilai . $tempexplode . $iksk;
                                            }
                                        }
                                        $urutiksk++;
                                    }
                                    $isikolom2 = $aditnilai;
                                    $formula_string2 = $isikolom2;
                                    //echo $formula_string1;
                                    //die;
                                    eval('$isikolom2 = ' . $formula_string2 . ';');
                                    if ($isikolom2 == '') {
                                        $isikolom2 = 0;
                                    }
                                    //////////////
                                }
                            } else {
                                $isikolom2 = $rowdata['kolom2'];
                            }

                            #kolom2

                            #kolom3
                            if (strstr($rumuskolom3, "IKSK")) {
                                $countIKSK = substr_count($rumuskolom3, "IKSK");
                                if ($countIKSK == 1) {
                                    $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                            and id = '" . $id . "'";
                                    $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                    while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                        $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                    }
                                    //echo $hitungan_pk;
                                    //die;

                                    $sqlskortahunsekarang = "select sum(target) as target
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom3 . "')
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


                                    $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                            ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom3 . "')
                                            and tahun = '" . $x . "'";
                                    //echo $sqlskor_komponen_tahun_berjalan;
                                    $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                    $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                    if ($numskor_komponen_tahun_berjalan  > 0) {
                                        while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                            $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                            $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                            $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                            $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                        }
                                    } else {
                                        $target_komponen_tahun_berjalan = 0;
                                    }

                                    if ($hitungan_pk == '2') {
                                        $isikolom3 = $target_komponen_tahun_berjalan;
                                    } else {
                                        $isikolom3 = $target_tahun_berjalan;
                                    }
                                }

                                if ($countIKSK > 1) {

                                    $tempexplode = "";
                                    if (strstr($rumuskolom3, "+")) {
                                        $tempexplode = "+";
                                    }
                                    if (strstr($rumuskolom3, "-")) {
                                        $tempexplode = "-";
                                    }
                                    if (strstr($rumuskolom3, "/")) {
                                        $tempexplode = "/";
                                    }
                                    if (strstr($rumuskolom3, "*")) {
                                        $tempexplode = "*";
                                    }

                                    $tempIKSK = explode($tempexplode, $rumuskolom3);
                                    $nilaiIKSK = array();
                                    //////////////
                                    for ($n = 0; $n < count($tempIKSK); $n++) {
                                        ///////////////////
                                        $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                                and id = '" . $id_indikator . "'";
                                        $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                        while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                            $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                        }
                                        //echo $hitungan_pk;

                                        $sqlskortahunsekarang = "select sum(target) as target
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
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

                                        $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                                ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
                                                and tahun = '" . $x . "'";
                                        //echo $sqlskor_komponen_tahun_berjalan;
                                        $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                        $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                        if ($numskor_komponen_tahun_berjalan  > 0) {
                                            while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                                $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                                $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                                $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                                $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                            }
                                        } else {
                                            $target_komponen_tahun_berjalan = 0;
                                        }


                                        if ($hitungan_pk == '2') {
                                            $target_tahun_berjalan = $target_komponen_tahun_berjalan;
                                        } else {
                                            $target_tahun_berjalan = $target_tahun_berjalan;
                                        }

                                        //$tempkolom3[$adi] = $target_tahun_berjalan;
                                        array_push($nilaiIKSK, $target_tahun_berjalan);
                                        $isikolom3 = $target_tahun_berjalan;
                                        ///////////////////
                                    }

                                    $aditnilai = "";
                                    $urutiksk = 1;
                                    foreach ($nilaiIKSK as $iksk) {
                                        if ($urutiksk == 1) {
                                            $aditnilai = $aditnilai . $iksk;
                                        } else {
                                            if ($tempexplode == "/" && (float)$iksk == 0) {
                                                $aditnilai = $aditnilai . "*" . "0";
                                            } else {
                                                $aditnilai = $aditnilai . $tempexplode . $iksk;
                                            }
                                        }
                                        $urutiksk++;
                                    }
                                    $isikolom3 = $aditnilai;
                                    $formula_string3 = $isikolom3;
                                    //echo $formula_string1;
                                    //die;
                                    eval('$isikolom3 = ' . $formula_string3 . ';');
                                    if ($isikolom3 == '') {
                                        $isikolom3 = 0;
                                    }
                                    //////////////
                                }
                            } else {
                                $isikolom3 = $rowdata['kolom3'];
                            }
                            #kolom3

                            #kolom4
                            if (strstr($rumuskolom4, "IKSK")) {
                                $countIKSK = substr_count($rumuskolom4, "IKSK");
                                if ($countIKSK == 1) {
                                    $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                            and id = '" . $id . "'";
                                    $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                    while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                        $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                    }
                                    //echo $hitungan_pk;
                                    //die;

                                    $sqlskortahunsekarang = "select sum(target) as target
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom4 . "')
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


                                    $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                            ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom4 . "')
                                            and tahun = '" . $x . "'";
                                    //echo $sqlskor_komponen_tahun_berjalan;
                                    $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                    $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                    if ($numskor_komponen_tahun_berjalan  > 0) {
                                        while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                            $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                            $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                            $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                            $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                        }
                                    } else {
                                        $target_komponen_tahun_berjalan = 0;
                                    }

                                    if ($hitungan_pk == '2') {
                                        $isikolom4 = $target_komponen_tahun_berjalan;
                                    } else {
                                        $isikolom4 = $target_tahun_berjalan;
                                    }
                                }

                                if ($countIKSK > 1) {

                                    $tempexplode = "";
                                    if (strstr($rumuskolom4, "+")) {
                                        $tempexplode = "+";
                                    }
                                    if (strstr($rumuskolom4, "-")) {
                                        $tempexplode = "-";
                                    }
                                    if (strstr($rumuskolom4, "/")) {
                                        $tempexplode = "/";
                                    }
                                    if (strstr($rumuskolom4, "*")) {
                                        $tempexplode = "*";
                                    }

                                    $tempIKSK = explode($tempexplode, $rumuskolom4);
                                    $nilaiIKSK = array();
                                    //////////////
                                    for ($n = 0; $n < count($tempIKSK); $n++) {
                                        ///////////////////
                                        $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                                and id = '" . $id_indikator . "'";
                                        $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                        while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                            $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                        }
                                        //echo $hitungan_pk;

                                        $sqlskortahunsekarang = "select sum(target) as target
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
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

                                        $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                                ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
                                                and tahun = '" . $x . "'";
                                        //echo $sqlskor_komponen_tahun_berjalan;
                                        $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                        $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                        if ($numskor_komponen_tahun_berjalan  > 0) {
                                            while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                                $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                                $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                                $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                                $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                            }
                                        } else {
                                            $target_komponen_tahun_berjalan = 0;
                                        }


                                        if ($hitungan_pk == '2') {
                                            $target_tahun_berjalan = $target_komponen_tahun_berjalan;
                                        } else {
                                            $target_tahun_berjalan = $target_tahun_berjalan;
                                        }

                                        //$tempkolom4[$adi] = $target_tahun_berjalan;
                                        array_push($nilaiIKSK, $target_tahun_berjalan);
                                        $isikolom4 = $target_tahun_berjalan;
                                        ///////////////////
                                    }

                                    $aditnilai = "";
                                    $urutiksk = 1;
                                    foreach ($nilaiIKSK as $iksk) {
                                        if ($urutiksk == 1) {
                                            $aditnilai = $aditnilai . $iksk;
                                        } else {
                                            if ($tempexplode == "/" && (float)$iksk == 0) {
                                                $aditnilai = $aditnilai . "*" . "0";
                                            } else {
                                                $aditnilai = $aditnilai . $tempexplode . $iksk;
                                            }
                                        }
                                        $urutiksk++;
                                    }
                                    $isikolom4 = $aditnilai;
                                    $formula_string4 = $isikolom4;
                                    //echo $formula_string1;
                                    //die;
                                    eval('$isikolom4 = ' . $formula_string4 . ';');
                                    if ($isikolom4 == '') {
                                        $isikolom4 = 0;
                                    }
                                    //////////////
                                }
                            } else {
                                $isikolom4 = $rowdata['kolom4'];
                            }
                            #kolom4

                            #kolom5
                            if (strstr($rumuskolom5, "IKSK")) {
                                $countIKSK = substr_count($rumuskolom5, "IKSK");
                                if ($countIKSK == 1) {
                                    $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                            and id = '" . $id . "'";
                                    $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                    while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                        $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                    }
                                    //echo $hitungan_pk;
                                    //die;

                                    $sqlskortahunsekarang = "select sum(target) as target
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom5 . "')
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


                                    $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                            ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                            from tb_data_pk
                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                            and kdbalai = '" . $kdbalai . "') 
                                            and kode_tahun='" . $tahun . "'
                                            and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                            and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $rumuskolom5 . "')
                                            and tahun = '" . $x . "'";
                                    //echo $sqlskor_komponen_tahun_berjalan;
                                    $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                    $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                    if ($numskor_komponen_tahun_berjalan  > 0) {
                                        while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                            $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                            $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                            $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                            $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                        }
                                    } else {
                                        $target_komponen_tahun_berjalan = 0;
                                    }

                                    if ($hitungan_pk == '2') {
                                        $isikolom5 = $target_komponen_tahun_berjalan;
                                    } else {
                                        $isikolom5 = $target_tahun_berjalan;
                                    }
                                }

                                if ($countIKSK > 1) {

                                    $tempexplode = "";
                                    if (strstr($rumuskolom5, "+")) {
                                        $tempexplode = "+";
                                    }
                                    if (strstr($rumuskolom5, "-")) {
                                        $tempexplode = "-";
                                    }
                                    if (strstr($rumuskolom5, "/")) {
                                        $tempexplode = "/";
                                    }
                                    if (strstr($rumuskolom5, "*")) {
                                        $tempexplode = "*";
                                    }

                                    $tempIKSK = explode($tempexplode, $rumuskolom5);
                                    $nilaiIKSK = array();
                                    //////////////
                                    for ($n = 0; $n < count($tempIKSK); $n++) {
                                        ///////////////////
                                        $sqlcekhitungan = "select hitungan_pk from tb_indikator where deleted = '0'
                                                and id = '" . $id_indikator . "'";
                                        $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                        while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                            $hitungan_pk = $rowcekhitungan['hitungan_pk'];
                                        }
                                        //echo $hitungan_pk;

                                        $sqlskortahunsekarang = "select sum(target) as target
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
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

                                        $sqlskor_komponen_tahun_berjalan = "select AVG(skor_komponen_a) as skor_komponen_a
                                                ,AVG(skor_komponen_b) as skor_komponen_b,AVG(skor_komponen_c) as skor_komponen_c
                                                from tb_data_pk
                                                where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                and kdbalai = '" . $kdbalai . "') 
                                                and kode_tahun='" . $tahun . "'
                                                and skor_komponen_a <> '0' and skor_komponen_b <> '0' and skor_komponen_b <> '0'
                                                and id_indikator = (select id from tb_indikator where deleted='0' and kode_unique = '" . $tempIKSK[$n] . "')
                                                and tahun = '" . $x . "'";
                                        //echo $sqlskor_komponen_tahun_berjalan;
                                        $resultskor_komponen_tahun_berjalan  = mysqli_query($link, $sqlskor_komponen_tahun_berjalan);
                                        $numskor_komponen_tahun_berjalan  = mysqli_num_rows($resultskor_komponen_tahun_berjalan);
                                        if ($numskor_komponen_tahun_berjalan  > 0) {
                                            while ($rowskor_komponen_tahun_berjalan  = mysqli_fetch_assoc($resultskor_komponen_tahun_berjalan)) {
                                                $skor_komponen_a_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_a'];
                                                $skor_komponen_b_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_b'];
                                                $skor_komponen_c_tahun_berjalan  = (float) $rowskor_komponen_tahun_berjalan['skor_komponen_c'];
                                                $target_komponen_tahun_berjalan  = ($skor_komponen_a_tahun_berjalan  * 0.4) + ($skor_komponen_b_tahun_berjalan  * 0.4) + ($skor_komponen_c_tahun_berjalan  * 0.2);
                                            }
                                        } else {
                                            $target_komponen_tahun_berjalan = 0;
                                        }


                                        if ($hitungan_pk == '2') {
                                            $target_tahun_berjalan = $target_komponen_tahun_berjalan;
                                        } else {
                                            $target_tahun_berjalan = $target_tahun_berjalan;
                                        }

                                        //$tempkolom5[$adi] = $target_tahun_berjalan;
                                        array_push($nilaiIKSK, $target_tahun_berjalan);
                                        $isikolom5 = $target_tahun_berjalan;
                                        ///////////////////
                                    }

                                    $aditnilai = "";
                                    $urutiksk = 1;
                                    foreach ($nilaiIKSK as $iksk) {
                                        if ($urutiksk == 1) {
                                            $aditnilai = $aditnilai . $iksk;
                                        } else {
                                            if ($tempexplode == "/" && (float)$iksk == 0) {
                                                $aditnilai = $aditnilai . "*" . "0";
                                            } else {
                                                $aditnilai = $aditnilai . $tempexplode . $iksk;
                                            }
                                        }
                                        $urutiksk++;
                                    }
                                    $isikolom5 = $aditnilai;
                                    $formula_string5 = $isikolom5;
                                    //echo $formula_string1;
                                    //die;
                                    eval('$isikolom5 = ' . $formula_string5 . ';');
                                    if ($isikolom5 == '') {
                                        $isikolom5 = 0;
                                    }
                                    //////////////
                                }
                            } else {
                                $isikolom5 = $rowdata['kolom5'];
                            }
                            //echo "aaaa".$isikolom2;
                            #kolom5
                            if ($isikolom1 == '') {
                                $isikolom1 = 0;
                            }
                            if ($isikolom2 == '') {
                                $isikolom2 = 0;
                            }
                            if ($isikolom3 == '') {
                                $isikolom3 = 0;
                            }
                            if ($isikolom4 == '') {
                                $isikolom4 = 0;
                            }
                            if ($isikolom5 == '') {
                                $isikolom5 = 0;
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
                                'kolom1' => $isikolom1,
                                'kolom2' => $isikolom2,
                                'kolom3' => $isikolom3,
                                'kolom4' => $isikolom4,
                                'kolom5' => $isikolom5,
                                'isian_kolom' => '',
                            ];
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
                ,a.kolom4,a.kolom5,a.isian_kolom
                from tb_data_pk_balai a where a.deleted='0' and a.tahun='" . $x . "' " . $filterquery . "";
                $resultdata = mysqli_query($link, $sqldata);
                $numdata = mysqli_num_rows($resultdata);
                if ($numdata > 0) {
                    if ($resultdata) {
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
                            ];
                        }
                    }
                } else {
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
                    ];
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

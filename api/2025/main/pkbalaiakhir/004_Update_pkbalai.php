<?php
include '../../library/config.php';
error_reporting(0);
//check_injection();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
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
$resultsqlcek = mysqli_query($link, $sqlcek);
$numcek = mysqli_num_rows($resultsqlcek);
if ($numcek > 0) {
    $id_indikator = ($_POST['id_indikator']);
    $id_balai = ($_POST['id_balai']);
    $kode_tahun = ($_POST['kode_tahun']);
    $m_rumuskolom1 = ($_POST['m_rumuskolom1']);
    $m_rumuskolom2 = ($_POST['m_rumuskolom2']);
    $m_rumuskolom3 = ($_POST['m_rumuskolom3']);
    $m_rumuskolom4 = ($_POST['m_rumuskolom4']);
    $m_rumuskolom5 = ($_POST['m_rumuskolom5']);
    $baselinekolom1 = replace_comma(($_POST['baselinekolom1']));
    $baselinekolom2 = replace_comma(($_POST['baselinekolom2']));
    $baselinekolom3 = replace_comma(($_POST['baselinekolom3']));
    $baselinekolom4 = replace_comma(($_POST['baselinekolom4']));
    $baselinekolom5 = replace_comma(($_POST['baselinekolom5']));

    $tahun_awal = ($_POST['tahun_awal']);
    $tahun_akhir = ($_POST['tahun_akhir']);
    $kolom1 = replace_comma(($_POST['kolom1']));
    $kolom2 = replace_comma(($_POST['kolom2']));
    $kolom3 = replace_comma(($_POST['kolom3']));
    $kolom4 = replace_comma(($_POST['kolom4']));
    $kolom5 = replace_comma(($_POST['kolom5']));
    $isian_kolom = replace_comma(($_POST['isian_kolom']));
    $hitungan_pk = ($_POST['hitungan_pk']);
    $hitungan_pk_awal = ($_POST['hitungan_pk']);
    $createdby = ($_POST['createdby']);

    $nilaibaseline = ($_POST['nilaibaseline']);
    $isinilai = ($_POST['isinilai']);
    //echo $hitungan_pk;
    //die;
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($id_indikator <> '') && ($id_balai <> '') && ($kode_tahun <> '')) {

        $sqlindikator = "select * from tb_indikator_akhir where deleted='0' and id='" . $id_indikator . "' ";
        //echo $sqlindikator;
        //die;
        $resultindikator = mysqli_query($link, $sqlindikator);
        while ($rowindikator = mysqli_fetch_assoc($resultindikator)) {
            $masterrumus = $rowindikator['rumuskomponen'];
            $kolomkomponen = $rowindikator['kolomkomponen'];
            $cekkolom1 = $rowindikator['kolom1'];
            $cekkolom2 = $rowindikator['kolom2'];
            $cekkolom3 = $rowindikator['kolom3'];
            $cekkolom4 = $rowindikator['kolom4'];
            $cekkolom5 = $rowindikator['kolom5'];
        }

        $sqlbalaiemon = "select kdbalai_pendek,kdbalai from master_kategori_satker where id = '" . $id_balai . "'";
        $resultbalaiemon = mysqli_query($link, $sqlbalaiemon);
        while ($rowbalaiemon = mysqli_fetch_assoc($resultbalaiemon)) {
            $kode_balaiemon = $rowbalaiemon['kdbalai'];
        }
        $fixnilaibaseline = "";
        if ($hitungan_pk_awal == '2' || $hitungan_pk_awal == '3') {
            $tempnilaibaseline = explode("|", $nilaibaseline);
            $rumusindikator = $masterrumus;
            $tempisirumus = explode("|", $masterrumus);
            for ($s = 0; $s < 10; $s++) {
                $rumusindikator = str_replace("input" . $s, (float) replace_comma($tempnilaibaseline[$s]), $rumusindikator);
            }
            for ($s = 0; $s < 10; $s++) {
                $rumusindikator = str_replace("nilai" . $s, (float) replace_comma($tempnilaibaseline[$s]), $rumusindikator);
            }

            for ($s = 10; $s < 21; $s++) {
                $rumusindikator = str_replace("entry" . $s, (float) replace_comma($tempnilaibaseline[$s]), $rumusindikator);
            }

            for ($s = 10; $s < 21; $s++) {
                $rumusindikator = str_replace("rumus" . $s, (float) replace_comma($tempnilaibaseline[$s]), $rumusindikator);
            }

            $temprumussementara = explode("|", $rumusindikator);
            $fixnilaibaseline = "";

            for ($zz = 0; $zz < count($temprumussementara); $zz++) {
                try {
                    eval('$nilai = ' . $temprumussementara[$zz] . ';');
                } catch (DivisionByZeroError $e) {
                    eval('$nilai = 0;');
                } catch (Exception $e) {
                    eval('$nilai = 0;');
                }
                //echo $nilai . "<br>";
                if ($zz == 0) {
                    $fixnilaibaseline = $fixnilaibaseline . $nilai;
                } else {
                    $fixnilaibaseline = $fixnilaibaseline . "|" . $nilai;
                }
            }
        }

        //echo $kode_balaiemon;
        //die;

        $sqlcek = "select id from tb_data_baseline_pk_balai_akhir where deleted='0' and kode_tahun='" . $kode_tahun . "'
        and id_indikator='" . $id_indikator . "' and id_balai='" . $id_balai . "' and deleted='0'";
        $resultcek = mysqli_query($link, $sqlcek);
        $numcek = mysqli_num_rows($resultcek);


        if ($numcek == 0) {
            $sql = "insert into tb_data_baseline_pk_balai_akhir (id,kode_tahun,id_indikator,id_balai,kode_balai,kolom1,kolom2,kolom3,kolom4
            ,kolom5,nilai,rumus,createddate,createdby,deleted) values 
            (null,'" . $kode_tahun . "','" . $id_indikator . "','" . $id_balai . "','" . $kode_balaiemon . "','" . $baselinekolom1 . "','" . $baselinekolom2 . "'
            ,'" . $baselinekolom3 . "','" . $baselinekolom4 . "','" . $baselinekolom5 . "'
            ,'" . $fixnilaibaseline . "','" . $masterrumus . "',SYSDATE(),'" . $createdby . "','0')";
            $result = mysqli_query($link, $sql);
            //echo $sql;
            //die;

        } else {
            $sql = "update tb_data_baseline_pk_balai_akhir set kolom1='" . $baselinekolom1 . "',kolom2='" . $baselinekolom2 . "'
            ,kolom3='" . $baselinekolom3 . "',kolom4='" . $baselinekolom4 . "',kolom5='" . $baselinekolom5 . "'
            , updateby='" . $createdby . "',nilai='" . $fixnilaibaseline . "',rumus='" . $masterrumus . "'
            ,updatedate=SYSDATE() where kode_tahun='" . $kode_tahun . "'
            and id_balai='" . $id_balai . "' and id_indikator = '" . $id_indikator . "'";
            //echo $sql;
            //die;
            $sqlnya = $sql;
            $result = mysqli_query($link, $sql);
        }


        $tempkolom1 = explode("|", $kolom1);
        $tempkolom2 = explode("|", $kolom2);
        $tempkolom3 = explode("|", $kolom3);
        $tempkolom4 = explode("|", $kolom4);
        $tempkolom5 = explode("|", $kolom5);
        $tempisian_kolom = explode("|", $isian_kolom);
        $adi = 0;
        $tempisinilai = explode("?", $isinilai);
        for ($x = $tahun_awal; $x <= $tahun_akhir; $x++) {
            #kolom1
            $nilaifinal = "";
            if ($hitungan_pk_awal == '2' || $hitungan_pk_awal == '3') {
                $tempisinilaipertahun = explode("|", $tempisinilai[$adi]);
                $rumusindikator = $masterrumus;
                $tempisirumus = explode("|", $masterrumus);
                for ($s = 0; $s < 10; $s++) {
                    $rumusindikator = str_replace("input" . $s, (float) replace_comma($tempisinilaipertahun[$s]), $rumusindikator);
                }
                for ($s = 0; $s < 10; $s++) {
                    $rumusindikator = str_replace("nilai" . $s, (float) replace_comma($tempisinilaipertahun[$s]), $rumusindikator);
                }

                for ($s = 10; $s < 21; $s++) {
                    $rumusindikator = str_replace("entry" . $s, (float) replace_comma($tempisinilaipertahun[$s]), $rumusindikator);
                }

                for ($s = 10; $s < 21; $s++) {
                    $rumusindikator = str_replace("rumus" . $s, (float) replace_comma($tempisinilaipertahun[$s]), $rumusindikator);
                }

                $temprumussementara = explode("|", $rumusindikator);
                $nilaifinal = "";

                for ($zz = 0; $zz < count($temprumussementara); $zz++) {
                    try {
                        eval('$nilai = ' . $temprumussementara[$zz] . ';');
                    } catch (DivisionByZeroError $e) {
                        eval('$nilai = 0;');
                    } catch (Exception $e) {
                        eval('$nilai = 0;');
                    }
                    //echo $nilai . "<br>";
                    if ($zz == 0) {
                        $nilaifinal = $nilaifinal . $nilai;
                    } else {
                        $nilaifinal = $nilaifinal . "|" . $nilai;
                    }
                }
                $sqlupdatedata = "update tb_data_pk_balai_akhir set nilai = '" . $nilaifinal . "', rumus = '" . $masterrumus . "'
                where id_indikator = '" . $id_indikator . "' and tahun = '" . $x . "' and id_balai = '" . $id_balai . "'";
                $result = mysqli_query($link, $sqlupdatedata);
            } else {
                
            }
            $adi++;
        }

        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah Diupdate'));
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
} else {
    mysqli_close($link);
    echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
    die;
}

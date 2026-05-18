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
    $id_indikator = fixup($_POST['id_indikator']);
    $hitungan_pk = fixup($_POST['hitungan_pk']);
    $id_satker = fixup($_POST['id_satker']);
    $kode_tahun = fixup($_POST['kode_tahun']);
    $kode_satker = fixup($_POST['kode_satker']);
    $baseline = replace_comma(fixup($_POST['baseline']));
    $baseline_komponen_a = (fixup($_POST['baseline_komponen_a']));
    $baseline_komponen_b = (fixup($_POST['baseline_komponen_b']));
    $baseline_komponen_c = (fixup($_POST['baseline_komponen_c']));
    $tahun_awal = fixup($_POST['tahun_awal']);
    $tahun_akhir = fixup($_POST['tahun_akhir']);
    $proyek = fixup($_POST['proyek']);
    $volume = (fixup($_POST['volume']));
    $target = (fixup($_POST['target']));
    $capaian = (fixup($_POST['capaian']));
    $capaian_outcome = (fixup($_POST['capaian_outcome']));
    $skor_komponen_a = (fixup($_POST['skor_komponen_a']));
    $skor_komponen_b = (fixup($_POST['skor_komponen_b']));
    $skor_komponen_c = (fixup($_POST['skor_komponen_c']));
    $jumlahkomponen = (fixup($_POST['jumlahkomponen']));
    $nilai = (fixup($_POST['nilai']));
    $createdby = fixup($_POST['createdby']);
    //echo $hitungan_pk;
    //die;
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($id_indikator <> '')) {
        $kode_satkeremon = "";
        $id_satker = "";
        $sqlsatkeremon = "select kode_satker_pendek,level_piu from master_satker where kode_satker = '" . $kode_satker . "'";
        $resultsatkeremon = mysqli_query($link, $sqlsatkeremon);
        while ($rowsatkeremon = mysqli_fetch_assoc($resultsatkeremon)) {
            $kode_satkeremon = $rowsatkeremon['kode_satker_pendek'];
            $id_satker = $rowsatkeremon['level_piu'];
        }
        $sqlcek = "select id from tb_data_baseline_akhir where deleted='0' and kode_tahun='" . $kode_tahun . "'
        and id_indikator='" . $id_indikator . "' and kode_satker='" . $kode_satker . "'
        and deleted='0'";
        $resultcek = mysqli_query($link, $sqlcek);
        $numcek = mysqli_num_rows($resultcek);
        if ($numcek == 0) {
            if ($hitungan_pk == '1') {
                $sql = "insert into tb_data_baseline_akhir (id,kode_tahun,id_indikator,id_satker,kode_satker,baseline
                ,createddate,createdby,deleted) values 
                (null,'" . $kode_tahun . "','" . $id_indikator . "','" . $id_satker . "','" . $kode_satker . "','" . $baseline . "'
                ,SYSDATE(),'" . $createdby . "','0')";
                $result = mysqli_query($link, $sql);
            }
            if ($hitungan_pk == '2' || $hitungan_pk == '3') {
                $sql = "insert into tb_data_baseline_akhir (id,kode_tahun,id_indikator,id_satker,kode_satker,baseline_komponen_a
                ,baseline_komponen_b,baseline_komponen_c,nilai
                ,createddate,createdby,deleted) values 
                (null,'" . $kode_tahun . "','" . $id_indikator . "','" . $id_satker . "','" . $kode_satker . "','" . $baseline_komponen_a . "'
                ,'" . $baseline_komponen_b . "','" . $baseline_komponen_c . "','" . $baseline . "'
                ,SYSDATE(),'" . $createdby . "','0')";
                $result = mysqli_query($link, $sql);
            }
            //echo $sql;
            //die;

        } else {
            if ($hitungan_pk == '1') {
                $sql = "update tb_data_baseline_akhir set baseline='" . $baseline . "', updateby='" . $createdby . "'
                ,updatedate=SYSDATE() where kode_tahun='" . $kode_tahun . "' and deleted='0'
                and id_satker='" . $id_satker . "' and kode_satker='" . $kode_satker . "' and id_indikator = '" . $id_indikator . "'";
                //echo $sql;
                //die;
                $result = mysqli_query($link, $sql);
            }
            if ($hitungan_pk == '2' || $hitungan_pk == '3') {
                $sql = "update tb_data_baseline_akhir set nilai='" . $baseline . "'
                , updateby='" . $createdby . "'
                ,updatedate=SYSDATE() where kode_tahun='" . $kode_tahun . "' and deleted='0'
                and id_satker='" . $id_satker . "' and kode_satker='" . $kode_satker . "' and id_indikator = '" . $id_indikator . "'";
                //echo $sql;
                //die;
                $result = mysqli_query($link, $sql);
            }
        }


        if ($hitungan_pk == '1') {
            $z = 0;
            $tempproyek = explode("|", $proyek);
            $tempvolume = explode("|", $volume);
            $temptarget = explode("|", $target);
            $tempcapaian = explode("|", $capaian);
            $tempcapaian_outcome = explode("|", $capaian_outcome);
            for ($x = $tahun_awal; $x <= $tahun_akhir; $x++) {
                $sqlcek2 = "select id from tb_data_pk_akhir where deleted='0' and kode_tahun='" . $kode_tahun . "' and tahun='" . $x . "'
                and id_indikator='" . $id_indikator . "' and id_satker='" . $id_satker . "' and kode_satker='" . $kode_satker . "'
                and deleted='0'";
                $resultcek2 = mysqli_query($link, $sqlcek2);
                $numcek2 = mysqli_num_rows($resultcek2);
                if ($numcek2 == 0) {
                    $sql = "insert into tb_data_pk_akhir (id,id_indikator,kode_tahun,tahun,id_satker,kode_satker
                    ,proyek,volume,target,capaian,capaian_outcome
                    ,createddate,createdby,deleted) values 
                    (null,'" . $id_indikator . "','" . $kode_tahun . "','" . $x . "','" . $id_satker . "','" . $kode_satker . "'
                    ,'" . $tempproyek[$z] . "','" . replace_comma($tempvolume[$z]) . "','" . replace_comma($temptarget[$z]) . "'
                    ,'" . replace_comma($tempcapaian[$z]) . "','" . replace_comma($tempcapaian_outcome[$z]) . "',SYSDATE(),'" . $createdby . "','0')";
                    $result = mysqli_query($link, $sql);
                } else {
                    $sql = "update tb_data_pk_akhir set proyek='" . $tempproyek[$z] . "',capaian='" . replace_comma($tempcapaian[$z]) . "'
                    ,capaian_outcome='" . replace_comma($tempcapaian_outcome[$z]) . "'
                    ,volume='" . replace_comma($tempvolume[$z]) . "',target='" . replace_comma($temptarget[$z]) . "', updateby='" . $createdby . "'
                    ,updatedate=SYSDATE() where kode_tahun='" . $kode_tahun . "' and id_satker='" . $id_satker . "' and deleted='0'
                    and kode_satker='" . $kode_satker . "' and tahun = '" . $x . "' and id_indikator = '" . $id_indikator . "'";
                    //echo $sql;
                    //die;
                    $result = mysqli_query($link, $sql);
                }
                $z++;
            }
        }
        if ($hitungan_pk == '2' || $hitungan_pk == '3') {
            $z = 0;
            $tempproyek = explode("|", $proyek);
            $tempskor_komponen_a = explode("|", $skor_komponen_a);
            $tempskor_komponen_b = explode("|", $skor_komponen_b);
            $tempskor_komponen_c = explode("|", $skor_komponen_c);
            $tempnilai = explode("?", $nilai);
            //$sqlrumus = "";
            $tempcapaian = explode("|", $capaian);
            $sqlrumus = "update tb_data_pk_akhir set deleted='1' where kode_tahun='" . $kode_tahun . "' and id_satker='" . $id_satker . "' and deleted='0'
            and kode_satker='" . $kode_satker . "' and tahun = '" . $x . "' and id_indikator = '" . $id_indikator . "'";
            //echo $sql . "<br>";
            //die;
            $resultrumus = mysqli_query($link, $sqlrumus);

            for ($x = $tahun_awal; $x <= $tahun_akhir; $x++) {
                $sqlcek2 = "select id,rumus from tb_data_pk_akhir where deleted='0' and kode_tahun='" . $kode_tahun . "' and tahun='" . $x . "'
                and id_indikator='" . $id_indikator . "' and id_satker='" . $id_satker . "' and kode_satker='" . $kode_satker . "'
                and deleted='0'";
                $resultcek2 = mysqli_query($link, $sqlcek2);
                $numcek2 = mysqli_num_rows($resultcek2);
                while ($rowcek2 = mysqli_fetch_assoc($resultcek2)) {
                    $rumus = $rowcek2['rumus'];
                }
                $rumusindikator = $rumus;
                $tempnilaisementara = explode("|", $tempnilai[$z]);

                for ($s = 0; $s < 10; $s++) {
                    $rumusindikator = str_replace("input" . $s, (float) replace_comma($tempnilaisementara[$s]), $rumusindikator);
                }
                //echo $rumusindikator;
                for ($s = 0; $s < 10; $s++) {
                    $rumusindikator = str_replace("nilai" . $s, (float) replace_comma($tempnilaisementara[$s]), $rumusindikator);
                }

                for ($s = 10; $s < 21; $s++) {
                    $rumusindikator = str_replace("entry" . $s, (float) replace_comma($tempnilaisementara[$s]), $rumusindikator);
                }

                for ($s = 10; $s < 21; $s++) {
                    $rumusindikator = str_replace("rumus" . $s, (float) replace_comma($tempnilaisementara[$s]), $rumusindikator);
                }


                //echo $rumusindikator;
                //die;
                if (!strstr($rumusindikator, "/0.")) {
                    $rumusindikator = str_replace("0/0", "0", $rumusindikator);
                    $rumusindikator = str_replace("/0", "*0", $rumusindikator);
                }

                $temprumussementara = explode("|", $rumusindikator);
                $nilaifinal = "";
                for ($s = 0; $s < count($temprumussementara); $s++) {
                    try {
                        eval('$nilai = ' . $temprumussementara[$s] . ';');
                    } catch (DivisionByZeroError $e) {
                        eval('$nilai = 0;');
                    } catch (Exception $e) {
                        eval('$nilai = 0;');
                    }
                    //echo $nilai . "<br>";
                    if ($s == 0) {
                        $nilaifinal = $nilaifinal . $nilai;
                    } else {
                        $nilaifinal = $nilaifinal . "|" . $nilai;
                    }
                }

                //echo $nilaifinal;
                //die;

                $sql = "update tb_data_pk_akhir set proyek='" . $tempproyek[$z] . "', capaian='" . replace_comma($tempcapaian[$z]) . "'
                ,skor_komponen_a='" . $tempskor_komponen_a[$z] . "',skor_komponen_b='" . $tempskor_komponen_b[$z] . "'
                ,skor_komponen_c='" . $tempskor_komponen_c[$z] . "',nilai='" . $nilaifinal . "', updateby='" . $createdby . "'
                ,updatedate=SYSDATE() where kode_tahun='" . $kode_tahun . "' and id_satker='" . $id_satker . "' and deleted='0'
                and kode_satker='" . $kode_satker . "' and tahun = '" . $x . "' and id_indikator = '" . $id_indikator . "'";
                //echo $sql . "<br>";
                //die;
                $result = mysqli_query($link, $sql);
                $z++;
            }
        }

        //die;
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

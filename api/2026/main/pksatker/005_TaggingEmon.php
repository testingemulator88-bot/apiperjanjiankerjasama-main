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
    $id_pk = fixup($_POST['id_pk']);
    $id_indikator = fixup($_POST['id_indikator']);
    $kode_tahun = fixup($_POST['kode_tahun']);
    $tahun = fixup($_POST['tahun']);
    $kode_satker = fixup($_POST['kode_satker']);
    $kodekegiatanemon = fixup($_POST['kodekegiatanemon']);
    $kode_emon = fixup($_POST['kode_emon']);
    $createdby = fixup($_POST['createdby']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($id_indikator <> '') && ($kode_satker <> '')) {

        $sqlindikator = "select a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen from tb_indikator a
        where a.deleted='0' and a.id = '" . $id_indikator . "'";
        //echo $sqlindikator;
        //;
        $resultindikator = mysqli_query($link, $sqlindikator);
        $nilai = "";
        $nilairumus = "";
        while ($rowindikator = mysqli_fetch_assoc($resultindikator)) {
            $jumlahkomponen = (float) $rowindikator['jumlahkomponen'];
            $tempkolom = explode("#", $rowindikator['kolomkomponen']);
            $temprumus = explode("#", $rowindikator['rumuskomponen']);
            for ($x = 0; $x < count($tempkolom); $x++) {
                if ($x == 0) {
                    $tempisi = explode("|", $tempkolom[$x]);
                    $temprumus = explode("|", $temprumus[$x]);
                    $isi = "";
                    $rumus = "";
                    for ($z = 0; $z < count($tempisi); $z++) {
                        if ($z == 0) {
                            $isi = $isi . "0";
                            $rumus = $rumus . $temprumus[$z];
                        } else {
                            $isi = $isi . "|" . "0";
                            $rumus = $rumus . "|" . $temprumus[$z];
                        }
                    }
                    $nilai = $nilai . $isi;
                    $nilairumus = $nilairumus . $rumus;
                } else {
                    $tempisi = explode("|", $tempkolom[$x]);
                    $temprumus = explode("|", $temprumus[$x]);
                    $isi = "";
                    $rumus = "";
                    for ($z = 0; $z < count($tempisi); $z++) {
                        if ($z == 0) {
                            $isi = $isi . "0";
                            $rumus = $rumus . $temprumus[$z];
                        } else {
                            $isi = $isi . "|" . "0";
                            $rumus = $rumus . "|" . $temprumus[$z];
                        }
                    }
                    $nilai = $nilai . "#" . $isi;
                    $nilairumus = $nilairumus . "#" . $rumus;
                }
            }
        }

        //echo $nilai;
        //die;

        $sqlcekemon = "select * from tb_data_pk_emon where deleted ='0'
        and id_pk='" . $id_pk . "' and id_indikator='" . $id_indikator . "' and kode_tahun='" . $kode_tahun . "'
        and tahun='" . $tahun . "' and kode_satker='" . $kode_satker . "' and kodekegiatanemon='" . $kodekegiatanemon . "'";

        $resultcekemon = mysqli_query($link, $sqlcekemon);
        $numcekemon = mysqli_num_rows($resultcekemon);
        $arrayemon = array();
        if ($numcekemon > 0) {
            if ($resultcekemon) {
                while ($rowcekemon = mysqli_fetch_assoc($resultcekemon)) {
                    $cekdataemon = $rowcekemon['kode_emon'];
                    array_push($arrayemon, $cekdataemon);
                }
                $tempkode_emon = explode("|", $kode_emon);
                for ($x = 0; $x <= count($tempkode_emon) - 1; $x++) {
                    $temporaryvalueToSearch = explode("_", $tempkode_emon[$x]);
                    $valueToSearch = $temporaryvalueToSearch[0];
                    if (!in_array($valueToSearch, $arrayemon)) {
                        $temporarykode = explode("_", $tempkode_emon[$x]);

                        $sql = "insert into tb_data_pk_emon (id,id_pk,id_indikator,kode_tahun,tahun,kode_satker,kodekegiatanemon
                        ,kode_emon,nmpaket,pagu,realisasi,keu,fisik,tanggal_data_emon, nilai, rumus
                        ,createddate,createdby,deleted) values 
                        (null,'" . $id_pk . "','" . $id_indikator . "','" . $kode_tahun . "','" . $tahun . "','" . $kode_satker . "'
                        ,'" . $kodekegiatanemon . "','" . $temporarykode[0] . "',null,'" . $temporarykode[1] . "','" . $temporarykode[2] . "'
                        ,'" . replace_comma($temporarykode[3]) . "','" . replace_comma($temporarykode[4]) . "','" . $temporarykode[5] . "', '" . $nilai . "'
                        , '" . $nilairumus . "',SYSDATE(),'" . $createdby . "','0')";
                        $result = mysqli_query($link, $sql);
                    }
                }
                $sqlupdate = "delete from tb_data_pk_emon
                where tanggal_data_emon='0000-00-00 00:00:00'";
                //echo $sqlupdate;
                //die;
                $resultupdate = mysqli_query($link, $sqlupdate);

                $sqlupdate = "delete from tb_data_pk_emon
                where deleted='1'";
                //echo $sqlupdate;
                //die;
                $resultupdate = mysqli_query($link, $sqlupdate);
                $tahunsekarang = get_Isi_Field1('tahun', 'master_pk', 'id', '2');
                $sqlupdate = "update tb_data_pk_emon a set a.nmpaket = 
                (select b.nmpaket from paket_pk b where b.kode = a.kode_emon) WHERE a.nmpaket is null
                and YEAR(a.tanggal_data_emon)='" . $tahunsekarang . "'";
            }
        } else {
            $z = 0;
            $tempkode_emon = explode("|", $kode_emon);
            for ($x = 0; $x <= count($tempkode_emon) - 1; $x++) {
                $temporarykode = explode("_", $tempkode_emon[$x]);
                $sql = "insert into tb_data_pk_emon (id,id_pk,id_indikator,kode_tahun,tahun,kode_satker,kodekegiatanemon
                ,kode_emon,pagu,realisasi,keu,fisik,tanggal_data_emon, nilai, rumus
                ,createddate,createdby,deleted) values 
                (null,'" . $id_pk . "','" . $id_indikator . "','" . $kode_tahun . "','" . $tahun . "','" . $kode_satker . "'
                ,'" . $kodekegiatanemon . "','" . $temporarykode[0] . "','" . $temporarykode[1] . "','" . $temporarykode[2] . "'
                ,'" . replace_comma($temporarykode[3]) . "','" . replace_comma($temporarykode[4]) . "','" . $temporarykode[5] . "', '" . $nilai . "'
                , '" . $nilairumus . "',SYSDATE(),'" . $createdby . "','0')";
                $result = mysqli_query($link, $sql);
                $z++;
            }

            $sqlupdate = "delete from tb_data_pk_emon
            where tanggal_data_emon='0000-00-00 00:00:00'";
            //echo $sqlupdate;
            //die;
            $resultupdate = mysqli_query($link, $sqlupdate);

            $sqlupdate = "delete from tb_data_pk_emon
            where deleted='1'";
            //echo $sqlupdate;
            //die;
            $resultupdate = mysqli_query($link, $sqlupdate);

            $resultupdate = mysqli_query($link, $sqlupdate);
            $tahunsekarang = get_Isi_Field1('tahun', 'master_pk', 'id', '2');
            $sqlupdate = "update tb_data_pk_emon a set a.nmpaket = 
            (select b.nmpaket from paket_pk b where b.kode = a.kode_emon) WHERE a.nmpaket is null
            and YEAR(a.tanggal_data_emon)='" . $tahunsekarang . "'";
        }

        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Tagging Emon Telah Diupdate'));
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

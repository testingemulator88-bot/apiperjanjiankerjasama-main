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
    $updateby = fixup($_POST['updateby']);
    $id = fixup($_POST['kode']);
    $id_indikator = fixup($_POST['id_indikator']);
    $kolom = fixup($_POST['kolom']);
    $isi = fixup($_POST['isi']);
    $idisian = fixup($_POST['idisian']);
    $baris = fixup($_POST['baris']);
    $isisemua = fixup($_POST['isisemua']);
    $urutan = fixup($_POST['urutan']);
    $explode = fixup($_POST['urutan']);
    $tahundiubah = fixup($_POST['tahundiubah']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($id <> '')) {

        $sqlisian = " select nilai from  tb_data_pk_emon 
        where id = '" . $id . "'";
        $resultisian = mysqli_query($link, $sqlisian);
        while ($rowisian = mysqli_fetch_assoc($resultisian)) {
            $isidatabase = $rowisian['nilai'];
        }
        //echo $idisian;
        //die;
        $tempidisian = explode("#", $idisian);
        $kolomnya = "";
        for ($x = 0; $x < count($tempidisian); $x++) {
            if ($x == 0) {
                $newString = str_replace("@", "|", $isisemua);
                $kolomnya = $kolomnya  . replace_comma($newString);
            } else {
                $newString = str_replace("@", "|", $isisemua);
                $kolomnya = $kolomnya  . "#" . replace_comma($newString);
            }
        }

        $sqlindikator = "select rumuskomponen from tb_indikator where id='" . $id_indikator . "'";
        //echo $sqlindikator;
        //die;
        $resultindikator = mysqli_query($link, $sqlindikator);
        while ($rowindikator = mysqli_fetch_assoc($resultindikator)) {
            $temprumus = explode("|", $rowindikator['rumuskomponen']);
            $rumusindikator = $rowindikator['rumuskomponen'];
            $rumusindikatorawal = $rowindikator['rumuskomponen'];
        }

        $sqlku = " update tb_data_pk_emon set nilai='" . $kolomnya . "', rumus = '" . $rumusindikatorawal . "'
        , updateby = '" . $updateby . "' , updatedate = SYSDATE()
        where id = '" . $id . "'";
        //echo $sqlku;
        //die;
        $result = mysqli_query($link, $sqlku);

        $sqlcekemon = "select id_pk,id_indikator,kode_satker,tahun from tb_data_pk_emon where id = '" . $id . "' and tahun = '" . $tahundiubah . "'";
        //echo $sqlcekemon;
        //die;
        $resultcekemon = mysqli_query($link, $sqlcekemon);
        $numemon = mysqli_num_rows($resultcekemon);
        if ($numemon > 0) {
            while ($rowemon = mysqli_fetch_assoc($resultcekemon)) {
                $id_pk = $rowemon['id_pk'];
                $id_indikator = $rowemon['id_indikator'];
                $kode_satker = $rowemon['kode_satker'];
                $tahun = $rowemon['tahun'];
            }
            $sqljumlah = "select nilai from tb_data_pk_emon where deleted = '0'
            and id_pk = '" . $id_pk . "' and id_indikator = '" . $id_indikator . "' 
            and kode_satker = '" . $kode_satker . "' and tahun = '" . $tahundiubah . "'";
            //echo $sqljumlah;
            //die;
            $resultjumlah = mysqli_query($link, $sqljumlah);
            $numejumlah = mysqli_num_rows($resultjumlah);
            //echo $numejumlah;
            if ($numejumlah > 0) {
                $jumlahnya = 0;
                while ($rowjumlah = mysqli_fetch_assoc($resultjumlah)) {
                    //echo "aaaaa";
                    //die;
                    $nilaidb = $rowjumlah['nilai'];
                    $tempjumlahnya = explode("|", $nilaidb);
                    //echo $tempjumlahnya[$urutan];
                    //die;
                    for ($z = 0; $z < count($tempjumlahnya); $z++) {
                        if ($z == $explode) {
                            $jumlahnya = $jumlahnya + (float) ($tempjumlahnya[$z]);
                        }
                    }
                }
            } else {
                $jumlahnya = 0;
            }
        } else {
            $jumlahnya = 0;
        }
        //echo $jumlahnya;
        //die;
        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah Diupdate', 'jumlahnya' => number_format($jumlahnya, 2, ",", ".")));
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

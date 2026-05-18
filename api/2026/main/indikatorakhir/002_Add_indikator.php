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
    $mode = fixup($_POST['mode']);
    $kdunor = fixup($_POST['kdunor']);
    $tahun = fixup($_POST['tahun']);
    $urutlevel = fixup($_POST['urutlevel']);
    $level = fixup($_POST['level']);
    $id_parent = fixup($_POST['id_parent']);
    $id = fixup($_POST['id']);
    $kode = fixup($_POST['kode']);
    $kode_unique = fixup($_POST['kode_unique']);
    $nama = fixup($_POST['nama']);
    $satuan = fixup($_POST['satuan']);
    $output = fixup($_POST['output']);
    $outcome = fixup($_POST['outcome']);
    $penanggungjawab = fixup($_POST['penanggungjawab']);
    $pelaksana = fixup($_POST['pelaksana']);
    $target = fixup($_POST['target']);
    $pelaksana = fixup($_POST['pelaksana']);
    $pelaksanacode = fixup($_POST['pelaksanacode']);
    $pelaksanalabel = fixup($_POST['pelaksanalabel']);
    $cetak = fixup($_POST['cetak']);
    $cetakcode = fixup($_POST['cetakcode']);
    $cetaklabel = fixup($_POST['cetaklabel']);
    $verif = fixup($_POST['verif']);
    $verifcode = fixup($_POST['verifcode']);
    $veriflabel = fixup($_POST['veriflabel']);
    $verif2 = fixup($_POST['verif2']);
    $verif2code = fixup($_POST['verif2code']);
    $verif2label = fixup($_POST['verif2label']);
    $hitungan_pk = fixup($_POST['hitungan_pk']);
    $urut = fixup($_POST['urut']);
    $kolom1 = ($_POST['kolom1']);
    $kolom2 = ($_POST['kolom2']);
    $kolom3 = ($_POST['kolom3']);
    $kolom4 = ($_POST['kolom4']);
    $kolom5 = ($_POST['kolom5']);
    $rumuskolom1 = ($_POST['rumuskolom1']);
    $rumuskolom2 = ($_POST['rumuskolom2']);
    $rumuskolom3 = ($_POST['rumuskolom3']);
    $rumuskolom4 = ($_POST['rumuskolom4']);
    $rumuskolom5 = ($_POST['rumuskolom5']);
    $isian_kolom = ($_POST['isian_kolom']);
    $jumlahkomponen = ($_POST['jumlahkomponen']);
    $kolomkomponen = ($_POST['kolomkomponen']);
    $bobotkomponen = ($_POST['bobotkomponen']);
    $rumuskomponen = ($_POST['rumuskomponen']);
    $rubahrumus = ($_POST['rubahrumus']);
    $belakangkoma = ($_POST['belakangkoma']);
    $kumulatif = ($_POST['kumulatif']);

    if ($target == '') {
        $target = 0;
    }

    if ($satuan == '') {
        $satuan = 0;
    }
    if ($output == '') {
        $output = 0;
    }
    if ($outcome == '') {
        $outcome = 0;
    }

    if ($id_parent == '') {
        $id_parent = null;
    }

    if (($kode == null) || ($kode == 'null')) {
        $kode = '';
    }

    if (($kolom1 == null) || ($kolom1 == 'null')) {
        $kolom1 = '';
    }
    if (($kolom2 == null) || ($kolom2 == 'null')) {
        $kolom2 = '';
    }
    if (($kolom3 == null) || ($kolom3 == 'null')) {
        $kolom3 = '';
    }
    if (($kolom4 == null) || ($kolom4 == 'null')) {
        $kolom4 = '';
    }
    if (($kolom5 == null) || ($kolom5 == 'null')) {
        $kolom5 = '';
    }

    if (($rumuskolom1 == null) || ($rumuskolom1 == 'null')) {
        $rumuskolom1 = '';
    }
    if (($rumuskolom2 == null) || ($rumuskolom2 == 'null')) {
        $rumuskolom2 = '';
    }
    if (($rumuskolom3 == null) || ($rumuskolom3 == 'null')) {
        $rumuskolom3 = '';
    }
    if (($rumuskolom4 == null) || ($rumuskolom4 == 'null')) {
        $rumuskolom4 = '';
    }
    if (($rumuskolom5 == null) || ($rumuskolom5 == 'null')) {
        $rumuskolom5 = '';
    }
    if (($isian_kolom == null) || ($isian_kolom == 'null')) {
        $isian_kolom = '';
    }

    $createdby = fixup($_POST['createdby']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($kdunor <> '') && ($tahun <> '')) {
        if ($mode == 'Tambah') {
            $sql = "insert into tb_indikator_akhir (id,tahun,id_parent,kdunor,urutlevel,level,kode,kode_unique,nama,satuan
            ,output,outcome,penanggungjawab,pelaksana,pelaksanacode,pelaksanalabel
            ,cetak,cetakcode,cetaklabel,verif,verifcode,veriflabel,verif2,verif2code,verif2label,hitungan_pk,target,urut, belakangkoma
            ,kolom1,kolom2,kolom3,kolom4,kolom5,rumuskolom1,rumuskolom2,rumuskolom3,rumuskolom4,rumuskolom5,isian_kolom
            ,jumlahkomponen,kolomkomponen,bobotkomponen,rumuskomponen,kumulatif
            ,createddate,createdby,deleted) values 
            (null,'" . $tahun . "','" . $id_parent . "','" . $kdunor . "','" . $urutlevel . "','" . $level . "','" . $kode . "'
            ,'" . $kode_unique . "','" . $nama . "','" . $satuan . "','" . $output . "','" . $outcome . "'
            ,'" . $penanggungjawab . "','" . $pelaksana . "','" . $pelaksanacode . "','" . $pelaksanalabel . "'
            ,'" . $cetak . "','" . $cetakcode . "','" . $cetaklabel . "','" . $verif . "','" . $verifcode . "','" . $veriflabel . "'
            ,'" . $verif2 . "','" . $verif2code . "','" . $verif2label . "','" . $hitungan_pk . "','" . $target . "','" . $urut . "','" . $belakangkoma . "'
            ,'" . $kolom1 . "','" . $kolom2 . "','" . $kolom3 . "','" . $kolom4 . "','" . $kolom5 . "'
            ,'" . $rumuskolom1 . "','" . $rumuskolom2 . "','" . $rumuskolom3 . "','" . $rumuskolom4 . "','" . $rumuskolom5 . "'
            ,'" . $isian_kolom . "','" . $jumlahkomponen . "','" . $kolomkomponen . "','" . $bobotkomponen . "','" . $rumuskomponen . "','" . $kumulatif . "'
            ,SYSDATE(),'" . $createdby . "','0')";
        } else {
            $sql = "update tb_indikator_akhir set tahun = '" . $tahun . "',kdunor = '" . $kdunor . "',nama = '" . $nama . "'
            ,kode = '" . $kode . "',kode_unique = '" . $kode_unique . "',urut = '" . $urut . "',satuan = '" . $satuan . "'
            ,pelaksana = '" . $pelaksana . "',pelaksanacode = '" . $pelaksanacode . "',pelaksanalabel = '" . $pelaksanalabel . "'
            ,cetak = '" . $cetak . "',cetakcode = '" . $cetakcode . "',cetaklabel = '" . $cetaklabel . "'
            ,verif = '" . $verif . "',verifcode = '" . $verifcode . "',veriflabel = '" . $veriflabel . "'
            ,verif2 = '" . $verif2 . "',verif2code = '" . $verif2code . "',verif2label = '" . $verif2label . "'
            ,output = '" . $output . "',outcome = '" . $outcome . "',hitungan_pk= '" . $hitungan_pk . "'
            ,kolom1 = '" . $kolom1 . "',kolom2 = '" . $kolom2 . "',kolom3= '" . $kolom3 . "',kolom4= '" . $kolom4 . "',kolom5= '" . $kolom5 . "'
            ,rumuskolom1 = '" . $rumuskolom1 . "',rumuskolom2 = '" . $rumuskolom2 . "', belakangkoma = '" . $belakangkoma . "'
            ,rumuskolom3= '" . $rumuskolom3 . "',rumuskolom4= '" . $rumuskolom4 . "',rumuskolom5= '" . $rumuskolom5 . "'
            ,isian_kolom= '" . $isian_kolom . "',jumlahkomponen= '" . $jumlahkomponen . "',kolomkomponen= '" . $kolomkomponen . "'
            ,bobotkomponen= '" . $bobotkomponen . "',rumuskomponen= '" . $rumuskomponen . "',kumulatif= '" . $kumulatif . "',updatedate = SYSDATE(), updateby='" . $createdby . "'
            where id = '" . $id . "'";

            if ($rubahrumus == '1') {
                if ($level == 'ISP') {
                    $sqlrubahrumussemuadata = "update tb_data_pk_balai set deleted ='1' where id_indikator = '" . $id . "'";
                    $resultrumus = mysqli_query($link, $sqlrubahrumussemuadata);
                    $sqlrubahrumussemuadata = "update tb_data_baseline_pk_balai set deleted ='1' where id_indikator = '" . $id . "'";
                    $resultrumus = mysqli_query($link, $sqlrubahrumussemuadata);
                }
                if ($level == 'IKSK') {
                    $sqlrubahrumussemuadata = "update tb_data_pk_emon set deleted ='1' where id_indikator = '" . $id . "'";
                    $resultrumus = mysqli_query($link, $sqlrubahrumussemuadata);
                    $sqlrubahrumussemuadata = "update tb_data_pk set deleted ='1' where id_indikator = '" . $id . "'";
                    $resultrumus = mysqli_query($link, $sqlrubahrumussemuadata);
                    $sqlrubahrumussemuadata = "update tb_data_baseline set deleted ='1' where id_indikator = '" . $id . "'";
                    $resultrumus = mysqli_query($link, $sqlrubahrumussemuadata);
                }
            }
        }
        //echo $sql;
        //die;
        $result = mysqli_query($link, $sql);
        $sql = "update tb_indikator_akhir set id_parent = null where id_parent ='0'";
        $result = mysqli_query($link, $sql);
        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah Ditambah'));
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

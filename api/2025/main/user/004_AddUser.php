<?php
include '../../library/config.php';
error_reporting(0);
// check_injection();
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
    $unor = fixup($_POST['unor']);
    $kategori_satker = fixup($_POST['kategori_satker']);
    $satker = fixup($_POST['satker']);
    $username = fixup($_POST['username']);
    $password = encrypt(fixup($_POST['password']));
    $sebelumpassword = (fixup($_POST['password']));
    $nama = fixup($_POST['nama']);
    $level = fixup($_POST['level']);
    $levelcode = fixup($_POST['levelcode']);
    $levellabel = fixup($_POST['levellabel']);
    $jabatan = fixup($_POST['jabatan']);
    $pangkat = fixup($_POST['pangkat']);
    $nip = fixup($_POST['nip']);
    $notelp = fixup($_POST['notelp']);
    $alamat = fixup($_POST['alamat']);
    $email = fixup($_POST['email']);
    $evaluasi = fixup($_POST['evaluasi']);
    $evaluasicode = fixup($_POST['evaluasicode']);
    $evaluasilabel = fixup($_POST['evaluasilabel']);
    $verif = fixup($_POST['verif']);
    $verifcode = fixup($_POST['verifcode']);
    $veriflabel = fixup($_POST['veriflabel']);
    $satkerwewenangpusat = fixup($_POST['hakaksesevaluatorlke']);
    $satkerwewenangpusatcode = fixup($_POST['hakaksesevaluatorlkecode']);
    $satkerwewenangpusatlabel = fixup($_POST['hakaksesevaluatorlkelabel']);
    $verif2 = fixup($_POST['verif2']);
    $verif2code = fixup($_POST['verif2code']);
    $verif2label = fixup($_POST['verif2label']);
    $verif3 = fixup($_POST['verif3']);
    $verif3code = fixup($_POST['verif3code']);
    $verif3label = fixup($_POST['verif3label']);
    $jenis_piu = fixup($_POST['jenis_piu']);
    $createdby = fixup($_POST['createdby']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($nama <> '') && ($username <> '') && ($password <> '')) {

        $sqlcekuser = "select username from tb_user where username='" . $username . "' and deleted='0'";
        $resultcekuser = mysqli_query($link, $sqlcekuser);
        $numcekuser = mysqli_num_rows($resultcekuser);
        if ($numcekuser > 0) {
            mysqli_close($link);
            echo json_encode(array('response' => 'error', 'message' => 'Username sudah terdaftar di sistem'));
            die;
        } else {
            $angka = 0;
            // password lebih dari 10 karakter
            if (strlen($sebelumpassword) > 10) {
                $angka = $angka + 20;
            }
            // password ada angkanya
            if (preg_match("#[0-9]+#", $sebelumpassword)) {
                $angka = $angka + 20;
            }
            // password ada huruf kecil
            if (preg_match("#[a-z]+#", $sebelumpassword)) {
                $angka = $angka + 20;
            }
            // password ada huruf besar
            if (preg_match("#[A-Z]+#", $sebelumpassword)) {
                $angka = $angka + 20;
            }
            // password ada simbol
            if (preg_match("#\W+#", $sebelumpassword)) {
                $angka = $angka + 20;
            }

            if ($angka < 60) {
                mysqli_close($link);
                echo json_encode(array('response' => 'error', 'message' => 'Password kurang kuat..'));
                die;
            }

            $kode_satker = "";
            $sqlceksatker = "select kode_satker from master_satker where id = '" . $satker . "'";
            $resultceksatker = mysqli_query($link, $sqlceksatker);
            while ($rowceksatker = mysqli_fetch_assoc($resultceksatker)) {
                $kode_satker = $rowceksatker['kode_satker'];
            }

            $sql = "insert into tb_user (id,unor,kategori_satker,satker,kode_satker,level
            ,levelcode,levellabel,username,password,jabatan,pangkat,nip,notelp,alamat,email
            ,verif,verifcode,veriflabel,jenis_piu,verif2,verif2code,verif2label,verif3,verif3code,verif3label
            ,evaluasi,evaluasicode,evaluasilabel
            ,hakaksesevaluatorlke,hakaksesevaluatorlkecode,hakaksesevaluatorlkelabel
            ,nama,token,ikon,createddate,createdby,deleted) values 
            (null,'" . $unor . "','" . $kategori_satker . "','" . $satker . "','" . $kode_satker . "','" . $level . "'
            ,'" . $levelcode . "','" . $levellabel . "','" . $username . "','" . $password . "'
            ,'" . $jabatan . "','" . $pangkat . "','" . $nip . "','" . $notelp . "'
            ,'" . $alamat . "','" . $email . "','" . $verif . "'
            ,'" . $verifcode . "','" . $veriflabel . "','" . $jenis_piu . "','" . $verif2 . "'
            ,'" . $verif2code . "','" . $verif2label . "'
            ,'" . $verif3 . "'
            ,'" . $verif3code . "','" . $verif3label . "'
            ,'" . $evaluasi . "'
            ,'" . $evaluasicode . "','" . $evaluasilabel . "','" . $satkerwewenangpusat . "'
            ,'" . $satkerwewenangpusatcode . "','" . $satkerwewenangpusatlabel . "'
            ,'" . $nama . "'
            ,md5('" . $username . "'),'3177440.png',SYSDATE(),'" . $createdby . "','0')";
             //echo $sql;
             //die;
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

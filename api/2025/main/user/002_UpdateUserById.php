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

$sqlcek = "select id as jumlah from tb_user where token='" . str_replace('"','',$tempBearer[1]) . "' and deleted='0'";
$resultsqlcek = mysqli_query($link, $sqlcek);
$numcek = mysqli_num_rows($resultsqlcek);
if ($numcek > 0) {
    $nama = fixup($_POST['nama']);
    $kduser = fixup($_POST['kduser']);
    $username = fixup($_POST['username']);
    $username_tmp = fixup($_POST['username_tmp']);
    $passwordlama = fixup($_POST['passwordlama']);
    $passwordbaru = fixup($_POST['passwordbaru']);
    $angka = 0;
    // password lebih dari 10 karakter
    if (strlen($passwordbaru) > 10) {
        $angka = $angka + 20;
    }
    // password ada angkanya
    if (preg_match("#[0-9]+#", $passwordbaru)) {
        $angka = $angka + 20;
    }
    // password ada huruf kecil
    if (preg_match("#[a-z]+#", $passwordbaru)) {
        $angka = $angka + 20;
    }
    // password ada huruf besar
    if (preg_match("#[A-Z]+#", $passwordbaru)) {
        $angka = $angka + 20;
    }
    // password ada simbol
    if (preg_match("#\W+#", $passwordbaru)) {
        $angka = $angka + 20;
    }

    if ($angka < 60) {
        mysqli_close($link);
        echo json_encode(array('response' => 'error', 'message' => 'Password kurang kuat..'));
        die;
    }
    $foto = fixup($_POST['foto']);
    if ($foto == '') {
        $foto = '3177440.png';
    }
    if ($foto == null) {
        $foto = '3177440.png';
    }
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($passwordlama <> '') && ($passwordbaru <> '')) {
        $sql = "select password from tb_user where deleted='0' and md5(id)='" . $kduser . "'";
        $result = mysqli_query($link, $sql);
        $numrow = mysqli_num_rows($result);
        if ($numrow > 0) {
            if ($result) {
                while ($row = mysqli_fetch_array($result)) {
                    if ($username <> $username_tmp) {
                        $sqlcek = "select username from tb_user where username='" . $username . "' and deleted='0'";
                        $resultcek = mysqli_query($link, $sqlcek);
                        $numrowcek = mysqli_num_rows($resultcek);
                        if ($numrowcek == 0) {

                            $passwordeksisting = $row['password'];
                            if ($passwordlama == decrypt($passwordeksisting)) {
                                $sql = "update tb_user set password='" . encrypt($passwordbaru) . "' 
                                ,username='" . $username . "',ikon='" . $foto . "',token=md5('" . $username . "')
                                ,nama='" . $nama . "'
                                where md5(id)='" . $kduser . "'";
                                $result = mysqli_query($link, $sql);
                                mysqli_close($link);
                                echo json_encode(array('response' => 'success', 'message' => 'Profil Telah Dirubah'));
                                die;
                            } else {
                                mysqli_close($link);
                                echo json_encode(array('response' => 'error', 'message' => 'Password Lama Tidak Cocok'));
                                die;
                            }
                        } else {
                            mysqli_close($link);
                            echo json_encode(array('response' => 'error', 'message' => 'Username Sudah Dipakai Pengguna Lain..'));
                            die;
                        }
                    } else {
                        $passwordeksisting = $row['password'];
                        if ($passwordlama == decrypt($passwordeksisting)) {
                            $sql = "update tb_user set password='" . encrypt($passwordbaru) . "' 
                            ,username='" . $username . "',ikon='" . $foto . "',token=md5('" . $username . "')
                            ,nama='" . $nama . "'
                            where md5(id)='" . $kduser . "'";
                            $result = mysqli_query($link, $sql);
                            mysqli_close($link);
                            echo json_encode(array('response' => 'success', 'message' => 'Profil Telah Dirubah'));
                            die;
                        } else {
                            mysqli_close($link);
                            echo json_encode(array('response' => 'error', 'message' => 'Password Lama Tidak Cocok'));
                            die;
                        }
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

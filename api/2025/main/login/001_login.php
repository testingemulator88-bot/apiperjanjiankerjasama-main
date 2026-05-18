<?php
include '../../library/config.php';
error_reporting(0);
check_injection();
if ((isset($_POST['username'])) && (isset($_POST['katakunci']))) {
    header("Access-Control-Allow-Origin: *");
    //header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Content-type: application/json");
    $ipacuan = base64_encode($_SERVER['REMOTE_ADDR']);
    $header = apache_request_headers();
    $Bearer = $header['authorization'];
    if ($Bearer == '') {
        $Bearer = $header['Authorization'];
    }

    $ipnya = fixup($_POST['ipnya']);

    //echo $Bearer;

    if (($Bearer == 'Bearer GPMop8LQ06S0rZXcJyEH3wk8jVrINbHwn7tBq2' . $ipnya) && ($ipacuan == $ipnya)) {
        $myArray = array();
        $pwd = fixup($_POST['katakunci']);
        $angka = 0;
        // password lebih dari 10 karakter
        if (strlen($pwd) > 10) {
            $angka = $angka + 20;
        }
        // password ada angkanya
        if (preg_match("#[0-9]+#", $pwd)) {
            $angka = $angka + 20;
        }
        // password ada huruf kecil
        if (preg_match("#[a-z]+#", $pwd)) {
            $angka = $angka + 20;
        }
        // password ada huruf besar
        if (preg_match("#[A-Z]+#", $pwd)) {
            $angka = $angka + 20;
        }
        // password ada simbol
        if (preg_match("#\W+#", $pwd)) {
            $angka = $angka + 20;
        }

        if ($angka < 60) {
            mysqli_close($link);
            echo json_encode(array('response' => 'error', 'message' => 'Autentifikasi kekuatan sandi pengguna tidak valid, silahkan hubungi administrator untuk merubah kata kunci'));
            die;
        } else {
            $sql = "select md5(id) as tmpsys,level as level,token from tb_user where deleted='0' 
            and username='" . fixup($_POST['username']) . "' and password='" . encrypt($_POST['katakunci']) . "' ";
            //echo $sql;
            //die;
            $result = mysqli_query($link, $sql);
            if ($result) {
                $num = mysqli_num_rows($result);
                if ($num > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $myArray[] = $row;
                        $token = $row['token'];
                    }
                    $response         = [];
                    $response['data'] =  $myArray;
                    $jumlahlogin = 0;
                    $sqlcek = "select count(id) as jumlah from ztable_detail_log where nama='" . fixup($_POST['username']) . "' and tanggal >= DATE_SUB(NOW(),INTERVAL 5 MINUTE)";
                    $resultcek = mysqli_query($link, $sqlcek);
                    while ($rowcek = mysqli_fetch_assoc($resultcek)) {
                        $jumlahlogin = (float) $rowcek['jumlah'];
                    }

                    if ($jumlahlogin > 6) {
                        mysqli_close($link);
                        echo json_encode(array('response' => 'error', 'message' => 'Indikasi login ilegal!! <br> <i>Silahkan coba lagi dalam 30 menit atau coba username lainnya!!</i>'));
                        die;
                    } else {
                        $sqlinsert = "insert into ztable_detail_log (id, nama,ipnya, tanggal) values (null,'" . fixup($_POST['username']) . "','" . $_SERVER['REMOTE_ADDR'] . "',SYSDATE())";
                        $resultinsert = mysqli_query($link, $sqlinsert);

                        mysqli_close($link);
                        echo json_encode(array('response' => 'success', 'accessToken' => $token, 'content' => $response), JSON_PRETTY_PRINT);
                        die;
                    }
                    mysqli_close($link);
                    echo json_encode(array('response' => 'success', 'accessToken' => $token, 'content' => $response), JSON_PRETTY_PRINT);
                    die;
                } else {
                    mysqli_close($link);
                    echo json_encode(array('response' => 'error', 'message' => 'User dan password salah!! <br> <i>Wrong user and password!!</i>'));
                    die;
                }
            } else {
                mysqli_close($link);
                echo json_encode(array('response' => 'error', 'message' => 'User dan password salah!! <br> <i>Wrong user and password!!</i>'));
                die;
            }
        }
    } else {
        mysqli_close($link);
        echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
        die;
    }
} else {
    header("Access-Control-Allow-Origin: *");
    //header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Methods: POST");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Content-type: application/json");
    mysqli_close($link);
    echo json_encode(array('response' => 'error', 'message' => 'User dan password salah!! <br> <i>Wrong user and password!!</i>'));
    die;
}

<?php
include '../../../library/config.php';
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
// echo $sqlcek;
// die;
$resultsqlcek = mysqli_query($link, $sqlcek);
$numcek = mysqli_num_rows($resultsqlcek);

if ($numcek > 0) {
    $level = fixup($_GET['level']);
    if (($Bearer <> '') && ($tempBearer[1] <> '')) {
        $filterquery    = "";
        $cari           = fixup($_GET['cari']);

        if ($cari <> '') {
            $filterquery = "AND a.nama_kriteria like '%" . $cari . "%'";
        }

        $halaman        = fixup($_GET['halaman']);

        if ($halaman == '') {
            $halaman = 1;
        }

        $mulai          = 0;
        $hal_maksimum   = 20;
        $jumlahdata     = 0;
        $jumlah_halaman = 0;

        $sqljumlah = "SELECT count(a.id_kriteria) as jumlahdata FROM kriteria_penilaian a WHERE a.is_active='1' " . $filterquery . " ORDER BY a.id_kriteria";
        // echo $sqljumlah;
        // die;
        $numjumlah      = 0;
        $resultjumlah   = mysqli_query($link, $sqljumlah);
        while ($rowjumlah = mysqli_fetch_array($resultjumlah)) {
            $numjumlah = $rowjumlah['jumlahdata'];
        }

        $jumlahdata = $numjumlah;
        $jumlah_halaman = ceil($jumlahdata / $hal_maksimum);

        if ($halaman <> '') {
            $mulai = (($halaman * $hal_maksimum) - $hal_maksimum);
        }

        $myArray = array();

        $sql    = "SELECT a.id_kriteria, b.nama_subkomponen, a.kode_kriteria, a.nama_kriteria, a.langkah_kerja, a.daftar_evidence 
                    FROM kriteria_penilaian as a
                    INNER JOIN subkomponen as b ON b.id_subkomponen = a.id_subkomponen
                    WHERE a.is_active='1' ". $filterquery ." ORDER BY a.id_subkomponen, a.kode_kriteria, a.nama_kriteria
                    LIMIT ". $mulai . " , 20 ";

        // echo $sql;
        // die();

        $result = mysqli_query($link, $sql);
        $num    = mysqli_num_rows($result);

        if ($num > 0) {
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $myArray[] = $row;
                }

                $response                   = [];
                $response['jumlahdata']     = $jumlahdata;
                $response['jumlahhalaman']  = $jumlah_halaman;
                $response['mulai']          = $mulai;
                $response['data']           =  $myArray;

                mysqli_close($link);
                echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
                die;
            }
        }
        else {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'data kosong'), JSON_PRETTY_PRINT);
            die;
        }
    }
    else {
        mysqli_close($link);
        echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
        die;
    }
}
else {
    mysqli_close($link);
    echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
    die;
}

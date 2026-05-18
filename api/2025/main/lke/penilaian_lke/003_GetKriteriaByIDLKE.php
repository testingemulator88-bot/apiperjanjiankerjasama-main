<?php
include '../../../library/config.php';
error_reporting(0);
// check_injection();
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

$sqlcek         = "select id as jumlah from tb_user where token='" . str_replace('"', '', $tempBearer[1]) . "' and deleted='0'";
$resultsqlcek   = mysqli_query($link, $sqlcek);
$numcek         = mysqli_num_rows($resultsqlcek);

if ($numcek > 0) {
    $id_kriteria    = fixup($_GET['id_kriteria']);
    $tahun          = fixup($_GET['tahun']);
    $kode_satker    = fixup($_GET['id_user']);
    $id_lke         = fixup($_GET['id_lke']);

    $sql_cek_user   = "SELECT id FROM tb_user WHERE kode_satker = '". $kode_satker ."' AND deleted = '0'";
    $result_cek_user= mysqli_query($link, $sql_cek_user);
    $row_cek_user   = mysqli_fetch_assoc($result_cek_user);
    $id_user        = $row_cek_user['id'];

    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($id_lke <> '')) {
        $myArray    = array();
        $myArray2   = array();
        $myArray3   = array();
        $myArray4   = array();
        $myArray5   = array();

        $sqlCekLKE  = "SELECT kriteria_penilaian1, kriteria_penilaian2, kriteria_penilaian3, kriteria_penilaian4, upload_dok_satker, nama_file_dok, jawaban_satker, link_bukti_dukung, catatan_evaluator 
                        FROM hasil_lke WHERE id_hasil_lke = '" . $id_lke . "'";

        $resultCekLKE = mysqli_query($link, $sqlCekLKE);
        while($rowLKE = mysqli_fetch_assoc($resultCekLKE)){

            if(!is_null($rowLKE['upload_dok_satker']) || $rowLKE['upload_dok_satker'] != '' || $rowLKE['link_bukti_dukung'] != '' || !is_null($rowLKE['link_bukti_dukung']) || $rowLKE['jawaban_satker'] != '' || !is_null($rowLKE['jawaban_satker']) || $rowLKE['jawaban_satker'] != ''){
                $dokumen    = explode(',', $rowLKE['upload_dok_satker']);
                $bukti_dok  = explode(',', $rowLKE['link_bukti_dukung']);
                $nama_file  = explode(',', $rowLKE['nama_file_dok']);
                $jawaban    = $rowLKE['jawaban_satker'];
                $catatan    = $rowLKE['catatan_evaluator'];
                
                for ($x = 0; $x < count($dokumen); $x++) {
                    if($nama_file[$x] == '' || is_null($nama_file[$x])){
                        $nama = $dokumen[$x];
                    }
                    else{
                        $nama = $nama_file[$x];
                    }

                    $myArray[] = (object)[
                        'dokumen'       => $dokumen[$x],
                        'nama_file_dok' => $nama,
                        'name'          => $dokumen[$x],
                        'status'        => 100,
                    ];
                }

                for( $y = 0; $y < count($bukti_dok); $y++) {
                    $myArray2[] = (object)[
                        'bukti_dokumen'   => $bukti_dok[$y]
                    ];
                }

                $myArray3[] = (object)[
                    'jawaban'   => $jawaban
                ];

                $myArray5[] = (object)[
                    'catatan_evaluator'   => $catatan
                ];
            }
            else{
                $myArray[] = (object)[
                    'dokumen'   => ''
                ];

                $myArray2[] = (object)[
                    'bukti_dokumen' => '',
                ];

                $myArray3[] = (object)[
                    'jawaban'   => ''
                ];

                $myArray5[] = (object)[
                    'catatan_evaluator'   => ''
                ];
            }

            $myArray4[] = (object)[
                    'kriteria_penilaian1'   => $rowLKE['kriteria_penilaian1'],
                    'kriteria_penilaian2'   => $rowLKE['kriteria_penilaian2'],
                    'kriteria_penilaian3'   => $rowLKE['kriteria_penilaian3'],
                    'kriteria_penilaian4'   => $rowLKE['kriteria_penilaian4'],
                ];
            
        }

        $response                           = [];
        $response['data']                   = $myArray;
        $response['bukti']                  = $myArray2;
        $response['jawaban']                = $myArray3;
        $response['kriteria_penilaian_lke'] = $myArray4;
        $response['catatan_evaluator']      = $myArray5;
        
        mysqli_close($link);
        echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
        die;
        // echo $sql;
        // die;
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
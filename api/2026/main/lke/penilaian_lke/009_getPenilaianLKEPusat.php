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
        if(strstr($level, '1') || strstr($level, '2')){
            $kode_satker    = fixup($_GET['id_user']);
            // $sql_cek_user   = "SELECT id FROM tb_user WHERE kode_satker = '". $kode_satker ."' AND (b.level in (4,6,5,8,9) or a.level_piu = 21 or a.level_piu = 6 or a.level_piu = 9 or a.level_piu = 31) AND deleted = '0'";
            // $sql_cek_user   = "SELECT b.id FROM master_satker as a 
            //         INNER JOIN tb_user as b ON b.kode_satker = a.kode_satker and b.deleted='0'
            //         WHERE (b.level in (4,6,5,8,9) or a.level_piu = 21 or a.level_piu = 6 or a.level_piu = 31) and b.kode_satker = '". $kode_satker . "'";

            $sql_cek_user   = "SELECT b.kode_satker FROM master_satker as a 
                                INNER JOIN tb_user as b ON b.kode_satker = a.kode_satker and b.deleted='0'
                                WHERE (b.level in (4,6,8,9) or a.level_piu = 21 or a.level_piu = 6 or a.level_piu = 31) and b.kode_satker = '". $kode_satker . "' GROUP BY b.kode_satker";
            
            $result_cek_user= mysqli_query($link, $sql_cek_user);
            $row_cek_user   = mysqli_fetch_assoc($result_cek_user);
            $id_user        = $row_cek_user['kode_satker'];
        }
        else{
            $id_user    = fixup($_GET['id_user']);
        }

        $tahun      = fixup($_GET['tahun']);

        if($id_user != '' && $tahun != ''){
            $komponenArray = array();

            $KomponenSql    = "SELECT id_komponen, kode_komponen, nama_komponen, bobot_komponen FROM komponen WHERE is_active = '1'";

            // echo $KomponenSql;
            // die();

            $KomponenResult = mysqli_query($link, $KomponenSql);
            $KomponenNum    = mysqli_num_rows($KomponenResult);

            $total_bobot_komponen = 0;
            $total_hasil_evaluasi = 0;

            if ($KomponenNum > 0) {
                if ($KomponenResult) {
                    while ($rowKomponen = mysqli_fetch_assoc($KomponenResult)) {
                        $nilai_komponen_instansi_pemerintah = 0;
                        
                        $subKomponenArray   = array();
                        $subKomponenSql     = "SELECT id_subkomponen, kode_subkomponen, nama_subkomponen, bobot_subkomponen FROM subkomponen WHERE id_komponen = '". $rowKomponen['id_komponen'] ."' and is_active ='1'";

                        $subKomponenResult  = mysqli_query($link, $subKomponenSql);
                        $subKomponenNum     = mysqli_num_rows($subKomponenResult);

                        if($subKomponenNum > 0){
                            if($subKomponenResult){
                                while($rowSubKomponen = mysqli_fetch_assoc($subKomponenResult)){
                                    $kriteriaArray  = array();

                                    $kriteriaSql    = "SELECT id_kriteria, kode_kriteria, nama_kriteria, langkah_kerja, daftar_evidence FROM kriteria_penilaian WHERE id_subkomponen = '". $rowSubKomponen['id_subkomponen'] ."' and is_active='1'";
                                    $kriteriaResult = mysqli_query($link, $kriteriaSql);
                                    $kriteriaNum    = mysqli_num_rows($kriteriaResult);

                                    $sql_cek_total_kriteria     = "SELECT count(id_kriteria) as total_kriteria FROM kriteria_penilaian WHERE id_subkomponen = '". $rowSubKomponen['id_subkomponen'] ."' and is_active='1'";
                                    $result_cek_total_kriteria  = mysqli_query($link, $sql_cek_total_kriteria);
                                    $row_cek_total_kriteria     = mysqli_fetch_assoc($result_cek_total_kriteria);
                                    $total_kriteria             = $row_cek_total_kriteria['total_kriteria'];

                                    $sql_kriteria_penilaian1 = "SELECT sum(a.kriteria_penilaian1) as pemenuhan_kriteria, sum(a.kriteria_penilaian2) as total_tkmn, 
                                                                sum(a.kriteria_penilaian3) as total_tds1, sum(a.kriteria_penilaian4) as total_tds5 FROM hasil_lke as a 
                                                                RIGHT JOIN kriteria_penilaian as b on b.id_kriteria = a.id_kriteria
                                                                WHERE a.tahun = '". $tahun ."' AND a.kode_satker = '". $id_user ."' 
                                                                AND (
                                                                        a.level in (4,6,8,9) 
                                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                                    ) 
                                                                AND b.id_subkomponen = '". $rowSubKomponen['id_subkomponen'] ."'
                                                                AND a.is_active = '1' AND b.is_active = '1'
                                                                ";

                                    // echo $sql_kriteria_penilaian1;
                                    // die;

                                    $result_kriteria_penilaian1 = mysqli_query($link, $sql_kriteria_penilaian1);
                                    $row_kriteria_penilaian1    = mysqli_fetch_assoc($result_kriteria_penilaian1);
                                    $pemenuhan_kriteria         = $row_kriteria_penilaian1['pemenuhan_kriteria'];
                                    $total_tkmn                 = $row_kriteria_penilaian1['total_tkmn'];
                                    $total_tds1                 = $row_kriteria_penilaian1['total_tds1'];
                                    $total_tds5                 = $row_kriteria_penilaian1['total_tds5'];

                                    if($pemenuhan_kriteria === null){
                                        $pm1 = 0;
                                    }
                                    else{
                                        $pm1 = (float)($pemenuhan_kriteria / $total_kriteria) * 100;
                                    }

                                    if($pm1 == 100){
                                        if($total_tds5 == $total_kriteria){
                                            $sql_pj             = "SELECT * FROM pilihan_jawaban WHERE id_jawaban = '1' AND is_active = '1'";
                                            $result_pj          = mysqli_query($link, $sql_pj);
                                            $row_pj             = mysqli_fetch_assoc($result_pj);
                                            $nilai_evaluasi     = $row_pj['pilihan_jawaban'];
                                            $nilai              = $row_pj['nilai'];
                                        }
                                        elseif($total_tds1 == $total_kriteria){
                                            $sql_pj             = "SELECT * FROM pilihan_jawaban WHERE id_jawaban = '2' AND is_active = '1'";
                                            $result_pj          = mysqli_query($link, $sql_pj);
                                            $row_pj             = mysqli_fetch_assoc($result_pj);
                                            $nilai_evaluasi     = $row_pj['pilihan_jawaban'];
                                            $nilai              = $row_pj['nilai'];
                                        }
                                        elseif($pemenuhan_kriteria == $total_kriteria){
                                            $sql_pj             = "SELECT * FROM pilihan_jawaban WHERE id_jawaban = '3' AND is_active = '1'";
                                            $result_pj          = mysqli_query($link, $sql_pj);
                                            $row_pj             = mysqli_fetch_assoc($result_pj);
                                            $nilai_evaluasi     = $row_pj['pilihan_jawaban'];
                                            $nilai              = $row_pj['nilai'];
                                        }
                                    }
                                    elseif($pm1 > 75 && $pm1 < 100){
                                        $sql_pj             = "SELECT * FROM pilihan_jawaban WHERE id_jawaban = '4' AND is_active = '1'";
                                        $result_pj          = mysqli_query($link, $sql_pj);
                                        $row_pj             = mysqli_fetch_assoc($result_pj);
                                        $nilai_evaluasi     = $row_pj['pilihan_jawaban'];
                                        $nilai              = $row_pj['nilai'];
                                    }
                                    elseif($pm1 > 50 && $pm1 <= 75){
                                        $sql_pj             = "SELECT * FROM pilihan_jawaban WHERE id_jawaban = '5' AND is_active = '1'";
                                        $result_pj          = mysqli_query($link, $sql_pj);
                                        $row_pj             = mysqli_fetch_assoc($result_pj);
                                        $nilai_evaluasi     = $row_pj['pilihan_jawaban'];
                                        $nilai              = $row_pj['nilai'];
                                    }
                                    elseif($pm1 > 25 && $pm1 <= 50){
                                        $sql_pj             = "SELECT * FROM pilihan_jawaban WHERE id_jawaban = '6' AND is_active = '1'";
                                        $result_pj          = mysqli_query($link, $sql_pj);
                                        $row_pj             = mysqli_fetch_assoc($result_pj);
                                        $nilai_evaluasi     = $row_pj['pilihan_jawaban'];
                                        $nilai              = $row_pj['nilai'];
                                    }
                                    elseif($pm1 > 0 && $pm1 <= 25){
                                        $sql_pj             = "SELECT * FROM pilihan_jawaban WHERE id_jawaban = '7' AND is_active = '1'";
                                        $result_pj          = mysqli_query($link, $sql_pj);
                                        $row_pj             = mysqli_fetch_assoc($result_pj);
                                        $nilai_evaluasi     = $row_pj['pilihan_jawaban'];
                                        $nilai              = $row_pj['nilai'];
                                    }
                                    else{
                                        $sql_pj             = "SELECT * FROM pilihan_jawaban WHERE id_jawaban = '8' AND is_active = '1'";
                                        $result_pj          = mysqli_query($link, $sql_pj);
                                        $row_pj             = mysqli_fetch_assoc($result_pj);
                                        $nilai_evaluasi     = $row_pj['pilihan_jawaban'];
                                        $nilai              = $row_pj['nilai'];
                                    }

                                    $bobot_subkomponen = (float) $rowKomponen['bobot_komponen'] * (float) $rowSubKomponen['bobot_subkomponen'];

                                    if($nilai_evaluasi == 'AA'){
                                        $nilai_instansi_pemerintah = 1 * $bobot_subkomponen;
                                    }
                                    elseif($nilai_evaluasi == 'A'){
                                        $nilai_instansi_pemerintah = 0.9 * $bobot_subkomponen;
                                    }
                                    elseif($nilai_evaluasi == 'BB'){
                                        $nilai_instansi_pemerintah = 0.8 * $bobot_subkomponen;
                                    }
                                    elseif($nilai_evaluasi == 'B'){
                                        $nilai_instansi_pemerintah = 0.7 * $bobot_subkomponen;
                                    }
                                    elseif($nilai_evaluasi == 'CC'){
                                        $nilai_instansi_pemerintah = 0.6 * $bobot_subkomponen;
                                    }
                                    elseif($nilai_evaluasi == 'C'){
                                        $nilai_instansi_pemerintah = 0.5 * $bobot_subkomponen;
                                    }
                                    elseif($nilai_evaluasi == 'D'){
                                        $nilai_instansi_pemerintah = 0.3 * $bobot_subkomponen;
                                    }
                                    elseif($nilai_evaluasi == 'E'){
                                        $nilai_instansi_pemerintah = 0 * $bobot_subkomponen;
                                    }

                                    if($kriteriaNum > 0){
                                        if($kriteriaResult){
                                            while($rowKriteria = mysqli_fetch_assoc($kriteriaResult)){

                                                if(strstr($level,'4') || strstr($level,'6') || strstr($level,'8') || strstr($level,'9')){
                                                    $cek_lke        = "SELECT a.* FROM hasil_lke as a
                                                                        WHERE a.id_kriteria = '". $rowKriteria['id_kriteria'] ."' AND a.tahun = '". $tahun ."' AND a.kode_satker ='". $id_user ."' 
                                                                        AND a.is_active = '1'
                                                                        AND (
                                                                                a.level in (4,6,8,9) 
                                                                                OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                                                OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                                                OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                                                OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                                            ) 
                                                                        GROUP BY a.id_hasil_lke
                                                                        ";
                                                }
                                                else{
                                                    $cek_lke        = "SELECT a.* FROM hasil_lke as a
                                                                        WHERE a.id_kriteria = '". $rowKriteria['id_kriteria'] ."' AND a.tahun = '". $tahun ."' AND a.kode_satker ='". $id_user ."' 
                                                                        AND a.is_active = '1' 
                                                                        AND (
                                                                                a.level in (4,6,8,9) 
                                                                                OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                                                OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                                                OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                                                OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                                            )
                                                                        GROUP BY a.id_hasil_lke
                                                                        ";
                                                }

                                                // echo $cek_lke;
                                                // die;
                                                
                                                $result_cek_lke = mysqli_query($link, $cek_lke);
                                                $cek_num_result = mysqli_num_rows($result_cek_lke);
                                                $rowLKE         = mysqli_fetch_assoc($result_cek_lke);

                                                if($cek_num_result > 0){
                                                    $data_lke               = 1;
                                                    $id_lke                 = $rowLKE['id_hasil_lke'];
                                                    $status_lke             = $rowLKE['status_lke'];
                                                    $dokumen                = explode(',', $rowLKE['upload_dok_satker']);
                                                    $kriteria_penilaian1    = $rowLKE['kriteria_penilaian1'];
                                                    $kriteria_penilaian2    = $rowLKE['kriteria_penilaian2'];
                                                    $kriteria_penilaian3    = $rowLKE['kriteria_penilaian3'];
                                                    $kriteria_penilaian4    = $rowLKE['kriteria_penilaian4'];
                                                    $tanggapan_evaluator    = $rowLKE['tanggapan_evaluator'];
                                                    $rekomendasi            = $rowLKE['deskripsi_rekomendasi'];
                                                    $tindak_lanjut          = $rowLKE['status_tindak_lanjut'];

                                                    if($kriteria_penilaian1 == 0){
                                                        $kp1 = 'Tidak Ada';
                                                    }
                                                    else{
                                                        $kp1 = 'Ada';
                                                    }

                                                    if($kriteria_penilaian2 == 0){
                                                        $kp2 = 'Tidak Ada';
                                                    }
                                                    else{
                                                        $kp2 = 'Ada';
                                                    }

                                                    if($kriteria_penilaian3 == 0){
                                                        $kp3 = 'Tidak Ada';
                                                    }
                                                    else{
                                                        $kp3 = 'Ada';
                                                    }

                                                    if($kriteria_penilaian4 == 0){
                                                        $kp4 = 'Tidak Ada';
                                                    }
                                                    else{
                                                        $kp4 = 'Ada';
                                                    }

                                                    if($kriteria_penilaian1 == 1 && $kriteria_penilaian2 == 1 && $kriteria_penilaian3 == 1 && $kriteria_penilaian4 == 1){
                                                        $catatan_evaluator  = '-';
                                                    }
                                                    else{
                                                        $catatan_evaluator  = $rowLKE['catatan_evaluator'];
                                                    }

                                                    if(!is_null($rowLKE['tanggal_evaluasi'])){
                                                        $tanggal_evaluasi = $rowLKE['tanggal_evaluasi'];
                                                    }
                                                    else{
                                                        $tanggal_evaluasi = '';
                                                    }

                                                    $array_dokumen  = array();
                                                    for ($x = 0; $x < count($dokumen); $x++) {
                                                        $array_dokumen[] = (object)[
                                                            'dokumen'   => $dokumen[$x]
                                                        ];
                                                    }
                                                }
                                                else{
                                                    $data_lke           = 0;
                                                    $id_lke             = 0;
                                                    $status_lke         = '';
                                                    $array_dokumen      = array();
                                                    $kp1                = '';
                                                    $kp2                = '';
                                                    $kp3                = '';
                                                    $kp4                = '';
                                                    $tanggal_evaluasi   = '';
                                                    $catatan_evaluator  = '';
                                                    $tanggapan_evaluator= '';
                                                    $rekomendasi        = '';
                                                    $tindak_lanjut      = '';
                                                }

                                                $kriteriaArray[]    = (object)
                                                [
                                                    'id_kriteria'           => $rowKriteria['id_kriteria'],
                                                    'kode_kriteria'         => $rowKriteria['kode_kriteria'],
                                                    'nama_kriteria'         => $rowKriteria['nama_kriteria'],
                                                    'langkah_kerja'         => $rowKriteria['langkah_kerja'],
                                                    'daftar_evidence'       => $rowKriteria['daftar_evidence'],
                                                    'data_lke'              => $data_lke,
                                                    'id_lke'                => $id_lke,
                                                    'kriteria_penilaian1'   => $kp1,
                                                    'kriteria_penilaian2'   => $kp2,
                                                    'kriteria_penilaian3'   => $kp3,
                                                    'kriteria_penilaian4'   => $kp4,
                                                    'tanggal_evaluasi'      => $tanggal_evaluasi,
                                                    'catatan_evaluator'     => $catatan_evaluator,
                                                    'tanggapan_evaluator'   => $tanggapan_evaluator,
                                                    'rekomendasi'           => $rekomendasi,
                                                    'tindak_lanjut'         => $tindak_lanjut,
                                                    'dokumen'               => $rowKriteria['dokumen'],
                                                    'status_lke'            => $status_lke,
                                                    'array_dokumen'         => $array_dokumen
                                                ];
                                            }
                                        }
                                    }

                                    $nilai_komponen_instansi_pemerintah += (float)$nilai_instansi_pemerintah;

                                    $subKomponenArray[] = (object)[
                                        'id_subkomponen'            => $rowSubKomponen['id_subkomponen'],
                                        'kode_subkomponen'          => $rowSubKomponen['kode_subkomponen'],
                                        'nama_subkomponen'          => $rowSubKomponen['nama_subkomponen'],
                                        'bobot_subkomponen'         => $bobot_subkomponen,
                                        'nilai_instansi_pemerintah' => $nilai_instansi_pemerintah,
                                        'pemenuhan_kriteria'        => number_format($pm1, 2, ',', '.'),
                                        'nilai_evaluasi'            => $nilai_evaluasi,
                                        'nilai'                     => $nilai,
                                        'rowspan'                   => $total_kriteria,
                                        'detailKriteria'            => $kriteriaArray
                                    ];
                                }
                            }
                        }
                        else {
                            mysqli_close($link);
                            echo json_encode(array('response' => 'success', 'message' => 'data kosong'), JSON_PRETTY_PRINT);
                            die;
                        }

                        $komponenArray[] = (object)
                        [
                            'id_komponen'           => $rowKomponen['id_komponen'],
                            'kode_komponen'         => $rowKomponen['kode_komponen'],
                            'nama_komponen'         => $rowKomponen['nama_komponen'],
                            'bobot_komponen'        => $rowKomponen['bobot_komponen'],
                            'nilai_komponen'        => (float)$nilai_komponen_instansi_pemerintah,
                            'detail_subkomponen'    => $subKomponenArray,
                        ];

                        $total_bobot_komponen += (float)$rowKomponen['bobot_komponen'];
                        $total_hasil_evaluasi += (float)$nilai_komponen_instansi_pemerintah;
                    }

                    $response           = [];
                    $response['data']   = $komponenArray;
                    $response['total_bobot_komponen'] = $total_bobot_komponen;
                    $response['total_hasil_evaluasi'] = $total_hasil_evaluasi;

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

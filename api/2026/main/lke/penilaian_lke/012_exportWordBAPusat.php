<?php
require '../../../../vendor/autoload.php';
include '../../../library/config.php';
error_reporting(0);
check_injection();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;

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
    $level              = fixup($_GET['level']);
    $kode_kategori_satker    = fixup($_GET['kategori_satker']);
    
    if (($Bearer <> '') && ($tempBearer[1] <> '')) {
        if(strstr($level, '1') || strstr($level, '2') || strstr($level, '5')){
            $kode_satker    = fixup($_GET['id_user']);
            if(strstr($level, '1')){
                // $sql_cek_user   = "SELECT id FROM tb_user WHERE kode_satker = '". $kode_satker ."' AND level in (6,8) AND deleted = '0'";
                $sql_cek_user   = "SELECT b.kode_satker FROM master_satker as a 
                    INNER JOIN tb_user as b ON b.kode_satker = a.kode_satker AND b.deleted='0'
                    WHERE (b.level in (4,6,8,9) or a.level_piu = 21 or a.level_piu = 6 or a.level_piu = 9 or a.level_piu = 31) AND b.kode_satker = '". $kode_satker . "'";

                $id_evaluator   = fixup($_GET['id_evaluator']);
            }
            else{
                // $sql_cek_user   = "SELECT id FROM tb_user WHERE kode_satker = '". $kode_satker ."' AND level in (6,8) AND deleted = '0'";
                $sql_cek_user   = "SELECT b.kode_satker FROM master_satker as a 
                    INNER JOIN tb_user as b ON b.kode_satker = a.kode_satker AND b.deleted='0'
                    WHERE (b.level in (4,6,8,9) or a.level_piu = 21 or a.level_piu = 6 or a.level_piu = 9 or a.level_piu = 31) AND b.kode_satker = '". $kode_satker . "'";

                 $id_evaluator   = '03694174';
            }
            
            $result_cek_user= mysqli_query($link, $sql_cek_user);
            $row_cek_user   = mysqli_fetch_assoc($result_cek_user);
            $id_user        = $row_cek_user['kode_satker'];
        }
        else{
            $id_user    = fixup($_GET['id_user']);
        }

        $tahun                  = fixup($_GET['tahun']);
        $kode_satker_evaluator  = fixup($_GET['kode_satker_evaluator']);

        if($id_user != '' && $tahun != ''){
            $sql_satker     = "SELECT kode_satker, kdbalai, replace(nama_satker, 'SATKER', '') as nama_satker FROM master_satker WHERE kode_satker = '". $kode_satker ."' AND deleted = '0'";
            $result_satker  = mysqli_query($link, $sql_satker);
            $row_satker     = mysqli_fetch_assoc($result_satker);
            $nama_satker    = $row_satker['nama_satker'];
            $kode_balai     = $row_satker['kdbalai'];

            $sql_master_kategori_satker     = "SELECT * FROM master_kategori_satker WHERE id = '". $kode_balai ."' AND deleted = '0' ORDER BY id ASC";
            $result_master_kategori_satker  = mysqli_query($link, $sql_master_kategori_satker);
            $row_kategori_satker            = mysqli_fetch_assoc($result_master_kategori_satker);
            $kategori_satker                = $row_kategori_satker['nama_kategori'];

            $templatePath =  __DIR__ . '/Format_BA_Kesepakatan_Satker.docx';

            // cek file
            if(!file_exists($templatePath)){
                die("Template file tidak ditemukan : ". $templatePath);
            }

            $template = new TemplateProcessor($templatePath);

            $sql_ttd    = "SELECT * FROM data_ttd_lke WHERE kode_satker = '" . $kode_satker_evaluator . "' AND tahun = '" . $tahun . "' AND is_active = '1' LIMIT 1";
            $result_ttd = mysqli_query($link, $sql_ttd);
            $ttd_num    = mysqli_num_rows($result_ttd);

            if($ttd_num > 0){
                $row_ttd    = mysqli_fetch_assoc($result_ttd);

                $hari_ttd       = haripadaTanggal($row_ttd['tgl_surat']);
                $tempat_ttd     = $row_ttd['tempat_ttd'];
                $tgl_ttd        = tanggal_indodoank($row_ttd['tgl_surat']);
            }
            else{
                $hari_ttd       = '';
                $tempat_ttd     = '';
                $tgl_ttd        = '';
            }

            // ganti value sederhana
            $template->setValue('hari_ttd', $hari_ttd);
            $template->setValue('tgl_ttd', $tgl_ttd);
            $template->setValue('tempat_ttd', ucwords(strtolower($tempat_ttd)));

            $template->setValue('tahun', (float)$tahun);
            $template->setValue('satker_kapital', $nama_satker);
            $template->setValue('satker', ucwords(strtolower($nama_satker)));
            $template->setValue('balai', $kategori_satker);

            // isi Evaluator
            $evaluatorArray = array();
            $sql_evaluator  = "SELECT a.tim, a.nip, a.jabatan, c.nama_kategori AS nama_kategori FROM evaluator as a
                                INNER JOIN master_kategori_satker as c ON c.id = a.wilayah
                                WHERE a.kode_satker = '". $id_evaluator ."' AND a.tahun_evaluasi = '". $tahun ."' 
                                AND a.is_active = '1' AND c.deleted = '0' AND a.wilayah = '". $kode_kategori_satker ."'
                                ORDER BY a.id_evaluator ASC";

            $result_evaluator = mysqli_query($link, $sql_evaluator);
            while($row_evaluator = mysqli_fetch_array($result_evaluator)){
                $evaluatorArray[] = (object)[
                    'tim'       => $row_evaluator['tim'],
                    'nip'       => $row_evaluator['nip'],
                    'jabatan'   => $row_evaluator['jabatan'],
                    'wilayah'   => $row_evaluator['nama_kategori']
                ];
            }

            // Clone row di template Evaluator
            $template->cloneRow('data_evaluator', count($evaluatorArray));

            // isi data ke template
            foreach($evaluatorArray as $key => $value){
                $template->setValue('no4#' . ($key+1), $key+1);
                $template->setValue('nip#' . ($key+1), $value->nip);
                $template->setValue('data_evaluator#' . ($key+1), ucwords(strtolower($value->tim)));
                $template->setValue('jabatan#' . ($key+1), ucwords(strtolower($value->jabatan)));
                $template->setValue('wilayah#' . ($key+1), ucwords(strtolower($value->wilayah)));
            }

            // isi evaluatan di BA template 1 data
            $sql_evaluatan_row = "SELECT nip, tim, jabatan, tahun FROM evaluatan 
                                    WHERE kode_satker = '". $id_user ."' AND tahun = '". $tahun ."' 
                                    AND (
                                            level in (4,6,8,9) 
                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                        )
                                    AND is_active = '1' ORDER BY id_evaluatan ASC LIMIT 1";
            $result_evaluatan_row = mysqli_query($link, $sql_evaluatan_row);
            $row_evaluatan_row    = mysqli_fetch_assoc($result_evaluatan_row);

            $template->setValue('nama_evaluatan', ucwords(strtolower($row_evaluatan_row['tim'])));
            $template->setValue('nip_evaluatan', $row_evaluatan_row['nip']);
            $template->setValue('jabatan_evaluatan', ucwords(strtolower($row_evaluatan_row['jabatan'])));

            if(strstr($level, '1') || strstr($level, '2') || strstr($level, '5')){
                $template->setValue('pdua', 'UPT');
                $template->setValue('pihak_kedua', $kategori_satker);
            }
            else if(strstr($level, '7') || strstr($level, '8')){
                $template->setValue('pdua', 'Satker');
                $template->setValue('pihak_kedua', ucwords(strtolower($nama_satker)));
            }

            // Clone row di template Evaluator Pihak Pertama
            $template->cloneRow('data_evaluator2', count($evaluatorArray));

            // isi data ke template
            foreach($evaluatorArray as $key => $value){
                $template->setValue('no5#' . ($key+1), $key+1);
                $template->setValue('data_evaluator2#' . ($key+1), ucwords(strtolower($value->tim)));
            }

            // isi evaluatan di BA template Banyak data
            $evaluatanArray         = array();
            $sql_evaluatan_rows     = "SELECT tim FROM evaluatan 
                                        WHERE kode_satker = '". $id_user ."' AND tahun = '". $tahun ."' 
                                        AND (
                                                level in (4,6,8,9) 
                                                OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                            )
                                        AND is_active = '1' ORDER BY id_evaluatan ASC";
            $result_evaluatan_rows  = mysqli_query($link, $sql_evaluatan_rows);
            while($row_evaluatan_rows = mysqli_fetch_array($result_evaluatan_rows)){
                $evaluatanArray[] = (object)[
                    'tim'   => $row_evaluatan_rows['tim']
                ];
            }

            // Clone row di template Evaluatan banyak data
            $template->cloneRow('evaluatan_semua', count($evaluatanArray));

            // isi data ke template
            foreach($evaluatanArray as $key => $value){
                $template->setValue('no6#' . ($key+1), $key+1);
                $template->setValue('evaluatan_semua#' . ($key+1), ucwords(strtolower($value->tim)));
            }

            $komponenArray = array();

            $KomponenSql    = "SELECT id_komponen, kode_komponen, nama_komponen, bobot_komponen FROM komponen WHERE is_active = '1'";
            $KomponenResult = mysqli_query($link, $KomponenSql);
            $KomponenNum    = mysqli_num_rows($KomponenResult);
            
            $total_bobot_komponen   = 0;
            $total_hasil_evaluasi   = 0;

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
                                                                AND b.id_subkomponen = '". $rowSubKomponen['id_subkomponen'] ."' 
                                                                AND (
                                                                        a.level in (4,6,8,9) 
                                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                                        OR (a.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                                    )
                                                                AND a.is_active = '1' AND b.is_active = '1'
                                                                ";

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
                                                // $cek_lke        = "SELECT * FROM hasil_lke WHERE id_kriteria = '". $rowKriteria['id_kriteria'] ."' AND tahun = '". $tahun ."' AND id_user = '". $id_user ."'";
                                                $cek_lke        = "SELECT * FROM hasil_lke WHERE id_kriteria = '". $rowKriteria['id_kriteria'] ."' AND tahun = '". $tahun ."' 
                                                                    AND kode_satker = '". $id_user ."' AND is_active = '1' 
                                                                    AND (
                                                                            level in (4,6,8,9) 
                                                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                                            OR (level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                                        ) 
                                                                    GROUP BY id_hasil_lke";
                                                $result_cek_lke = mysqli_query($link, $cek_lke);
                                                $cek_num_result = mysqli_num_rows($result_cek_lke);
                                                $rowLKE         = mysqli_fetch_assoc($result_cek_lke);

                                                if($cek_num_result > 0){
                                                    $data_lke               = 1;
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
                }
            }

            // Clone row di template
            $template->cloneRow('data_komponen', count($komponenArray));

            // isi data ke template
            foreach($komponenArray as $key => $value){
                $template->setValue('no#' . ($key+1), $key+1);
                $template->setValue('data_komponen#' . ($key+1), ucwords(strtolower(strip_tags($value->nama_komponen))));
                $template->setValue('bobot#' . ($key+1), (float)$value->bobot_komponen);
                $template->setValue('nilai#' . ($key+1), (float)$value->nilai_komponen);
            }

            // isi data total bobot komponen
            $template->setValue('total_bobot_komponen', (float)$total_bobot_komponen);
            $template->setValue('total_hasil_evaluasi', (float)$total_hasil_evaluasi);

            if($total_hasil_evaluasi >90 && $total_hasil_evaluasi <=100){
                $template->setValue('kategori_hasil_evaluasi', 'AA (Sangat Memuaskan) ');
                $template->setValue('khe', 'AA');
            }
            elseif($total_hasil_evaluasi >80 && $total_hasil_evaluasi <=90){
                $template->setValue('kategori_hasil_evaluasi', 'A (Memuaskan) ');
                $template->setValue('khe', 'A');
            }
            elseif($total_hasil_evaluasi >70 && $total_hasil_evaluasi <=80){
                $template->setValue('kategori_hasil_evaluasi', 'BB (Sangat Baik) ');
                $template->setValue('khe', 'BB');
            }
            elseif($total_hasil_evaluasi >60 && $total_hasil_evaluasi <=70){
                $template->setValue('kategori_hasil_evaluasi', 'B (Baik) ');
                $template->setValue('khe', 'B');
            }
            elseif($total_hasil_evaluasi >50 && $total_hasil_evaluasi <=60){
                $template->setValue('kategori_hasil_evaluasi', 'CC (Cukup) ');
                $template->setValue('khe', 'CC');
            }
            elseif($total_hasil_evaluasi >30 && $total_hasil_evaluasi <=50){
                $template->setValue('kategori_hasil_evaluasi', 'C (Kurang) ');
                $template->setValue('khe', 'C');
            }
            elseif($total_hasil_evaluasi >0 && $total_hasil_evaluasi <=30){
                $template->setValue('kategori_hasil_evaluasi', 'D (Sangat Kurang) ');
                $template->setValue('khe', 'D');
            }
            else{
                $template->setValue('kategori_hasil_evaluasi', 'Belum Diisi ');
                $template->setValue('khe', 'E');
            }

            // tabel 2 di word
            $count_komponen = 0;
            foreach($komponenArray as $i => $val){
                ++$count_komponen;

                foreach($val->detail_subkomponen as $j => $val2){
                    ++$count_komponen;
                }
            }
            // Clone row Komponen2 di template
            
            $template->cloneRow('data_komponen2', $count_komponen);
            $child_count = 1;
            foreach($komponenArray as $key => $value){
                $template->setValue('no2#' . ($child_count), (float)$value->kode_komponen);
                $template->setValue('data_komponen2#' . ($child_count), ucwords(strtolower(strip_tags($value->nama_komponen))));
                $template->setValue('catatan2#' . $child_count, '');

                $child_count++;

                foreach($value->detail_subkomponen as $key2 => $value2){
                    $template->setValue('no2#' . $child_count, '');
                    $template->setValue('data_komponen2#' . $child_count, $value2->kode_subkomponen . '. '. ucwords(strtolower(strip_tags($value2->nama_subkomponen))));

                    $catatan_var = '';
                    foreach($value2->detailKriteria as $key3 => $value3){
                        if(!empty($value3->catatan_evaluator) && !is_null($catatan_evaluator)){
                            $catatan_var .= chr(($key3 + 1) + 96) .'. ' . strip_tags(html_entity_decode($value3->catatan_evaluator)) . "\n";
                        }
                    }
                    
                    $template->setValue('catatan2#' . $child_count, $catatan_var);

                    $child_count++;
                }
            }

            $komponenArray2 = array();

            $KomponenSql2    = "SELECT id_komponen, kode_komponen, nama_komponen, bobot_komponen FROM komponen WHERE is_active = '1'";
            $KomponenResult2 = mysqli_query($link, $KomponenSql2);
            $KomponenNum2    = mysqli_num_rows($KomponenResult2);

            if ($KomponenNum2 > 0) {
                if ($KomponenResult2) {
                    while ($rowKomponen2 = mysqli_fetch_assoc($KomponenResult2)) {
                        $subKomponenArray2 = array();

                        $subKomponenSql2    = "SELECT d.deskripsi_rekomendasi, d.status_tindak_lanjut FROM subkomponen as b 
                                                LEFT JOIN kriteria_penilaian as c on c.id_subkomponen = b.id_subkomponen and c.is_active = 1
                                                LEFT JOIN hasil_lke as d on d.id_kriteria = c.id_kriteria AND d.is_active = 1
                                                WHERE d.tahun = '". $tahun ."' AND d.kode_satker = '". $id_user ."' AND b.is_active = 1 
                                                AND b.id_komponen = '". $rowKomponen2['id_komponen'] ."' 
                                                AND (
                                                        d.level in (4,6,8,9) 
                                                        OR (d.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 21 AND level = 7))
                                                        OR (d.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 6 AND level = 7))
                                                        OR (d.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 9 AND level = 7))
                                                        OR (d.level = 7 AND kode_satker = (SELECT kode_satker FROM tb_user WHERE jenis_piu = 31 AND level = 7))
                                                    )
                                                GROUP BY d.id_hasil_lke";
                        $subKomponenResult2 = mysqli_query($link, $subKomponenSql2);
                        while($rowSubKomponen2 = mysqli_fetch_assoc($subKomponenResult2)){
                            $subKomponenArray2[] = (object)[
                                'rekomendasi'   => strip_tags(html_entity_decode($rowSubKomponen2['deskripsi_rekomendasi'])),
                                'tindak_lanjut' => strip_tags(html_entity_decode($rowSubKomponen2['status_tindak_lanjut'])),
                            ];
                        }

                        $komponenArray2[] = (object)
                        [
                            'kode_komponen' => $rowKomponen2['kode_komponen'],
                            'nama_komponen' => $rowKomponen2['nama_komponen'],
                            'detail'        => $subKomponenArray2
                        ];
                    }
                }
            }

            // tabel 3 di word
            // Clone row Komponen3 di template
            $template->cloneRow('data_komponen3', count($komponenArray2));
            
            foreach($komponenArray2 as $key => $value){
                $rekomendasi_var    = '';
                $tindak_lanjut_var  = '';

                foreach($value->detail as $key2 => $value2){
                    if(!empty($value2->rekomendasi) && !is_null($value2->rekomendasi)){
                        $rekomendasi_var .= chr(($key2 + 1) + 96) .'. ' . strip_tags($value2->rekomendasi) . "\n";
                    }

                    if(!empty($value2->tindak_lanjut) && !is_null($value2->tindak_lanjut)){
                        $tindak_lanjut_var .= chr(($key2 + 1) + 96) .'. ' . strip_tags($value2->tindak_lanjut) . "\n";
                    }
                }

                $template->setValue('no3#' . ($key+1), (float)$value->kode_komponen);
                $template->setValue('data_komponen3#' . ($key+1), ucwords(strtolower(strip_tags($value->nama_komponen))));
                $template->setValue('rekomendasi3#' . ($key+1), $rekomendasi_var);
                $template->setValue('tindak_lanjut3#' . ($key+1), $tindak_lanjut_var);
            }
            
            header("Content-Description: File Transfer");
            header('Content-Disposition: attachment; filename="Berita_Acara_Kesepakatan_'. $nama_satker .'_'. date('YmdHis') .'.docx"');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Expires: 0');
            
            $template->saveAs('php://output');
            exit;
        }
    }
}
?>
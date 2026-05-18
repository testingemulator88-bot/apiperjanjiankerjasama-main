<?php
    require '../../../../vendor/autoload.php';
    include '../../../library/config.php';
    error_reporting(0);
    check_injection();
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");

    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
    use PhpOffice\PhpSpreadsheet\Style\Border;
    use PhpOffice\PhpSpreadsheet\Style\Color;

    $spreadsheet = new Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Penilaian LKE'); // Tambahkan baris ini untuk memberi nama sheet pertama

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
            if(strstr($level, '1')){
                // $sql_cek_user   = "SELECT id FROM tb_user WHERE kode_satker = '". $kode_satker ."' AND level in (6,8) AND deleted = '0'";
                $sql_cek_user   = "SELECT b.kode_satker FROM master_satker as a 
                    INNER JOIN tb_user as b ON b.kode_satker = a.kode_satker and b.deleted='0'
                    WHERE (b.level in (4,6,8,9) or a.level_piu = 21 or a.level_piu = 6 or a.level_piu = 9 or a.level_piu = 31) and b.kode_satker = '". $kode_satker . "'";
            }
            else{
                // $sql_cek_user   = "SELECT id FROM tb_user WHERE kode_satker = '". $kode_satker ."'  AND level in (6,8) AND deleted = '0'";
                $sql_cek_user   = "SELECT b.kode_satker FROM master_satker as a 
                    INNER JOIN tb_user as b ON b.kode_satker = a.kode_satker and b.deleted='0'
                    WHERE (b.level in (4,6,8,9) or a.level_piu = 21 or a.level_piu = 6 or a.level_piu = 9 or a.level_piu = 31) and b.kode_satker = '". $kode_satker . "'";
            }
            
            $result_cek_user= mysqli_query($link, $sql_cek_user);
            $row_cek_user   = mysqli_fetch_assoc($result_cek_user);
            $id_user        = $row_cek_user['kode_satker'];
        }
        else{
            $id_user    = fixup($_GET['id_user']);
        }

        $tahun          = fixup($_GET['tahun']);

        if($id_user != '' && $tahun != ''){
            $sql_satker     = "SELECT kode_satker, kdbalai, replace(nama_satker, 'SATKER', '') as nama_satker FROM master_satker WHERE kode_satker = '". $kode_satker ."' AND deleted = '0'";
            $result_satker  = mysqli_query($link, $sql_satker);
            $row_satker     = mysqli_fetch_assoc($result_satker);
            $nama_satker    = ucwords(strtolower($row_satker['nama_satker']));
            $kode_balai     = $row_satker['kdbalai'];

            $sql_master_kategori_satker     = "SELECT * FROM master_kategori_satker WHERE id = '". $kode_balai ."' AND deleted = '0' ORDER BY id ASC";
            $result_master_kategori_satker  = mysqli_query($link, $sql_master_kategori_satker);
            $row_kategori_satker            = mysqli_fetch_assoc($result_master_kategori_satker);
            $kategori_satker                = $row_kategori_satker['nama_kategori'];

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
                                                                        a.level in (4,6,5,8,9) 
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
                                                    $jawaban_satker         = $rowLKE['jawaban_satker'];
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
                                                    $jawaban_satker     = '';
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
                                                    'jawaban_satker'        => $jawaban_satker,
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
                                        'pemenuhan_kriteria'        => $pm1,
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

            // Header Judul
            $sheet->mergeCells('A1:Q1');
            $sheet->setCellValue('A1', 'Lembar Kerja Evaluasi Akuntabilitas Kinerja Instansi Pemerintah di');
            $sheet->mergeCells('A2:Q2');
            $sheet->setCellValue('A2', 'Kementerian Pekerjaan Umum');
            $sheet->mergeCells('A3:Q3');
            $sheet->setCellValue('A3', $nama_satker . ' - Tahun Anggaran ' . $tahun);

            // Header Tabel Kolom
            $sheet->mergeCells('A5:A7');
            $sheet->setCellValue('A5', 'No');

            $sheet->mergeCells('B5:B7');
            $sheet->setCellValue('B5', 'Komponen/Subkomponen/Kriteria');
            
            $sheet->mergeCells('C5:C7');
            $sheet->setCellValue('C5', 'Bobot');

            $sheet->mergeCells('D5:E5');
            $sheet->setCellValue('D5', 'Instansi Pemerintah');

            $sheet->mergeCells('D6:D7');
            $sheet->setCellValue('D6', 'Jawaban');

            $sheet->mergeCells('E6:E7');
            $sheet->setCellValue('E6', 'Nilai');

            $sheet->mergeCells('F5:F7');
            $sheet->setCellValue('F5', 'Langkah Kerja');

            $sheet->mergeCells('G5:G7');
            $sheet->setCellValue('G5', 'Daftar Evidence');

            $sheet->mergeCells('H5:L5');
            $sheet->setCellValue('H5', 'Kriteria Penilaian');

            $sheet->mergeCells('H6:I6');
            $sheet->setCellValue('H6', 'Pemenuhan Kriteria');

            $sheet->setCellValue('H7', 'Ada/Tidak Ada');
            $sheet->setCellValue('I7', 'Nilai');

            $sheet->mergeCells('J6:J7');
            $sheet->setCellValue('J6', 'Terpenuhi dan sesuai kebijakan Mandat Nasional');

            $sheet->mergeCells('K6:K7');
            $sheet->setCellValue('K6', 'Terpenuhi dan telah dipertahankan dalam setidaknya 1 tahun terakhir');

            $sheet->mergeCells('L6:L7');
            $sheet->setCellValue('L6', 'Terpenuhi dan telah dipertahankan dalam setidaknya 5 tahun terakhir');

            $sheet->mergeCells('M5:M7');
            $sheet->setCellValue('M5', 'Hasil Evaluasi');

            $sheet->mergeCells('N5:N7');
            $sheet->setCellValue('N5', 'Nilai Hasil Evaluasi');

            $sheet->mergeCells('O5:O7');
            $sheet->setCellValue('O5', 'Catatan Hasil Evaluasi');

            $sheet->mergeCells('P5:P7');
            $sheet->setCellValue('P5', 'Tanggapan Satker (Agar dilengkapi beserta bukti pendukung untuk masing-masing kriteria pada LKE)');

            $sheet->mergeCells('Q5:Q7');
            $sheet->setCellValue('Q5', 'Tanggapan Tim Evaluator');

            // Style header Judul
            $sheet->getStyle('A1')->getFont()->setBold(true);
            $sheet->getStyle('A1:Q1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A1:Q1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $sheet->getStyle('A2')->getFont()->setBold(true);
            $sheet->getStyle('A2:Q2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A2:Q2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $sheet->getStyle('A3')->getFont()->setBold(true);
            $sheet->getStyle('A3:Q3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A3:Q3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            // Style header Tabel Kolom
            $sheet->getStyle('A5:Q7')->getFont()->setBold(true);
            $sheet->getStyle('A5:Q7')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A5:Q7')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A5:O7')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('003d68');
            $sheet->getStyle('P5:Q7')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('5a1313');
            $sheet->getStyle('A5:Q7')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

            // Atur tinggi baris header
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);

            // Atur lebar kolom manual
            $sheet->getColumnDimension('A')->setWidth(3);
            $sheet->getColumnDimension('B')->setWidth(40);
            $sheet->getColumnDimension('C')->setWidth(6);
            $sheet->getColumnDimension('F')->setWidth(30);
            $sheet->getColumnDimension('G')->setWidth(30);
            $sheet->getColumnDimension('H')->setWidth(13);
            $sheet->getColumnDimension('I')->setWidth(8);
            $sheet->getColumnDimension('J')->setWidth(28);
            $sheet->getColumnDimension('K')->setWidth(32);
            $sheet->getColumnDimension('L')->setWidth(32);
            $sheet->getColumnDimension('M')->setWidth(13);
            $sheet->getColumnDimension('N')->setWidth(17);
            $sheet->getColumnDimension('O')->setWidth(31);
            $sheet->getColumnDimension('P')->setWidth(31);
            $sheet->getColumnDimension('Q')->setWidth(31);

            // Style Header wrap text
            $sheet->getStyle('J6:J7')->getAlignment()->setWrapText(true);
            $sheet->getStyle('K6:K7')->getAlignment()->setWrapText(true);
            $sheet->getStyle('L6:L7')->getAlignment()->setWrapText(true);
            $sheet->getStyle('P5:P7')->getAlignment()->setWrapText(true);

            // Isi data ke dalam spreadsheet
            $rowNum = 8; // Baris awal untuk data
            foreach($komponenArray as $i => $row){
                $sheet->setCellValue('A' . $rowNum, $row->kode_komponen);
                $sheet->setCellValue('B' . $rowNum, $row->nama_komponen);
                $sheet->setCellValue('C' . $rowNum, $row->bobot_komponen);
                $sheet->setCellValue('E' . $rowNum, (float)$row->nilai_komponen);

                // Tambahkan Style Warna untuk Komponen

                $sheet->getStyle('A' . $rowNum . ':Q' . $rowNum)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('6caef5');
                $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                $rowNum++;

                foreach($row->detail_subkomponen as $j => $subRow){
                    $startRow = $rowNum +1;
                    $endRow   = $rowNum + $subRow->rowspan;

                    $sheet->setCellValue('A' . $rowNum, $subRow->kode_subkomponen);
                    $sheet->setCellValue('B' . $rowNum, $subRow->nama_subkomponen);
                    $sheet->setCellValue('C' . $rowNum, (float)$subRow->bobot_subkomponen);
                    $sheet->setCellValue('D' . $rowNum, $subRow->nilai_evaluasi);
                    $sheet->setCellValue('E' . $rowNum, (float)$subRow->nilai_instansi_pemerintah);

                    // Merge cell hanya sekali untuk setiap subkomponen
                    $sheet->mergeCells('I' . $startRow . ':I' . $endRow);
                    $sheet->setCellValue('I' . $startRow, (float)$subRow->pemenuhan_kriteria);

                    $sheet->mergeCells('M' . $startRow . ':M' . $endRow);
                    $sheet->setCellValue('M' . $startRow, $subRow->nilai_evaluasi);

                    $sheet->mergeCells('N' . $startRow . ':N' . $endRow);
                    $sheet->setCellValue('N' . $startRow, (float)$subRow->nilai);

                    // Tambahkan Style Warna untuk Subkomponen
                    $sheet->getStyle('A' . $rowNum . ':Q' . $rowNum)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('9dc4ee');

                    $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('A' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                    $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('C' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                    $sheet->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('D' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                    $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('E' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                    $sheet->getStyle('B' . $rowNum)->getAlignment()->setWrapText(true);

                    $sheet->getStyle('I' . $startRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    $sheet->getStyle('I' . $startRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('I' . $startRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                    $sheet->getStyle('M' . $startRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('M' . $startRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                    $sheet->getStyle('N' . $startRow)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    $sheet->getStyle('N' . $startRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('N' . $startRow)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                    $rowNum++;

                    foreach($subRow->detailKriteria as $k => $kriteriaRow){
                        $sheet->setCellValue('A' . $rowNum, $kriteriaRow->kode_kriteria);
                        $sheet->mergeCells('B' . $rowNum . ':E' . $rowNum);
                        $sheet->setCellValue('B' . $rowNum, '' . $kriteriaRow->nama_kriteria);

                        $langkah_kerja                = strip_tags($kriteriaRow->langkah_kerja); // Menghapus tag HTML & PHP
                        $langkah_kerja2               = html_entity_decode($langkah_kerja, ENT_QUOTES | ENT_HTML5); // Ubah entitas HTML menjadi karakter asli
                        $langkah_kerja_bersih         = trim($langkah_kerja2);

                        $daftar_evidence              = strip_tags($kriteriaRow->daftar_evidence); // Menghapus tag HTML & PHP
                        $daftar_evidence2             = html_entity_decode($daftar_evidence, ENT_QUOTES | ENT_HTML5); // Ubah entitas HTML menjadi karakter asli
                        $daftar_evidence_bersih       = trim($daftar_evidence2);

                        $catatan_evaluator            = strip_tags($kriteriaRow->catatan_evaluator); // Menghapus tag HTML & PHP
                        $catatan_evaluator2           = html_entity_decode($catatan_evaluator, ENT_QUOTES | ENT_HTML5); // Ubah entitas HTML menjadi karakter asli
                        $catatan_evaluator_bersih     = trim($catatan_evaluator2);

                        $jawaban_satker               = strip_tags($kriteriaRow->jawaban_satker); // Menghapus tag HTML & PHP
                        $jawaban_satker2              = html_entity_decode($jawaban_satker, ENT_QUOTES | ENT_HTML5); // Ubah entitas HTML menjadi karakter asli
                        $jawaban_satker_bersih        = trim($jawaban_satker2);

                        $tanggapan_evaluator          = strip_tags($kriteriaRow->tanggapan_evaluator); // Menghapus tag HTML & PHP
                        $tanggapan_evaluator2         = html_entity_decode($tanggapan_evaluator, ENT_QUOTES | ENT_HTML5); // Ubah entitas HTML menjadi karakter asli
                        $tanggapan_evaluator_bersih   = trim($tanggapan_evaluator2);

                        $sheet->setCellValue('F' . $rowNum, $langkah_kerja_bersih);
                        $sheet->setCellValue('G' . $rowNum, $daftar_evidence_bersih);
                        $sheet->setCellValue('H' . $rowNum, $kriteriaRow->kriteria_penilaian1);

                        $sheet->setCellValue('J' . $rowNum, $kriteriaRow->kriteria_penilaian2);
                        $sheet->setCellValue('K' . $rowNum, $kriteriaRow->kriteria_penilaian3);
                        $sheet->setCellValue('L' . $rowNum, $kriteriaRow->kriteria_penilaian4);

                        $sheet->setCellValue('O' . $rowNum, $catatan_evaluator_bersih);

                        $sheet->setCellValue('P' . $rowNum, $jawaban_satker_bersih);

                        $sheet->setCellValue('Q' . $rowNum, $tanggapan_evaluator_bersih);

                        // Tambahkan Style untuk Kriteria
                        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('A' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                        $sheet->getStyle('B' . $rowNum . ':E' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                        $sheet->getStyle('B' . $rowNum . ':E' . $rowNum)->getAlignment()->setWrapText(true);

                        $sheet->getStyle('F' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                        $sheet->getStyle('F' . $rowNum)->getAlignment()->setWrapText(true);

                        $sheet->getStyle('G' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                        $sheet->getStyle('G' . $rowNum)->getAlignment()->setWrapText(true);

                        $sheet->getStyle('H' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('H' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                        $sheet->getStyle('J' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('J' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                        $sheet->getStyle('K' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('K' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

                        $sheet->getStyle('L' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('L' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                        
                        $sheet->getStyle('O' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('O' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                        $sheet->getStyle('O' . $rowNum)->getAlignment()->setWrapText(true);

                        $sheet->getStyle('P' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('P' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                        $sheet->getStyle('P' . $rowNum)->getAlignment()->setWrapText(true);

                        $sheet->getStyle('Q' . $rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle('Q' . $rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                        $sheet->getStyle('Q' . $rowNum)->getAlignment()->setWrapText(true);

                        $rowNum++;
                    }
                }
            }

            $lastRow = $rowNum - 1;

            $styleArray = [
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'], // Black color
                                ],
                            ],
                        ];
            
            $sheet->getStyle('A5:Q'. $lastRow)->applyFromArray($styleArray);
            
            // --- SHEET 2: REKAP LKE ---
            $rekapSheet = $spreadsheet->createSheet();
            $rekapSheet->setTitle('Rekap LKE');

            // Header Judul Rekap (Sama seperti sheet 1)
            $rekapSheet->mergeCells('A1:D1');
            $rekapSheet->setCellValue('A1', 'Lembar Kerja Evaluasi Akuntabilitas Kinerja Instansi Pemerintah di');
            $rekapSheet->mergeCells('A2:D2');
            $rekapSheet->setCellValue('A2', 'Kementerian Pekerjaan Umum');
            $rekapSheet->mergeCells('A3:D3');
            $rekapSheet->setCellValue('A3', $nama_satker . ' - Tahun Anggaran ' . $tahun);
            
            $rekapSheet->getStyle('A1:D3')->getFont()->setBold(true);
            $rekapSheet->getStyle('A1:D3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rekapSheet->getStyle('A1:D3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            // Table Headers Rekap
            $rekapSheet->setCellValue('A5', 'No');
            $rekapSheet->setCellValue('B5', 'Nama Komponen');
            $rekapSheet->setCellValue('C5', 'Bobot');
            $rekapSheet->setCellValue('D5', 'Nilai');
            
            $rekapSheet->getStyle('A5:D5')->getFont()->setBold(true);
            $rekapSheet->getStyle('A5:D5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rekapSheet->getStyle('A5:D5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('003d68');
            $rekapSheet->getStyle('A5:D5')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);

            $rowRekap = 6;
            foreach($komponenArray as $idx => $comp) {
                $rekapSheet->setCellValue('A' . $rowRekap, $idx + 1);
                $rekapSheet->setCellValue('B' . $rowRekap, strip_tags($comp->nama_komponen));
                $rekapSheet->setCellValue('C' . $rowRekap, (float)$comp->bobot_komponen);
                $rekapSheet->setCellValue('D' . $rowRekap, (float)$comp->nilai_komponen);
                $rowRekap++;
            }

            $rekapSheet->mergeCells('A10:C10');
            $rekapSheet->setCellValue('A10', 'Nilai Akuntabilitas Kinerja');
            $rekapSheet->setCellValue('D10', (float)$total_hasil_evaluasi);

            $rekapSheet->getStyle('A10:D10')->getFont()->setBold(true);
            $rekapSheet->getStyle('A10:C10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            if($total_hasil_evaluasi >90 && $total_hasil_evaluasi <=100){
                $rekapSheet->setCellValue('D11', 'AA');
            }
            elseif($total_hasil_evaluasi >80 && $total_hasil_evaluasi <=90){
                $rekapSheet->setCellValue('D11', 'A');
            }
            elseif($total_hasil_evaluasi >70 && $total_hasil_evaluasi <=80){
                $rekapSheet->setCellValue('D11', 'BB');
            }
            elseif($total_hasil_evaluasi >60 && $total_hasil_evaluasi <=70){
                $rekapSheet->setCellValue('D11', 'B');
            }
            elseif($total_hasil_evaluasi >50 && $total_hasil_evaluasi <=60){
                $rekapSheet->setCellValue('D11', 'CC');
            }
            elseif($total_hasil_evaluasi >30 && $total_hasil_evaluasi <=50){
                $rekapSheet->setCellValue('D11', 'C');
            }
            elseif($total_hasil_evaluasi >0 && $total_hasil_evaluasi <=30){
                $rekapSheet->setCellValue('D11', 'D');
            }
            else{
                $rekapSheet->setCellValue('D11', 'E');
            }

            $rekapSheet->getStyle('D11')->getFont()->setBold(true);
            $rekapSheet->getStyle('D11')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $rekapSheet->getStyle('D11')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $rekapSheet->getStyle('D11')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            
            $rekapSheet->getStyle('A5:D' . ($rowRekap))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $rekapSheet->getColumnDimension('B')->setAutoSize(true);
            $rekapSheet->getColumnDimension('C')->setAutoSize(true);
            $rekapSheet->getColumnDimension('D')->setAutoSize(true);

            $spreadsheet->setActiveSheetIndex(0); // Kembali fokus ke sheet pertama

            header("Content-Description: File Transfer");
            header('Content-Disposition: attachment; filename="Data_LKE_'. $nama_satker .'_'. date('YmdHis') .'.xlsx"');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Cache-Control: max-age=0');
            header('Pragma: public');
            header('Expires: 0');
            
            $writer = new Xlsx($spreadsheet);
            $writer->save("php://output");
            exit;
        }
    }
}
?>
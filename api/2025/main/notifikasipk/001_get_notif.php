<?php
include '../../library/config.php';
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
//echo $sqlcek;
//die;
$resultsqlcek = mysqli_query($link, $sqlcek);
$numcek = mysqli_num_rows($resultsqlcek);
if ($numcek > 0) {
    if (($Bearer <> '') && ($tempBearer[1] <> '')) {
        $sqlpkaktif = "select id from master_pk where deleted = '0' and is_active = '1'";
        $resultpkaktif  = mysqli_query($link, $sqlpkaktif);
        while ($rowpkaktif  = mysqli_fetch_assoc($resultpkaktif)) {
            $pk_aktif = $rowpkaktif['id'];
        }
        $filterquery = "";
        $filterquery2 = "";
        $kode_pelaksana = fixup($_GET['kode_pelaksana']);
        $kode_balai = fixup($_GET['kode_balai']);
        $level = fixup($_GET['level']);
        $levpiusistem = fixup($_GET['levpiusistem']);
        $jenis_piusistem = fixup($_GET['jenis_piusistem']);

        if ($pk_aktif <> '') {
            //$filterquery = $filterquery . " and a.jenis_pk = '" . $pk_aktif . "'";
            //$filterquery2 = $filterquery2 . " and a.jenis_pk = '" . $pk_aktif . "'";
        }

        if ($kode_pelaksana <> '') {
            $filterquery = $filterquery . " and a.kode_pelaksana = '" . $kode_pelaksana . "'";
        }
        if ($levpiusistem <> '') {
            $filterquery = $filterquery . " and a.kepada = '" . $levpiusistem . "'";
            $filterquery2 = $filterquery2 . " and a.kepada = '" . $levpiusistem . "'";
        }
        if ($kode_balai <> '') {
            $filterquery2 = $filterquery2 . " and a.kode_pelaksana in (select kode_satker from master_satker
            where deleted = '0' and kdbalai = '" . $kode_balai . "')";
        }
        $myArray = array();

        if ($level == '7' || $level == '8' || $level == '4') {
            $sql = "select a.id,b.nama, IF(a.updatedate > a.createddate, a.updatedate, a.createddate) as tanggal
            , a.verifikasi_ke ,'hasil verifikasi' as mode, a.id_pelaksana, a.kode_pelaksana,a.jenis_pk
            , c.nama_satker, c.kdbalai, a.hasil_verif
            from tb_data_pk_verifikasi a 
            left join master_verifikasi b
            on a.hasil_verif = b.id
            left join master_satker c
            on a.kode_pelaksana = c.kode_satker
            where a.deleted='0' and a.readed='0'
            " . $filterquery . "
            group by a.id order by tanggal DESC ";
        } else if ((strstr($level, '6')) || (strstr($level, '5'))) {
            if ($jenis_piusistem == '34') {
                $sql = "select a.id,'Pengajuan' as nama, IF(a.updatedate > a.createddate, a.updatedate, a.createddate) as tanggal
                , a.verifikasi_ke ,'pengajuan verifikasi' as mode, a.id_pelaksana, a.kode_pelaksana,a.jenis_pk
                , a.dari, a.kepada, a.kepada2, a.evaluasi, a.status_ajuan, a.status_verifikasi
                , b.nama_satker, b.kdbalai, a.hasil_verif
                from tb_data_pk_verifikasi a 
                left join master_satker b
                on a.kode_pelaksana = b.kode_satker
                where a.deleted='0' and a.readed='0'
                " . $filterquery . "
                group by a.id order by tanggal DESC ";
            } else {
                $sql = "select a.id,'Pengajuan' as nama, IF(a.updatedate > a.createddate, a.updatedate, a.createddate) as tanggal
                , a.verifikasi_ke ,'pengajuan verifikasi' as mode, a.id_pelaksana, a.kode_pelaksana,a.jenis_pk
                , a.dari, a.kepada, a.kepada2, a.evaluasi, a.status_ajuan, a.status_verifikasi, a.hasil_verif, a.id_pelaksana
                , b.nama_satker, b.kdbalai, a.hasil_verif
                from tb_data_pk_verifikasi a 
                left join master_satker b
                on a.kode_pelaksana = b.kode_satker
                where a.deleted='0' and a.readed='0'
                " . $filterquery2 . "
                group by a.id order by tanggal DESC ";
            }
        } else {
            $sql = "select a.id,'Pengajuan' as nama, IF(a.updatedate > a.createddate, a.updatedate, a.createddate) as tanggal
            , a.verifikasi_ke ,'pengajuan verifikasi' as mode, a.id_pelaksana, a.kode_pelaksana,a.jenis_pk
            , a.dari, a.kepada, a.kepada2, a.evaluasi, a.status_ajuan, a.status_verifikasi
            , b.nama_satker, b.kdbalai, a.hasil_verif
            from tb_data_pk_verifikasi a 
            left join master_satker b
            on a.kode_pelaksana = b.kode_satker
            where a.deleted='0' and a.readed='0'
            and a.kepada = '" . $jenis_piusistem . "'
            group by a.id order by tanggal DESC ";
        }


        //echo $sql;
        //die();
        $result = mysqli_query($link, $sql);
        $num = mysqli_num_rows($result);
        if ($num > 0) {
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    if ($row['nama'] == 'Pengajuan') {
                        if (($row['id_pelaksana'] == '32') || ($row['id_pelaksana'] == '45')) {
                            $sqlbalai = "select nama_kategori from master_kategori_satker where deleted = '0' and
                            kdbalai = '" . $row['kode_pelaksana'] . "'";
                            $resultbalai = mysqli_query($link, $sqlbalai);
                            while ($rowbalai = mysqli_fetch_assoc($resultbalai)) {
                                $namabalai = $rowbalai['nama_kategori'];
                            }
                            $textawal = "";
                            if ($row['jenis_pk'] == '1') {
                                $textawal = "[PK Awal] ";
                            } else if ($row['jenis_pk'] == '2') {
                                $textawal = "[PK Revisi] ";
                            } else {
                                $textawal = "[PK Akhir] ";
                            }
                            if ($row['hasil_verif'] == '2') {
                                $text = 'Mohon Perbaikan Verifikasi ke ' . $row['verifikasi_ke'] . ' PK ' . $namabalai;
                            } else if ($row['hasil_verif'] == '1') {
                                $text = 'Verifikasi ke ' . $row['verifikasi_ke'] . ' PK ' . $namabalai . ' Diterima';
                            } else {
                                $text = 'Pengajuan Verifikasi ke ' . $row['verifikasi_ke'] . ' PK ' . $namabalai;
                            }
                        } else {
                            $sqlbalai = "select nama_satker from master_satker where deleted = '0' and
                            kode_satker = '" . $row['kode_pelaksana'] . "'";
                            $resultbalai = mysqli_query($link, $sqlbalai);
                            while ($rowbalai = mysqli_fetch_assoc($resultbalai)) {
                                $namabalai = $rowbalai['nama_satker'];
                            }
                            $textawal = "";
                            if ($row['jenis_pk'] == '1') {
                                $textawal = "[PK Awal] ";
                            } else if ($row['jenis_pk'] == '2') {
                                $textawal = "[PK Revisi] ";
                            } else {
                                $textawal = "[PK Akhir] ";
                            }
                            if ($row['hasil_verif'] == '2') {
                                $text = 'Mohon Perbaikan Verifikasi ke ' . $row['verifikasi_ke'] . ' PK ' . $namabalai;
                            } else if ($row['hasil_verif'] == '1') {
                                $text = 'Verifikasi ke ' . $row['verifikasi_ke'] . ' PK ' . $namabalai . ' Diterima';
                            } else {
                                $text = 'Pengajuan Verifikasi ke ' . $row['verifikasi_ke'] . ' PK ' . $namabalai;
                            }
                        }
                    } else if ($row['nama'] == 'Perbaikan') {
                        $textawal = "";
                        if ($row['jenis_pk'] == '1') {
                            $textawal = "[PK Awal] ";
                        } else if ($row['jenis_pk'] == '2') {
                            $textawal = "[PK Revisi] ";
                        } else {
                            $textawal = "[PK Akhir] ";
                        }
                        $text = 'Mohon Perbaikan Verifikasi ke ' . $row['verifikasi_ke'] . ' PK ' . $row['nama_satker'];
                    } else {
                        $textawal = "";
                        if ($row['jenis_pk'] == '1') {
                            $textawal = "[PK Awal] ";
                        } else if ($row['jenis_pk'] == '2') {
                            $textawal = "[PK Revisi] ";
                        } else {
                            $textawal = "[PK Akhir] ";
                        }
                        $text = 'Verifikasi ke ' . $row['verifikasi_ke'] . ' PK ' . $row['nama_satker'] . ' Diterima';
                    }

                    $myArray[] = (object)
                    [
                        'id' => (float) $row['id'],
                        'nama' => $text,
                        'tanggal' => $row['tanggal'],
                        'mode' => $row['mode'],
                        'id_pelaksana' => $row['id_pelaksana'],
                        'kode_pelaksana' => $row['kode_pelaksana'],
                        'nama_satker' => $row['nama_satker'],
                        'kdbalai' => $row['kdbalai'],
                        'jenis_pk' => $row['jenis_pk'],
                        'textawal' => $textawal,
                    ];
                }
                $response         = [];
                $response['jumlah'] =  $num;
                $response['data'] =  $myArray;
                mysqli_close($link);
                echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
                die;
            }
        } else {
            $filterquery1 = "";
            $filterquery21 = "";
            $levpiusistem = fixup($_GET['levpiusistem']);
            $jenis_piusistem = fixup($_GET['jenis_piusistem']);

            if ($pk_aktif <> '') {
                //$filterquery1 = $filterquery1 . " and a.jenis_pk = '" . $pk_aktif . "'";
                //$filterquery21 = $filterquery21 . " and a.jenis_pk = '" . $pk_aktif . "'";
            }

            if ($jenis_piusistem <> '') {
                $filterquery1 = $filterquery1 . " and a.kepada = '" . $jenis_piusistem . "'";
                $filterquery21 = $filterquery21 . " and a.kepada = '" . $jenis_piusistem . "'";
                $filterquery1 = $filterquery1 . " and a.kode_pelaksana = '" . $kode_pelaksana . "'";
            }
            if ($level == '7' || $level == '8') {
                $sql1 = "select a.id,b.nama, IF(a.updatedate > a.createddate, a.updatedate, a.createddate) as tanggal
                , a.verifikasi_ke ,'hasil verifikasi' as mode, a.id_pelaksana, a.kode_pelaksana,a.jenis_pk
                , c.nama_satker, c.kdbalai, a.hasil_verif
                from tb_data_pk_verifikasi a 
                left join master_verifikasi b
                on a.hasil_verif = b.id
                left join master_satker c
                on a.kode_pelaksana = c.kode_satker
                where a.deleted='0' and a.readed='0'
                " . $filterquery1 . "
                group by a.id order by tanggal DESC ";
            } else if ((strstr($level, '6')) || (strstr($level, '5'))) {
                if (($jenis_piusistem == '34') || ($jenis_piusistem == '35') || ($jenis_piusistem == '37') || ($jenis_piusistem == '38') || ($jenis_piusistem == '42') || ($jenis_piusistem == '43') || ($jenis_piusistem == '44')) {
                    $sql1 = "select a.id,'Pengajuan' as nama, IF(a.updatedate > a.createddate, a.updatedate, a.createddate) as tanggal
                    , a.verifikasi_ke ,'pengajuan verifikasi' as mode, a.id_pelaksana, a.kode_pelaksana,a.jenis_pk
                    , a.dari, a.kepada, a.kepada2, a.evaluasi, a.status_ajuan, a.status_verifikasi
                    , b.nama_satker, b.kdbalai, a.hasil_verif
                    from tb_data_pk_verifikasi a 
                    left join master_satker b
                    on a.kode_pelaksana = b.kode_satker
                    where a.deleted='0' and a.readed='0'
                    " . $filterquery21 . "
                    group by a.id order by tanggal DESC ";
                } else {
                    $sql1 = "select a.id,'Pengajuan' as nama, IF(a.updatedate > a.createddate, a.updatedate, a.createddate) as tanggal
                    , a.verifikasi_ke ,'pengajuan verifikasi' as mode, a.id_pelaksana, a.kode_pelaksana,a.jenis_pk
                    , a.dari, a.kepada, a.kepada2, a.evaluasi, a.status_ajuan, a.status_verifikasi
                    , b.nama_satker, b.kdbalai, a.hasil_verif
                    from tb_data_pk_verifikasi a 
                    left join master_satker b
                    on a.kode_pelaksana = b.kode_satker
                    where a.deleted='0' and a.readed='0'
                    " . $filterquery21 . "
                    " . $filterquery2 . "
                    group by a.id order by tanggal DESC ";
                }
            }
            //echo $sql1;
            //die;
            $result1 = mysqli_query($link, $sql1);
            $num1 = mysqli_num_rows($result1);
            if ($num1 > 0) {
                if ($result1) {
                    while ($row1 = mysqli_fetch_assoc($result1)) {
                        if ($row1['nama'] == 'Pengajuan') {
                            if (($row1['dari'] == '32') || ($row1['dari'] == '45')) {
                                $sqlbalai = "select nama_kategori from master_kategori_satker where deleted = '0' and
                                kdbalai = '" . $row1['kode_pelaksana'] . "'";
                                $resultbalai = mysqli_query($link, $sqlbalai);
                                while ($rowbalai = mysqli_fetch_assoc($resultbalai)) {
                                    $namabalai = $rowbalai['nama_kategori'];
                                }
                                $textawal = "";
                                if ($row1['jenis_pk'] == '1') {
                                    $textawal = "[PK Awal] ";
                                } else if ($row1['jenis_pk'] == '2') {
                                    $textawal = "[PK Revisi] ";
                                } else {
                                    $textawal = "[PK Akhir] ";
                                }
                                $text = 'Pengajuan Verifikasi ke ' . $row1['verifikasi_ke'] . ' PK ' . $namabalai;
                            } else if ($row1['hasil_verif'] == '2') {
                                $text = 'Mohon Perbaikan Verifikasi ke ' . $row1['verifikasi_ke'] . ' PK ' . $row1['nama_satker'];
                            } else {
                                $text = 'Pengajuan Verifikasi ke ' . $row1['verifikasi_ke'] . ' PK ' . $row1['nama_satker'];
                            }
                        } else if ($row1['nama'] == 'Perbaikan') {
                            $textawal = "";
                            if ($row1['jenis_pk'] == '1') {
                                $textawal = "[PK Awal] ";
                            } else if ($row1['jenis_pk'] == '2') {
                                $textawal = "[PK Revisi] ";
                            } else {
                                $textawal = "[PK Akhir] ";
                            }
                            $text = 'Mohon Perbaikan Verifikasi ke ' . $row1['verifikasi_ke'] . ' PK ' . $row1['nama_satker'];
                        } else if ($row1['hasil_verif'] == '2') {
                            $textawal = "";
                            if ($row1['jenis_pk'] == '1') {
                                $textawal = "[PK Awal] ";
                            } else if ($row1['jenis_pk'] == '2') {
                                $textawal = "[PK Revisi] ";
                            } else {
                                $textawal = "[PK Akhir] ";
                            }
                            $text = 'Mohon Perbaikan Verifikasi ke ' . $row1['verifikasi_ke'] . ' PK ' . $row1['nama_satker'];
                        } else {
                            $textawal = "";
                            if ($row1['jenis_pk'] == '1') {
                                $textawal = "[PK Awal] ";
                            } else if ($row1['jenis_pk'] == '2') {
                                $textawal = "[PK Revisi] ";
                            } else {
                                $textawal = "[PK Akhir] ";
                            }
                            $text = 'Verifikasi ke ' . $row1['verifikasi_ke'] . ' PK ' . $row1['nama_satker'] . ' Diterima';
                        }

                        $myArray[] = (object)
                        [
                            'id' => (float) $row1['id'],
                            'nama' => $text,
                            'tanggal' => $row1['tanggal'],
                            'mode' => $row1['mode'],
                            'id_pelaksana' => $row1['id_pelaksana'],
                            'kode_pelaksana' => $row1['kode_pelaksana'],
                            'nama_satker' => $row1['nama_satker'],
                            'kdbalai' => $row1['kdbalai'],
                            'jenis_pk' => $row1['jenis_pk'],
                            'textawal' => $textawal,
                        ];
                    }
                    $response         = [];
                    $response['jumlah'] =  $num1;
                    $response['data'] =  $myArray;
                    mysqli_close($link);
                    echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
                    die;
                }
            } else {
                mysqli_close($link);
                echo json_encode(array('response' => 'success', 'message' => 'data kosong'), JSON_PRETTY_PRINT);
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

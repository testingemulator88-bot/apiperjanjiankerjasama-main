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
        $filterquery = "";
        $filterquerycantek = "";
        $filterquerypk = "";
        $kodebalai = fixup($_GET['kodebalai']);
        $pk_aktif = fixup($_GET['pk_aktif']);
        if ($kodebalai <> '') {
            $filterquery = $filterquery . " and a.id = '" . $kodebalai . "'";
        }
        if ($pk_aktif <> '') {
            $filterquerypk = $filterquerypk . " and a.jenis_pk = '" . $pk_aktif . "'";
        }
        $cantek = fixup($_GET['cantek']);
        $myArray = array();
        if ($cantek <> '') {
            $sql = "select a.id,b.kdbalai as kd_unor,b.nama_kategori as nama_unor
            ,a.kdunor,a.kdbalai,a.kdbalai_pendek,a.kdbalai_old,a.kdbalai_old_pendek,a.nama_kategori as nama,a.alamat
            ,a.latitude,a.longitude,a.urut,a.level_piu from master_kategori_satker a 
            left join master_unor b
            on a.kdunor = b.id
            where a.deleted='0' 
            and a.id in (select kdbalai from master_satker where deleted='0' and aktif= '0' 
            and level_piu in (select id from master_level_piu where deleted = '0'
            and (verif in (" . $cantek . ") or verif2 in (" . $cantek . ") or verif3 in (" . $cantek . "))))
            order by a.urut ";
        } else {
            $sql = "select a.id,b.kdbalai as kd_unor,b.nama_kategori as nama_unor
            ,a.kdunor,a.kdbalai,a.kdbalai_pendek,a.kdbalai_old,a.kdbalai_old_pendek,a.nama_kategori as nama,a.alamat
            ,a.latitude,a.longitude,a.urut,a.level_piu from master_kategori_satker a 
            left join master_unor b
            on a.kdunor = b.id
            where a.deleted='0' 
            " . $filterquery . "
            order by a.urut ";
        }

        //echo $sql;
        //die();
        $result = mysqli_query($link, $sql);
        $num = mysqli_num_rows($result);
        if ($num > 0) {
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $sql_cek_master_verif = "select verif, verif2, verif3 from master_level_piu where deleted='0'
                    and id = '" . $row['level_piu'] . "'";
                    //echo $sql_cek_master_verif."<br>";
                    //die;
                    $resultcek_master_verif = mysqli_query($link, $sql_cek_master_verif);
                    while ($rowcek_master_verif = mysqli_fetch_assoc($resultcek_master_verif)) {
                        $verif1balai = '';
                        $revisi1balai = '';
                        $acc1balai = '';
                        $sqlcekverifikasi = "select a.dari,a.kepada,a.hasil_verif,a.createddate from tb_data_pk_verifikasi a
                        where a.kode_pelaksana = '" . $row['kdbalai'] . "' and a.deleted='0'
                        and a.kepada = '" . $row['level_piu'] . "'
                        and a.dari = '" . $rowcek_master_verif['verif'] . "' and a.id_pelaksana in ('32','45')
                        " . $filterquerypk . "
                        and a.verifikasi_ke='1' order by a.id DESC limit 1";
                        //echo $sqlcekverifikasi."<br>";

                        $resultcekverifikasi = mysqli_query($link, $sqlcekverifikasi);
                        $numcekverifikasi = mysqli_num_rows($resultcekverifikasi);
                        if ($numcekverifikasi > 0) {
                            while ($rowcekverifikasi = mysqli_fetch_assoc($resultcekverifikasi)) {
                                if ((float) $rowcekverifikasi['hasil_verif'] == 0) {
                                    $verif1balai = 'fas fa-check-circle text-success';
                                    $revisi1balai = 'fas fa-times-circle text-danger';
                                    $acc1balai = 'fas fa-times-circle text-danger';
                                    $tanggalprosesbalai = null;
                                } else if ((float) $rowcekverifikasi['hasil_verif'] == 2) {
                                    $verif1balai = 'fas fa-check-circle text-success';
                                    $revisi1balai = 'fas fa-check-circle text-success';
                                    $acc1balai = 'fas fa-times-circle text-danger';
                                    $tanggalprosesbalai = null;
                                } else {
                                    $verif1balai = 'fas fa-check-circle text-success';
                                    $revisi1balai = 'fas fa-check-circle text-success';
                                    $acc1balai = 'fas fa-check-circle text-success';
                                    $tanggalprosesbalai = $rowcekverifikasi['createddate'];
                                }
                            }
                        } else {
                            $sqlcekverifikasi = "select a.dari,a.kepada,a.hasil_verif,a.createddate from tb_data_pk_verifikasi a
                            where a.kode_pelaksana = '" . $row['kdbalai'] . "' and a.deleted='0'
                            and a.kepada = '" . $rowcek_master_verif['verif'] . "' 
                            and a.dari = '" . $row['level_piu'] . "'  and a.id_pelaksana in ('32','45')
                            " . $filterquerypk . "
                            and a.verifikasi_ke='1' order by a.id DESC limit 1";
                            //echo $sqlcekverifikasi."<br>";
                            $resultcekverifikasi = mysqli_query($link, $sqlcekverifikasi);
                            $numcekverifikasi = mysqli_num_rows($resultcekverifikasi);
                            if ($numcekverifikasi > 0) {
                                $verif1balai = 'fas fa-check-circle text-success';
                                $revisi1balai = 'fas fa-times-circle text-danger';
                                $acc1balai = 'fas fa-times-circle text-danger';
                                $tanggalprosesbalai = null;
                            } else {
                                $verif1balai = 'fas fa-times-circle text-danger';
                                $revisi1balai = 'fas fa-times-circle text-danger';
                                $acc1balai = 'fas fa-times-circle text-danger';
                                $tanggalprosesbalai = null;
                            }
                        }
                    }
                    $myArraydetail = array();
                    if ($cantek <> '') {
                        $sqlsatker = "select a.id,c.id as idunor,c.kdbalai as kdunor,c.nama_kategori as namaunor,a.kdbalai as idbalai
                        ,b.kdbalai as kdbalai
                        ,b.nama_kategori as nama_kategori_satker,a.kode_satker,a.kode_satker_pendek
                        ,a.kode_satker_old,a.kode_satker_old_pendek,a.nama_satker
                        ,a.status_piu, d.nama as namastatus_piu,a.level_piu, e.nama as namalevel_piu,a.urut,b.urut as urutbalai
                        from master_satker a 
                        left join master_unor c on
                        a.kdunor=c.id
                        left join master_kategori_satker b on
                        a.kdbalai=b.id
                        left join master_status_piu d on
                        a.status_piu=d.id
                        left join master_level_piu e on
                        a.level_piu=e.id
                        where a.deleted='0' and a.aktif= '0' 
                        and a.kdbalai = '" . $row['id'] . "'
                        and a.level_piu in (select id from master_level_piu where deleted = '0'
                        and (verif in (" . $cantek . ") or verif2 in (" . $cantek . ") or verif3 in (" . $cantek . ")))
                        order by b.urut ,a.urut, a.kode_satker";
                    } else {
                        $sqlsatker = "select a.id,c.id as idunor,c.kdbalai as kdunor,c.nama_kategori as namaunor,a.kdbalai as idbalai
                        ,b.kdbalai as kdbalai
                        ,b.nama_kategori as nama_kategori_satker,a.kode_satker,a.kode_satker_pendek
                        ,a.kode_satker_old,a.kode_satker_old_pendek,a.nama_satker
                        ,a.status_piu, d.nama as namastatus_piu,a.level_piu, e.nama as namalevel_piu,a.urut,b.urut as urutbalai
                        from master_satker a 
                        left join master_unor c on
                        a.kdunor=c.id
                        left join master_kategori_satker b on
                        a.kdbalai=b.id
                        left join master_status_piu d on
                        a.status_piu=d.id
                        left join master_level_piu e on
                        a.level_piu=e.id
                        where a.deleted='0' and a.aktif= '0' 
                        and a.kdbalai = '" . $row['id'] . "' and a.kode_satker <> '03'
                        order by b.urut ,a.urut, a.kode_satker";
                    }

                    //echo $sqlsatker."<br>";
                    $verif2 = 'fas fa-times-circle text-danger';
                    $revisi2 = 'fas fa-times-circle text-danger';
                    $acc2 = 'fas fa-times-circle text-danger';
                    $resultsatker = mysqli_query($link, $sqlsatker);
                    while ($rowsatker = mysqli_fetch_assoc($resultsatker)) {
                        $sql_cek_master_verif = "select verif, verif2, verif3 from master_level_piu where deleted='0'
                        and id = '" . $rowsatker['level_piu'] . "'";
                        $resultcek_master_verif = mysqli_query($link, $sql_cek_master_verif);
                        while ($rowcek_master_verif = mysqli_fetch_assoc($resultcek_master_verif)) {
                            $verif1 = '';
                            $revisi1 = '';
                            $acc1 = '';
                            $verif2 = '';
                            $revisi2 = '';
                            $acc2 = '';
                            $tanggalproses = '';
                            if (($rowcek_master_verif['verif2'] == '') && ($rowcek_master_verif['verif3'] == '')) {
                                //verifikasi 1x
                                $sqlcekverifikasi = "select a.dari,a.kepada,a.hasil_verif,a.createddate from tb_data_pk_verifikasi a
                                where a.kode_pelaksana = '" . $rowsatker['kode_satker'] . "' and a.deleted='0'
                                and a.kepada = '" . $rowsatker['level_piu'] . "'
                                and a.dari = '" . $rowcek_master_verif['verif'] . "'
                                " . $filterquerypk . "
                                and a.verifikasi_ke='1' order by a.id DESC limit 1";
                                $resultcekverifikasi = mysqli_query($link, $sqlcekverifikasi);
                                $numcekverifikasi = mysqli_num_rows($resultcekverifikasi);
                                if ($numcekverifikasi > 0) {
                                    while ($rowcekverifikasi = mysqli_fetch_assoc($resultcekverifikasi)) {
                                        if ((float) $rowcekverifikasi['hasil_verif'] == 0) {
                                            $verif1 = 'fas fa-check-circle text-success';
                                            $revisi1 = 'fas fa-times-circle text-danger';
                                            $acc1 = 'fas fa-times-circle text-danger';
                                            $tanggalproses = null;
                                        } else if ((float) $rowcekverifikasi['hasil_verif'] == 2) {
                                            $verif1 = 'fas fa-check-circle text-success';
                                            $revisi1 = 'fas fa-check-circle text-success';
                                            $acc1 = 'fas fa-times-circle text-danger';
                                            $tanggalproses = null;
                                        } else {
                                            $verif1 = 'fas fa-check-circle text-success';
                                            $revisi1 = 'fas fa-check-circle text-success';
                                            $acc1 = 'fas fa-check-circle text-success';
                                            $tanggalproses = $rowcekverifikasi['createddate'];
                                        }
                                    }
                                } else {
                                    $sqlcekverifikasi = "select a.dari,a.kepada,a.hasil_verif,a.createddate from tb_data_pk_verifikasi a
                                    where a.kode_pelaksana = '" . $rowsatker['kode_satker'] . "' and a.deleted='0'
                                    and a.kepada = '" . $rowcek_master_verif['verif'] . "'
                                    and a.dari = '" . $rowsatker['level_piu'] . "'
                                    " . $filterquerypk . "
                                    and a.verifikasi_ke='1' order by a.id DESC limit 1";
                                    $resultcekverifikasi = mysqli_query($link, $sqlcekverifikasi);
                                    $numcekverifikasi = mysqli_num_rows($resultcekverifikasi);
                                    if ($numcekverifikasi > 0) {
                                        while ($rowcekverifikasi = mysqli_fetch_assoc($resultcekverifikasi)) {
                                            $verif1 = 'fas fa-check-circle text-success';
                                            $revisi1 = 'fas fa-times-circle text-danger';
                                            $acc1 = 'fas fa-times-circle text-danger';
                                            $tanggalproses = null;
                                        }
                                    } else {
                                        $verif1 = 'fas fa-times-circle text-danger';
                                        $revisi1 = 'fas fa-times-circle text-danger';
                                        $acc1 = 'fas fa-times-circle text-danger';
                                        $tanggalproses = null;
                                    }
                                }
                                //verifikasi 1x
                            } else {
                                //verifikasi 2x
                                // verif 1
                                $sqlcekverifikasi = "select a.dari,a.kepada,a.hasil_verif,a.createddate from tb_data_pk_verifikasi a
                                where a.kode_pelaksana = '" . $rowsatker['kode_satker'] . "' and a.deleted='0'
                                and a.kepada = '" . $rowsatker['level_piu'] . "'
                                and a.dari in (" . $rowcek_master_verif['verif'] . ") and a.verifikasi_ke='1'
                                " . $filterquerypk . "
                                order by a.id DESC limit 1";
                                //echo $sqlcekverifikasi;
                                $resultcekverifikasi = mysqli_query($link, $sqlcekverifikasi);
                                $numcekverifikasi = mysqli_num_rows($resultcekverifikasi);
                                if ($numcekverifikasi > 0) {
                                    while ($rowcekverifikasi = mysqli_fetch_assoc($resultcekverifikasi)) {
                                        if ((float) $rowcekverifikasi['hasil_verif'] == 0) {
                                            $verif1 = 'fas fa-check-circle text-success';
                                            $revisi1 = 'fas fa-times-circle text-danger';
                                            $acc1 = 'fas fa-times-circle text-danger';
                                            $tanggalproses = null;
                                        } else if ((float) $rowcekverifikasi['hasil_verif'] == 2) {
                                            $verif1 = 'fas fa-check-circle text-success';
                                            $revisi1 = 'fas fa-check-circle text-success';
                                            $acc1 = 'fas fa-times-circle text-danger';
                                            $tanggalproses = null;
                                        } else {
                                            $verif1 = 'fas fa-check-circle text-success';
                                            $revisi1 = 'fas fa-check-circle text-success';
                                            $acc1 = 'fas fa-check-circle text-success';
                                            $tanggalproses = null;
                                        }
                                    }
                                } else {
                                    $sqlcekverifikasi = "select a.dari,a.kepada,a.hasil_verif,a.createddate from tb_data_pk_verifikasi a
                                    where a.kode_pelaksana = '" . $rowsatker['kode_satker'] . "' and a.deleted='0'
                                    and a.kepada in (" . $rowcek_master_verif['verif'] . ")
                                    and a.dari = '" . $rowsatker['level_piu'] . "'
                                    " . $filterquerypk . "
                                    and a.verifikasi_ke='1' order by a.id DESC limit 1";
                                    $resultcekverifikasi = mysqli_query($link, $sqlcekverifikasi);
                                    $numcekverifikasi = mysqli_num_rows($resultcekverifikasi);
                                    if ($numcekverifikasi > 0) {
                                        while ($rowcekverifikasi = mysqli_fetch_assoc($resultcekverifikasi)) {
                                            $verif1 = 'fas fa-check-circle text-success';
                                            $revisi1 = 'fas fa-times-circle text-danger';
                                            $acc1 = 'fas fa-times-circle text-danger';
                                            $tanggalproses = null;
                                        }
                                    } else {
                                        $verif1 = 'fas fa-times-circle text-danger';
                                        $revisi1 = 'fas fa-times-circle text-danger';
                                        $acc1 = 'fas fa-times-circle text-danger';
                                        $tanggalproses = null;
                                    }
                                }
                                // verif 1
                                // verif 2
                                if ($rowcek_master_verif['verif3'] == '') {
                                    $sqlcekverifikasi = "select a.dari,a.kepada,a.hasil_verif,a.createddate from tb_data_pk_verifikasi a
                                    where a.kode_pelaksana = '" . $rowsatker['kode_satker'] . "' and a.deleted='0'
                                    and a.kepada = '" . $rowsatker['level_piu'] . "'
                                    and a.dari = '" . $rowcek_master_verif['verif2'] . "' and a.verifikasi_ke='2'
                                    " . $filterquerypk . "
                                    order by a.id DESC limit 1";
                                } else {
                                    $sqlcekverifikasi = "select a.dari,a.kepada,a.hasil_verif,a.createddate from tb_data_pk_verifikasi a
                                    where a.kode_pelaksana = '" . $rowsatker['kode_satker'] . "' and a.deleted='0'
                                    and a.kepada = '" . $rowsatker['level_piu'] . "'
                                    " . $filterquerypk . "
                                    and a.dari = '" . $rowcek_master_verif['verif3'] . "' and a.verifikasi_ke='2'
                                    order by a.id DESC limit 1";
                                }
                                

                                $resultcekverifikasi = mysqli_query($link, $sqlcekverifikasi);
                                $numcekverifikasi = mysqli_num_rows($resultcekverifikasi);
                                if ($numcekverifikasi > 0) {
                                    while ($rowcekverifikasi = mysqli_fetch_assoc($resultcekverifikasi)) {
                                        if ((float) $rowcekverifikasi['hasil_verif'] == 0) {
                                            $verif2 = 'fas fa-check-circle text-success';
                                            $revisi2 = 'fas fa-times-circle text-danger';
                                            $acc2 = 'fas fa-times-circle text-danger';
                                            $tanggalproses = null;
                                        } else if ((float) $rowcekverifikasi['hasil_verif'] == 2) {
                                            $verif2 = 'fas fa-check-circle text-success';
                                            $revisi2 = 'fas fa-check-circle text-success';
                                            $acc2 = 'fas fa-times-circle text-danger';
                                            $tanggalproses = null;
                                        } else {
                                            $verif2 = 'fas fa-check-circle text-success';
                                            $revisi2 = 'fas fa-check-circle text-success';
                                            $acc2 = 'fas fa-check-circle text-success';
                                            $tanggalproses = $rowcekverifikasi['createddate'];
                                        }
                                    }
                                } else {
                                    if ($rowcek_master_verif['verif3'] == '') {
                                        $sqlcekverifikasi = "select a.dari,a.kepada,a.hasil_verif,a.createddate from tb_data_pk_verifikasi a
                                        where a.kode_pelaksana = '" . $rowsatker['kode_satker'] . "' and a.deleted='0'
                                        and a.kepada = '" . $rowcek_master_verif['verif2'] . "'
                                        and a.dari = '" . $rowsatker['level_piu'] . "'
                                        " . $filterquerypk . "
                                        and a.verifikasi_ke='2' order by a.id DESC limit 1";
                                    } else {
                                        $sqlcekverifikasi = "select a.dari,a.kepada,a.hasil_verif,a.createddate from tb_data_pk_verifikasi a
                                        where a.kode_pelaksana = '" . $rowsatker['kode_satker'] . "' and a.deleted='0'
                                        and a.kepada in ('" . $rowcek_master_verif['verif2'] . "','" . $rowcek_master_verif['verif3'] . "')
                                        and a.dari = '" . $rowsatker['level_piu'] . "'
                                        " . $filterquerypk . "
                                        and a.verifikasi_ke='2' order by a.id DESC limit 1";
                                    }

                                    $resultcekverifikasi = mysqli_query($link, $sqlcekverifikasi);
                                    $numcekverifikasi = mysqli_num_rows($resultcekverifikasi);
                                    if ($numcekverifikasi > 0) {
                                        while ($rowcekverifikasi = mysqli_fetch_assoc($resultcekverifikasi)) {
                                            $verif2 = 'fas fa-check-circle text-success';
                                            $revisi2 = 'fas fa-times-circle text-danger';
                                            $acc2 = 'fas fa-times-circle text-danger';
                                            $tanggalproses = null;
                                        }
                                    } else {
                                        $verif2 = 'fas fa-times-circle text-danger';
                                        $revisi2 = 'fas fa-times-circle text-danger';
                                        $acc2 = 'fas fa-times-circle text-danger';
                                        $tanggalproses = null;
                                    }
                                }
                                // verif 2

                                //verifikasi 2x
                            }
                        }
                        $myArraydetail[] = (object)
                        [
                            'id' => (float) $rowsatker['id'],
                            'kode_satker' => $rowsatker['kode_satker'],
                            'nama' => $rowsatker['nama_satker'],
                            'level_piu' => $rowsatker['level_piu'],
                            'verif1' => $verif1,
                            'revisi1' => $revisi1,
                            'acc1' => $acc1,
                            'verif2' => $verif2,
                            'revisi2' => $revisi2,
                            'acc2' => $acc2,
                            'tanggal' => $tanggalproses,
                        ];
                    }
                    $sql_cek_master_verif = "select verif, verif2, verif3 from master_level_piu where deleted='0'
                    and id = '" . $rowsatker['level_piu'] . "'";
                    $resultcek_master_verif = mysqli_query($link, $sql_cek_master_verif);
                    while ($rowcek_master_verif = mysqli_fetch_assoc($resultcek_master_verif)) {
                        if (($rowcek_master_verif['verif2'] == '') && ($rowcek_master_verif['verif3'] == '')) {
                            $verif1 = 'fas fa-check-circle text-success';
                            $verif2 = 'fas fa-times-circle text-danger';
                            $verif3 = 'fas fa-times-circle text-danger';
                        } else {
                            $verif1 = 'fas fa-check-circle text-success';
                            $verif2 = 'fas fa-check-circle text-success';
                            $verif3 = 'fas fa-check-circle text-success';
                        }
                    }
                    $myArray[] = (object)
                    [
                        'id' => (float) $row['id'],
                        'kdbalai' => $row['kdbalai'],
                        'nama' => $row['nama'],
                        'level_piu' => $row['level_piu'],
                        'verif1' => $verif1balai,
                        'revisi1' => $revisi1balai,
                        'acc1' => $acc1balai,
                        'tanggal' => $tanggalprosesbalai,
                        'detail' => $myArraydetail,
                    ];
                }
                $response         = [];
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

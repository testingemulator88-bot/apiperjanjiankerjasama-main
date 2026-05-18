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
    $filterquery = "";
    $balai = fixup($_GET['balai']);
    $kode_satker = fixup($_GET['kode_satker']);
    $kode_satkeremon = "";
    $kode_satkeremon2 = "";
    $sqlsatkeremon = "select kode_satker_old_pendek,kode_satker_pendek 
        from master_satker where kode_satker = '" . $kode_satker . "'";
    //echo $sqlsatkeremon;
    //die;
    $resultsatkeremon = mysqli_query($link, $sqlsatkeremon);
    while ($rowsatkeremon = mysqli_fetch_assoc($resultsatkeremon)) {
        $kode_satkeremon = $rowsatkeremon['kode_satker_old_pendek'];
        $kode_satkeremon2 = $rowsatkeremon['kode_satker_pendek'];
    }
    if ($kode_satkeremon <> '') {
        $filterquery = $filterquery . " and (a.kdsatker = '" . $kode_satkeremon . "' or a.kdsatker = '" . $kode_satkeremon2 . "')";
    }
    if (($kode_satkeremon == '') && ($balai == '')) {
        $filterquery = $filterquery . " and (a.kdsatker = '----')";
    }

    if ($balai <> '') {
        $filterquery = $filterquery . "  and a.kdsatker in (select kode_satker_pendek from master_satker where kdbalai='" . $balai . "')";
    }


    $tahun = fixup($_GET['tahun']);
    $filterquery = $filterquery . " and a.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "') and YEAR(a.tanggaldata) ='" . $tahun . "'";

    $kdgiat = fixup($_GET['kdgiat']);
    if ($kdgiat <> '') {
        $filterquery = $filterquery . " and a.kdgiat = '" . $kdgiat . "'";
    }

    $kdoutput = fixup($_GET['kdoutput']);
    if ($kdoutput <> '') {
        $filterquery = $filterquery . " and a.kdoutput = '" . $kdoutput . "'";
    }

    $kdsoutput = fixup($_GET['kdsoutput']);
    if ($kdsoutput <> '') {
        $filterquery = $filterquery . " and a.kdsoutput = '" . $kdsoutput . "'";
    }

    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($tahun <> '')) {
        $myArray = array();
        $myArraytotal = array();
        $tanggalemon = date("Y-m-d H:i:s");
        $sqltanggalemon = "select MAX(nama) as tanggalemon from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "'";
        //echo $sqltanggalemon;
        //die;
        $resulttanggalemon = mysqli_query($link, $sqltanggalemon);
        while ($rowtanggalemon = mysqli_fetch_assoc($resulttanggalemon)) {
            $tanggalemon = $rowtanggalemon['tanggalemon'];
        }

        $sqltotal = "select 
        sum(a.pagu) as pagu,sum(a.realisasi) as realisasi
        ,sum(a.ufis) as ufis,sum(a.pfis) as pfis,sum(a.pagu_efisiensi) as pagu_efisiensi
        ,sum(a.blokir) as blokir
        from tb_master_data_paket a 
        where a.kdgiat<>'' 
        " . $filterquery . "";

        //echo $sqltotal;
        //die;

        $resulttotal = mysqli_query($link, $sqltotal);
        while ($rowtotal = mysqli_fetch_assoc($resulttotal)) {
            $persenfisik = 0;
            if ((float) $rowtotal['pfis'] == 0) {
                $persenfisik = 0;
            } else {
                $persenfisik = (((float) $rowtotal['ufis'] / 1000) / ((float) $rowtotal['pfis'] / 1000)) * 100;
            }

            if ((float) $rowtotal['pagu'] == 0) {
                $persenkeu = 0;
            } else {
                $persenkeu = (((float) $rowtotal['realisasi'] / 1000) / ((float) $rowtotal['pagu'] / 1000)) * 100;
            }


            if ((float) $rowtotal['pagu_efisiensi'] == 0) {
                $keu_efisiensi = 0;
                $fisik_efisiensi = 0;
            } else {
                $keu_efisiensi = (((float) $rowtotal['realisasi'] / 1000) / ((float) $rowtotal['pagu_efisiensi'] / 1000)) * 100;
                $fisik_efisiensi = (((float) $rowtotal['ufis'] / 1000) / ((float) $rowtotal['pagu_efisiensi'] / 1000)) * 100;
            }

            if ($fisik_efisiensi > 100) {
                $fisik_efisiensi = 100;
            }

            $myArray[] = (object)
            [
                'kdsatker' => '',
                'nama_satker' => '',
                'kdprogram' => '',
                'nama_kegiatan' => '',
                'kdoutput' => '',
                'nmoutput' => '',
                'sat_output' => '',
                'kdsoutput' => '',
                'ursoutput' => '',
                'kdkmpnen' => '',
                'kdskmpnen' => '',
                'kdgiat' => '',
                'nmgiat' => '',
                'kode' => 'TOTAL',
                'vol' => '',
                'satuan' => '',
                'lokasi' => '',
                'kabkota' => '',
                'kategori' => '',
                'metode' => '',
                'sumber_dana' => '',
                'pagu' => ((float) $rowtotal['pagu'] / 1000),
                'blokir' => (float) $rowtotal['blokir'] / 1000,
                'realisasi' => (float) $rowtotal['realisasi'] / 1000,
                'ufis' => (float) $rowtotal['ufis'] / 1000,
                'pfis' => (float) $rowtotal['pfis'] / 1000,
                'persenkeu' => $persenkeu,
                'persenfisik' => $persenfisik,
                'pagu_efisiensi' => (float) $rowtotal['pagu_efisiensi'] / 1000,
                'persenkeu_efisiensi' => $keu_efisiensi,
                'persenfisik_efisiensi' => $fisik_efisiensi,
                'class'  => 'totalbaru',
            ];
        }

        $sqlgiat = "select a.kdgiat,a.nama_kegiatan,sum(a.pagu) as pagu,sum(a.realisasi) as realisasi
        ,sum(a.ufis) as ufis,sum(a.pfis) as pfis,sum(a.pagu_efisiensi) as pagu_efisiensi
        ,sum(a.blokir) as blokir,group_concat(DISTINCT a.nama_satker) as nama_satker
        from tb_master_data_paket a 
        where a.nama_kegiatan<>'' 
        " . $filterquery . "
        group by a.kdgiat
        order by a.kdgiat, a.kdprogram ";
        //echo $sqlgiat;
        //die;
        $resultgiat = mysqli_query($link, $sqlgiat);
        $numgiat = mysqli_num_rows($resultgiat);
        if ($resultgiat) {
            while ($rowgiat = mysqli_fetch_assoc($resultgiat)) {
                $persenfisik = 0;
                if ((float) $rowgiat['pfis'] == 0) {
                    $persenfisik = 0;
                } else {
                    $persenfisik = (((float) $rowgiat['ufis'] / 1000) / ((float) $rowgiat['pfis'] / 1000)) * 100;
                }

                if ((float) $rowgiat['pagu'] == 0) {
                    $persenkeu = 0;
                } else {
                    $persenkeu = (((float) $rowgiat['realisasi'] / 1000) / ((float) $rowgiat['pagu'] / 1000)) * 100;
                }

                if ((float) $rowgiat['pagu_efisiensi'] == 0) {
                    $keu_efisiensi = 0;
                    $fisik_efisiensi = 0;
                } else {
                    $keu_efisiensi = (((float) $rowgiat['realisasi'] / 1000) / ((float) $rowgiat['pagu_efisiensi'] / 1000)) * 100;
                    $fisik_efisiensi = (((float) $rowgiat['ufis'] / 1000) / ((float) $rowgiat['pagu_efisiensi'] / 1000)) * 100;
                }

                if ($fisik_efisiensi > 100) {
                    $fisik_efisiensi = 100;
                }

                $myArray[] = (object)
                [
                    'kdsatker' => '',
                    'nama_satker' => $rowgiat['nama_satker'],
                    'kdprogram' => '',
                    'nama_kegiatan' => '',
                    'kdoutput' => '',
                    'nmoutput' => '',
                    'sat_output' => '',
                    'kdsoutput' => '',
                    'ursoutput' => '',
                    'kdkmpnen' => '',
                    'kdskmpnen' => '',
                    'kdgiat' => '',
                    'nmgiat' => $rowgiat['nama_kegiatan'],
                    'kode' => $rowgiat['kdgiat'],
                    'vol' => '',
                    'satuan' => '',
                    'lokasi' => '',
                    'kabkota' => '',
                    'kategori' => '',
                    'metode' => '',
                    'sumber_dana' => '',
                    'pagu' => ((float) $rowgiat['pagu'] / 1000),
                    'blokir' => (float) $rowgiat['blokir'] / 1000,
                    'realisasi' => (float) $rowgiat['realisasi'] / 1000,
                    'ufis' => (float) $rowgiat['ufis'] / 1000,
                    'pfis' => (float) $rowgiat['pfis'] / 1000,
                    'persenkeu' => $persenkeu,
                    'persenfisik' => $persenfisik,
                    'pagu_efisiensi' => (float) $rowgiat['pagu_efisiensi'] / 1000,
                    'persenkeu_efisiensi' => $keu_efisiensi,
                    'persenfisik_efisiensi' => $fisik_efisiensi,
                    'class'  => 'levelkomponenbaru',
                ];
                $sqlkro = "select a.kdgiat,a.kdoutput,a.nmoutput,a.sat_output,sum(a.pagu) as pagu,sum(a.realisasi) as realisasi
                ,sum(a.vol) as vol,sum(a.ufis) as ufis,sum(a.pfis) as pfis,sum(a.pagu_efisiensi) as pagu_efisiensi
                ,sum(a.blokir) as blokir,group_concat(DISTINCT a.nama_satker) as nama_satker
                from tb_master_data_paket a 
                where a.nama_kegiatan<>'' 
                " . $filterquery . " and a.kdgiat = '" . $rowgiat['kdgiat'] . "'
                group by a.kdgiat,a.kdoutput,a.nmoutput,a.sat_output
                order by a.kdgiat,a.kdoutput";
                $resultkro = mysqli_query($link, $sqlkro);
                if ($resultkro) {
                    while ($rowkro = mysqli_fetch_assoc($resultkro)) {
                        $persenfisik = 0;
                        if ((float) $rowkro['pfis'] == 0) {
                            $persenfisik = 0;
                        } else {
                            $persenfisik = (((float) $rowkro['ufis'] / 1000) / ((float) $rowkro['pfis'] / 1000)) * 100;
                        }

                        if ((float) $rowkro['pagu'] == 0) {
                            $persenkeu = 0;
                        } else {
                            $persenkeu = (((float) $rowkro['realisasi'] / 1000) / ((float) $rowkro['pagu'] / 1000)) * 100;
                        }


                        if ((float) $rowkro['pagu_efisiensi'] == 0) {
                            $keu_efisiensi = 0;
                            $fisik_efisiensi = 0;
                        } else {
                            $keu_efisiensi = (((float) $rowkro['realisasi'] / 1000) / ((float) $rowkro['pagu_efisiensi'] / 1000)) * 100;
                            $fisik_efisiensi = (((float) $rowkro['ufis'] / 1000) / ((float) $rowkro['pagu_efisiensi'] / 1000)) * 100;
                        }

                        if ($fisik_efisiensi > 100) {
                            $fisik_efisiensi = 100;
                        }

                        $myArray[] = (object)
                        [
                            'kdsatker' => '',
                            'nama_satker' => $rowkro['nama_satker'],
                            'kdprogram' => '',
                            'nama_kegiatan' => '',
                            'kdoutput' => '',
                            'nmoutput' => '',
                            'sat_output' => '',
                            'kdsoutput' => '',
                            'ursoutput' => '',
                            'kdkmpnen' => '',
                            'kdskmpnen' => '',
                            'kdgiat' => '',
                            'nmgiat' => $rowkro['nmoutput'],
                            'kode' => $rowgiat['kdgiat'] . '.' . $rowkro['kdoutput'],
                            'vol' => (float) $rowkro['vol'],
                            'satuan' => $rowkro['sat_output'],
                            'lokasi' => '',
                            'kabkota' => '',
                            'kategori' => '',
                            'metode' => '',
                            'sumber_dana' => '',
                            'pagu' => ((float) $rowkro['pagu'] / 1000),
                            'blokir' => (float) $rowkro['blokir'] / 1000,
                            'realisasi' => (float) $rowkro['realisasi'] / 1000,
                            'ufis' => (float) $rowkro['ufis'] / 1000,
                            'pfis' => (float) $rowkro['pfis'] / 1000,
                            'persenkeu' => $persenkeu,
                            'persenfisik' => $persenfisik,
                            'pagu_efisiensi' => (float) $rowkro['pagu_efisiensi'] / 1000,
                            'persenkeu_efisiensi' => $keu_efisiensi,
                            'persenfisik_efisiensi' => $fisik_efisiensi,
                            'class'  => 'levelsubkomponenbaru',
                        ];

                        $sqlro = "select a.kdgiat,a.kdoutput,a.nmoutput,a.sat_output,a.kdsoutput,a.ursoutput
                        ,sum(a.pagu) as pagu,sum(a.realisasi) as realisasi
                        ,sum(a.vol) as vol,sum(a.ufis) as ufis,sum(a.pfis) as pfis,sum(a.pagu_efisiensi) as pagu_efisiensi
                        ,sum(a.blokir) as blokir,group_concat(DISTINCT a.nama_satker) as nama_satker
                        from tb_master_data_paket a 
                        where a.nama_kegiatan<>'' 
                        " . $filterquery . " and a.kdgiat = '" . $rowgiat['kdgiat'] . "'
                        and a.kdoutput = '" . $rowkro['kdoutput'] . "' 
                        group by a.kdgiat, a.kdoutput, a.kdsoutput
                        order by a.kdgiat,a.kdoutput";

                        $resultro = mysqli_query($link, $sqlro);
                        if ($resultro) {
                            while ($rowro = mysqli_fetch_assoc($resultro)) {
                                $persenfisik = 0;
                                if ((float) $rowro['pfis'] == 0) {
                                    $persenfisik = 0;
                                } else {
                                    $persenfisik = (((float) $rowro['ufis'] / 1000) / ((float) $rowro['pfis'] / 1000)) * 100;
                                }

                                if ((float) $rowro['pagu'] == 0) {
                                    $persenkeu = 0;
                                } else {
                                    $persenkeu = (((float) $rowro['realisasi'] / 1000) / ((float) $rowro['pagu'] / 1000)) * 100;
                                }


                                if ((float) $rowro['pagu_efisiensi'] == 0) {
                                    $keu_efisiensi = 0;
                                    $fisik_efisiensi = 0;
                                } else {
                                    $keu_efisiensi = (((float) $rowro['realisasi'] / 1000) / ((float) $rowro['pagu_efisiensi'] / 1000)) * 100;
                                    $fisik_efisiensi = (((float) $rowro['ufis'] / 1000) / ((float) $rowro['pagu_efisiensi'] / 1000)) * 100;
                                }

                                if ($fisik_efisiensi > 100) {
                                    $fisik_efisiensi = 100;
                                }

                                $myArray[] = (object)
                                [
                                    'kdsatker' => '',
                                    'nama_satker' => $rowro['nama_satker'],
                                    'kdprogram' => '',
                                    'nama_kegiatan' => '',
                                    'kdoutput' => '',
                                    'nmoutput' => '',
                                    'sat_output' => '',
                                    'kdsoutput' => '',
                                    'ursoutput' => '',
                                    'kdkmpnen' => '',
                                    'kdskmpnen' => '',
                                    'kdgiat' => '',
                                    'nmgiat' => $rowro['ursoutput'],
                                    'kode' => $rowgiat['kdgiat'] . '.' . $rowkro['kdoutput'] . "." . $rowro['kdsoutput'],
                                    'vol' => (float) $rowro['vol'],
                                    'satuan' => $rowro['sat_output'],
                                    'lokasi' => '',
                                    'kabkota' => '',
                                    'kategori' => '',
                                    'metode' => '',
                                    'sumber_dana' => '',
                                    'pagu' => ((float) $rowro['pagu'] / 1000),
                                    'blokir' => (float) $rowro['blokir'] / 1000,
                                    'realisasi' => (float) $rowro['realisasi'] / 1000,
                                    'ufis' => (float) $rowro['ufis'] / 1000,
                                    'pfis' => (float) $rowro['pfis'] / 1000,
                                    'persenkeu' => $persenkeu,
                                    'persenfisik' => $persenfisik,
                                    'pagu_efisiensi' => (float) $rowro['pagu_efisiensi'] / 1000,
                                    'persenkeu_efisiensi' => $keu_efisiensi,
                                    'persenfisik_efisiensi' => $fisik_efisiensi,
                                    'class'  => 'levelunitkomponenbaru',
                                ];

                                $sql = "select a.*
                                from tb_master_data_paket a 
                                where a.kdgiat<>'' 
                                " . $filterquery . " and a.kdgiat = '" . $rowgiat['kdgiat'] . "'
                                and a.kdoutput = '" . $rowkro['kdoutput'] . "' 
                                and a.kdsoutput = '" . $rowro['kdsoutput'] . "'
                                order by a.kdgiat, a.kdprogram, a.kdsatker ";
                                //echo $sql;
                                //die();
                                $result = mysqli_query($link, $sql);
                                $num = mysqli_num_rows($result);
                                if ($result) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $myArray[] = (object)
                                        [
                                            'kdsatker' => $row['kdsatker'],
                                            'nama_satker' => $row['nama_satker'],
                                            'kdprogram' => $row['kdprogram'],
                                            'nama_kegiatan' => $row['nama_kegiatan'],
                                            'kdoutput' => $row['kdoutput'],
                                            'nmoutput' => $row['nmoutput'],
                                            'sat_output' => $row['sat_output'],
                                            'kdsoutput' => $row['kdsoutput'],
                                            'ursoutput' => $row['ursoutput'],
                                            'kdkmpnen' => $row['kdkmpnen'],
                                            'kdskmpnen' => $row['kdskmpnen'],
                                            'kdgiat' => $row['kdgiat'],
                                            'nmgiat' => $row['nmgiat'],
                                            'kode' => $rowgiat['kdgiat'] . '.' . $rowkro['kdoutput'] . "." . $rowro['kdsoutput'] . "." . $row['kdkmpnen'] . "." . $row['kdskmpnen'],
                                            'vol' => (float) $row['vol'],
                                            'satuan' => $row['satuan'],
                                            'lokasi' => $row['lokasi'],
                                            'kabkota' => $row['kabkota'],
                                            'kategori' => $row['kategori'],
                                            'metode' => $row['metode'],
                                            'sumber_dana' => $row['sumber_dana'],
                                            'pagu' => ((float) $row['pagu'] / 1000),
                                            'blokir' => (float) $row['blokir'] / 1000,
                                            'realisasi' => (float) $row['realisasi'] / 1000,
                                            'ufis' => (float) $row['ufis'] / 1000,
                                            'pfis' => (float) $row['pfis'] / 1000,
                                            'persenkeu' => (float) $row['persenkeu'],
                                            'persenfisik' => (float) $row['persenkeu'],
                                            'pagu_efisiensi' => (float) $row['pagu_efisiensi'] / 1000,
                                            'persenkeu_efisiensi' => (float) $row['persenkeu_efisiensi'],
                                            'persenfisik_efisiensi' => (float) $row['persenfisik_efisiensi'],
                                            'class'  => 'akhirbaru',
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $response         = [];
            $response['tanggalemon'] =  $tanggalemon;
            $response['data'] =  $myArray;
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
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

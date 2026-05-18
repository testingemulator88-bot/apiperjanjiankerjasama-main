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
    if ($kode_satkeremon == '') {
        $filterquery = $filterquery . " and (a.kdsatker = '----')";
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

        $sqltotal = "select a.kdsatker,a.kdprogram,a.kdgiat,b.nmgiat
        ,a.kdoutput,a.kdsoutput,a.kdkmpnen,a.kdskmpnen,a.kode
        ,sum(a.pgrupiah) as pgrupiah,sum(a.pgpln) as pgpln,sum(a.pgsbsn) as pgsbsn
        ,sum(a.pgrupiah+a.pgpln+a.pgsbsn) as pagu
        ,sum(a.rtot) as rtot,sum(a.ufis) as ufis,sum(a.pfis) as pfis,sum(a.pg57) as pg57
        ,sum(a.blokir) as blokir
        from paket a 
        left join tgiat b
        on a.kdgiat = b.kdgiat
        where a.kdgiat<>'' 
        " . $filterquery . "
        and b.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "')
        group by a.kdsatker
        order by a.kdsatker ";
        $resulttotal = mysqli_query($link, $sqltotal);
        while ($rowtotal = mysqli_fetch_assoc($resulttotal)) {
            $sumber_dana = "";
            $pagu = 0;
            if ((float) $rowtotal['pgrupiah'] > 0) {
                $sumber_dana = "RPM";
            } else if ((float) $rowtotal['pgpln'] > 0) {
                $sumber_dana = "PHLN";
            } else {
                $sumber_dana = "SBSN";
            }
            $pagu = (float) $rowtotal['pagu'];
            $persenfisik = 0;
            if ((float) $rowtotal['pfis'] == 0) {
                $persenfisik = 0;
            } else {
                $persenfisik = (((float) $rowtotal['ufis'] / 1000) / ((float) $rowtotal['pfis'] / 1000)) * 100;
            }

            $keu_efisiensi = 0;
            $fisik_efisiensi = 0;

            $pagu_efisiensi = (float) $rowtotal['pg57'];
            if ($pagu_efisiensi == 0) {
                if ($pagu_efisiensi == 0) {
                    if ($sumber_dana == "RPM") {
                        $pagu_efisiensi = $pagu - (float) $rowtotal['blokir'];
                    } else {
                        $pagu_efisiensi = $pagu;
                    }
                }
            }

            if ($pagu_efisiensi == 0) {
                $keu_efisiensi = 0;
                $fisik_efisiensi = 0;
            } else {
                $keu_efisiensi = (((float) $rowtotal['rtot'] / 1000) / ($pagu_efisiensi / 1000)) * 100;
                $fisik_efisiensi = (((float) $rowtotal['ufis'] / 1000) / ($pagu_efisiensi / 1000)) * 100;
            }
            if ($fisik_efisiensi > 100) {
                $fisik_efisiensi = 100;
            }

            $myArray[] = (object)
            [
                'kdgiat' => 'TOTAL',
                'nmgiat' => '',
                'vol' => '',
                'satuan' => '',
                'lokasi' => '',
                'kabkota' => '',
                'kategori' => '',
                'metode' => '',
                'sumber_dana' => '',
                'pagu' => $pagu / 1000,
                'realisasi' => (float) $rowtotal['rtot'] / 1000,
                'persenkeu' => (((float) $rowtotal['rtot'] / 1000) / ($pagu / 1000)) * 100,
                'persenfisik' => $persenfisik,
                'pagu_efisiensi' => $pagu_efisiensi / 1000,
                'persenkeu_efisiensi' => $keu_efisiensi,
                'persenfisik_efisiensi' => $fisik_efisiensi,
                'class'  => 'totalbaru',
            ];
        }

        $sql = "select a.kdsatker,a.kdprogram,a.kdgiat,b.nmgiat
        ,a.kdoutput,a.kdsoutput,a.kdkmpnen,a.kdskmpnen,a.kode
        ,sum(a.pgrupiah) as pgrupiah,sum(a.pgpln) as pgpln,sum(a.pgsbsn) as pgsbsn
        ,sum(a.pgrupiah+a.pgpln+a.pgsbsn) as pagu
        ,sum(a.rtot) as rtot,sum(a.ufis) as ufis,sum(a.pfis) as pfis,sum(a.pg57) as pg57
        ,sum(a.blokir) as blokir
        from paket a 
        left join tgiat b
        on a.kdgiat = b.kdgiat
        where a.kdgiat<>'' 
        " . $filterquery . "
        and b.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "')
        group by a.kdgiat
        order by a.kdgiat, a.kdprogram ";
        //echo $sql;
        //die();
        $result = mysqli_query($link, $sql);
        $num = mysqli_num_rows($result);
        if ($num > 0) {
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $sumber_dana = "";
                    $pagu = 0;
                    if ((float) $row['pgrupiah'] > 0) {
                        $sumber_dana = "RPM";
                    } else if ((float) $row['pgpln'] > 0) {
                        $sumber_dana = "PHLN";
                    } else {
                        $sumber_dana = "SBSN";
                    }
                    $pagu = (float) $row['pagu'];
                    $persenfisik = 0;
                    if ((float) $row['pfis'] == 0) {
                        $persenfisik = 0;
                    } else {
                        $persenfisik = (((float) $row['ufis'] / 1000) / ((float) $row['pfis'] / 1000)) * 100;
                    }

                    $keu_efisiensi = 0;
                    $fisik_efisiensi = 0;

                    $pagu_efisiensi = (float) $row['pg57'];
                    if ($pagu_efisiensi == 0) {
                        if ($pagu_efisiensi == 0) {
                            if ($sumber_dana == "RPM") {
                                $pagu_efisiensi = $pagu - (float) $row['blokir'];
                            } else {
                                $pagu_efisiensi = $pagu;
                            }
                        }
                    }

                    if ($pagu_efisiensi == 0) {
                        $keu_efisiensi = 0;
                        $fisik_efisiensi = 0;
                    } else {
                        $keu_efisiensi = (((float) $row['rtot'] / 1000) / ($pagu_efisiensi / 1000)) * 100;
                        $fisik_efisiensi = (((float) $row['ufis'] / 1000) / ($pagu_efisiensi / 1000)) * 100;
                    }

                    if ($fisik_efisiensi > 100) {
                        $fisik_efisiensi = 100;
                    }

                    $myArray[] = (object)
                    [
                        'kdgiat' => $row['kdgiat'],
                        'nmgiat' => $row['nmgiat'],
                        'vol' => '',
                        'satuan' => '',
                        'lokasi' => '',
                        'kabkota' => '',
                        'kategori' => '',
                        'metode' => '',
                        'sumber_dana' => '',
                        'pagu' => $pagu / 1000,
                        'realisasi' => (float) $row['rtot'] / 1000,
                        'persenkeu' => (((float) $row['rtot'] / 1000) / ($pagu / 1000)) * 100,
                        'persenfisik' => $persenfisik,
                        'pagu_efisiensi' => $pagu_efisiensi / 1000,
                        'persenkeu_efisiensi' => $keu_efisiensi,
                        'persenfisik_efisiensi' => $fisik_efisiensi,
                        'class'  => 'levelkomponenbaru',
                    ];

                    $sqloutput = "select a.kdsatker,a.kdprogram,a.kdgiat
                    ,a.kdoutput,a.kdsoutput,a.kdkmpnen,a.kdskmpnen,a.kode,b.nmoutput,b.sat
                    ,sum(a.vol) as vol
                    ,sum(a.pgrupiah) as pgrupiah,sum(a.pgpln) as pgpln,sum(a.pgsbsn) as pgsbsn
                    ,sum(a.pgrupiah+a.pgpln+a.pgsbsn) as pagu
                    ,sum(a.rtot) as rtot,sum(a.ufis) as ufis,sum(a.pfis) as pfis,sum(a.pg57) as pg57
                    ,sum(a.blokir) as blokir
                    from paket a 
                    left join toutput b
                    on a.kdgiat = b.kdgiat 
                    and a.kdoutput = b.kdoutput 
                    where a.kdgiat<>'' 
                    " . $filterquery . " and a.kdgiat = '" . $row['kdgiat'] . "' 
                    and b.nmoutput<>''
                    and b.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "')
                    group by a.kdgiat,a.kdoutput,b.nmoutput,b.sat
                    order by a.kdgiat,a.kdoutput";
                    //echo $sqloutput;
                    //die;
                    $resultoutput = mysqli_query($link, $sqloutput);
                    while ($rowoutput = mysqli_fetch_assoc($resultoutput)) {
                        $sumber_dana = "";
                        $pagu = 0;
                        if ((float) $rowoutput['pgrupiah'] > 0) {
                            $sumber_dana = "RPM";
                        } else if ((float) $rowoutput['pgpln'] > 0) {
                            $sumber_dana = "PHLN";
                        } else {
                            $sumber_dana = "SBSN";
                        }
                        $pagu = (float) $rowoutput['pagu'];
                        $persenfisik = 0;
                        if ((float) $rowoutput['pfis'] == 0) {
                            $persenfisik = 0;
                        } else {
                            $persenfisik = (((float) $rowoutput['ufis'] / 1000) / ((float) $rowoutput['pfis'] / 1000)) * 100;
                        }

                        $keu_efisiensi = 0;
                        $fisik_efisiensi = 0;

                        $pagu_efisiensi = (float) $rowoutput['pg57'];
                        if ($pagu_efisiensi == 0) {
                            if ($pagu_efisiensi == 0) {
                                if ($sumber_dana == "RPM") {
                                    $pagu_efisiensi = $pagu - (float) $rowoutput['blokir'];
                                } else {
                                    $pagu_efisiensi = $pagu;
                                }
                            }
                        }

                        if ($pagu_efisiensi == 0) {
                            $keu_efisiensi = 0;
                            $fisik_efisiensi = 0;
                        } else {
                            $keu_efisiensi = (((float) $rowoutput['rtot'] / 1000) / ($pagu_efisiensi / 1000)) * 100;
                            $fisik_efisiensi = (((float) $rowoutput['ufis'] / 1000) / ($pagu_efisiensi / 1000)) * 100;
                        }
                        if ($fisik_efisiensi > 100) {
                            $fisik_efisiensi = 100;
                        }

                        $myArray[] = (object)
                        [
                            'kdgiat' => $rowoutput['kdgiat'] . "." . $rowoutput['kdoutput'],
                            'nmgiat' => $rowoutput['nmoutput'],
                            'vol' => (float) $rowoutput['vol'],
                            'satuan' => str_replace("/", " / ", $rowoutput['sat']),
                            'lokasi' => '',
                            'kabkota' => '',
                            'kategori' => '',
                            'metode' => '',
                            'sumber_dana' => '',
                            'pagu' => $pagu / 1000,
                            'realisasi' => (float) $rowoutput['rtot'] / 1000,
                            'persenkeu' => (((float) $rowoutput['rtot'] / 1000) / ($pagu / 1000)) * 100,
                            'persenfisik' => $persenfisik,
                            'pagu_efisiensi' => $pagu_efisiensi / 1000,
                            'persenkeu_efisiensi' => $keu_efisiensi,
                            'persenfisik_efisiensi' => $fisik_efisiensi,
                            'class'  => 'levelsubkomponenbaru',
                        ];
                        $sqldetailoutput = "select a.kdsatker,a.kdprogram,a.kdgiat
                        ,a.kdoutput,a.kdsoutput,a.kdkmpnen,a.kdskmpnen,a.kode,b.ursoutput
                        ,sum(a.vol) as vol
                        ,sum(a.pgrupiah) as pgrupiah,sum(a.pgpln) as pgpln,sum(a.pgsbsn) as pgsbsn
                        ,sum(a.rtot) as rtot,sum(a.ufis) as ufis,sum(a.pfis) as pfis,sum(a.pg57) as pg57
                        ,sum(a.blokir) as blokir
                        from paket a 
                        left join d_soutput b on
                        a.kdgiat = b.kdgiat and a.kdoutput = b.kdoutput and a.kdsoutput = b.kdsoutput
                        and a.kdsatker = b.kdsatker
                        where a.kdgiat<>'' 
                        " . $filterquery . " and a.kdgiat = '" . $rowoutput['kdgiat'] . "'
                        and a.kdoutput = '" . $rowoutput['kdoutput'] . "' 
                        and b.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "')
                        group by a.kdgiat, a.kdoutput, a.kdsoutput";
                        //echo $sqldetailoutput;
                        //die;
                        $resultdetailoutput = mysqli_query($link, $sqldetailoutput);
                        while ($rowdetailoutput = mysqli_fetch_assoc($resultdetailoutput)) {
                            $sumber_dana = "";
                            $pagu = 0;
                            if ((float) $rowdetailoutput['pgrupiah'] > 0) {
                                $sumber_dana = "RPM";
                                $pagu = (float) $rowdetailoutput['pgrupiah'];
                            } else if ((float) $rowdetailoutput['pgpln'] > 0) {
                                $sumber_dana = "PHLN";
                                $pagu = (float) $rowdetailoutput['pgpln'];
                            } else {
                                $sumber_dana = "SBSN";
                                $pagu = (float) $rowdetailoutput['pgsbsn'];
                            }

                            $persenfisik = 0;
                            if ((float) $rowdetailoutput['pfis'] == 0) {
                                $persenfisik = 0;
                            } else {
                                $persenfisik = (((float) $rowdetailoutput['ufis'] / 1000) / ((float) $rowdetailoutput['pfis'] / 1000)) * 100;
                            }

                            $keu_efisiensi = 0;
                            $fisik_efisiensi = 0;

                            $pagu_efisiensi = (float) $rowdetailoutput['pg57'];
                            if ($pagu_efisiensi == 0) {
                                if ($pagu_efisiensi == 0) {
                                    if ($sumber_dana == "RPM") {
                                        $pagu_efisiensi = $pagu - (float) $rowdetailoutput['blokir'];
                                    } else {
                                        $pagu_efisiensi = $pagu;
                                    }
                                }
                            }

                            if ($pagu_efisiensi == 0) {
                                $keu_efisiensi = 0;
                                $fisik_efisiensi = 0;
                            } else {
                                $keu_efisiensi = (((float) $rowdetailoutput['rtot'] / 1000) / ($pagu_efisiensi / 1000)) * 100;
                                $fisik_efisiensi = (((float) $rowdetailoutput['ufis'] / 1000) / ($pagu_efisiensi / 1000)) * 100;
                            }
                            if ($fisik_efisiensi > 100) {
                                $fisik_efisiensi = 100;
                            }

                            $myArray[] = (object)
                            [
                                'kdgiat' => $rowoutput['kdgiat'] . "." . $rowoutput['kdoutput'] . "." . $rowdetailoutput['kdsoutput'],
                                'nmgiat' => $rowdetailoutput['ursoutput'],
                                'vol' => (float) $rowdetailoutput['vol'],
                                'satuan' => str_replace("/", " / ", $rowoutput['sat']),
                                'lokasi' => '',
                                'kabkota' => '',
                                'kategori' => '',
                                'metode' => '',
                                'sumber_dana' => '',
                                'pagu' => $pagu / 1000,
                                'realisasi' => (float) $rowdetailoutput['rtot'] / 1000,
                                'persenkeu' => (((float) $rowdetailoutput['rtot'] / 1000) / ($pagu / 1000)) * 100,
                                'persenfisik' => $persenfisik,
                                'pagu_efisiensi' => $pagu_efisiensi / 1000,
                                'persenkeu_efisiensi' => $keu_efisiensi,
                                'persenfisik_efisiensi' => $fisik_efisiensi,
                                'class'  => 'levelunitkomponenbaru',
                            ];

                            $sqlpaket = "select a.kdsatker,a.kdprogram,a.kdgiat
                            ,a.kdoutput,a.kdsoutput,a.kdkmpnen,a.kdskmpnen,a.kode,a.nmpaket,a.vol,a.sat
                            ,(select b.nmlokasi from tlokasi b where b.kdlokasi = a.kdlokasi
                            and b.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "') 
                            and YEAR(b.tanggaldata) ='" . $tahun . "') as nmlokasi
                            ,(select c.nmkabkota from tkabkota c where c.kdkabkota = a.kdkabkota
                            and c.kdlokasi = a.kdlokasi and c.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "') 
                            and YEAR(c.tanggaldata) ='" . $tahun . "') as nmkabkota
                            ,(select d.nmkategori from tkategori d where d.kdkategori = a.kdkategori 
                            and d.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "') 
                            and YEAR(d.tanggaldata) ='" . $tahun . "') as nmkategori
                            ,(select e.urmetode from tmetode e where e.kdmetode = a.metode
                            and e.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "') 
                            and YEAR(e.tanggaldata) ='" . $tahun . "' ) as urmetode
                            ,a.pgrupiah,a.pgpln,a.pgsbsn,a.rtot,a.ufis,a.pfis,a.pg57,a.blokir
                            from paket a 
                            where a.kdgiat<>''" . $filterquery . " and a.kdgiat = '" . $rowdetailoutput['kdgiat'] . "'
                            and a.kdoutput = '" . $rowdetailoutput['kdoutput'] . "' 
                            and a.kdsoutput = '" . $rowdetailoutput['kdsoutput'] . "'";
                            //echo $sqlpaket;
                            //die;
                            $resultpaket = mysqli_query($link, $sqlpaket);
                            while ($rowpaket = mysqli_fetch_assoc($resultpaket)) {
                                $sumber_dana = "";
                                $pagu = 0;
                                if ((float) $rowpaket['pgrupiah'] > 0) {
                                    $sumber_dana = "RPM";
                                    $pagu = (float) $rowpaket['pgrupiah'];
                                } else if ((float) $rowpaket['pgpln'] > 0) {
                                    $sumber_dana = "PHLN";
                                    $pagu = (float) $rowpaket['pgpln'];
                                } else {
                                    $sumber_dana = "SBSN";
                                    $pagu = (float) $rowpaket['pgsbsn'];
                                }

                                $persenfisik = 0;
                                if ((float) $rowpaket['pfis'] == 0) {
                                    $persenfisik = 0;
                                } else {
                                    $persenfisik = (((float) $rowpaket['ufis'] / 1000) / ((float) $rowpaket['pfis'] / 1000)) * 100;
                                }

                                $keu_efisiensi = 0;
                                $fisik_efisiensi = 0;

                                $pagu_efisiensi = (float) $rowpaket['pg57'];
                                if ($pagu_efisiensi == 0) {
                                    if ($sumber_dana == "RPM") {
                                        $pagu_efisiensi = $pagu - (float) $rowpaket['blokir'];
                                    } else {
                                        $pagu_efisiensi = $pagu;
                                    }
                                }

                                if ($pagu_efisiensi == 0) {
                                    $keu_efisiensi = 0;
                                    $fisik_efisiensi = 0;
                                } else {
                                    $keu_efisiensi = (((float) $rowpaket['rtot'] / 1000) / ($pagu_efisiensi / 1000)) * 100;
                                    $fisik_efisiensi = (((float) $rowpaket['ufis'] / 1000) / ($pagu_efisiensi / 1000)) * 100;
                                }

                                if ($fisik_efisiensi > 100) {
                                    $fisik_efisiensi = 100;
                                }

                                $myArray[] = (object)
                                [
                                    'kdgiat' => $rowoutput['kdgiat'] . "." . $rowoutput['kdoutput'] . "." . $rowdetailoutput['kdsoutput'] . "." . $rowpaket['kdkmpnen'] . "." . $rowpaket['kdskmpnen'],
                                    'nmgiat' => $rowpaket['nmpaket'],
                                    'vol' => (float) $rowpaket['vol'],
                                    'satuan' => $rowpaket['sat'],
                                    'lokasi' => $rowpaket['nmlokasi'],
                                    'kabkota' => $rowpaket['nmkabkota'],
                                    'kategori' => $rowpaket['nmkategori'],
                                    'metode' => $rowpaket['urmetode'],
                                    'sumber_dana' => $sumber_dana,
                                    'pagu' => ($pagu / 1000),
                                    'realisasi' => (float) $rowpaket['rtot'] / 1000,
                                    'persenkeu' => (((float) $rowpaket['rtot'] / 1000) / ($pagu / 1000)) * 100,
                                    'persenfisik' => $persenfisik,
                                    'pagu_efisiensi' => $pagu_efisiensi / 1000,
                                    'persenkeu_efisiensi' => $keu_efisiensi,
                                    'persenfisik_efisiensi' => $fisik_efisiensi,
                                    'class'  => 'akhirbaru',
                                ];
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

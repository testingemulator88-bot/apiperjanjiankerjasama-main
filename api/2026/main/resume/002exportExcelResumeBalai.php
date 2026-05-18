<?php
require '../../../vendor/autoload.php';
include '../../library/config.php';
error_reporting(0);
check_injection();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
$header = apache_request_headers();
//var_dump($header);
$Bearer = $header['authorization'];
if ($Bearer == '') {
    $Bearer = $header['Authorization'];
}
$tempBearer = explode(" ", $Bearer);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\Axis;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Helper\HTML;
use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = new Spreadsheet();
$drawing = new Drawing();
$sheet = $spreadsheet->getActiveSheet();

$styleRangeArray = [
    'borders' => [
        'outline' => [ // Applies to the outline of the range
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'], // Red color
        ],
        'inside' => [ // Applies to inside borders within the range
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'], // Blue color
        ],
    ],
];


$filter_query = "";
$sqlcek = "select id as jumlah from tb_user where token='" . str_replace('"', '', $tempBearer[1]) . "' and deleted='0'";
$resultsqlcek = mysqli_query($link, $sqlcek);
$numcek = mysqli_num_rows($resultsqlcek);

if (($numcek > 0) && ($Bearer <> '') && ($tempBearer[1] <> '')) {

    $sqlpkaktif = "select id from master_pk where deleted = '0' and is_active = '1'";
    $resultpkaktif  = mysqli_query($link, $sqlpkaktif);
    while ($rowpkaktif  = mysqli_fetch_assoc($resultpkaktif)) {
        $pk_aktif = $rowpkaktif['id'];
    }
    $pk_aktif = '3';
    $tahunsekarang = get_Isi_Field1('tahun', 'master_pk', 'id', '3');
    $filterquery = "";
    $filterquerybalai = "";
    $tahun = fixup($_GET['tahun']);

    if ($tahun <> '') {
        $filterquery = $filterquery . " and a.tahun = '" . $tahun . "'";
    }
    $kodebalai = fixup($_GET['kodebalai']);
    if (($kodebalai <> '') && ($kodebalai <> '1')) {
        $filterquerybalai = $filterquerybalai . " and a.id_balai = '" . $kodebalai . "'";
    }

    if ($kodebalai <> '') {
        $sqlfix = "select level_piu from master_kategori_satker where id = '" . $kodebalai . "'";
        //echo $sqlfix;
        //die;
        $resultfix = mysqli_query($link, $sqlfix);
        while ($rowfix = mysqli_fetch_assoc($resultfix)) {
            $kode_fix = $rowfix['level_piu'];
            //$id_balai = $rowfix['kdbalai'];
        }
        $filterquery = $filterquery . " and a.pelaksana like '%" . $kode_fix . "%'";
        $filterquery = $filterquery . " and (pelaksana in ('" . $kode_fix . "') or pelaksana in (" . $kode_fix . ")) or pelaksana like ('%," . $kode_fix . ",%') or pelaksana like ('" . $kode_fix . ",%') or pelaksana like ('%," . $kode_fix . "') or pelaksana like ('%," . $kode_fix . ",%')";
    } else {
        $filterquery = $filterquery . " and a.pelaksana in ('---')";
    }

    $sqlbalaiemon = "select kdbalai_pendek,kdbalai from master_kategori_satker where id = '" . $kodebalai . "'";
    $resultbalaiemon = mysqli_query($link, $sqlbalaiemon);
    while ($rowbalaiemon = mysqli_fetch_assoc($resultbalaiemon)) {
        $kode_balaiemon = $rowbalaiemon['kdbalai'];
        //$id_balai = $rowbalaiemon['kdbalai'];
    }
    //echo $kode_balaiemon;
    //die;

    $myArray = array();
    // IKSK
    $sql = "select a.id,GROUP_CONCAT(a.id) as grupid,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level,a.deleted
        ,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
        ,IF(a.satuan = '0', null, a.satuan) as satuan
        ,IF(a.output = '0', null, a.output) as output
        ,IF(a.outcome = '0', null, a.outcome) as outcome
        , (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
        , (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
        , (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
        , (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
        ,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
        and zz.kode_satker='" . $kodebalai . "') as terpilih
        ,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk, a.belakangkoma
        ,a.kolom1,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.rumuskolom1,a.rumuskolom2,a.rumuskolom3,a.rumuskolom4,a.rumuskolom5,a.isian_kolom
        ,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
        IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
        IF(a.level = 'IKSP', 'levelsubsubkegiatan', 
        IF(a.level = 'KEGIATAN', 'levelpaket', 
        IF(a.level = 'SK', 'levelpekerjaan', 
        IF(a.level = 'IKSK', 'levelakhir', 
        IF(a.level = 'KRO', 'levelakhir', 
        IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
        from tb_indikator_akhir a 
        left join master_unor b
        on a.kdunor = b.id
        where a.deleted='0' and a.level = 'ISP' 
        and a.id in (select id_indikator from tb_data_pk_balai_akhir where deleted='0') group by a.kode_unique
        order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";

    //echo $sql;
    //die();
    $result = mysqli_query($link, $sql);
    $num = mysqli_num_rows($result);
    if ($num > 0) {
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $belakangkoma = (float) $row['belakangkoma'];
                $rumuskolom1 = $row['rumuskolom1'];
                $rumuskolom2 = $row['rumuskolom2'];
                $rumuskolom3 = $row['rumuskolom3'];
                $rumuskolom4 = $row['rumuskolom4'];
                $rumuskolom5 = $row['rumuskolom5'];
                $targetkumulatif = 0;
                $baseline = 0;
                $target = 0;
                $target_komponen = 0;
                $target_tahun_berjalan = 0;
                $target_komponen_tahun_berjalan = 0;
                $skor_komponen_a = 0;
                $skor_komponen_b = 0;
                $skor_komponen_c = 0;
                $output_tahun_berjalan = 0;
                $output_komponen_tahun_berjalan = 0;
                $skor_komponen_a_tahun_berjalan = 0;
                $skor_komponen_b_tahun_berjalan = 0;
                $skor_komponen_c_tahun_berjalan = 0;
                $capaian = 0;

                $sqlbalai = "select b.nama_kategori, a.id_balai from tb_data_pk_balai_akhir a 
                    left join master_kategori_satker b
                    on a.id_balai = b.id
                    where a.deleted='0' and a.id_indikator in (" . $row['grupid'] . ")
                    and a.id_indikator not in (select x.id_indikator from tb_data_pk_tidak_cetak_akhir x
                    where x.deleted='0' and x.kode_satker=a.id_balai)
                    and a.id_balai<>'1' " . $filterquerybalai . "
                    group by a.id_balai order by a.id_balai";
                $myArraybalai = array();
                $resultbalai = mysqli_query($link, $sqlbalai);
                while ($rowbalai = mysqli_fetch_assoc($resultbalai)) {

                    $targetkumulatif = 0;
                    $baseline = 0;
                    $target = 0;
                    $target_komponen = 0;
                    $target_tahun_berjalan = 0;
                    $target_komponen_tahun_berjalan = 0;
                    $skor_komponen_a = 0;
                    $skor_komponen_b = 0;
                    $skor_komponen_c = 0;
                    $output_tahun_berjalan = 0;
                    $output_komponen_tahun_berjalan = 0;
                    $skor_komponen_a_tahun_berjalan = 0;
                    $skor_komponen_b_tahun_berjalan = 0;
                    $skor_komponen_c_tahun_berjalan = 0;
                    $capaian = 0;
                    $sqlcapaiantahunsekarang = "select sum(a.capaian) as capaian
                        from tb_data_pk_balai_akhir a where a.deleted='0' 
                        and a.id_balai = '" . $rowbalai['id_balai'] . "' and a.id_indikator in (" . $row['grupid'] . ")
                        and a.tahun = '" . $tahunsekarang . "' and a.kode_tahun='" . $tahun . "'";

                    $resultcapaiantahunsekarang = mysqli_query($link, $sqlcapaiantahunsekarang);
                    $numcapaiantahunsekarang = mysqli_num_rows($resultcapaiantahunsekarang);
                    if ($numcapaiantahunsekarang > 0) {
                        while ($rowcapaiantahunsekarang = mysqli_fetch_assoc($resultcapaiantahunsekarang)) {
                            $capaian = (float) $rowcapaiantahunsekarang['capaian'];
                        }
                    }

                    if ($row['hitungan_pk'] == '1') {
                        $baselinekolom1 = 0;
                        $baselinekolom2 = 0;
                        $baselinekolom3 = 0;
                        $baselinekolom4 = 0;
                        $baselinekolom5 = 0;
                        $sqlbaseline = " select a.id,a.kode_tahun,a.id_indikator,a.id_balai,a.kode_balai,a.kolom1 
                            ,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.isian_kolom, b.nama as tahun
                            from tb_data_baseline_pk_balai_akhir a 
                            left join master_tahun b
                            on a.kode_tahun = b.id
                            where a.deleted='0' 
                            and a.kode_tahun='" . $tahun . "'
                            and a.id_indikator = '" . $row['id'] . "'
                            and a.id_balai = '" . $rowbalai['id_balai'] . "'
                            order by a.id";
                        //echo $sqlbaseline;
                        //die;
                        $resultbaseline = mysqli_query($link, $sqlbaseline);
                        $numbaseline = mysqli_num_rows($resultbaseline);
                        if ($numbaseline > 0) {
                            while ($rowbaseline = mysqli_fetch_assoc($resultbaseline)) {
                                $baselinekolom1 = (float) $rowbaseline['kolom1'];
                                $baselinekolom2 = (float) $rowbaseline['kolom2'];
                                $baselinekolom3 = (float) $rowbaseline['kolom3'];
                                $baselinekolom4 = (float) $rowbaseline['kolom4'];
                                $baselinekolom5 = (float) $rowbaseline['kolom5'];
                            }
                        }
                        $sqltahun = "select a.tahun_awal,a.tahun_akhir from master_tahun a where a.deleted='0' and a.id = '" . $tahun . "'";
                        //echo $sqltahun;
                        //die;
                        $resulttahun = mysqli_query($link, $sqltahun);
                        while ($rowtahun = mysqli_fetch_assoc($resulttahun)) {
                            $tahun_awal = (float) $rowtahun['tahun_awal'];
                            $tahun_akhir = (float) $rowtahun['tahun_akhir'];
                        }
                        $arraytahun = array();
                        $arraykolom1 = array();
                        $arraykolom2 = array();
                        $arraykolom3 = array();
                        $arraykolom4 = array();
                        $arraykolom5 = array();
                        $arrayminuskolom1 = array();
                        $arrayminuskolom2 = array();
                        $arrayminuskolom3 = array();
                        $arrayminuskolom4 = array();
                        $arrayminuskolom5 = array();

                        $arrayisiminuskolom1 = array();
                        $arrayisiminuskolom2 = array();
                        $arrayisiminuskolom3 = array();
                        $arrayisiminuskolom4 = array();
                        $arrayisiminuskolom5 = array();
                        $s = 0;
                        $totalkolom1 = 0;
                        $totalkolom2 = 0;
                        $totalkolom3 = 0;
                        $totalkolom4 = 0;
                        $totalkolom5 = 0;
                        for ($x = $tahun_awal; $x <= $tahunsekarang; $x++) {
                            array_push($arraytahun, $x);
                            $myArraydetail = array();
                            $sqldata = "select a.id,a.id_balai,a.kode_balai,a.kolom1,a.kolom2,a.kolom3
                                ,a.kolom4,a.kolom5,a.isian_kolom
                                from tb_data_pk_balai_akhir a where a.deleted='0' and a.tahun='" . $x . "' 
                                and a.id_balai = '" . $rowbalai['id_balai'] . "' and a.id_indikator = '" . $row['id'] . "'";
                            //echo $sqldata;
                            //die;
                            $resultdata = mysqli_query($link, $sqldata);
                            $numdata = mysqli_num_rows($resultdata);
                            if ($numdata > 0) {
                                if ($resultdata) {
                                    while ($rowdata = mysqli_fetch_assoc($resultdata)) {
                                        if ($row['hitungan_pk'] == '1') {
                                            $isikolom1 = 0;
                                            $isikolom2 = 0;
                                            $isikolom3 = 0;
                                            $isikolom4 = 0;
                                            $isikolom5 = 0;
                                            #kolom1
                                            if (strstr($rumuskolom1, "IKSK")) {
                                                $temprumuskolom1 = explode("+", $rumuskolom1);
                                                for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom1); $jumlahtambah++) {
                                                    $sqlcekhitungan = "select id,hitungan_pk from tb_indikator_akhir where deleted = '0'
                                                        and kode_unique = '" . $temprumuskolom1[$jumlahtambah] . "'";
                                                    $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                                    while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                        $hitungan_pkdetail = $rowcekhitungan['hitungan_pk'];
                                                    }
                                                    if ($hitungan_pkdetail == '1') {
                                                        $sqlskortahunsekarang = "select sum(target) as target
                                                            from tb_data_pk_akhir
                                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                            and kdbalai = '" . $rowbalai['id_balai'] . "') 
                                                            and kode_tahun='" . $tahun . "'
                                                            and id_indikator = (select id from tb_indikator_akhir where deleted='0' and kode_unique = '" . $temprumuskolom1[$jumlahtambah] . "')
                                                            and tahun = '" . $x . "'";
                                                        //echo $sqlskortahunsekarang;
                                                        //die;
                                                        $resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
                                                        $numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
                                                        if ($numskortahunsekarang > 0) {
                                                            while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
                                                                $target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
                                                            }
                                                        } else {
                                                            $target_tahun_berjalan = 0;
                                                        }
                                                    }
                                                    $isikolom1 = $isikolom1 + $target_tahun_berjalan;
                                                }
                                            } else if (strstr($rumuskolom1, "ambildatasebelum")) {
                                                $ambildatasebelum = 0;
                                                if ($x == $tahun_awal) {
                                                    array_push($arraykolom1, (float) $baselinekolom1);
                                                    $ambildatasebelum = ((float) $baselinekolom1);
                                                } else {
                                                    array_push($arraykolom1, (float) $rowdata['kolom1'] + (float) $arraykolom1[$s - 1]);
                                                    $ambildatasebelum = ((float) $rowdata['kolom1'] + (float) $arraykolom1[$s - 1]);
                                                }
                                                $cekrumuskolom1 = str_replace("ambildatasebelumbaseline1", $ambildatasebelum, $rumuskolom1);
                                                //echo $cekrumuskolom1."<br>";
                                                $tempformula_string5 = explode("/", $cekrumuskolom1);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom1 = str_replace("0/0", "0", $cekrumuskolom1);
                                                        $cekrumuskolom1 = str_replace("0/(0+0)", "0", $cekrumuskolom1);
                                                        $cekrumuskolom1 = str_replace("/0", "*0", $cekrumuskolom1);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom1 = ' . $cekrumuskolom1 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom1 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom1 = 0;');
                                                }
                                            } else if (strstr($rumuskolom1, "given")) {
                                                $cekrumuskolom1 = $rumuskolom1;
                                                for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                    $cekrumuskolom1 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom1);
                                                }
                                                $tempformula_string5 = explode("/", $cekrumuskolom1);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom1 = str_replace("0/0", "0", $cekrumuskolom1);
                                                        $cekrumuskolom1 = str_replace("0/(0+0)", "0", $cekrumuskolom1);
                                                        $cekrumuskolom1 = str_replace("/0", "*0", $cekrumuskolom1);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom1 = ' . $cekrumuskolom1 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom1 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom1 = 0;');
                                                }
                                            } else if (strstr($rumuskolom1, "isikolom")) {
                                                $cekrumuskolom1 = $rumuskolom1;
                                                for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                    $cekrumuskolom1 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom1);
                                                }
                                                $tempformula_string5 = explode("/", $cekrumuskolom1);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom1 = str_replace("0/0", "0", $cekrumuskolom1);
                                                        $cekrumuskolom1 = str_replace("0/(0+0)", "0", $cekrumuskolom1);
                                                        $cekrumuskolom1 = str_replace("/0", "*0", $cekrumuskolom1);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom1 = ' . $cekrumuskolom1 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom1 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom1 = 0;');
                                                }
                                                //echo $isikolom1."<br>";
                                            } else if (strstr($rumuskolom1, "totalkolom")) {
                                                $cekrumuskolom1 = $rumuskolom1;
                                                for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                    $cekrumuskolom1 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom1);
                                                }
                                                $tempformula_string5 = explode("/", $cekrumuskolom1);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom1 = str_replace("0/0", "0", $cekrumuskolom1);
                                                        $cekrumuskolom1 = str_replace("0/(0+0)", "0", $cekrumuskolom1);
                                                        $cekrumuskolom1 = str_replace("/0", "*0", $cekrumuskolom1);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom1 = ' . $cekrumuskolom1 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom1 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom1 = 0;');
                                                }
                                                //echo $isikolom1."<br>";
                                            } else {
                                                $isikolom1 = (float) $rowdata['kolom1'];
                                            }
                                            $totalkolom1 = $totalkolom1 + (float) $isikolom1;
                                            array_push($arrayminuskolom1, (float) $isikolom1);
                                            #kolom1

                                            #kolom2
                                            if (strstr($rumuskolom2, "IKSK")) {
                                                $temprumuskolom2 = explode("+", $rumuskolom2);
                                                for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom2); $jumlahtambah++) {
                                                    $sqlcekhitungan = "select id,hitungan_pk from tb_indikator_akhir where deleted = '0'
                                                    and kode_unique = '" . $temprumuskolom2[$jumlahtambah] . "'";
                                                    $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                                    while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                        $hitungan_pkdetail = $rowcekhitungan['hitungan_pk'];
                                                    }
                                                    if ($hitungan_pkdetail == '1') {
                                                        $sqlskortahunsekarang = "select sum(target) as target
                                                            from tb_data_pk_akhir
                                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                            and kdbalai = '" . $rowbalai['id_balai'] . "') 
                                                            and kode_tahun='" . $tahun . "'
                                                            and id_indikator = (select id from tb_indikator_akhir where deleted='0' and kode_unique = '" . $temprumuskolom2[$jumlahtambah] . "')
                                                            and tahun = '" . $x . "'";
                                                        //echo $sqlskortahunsekarang;
                                                        $resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
                                                        $numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
                                                        if ($numskortahunsekarang > 0) {
                                                            while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
                                                                $target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
                                                            }
                                                        } else {
                                                            $target_tahun_berjalan = 0;
                                                        }
                                                    }
                                                    $isikolom2 = $isikolom2 + $target_tahun_berjalan;
                                                }
                                            } else if (strstr($rumuskolom2, "ambildatasebelum")) {
                                                $ambildatasebelum = 0;
                                                if ($x == $tahun_awal) {
                                                    array_push($arraykolom2, (float) $baselinekolom2);
                                                    $ambildatasebelum = ((float) $baselinekolom2);
                                                } else {
                                                    array_push($arraykolom2, (float) $rowdata['kolom2'] + (float) $arraykolom2[$s - 1]);
                                                    $ambildatasebelum = ((float) $rowdata['kolom2'] + (float) $arraykolom2[$s - 1]);
                                                }
                                                $cekrumuskolom2 = str_replace("ambildatasebelumbaseline2", $ambildatasebelum, $rumuskolom2);
                                                //echo $cekrumuskolom2."<br>";
                                                $tempformula_string5 = explode("/", $cekrumuskolom2);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom2 = str_replace("0/0", "0", $cekrumuskolom2);
                                                        $cekrumuskolom2 = str_replace("0/(0+0)", "0", $cekrumuskolom2);
                                                        $cekrumuskolom2 = str_replace("/0", "*0", $cekrumuskolom2);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom2 = ' . $cekrumuskolom2 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom2 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom2 = 0;');
                                                }
                                            } else if (strstr($rumuskolom2, "given")) {
                                                $cekrumuskolom2 = $rumuskolom2;
                                                for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                    $cekrumuskolom2 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom2);
                                                }
                                                $tempformula_string5 = explode("/", $cekrumuskolom2);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom2 = str_replace("0/0", "0", $cekrumuskolom2);
                                                        $cekrumuskolom2 = str_replace("0/(0+0)", "0", $cekrumuskolom2);
                                                        $cekrumuskolom2 = str_replace("/0", "*0", $cekrumuskolom2);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom2 = ' . $cekrumuskolom2 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom2 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom2 = 0;');
                                                }
                                            } else if (strstr($rumuskolom2, "isikolom")) {
                                                $cekrumuskolom2 = $rumuskolom2;
                                                for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                    $cekrumuskolom2 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom2);
                                                }
                                                $tempformula_string5 = explode("/", $cekrumuskolom2);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom2 = str_replace("0/0", "0", $cekrumuskolom2);
                                                        $cekrumuskolom2 = str_replace("0/(0+0)", "0", $cekrumuskolom2);
                                                        $cekrumuskolom2 = str_replace("/0", "*0", $cekrumuskolom2);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom2 = ' . $cekrumuskolom2 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom2 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom2 = 0;');
                                                }
                                                //echo $isikolom2."<br>";
                                            } else if (strstr($rumuskolom2, "totalkolom")) {
                                                $cekrumuskolom2 = $rumuskolom2;
                                                for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                    $cekrumuskolom2 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom2);
                                                }
                                                $tempformula_string5 = explode("/", $cekrumuskolom2);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom2 = str_replace("0/0", "0", $cekrumuskolom2);
                                                        $cekrumuskolom2 = str_replace("0/(0+0)", "0", $cekrumuskolom2);
                                                        $cekrumuskolom2 = str_replace("/0", "*0", $cekrumuskolom2);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom2 = ' . $cekrumuskolom2 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom2 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom2 = 0;');
                                                }
                                                //echo $isikolom2."<br>";
                                            } else {
                                                $isikolom2 = (float) $rowdata['kolom2'];
                                            }
                                            $totalkolom2 = $totalkolom2 + (float) $isikolom2;
                                            array_push($arrayminuskolom2, (float) $isikolom2);
                                            #kolom2

                                            #kolom3
                                            if (strstr($rumuskolom3, "IKSK")) {
                                                $temprumuskolom3 = explode("+", $rumuskolom3);
                                                for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom3); $jumlahtambah++) {
                                                    $sqlcekhitungan = "select id,hitungan_pk from tb_indikator_akhir where deleted = '0'
                                                        and kode_unique = '" . $temprumuskolom3[$jumlahtambah] . "'";
                                                    $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                                    while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                        $hitungan_pkdetail = $rowcekhitungan['hitungan_pk'];
                                                    }
                                                    if ($hitungan_pkdetail == '1') {
                                                        $sqlskortahunsekarang = "select sum(target) as target
                                                            from tb_data_pk_akhir
                                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                            and kdbalai = '" . $rowbalai['id_balai'] . "') 
                                                            and kode_tahun='" . $tahun . "'
                                                            and id_indikator = (select id from tb_indikator_akhir where deleted='0' and kode_unique = '" . $temprumuskolom3[$jumlahtambah] . "')
                                                            and tahun = '" . $x . "'";
                                                        //echo $sqlskortahunsekarang;
                                                        $resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
                                                        $numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
                                                        if ($numskortahunsekarang > 0) {
                                                            while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
                                                                $target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
                                                            }
                                                        } else {
                                                            $target_tahun_berjalan = 0;
                                                        }
                                                    }
                                                    $isikolom3 = $isikolom3 + $target_tahun_berjalan;
                                                }
                                            } else if (strstr($rumuskolom3, "ambildatasebelum")) {
                                                $ambildatasebelum = 0;
                                                if ($x == $tahun_awal) {
                                                    array_push($arraykolom3, (float) $baselinekolom3);
                                                    $ambildatasebelum = ((float) $baselinekolom3);
                                                } else {
                                                    array_push($arraykolom3, (float) $rowdata['kolom3'] + (float) $arraykolom3[$s - 1]);
                                                    $ambildatasebelum = ((float) $rowdata['kolom3'] + (float) $arraykolom3[$s - 1]);
                                                }
                                                $cekrumuskolom3 = str_replace("ambildatasebelumbaseline3", $ambildatasebelum, $rumuskolom3);
                                                //echo $cekrumuskolom3."<br>";
                                                $tempformula_string5 = explode("/", $cekrumuskolom3);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom3 = str_replace("0/0", "0", $cekrumuskolom3);
                                                        $cekrumuskolom3 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
                                                        $cekrumuskolom3 = str_replace("/0", "*0", $cekrumuskolom3);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom3 = ' . $cekrumuskolom3 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom3 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom3 = 0;');
                                                }
                                            } else if (strstr($rumuskolom3, "given")) {
                                                $cekrumuskolom3 = $rumuskolom3;
                                                for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                    $cekrumuskolom3 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom3);
                                                }
                                                $tempformula_string5 = explode("/", $cekrumuskolom3);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom3 = str_replace("0/0", "0", $cekrumuskolom3);
                                                        $cekrumuskolom3 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
                                                        $cekrumuskolom3 = str_replace("/0", "*0", $cekrumuskolom3);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom3 = ' . $cekrumuskolom3 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom3 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom3 = 0;');
                                                }
                                            } else if (strstr($rumuskolom3, "isikolom")) {
                                                $cekrumuskolom3 = $rumuskolom3;
                                                for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                    $cekrumuskolom3 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom3);
                                                }
                                                $tempformula_string5 = explode("/", $cekrumuskolom3);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom3 = str_replace("0/0", "0", $cekrumuskolom3);
                                                        $cekrumuskolom3 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
                                                        $cekrumuskolom3 = str_replace("/0", "*0", $cekrumuskolom3);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom3 = ' . $cekrumuskolom3 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom3 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom3 = 0;');
                                                }
                                                //echo $isikolom3."<br>";
                                            } else if (strstr($rumuskolom3, "totalkolom")) {
                                                $cekrumuskolom3 = $rumuskolom3;
                                                for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                    $cekrumuskolom3 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom3);
                                                }
                                                $tempformula_string5 = explode("/", $cekrumuskolom3);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom3 = str_replace("0/0", "0", $cekrumuskolom3);
                                                        $cekrumuskolom3 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
                                                        $cekrumuskolom3 = str_replace("/0", "*0", $cekrumuskolom3);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom3 = ' . $cekrumuskolom3 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom3 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom3 = 0;');
                                                }
                                                //echo $isikolom3."<br>";
                                            } else {
                                                $isikolom3 = (float) $rowdata['kolom3'];
                                            }
                                            $totalkolom3 = $totalkolom3 + (float) $isikolom3;
                                            array_push($arrayminuskolom3, (float) $isikolom3);
                                            #kolom3

                                            #kolom4
                                            if (strstr($rumuskolom4, "IKSK")) {
                                                $temprumuskolom4 = explode("+", $rumuskolom4);
                                                for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom4); $jumlahtambah++) {
                                                    $sqlcekhitungan = "select id,hitungan_pk from tb_indikator_akhir where deleted = '0'
                                                        and kode_unique = '" . $temprumuskolom4[$jumlahtambah] . "'";
                                                    $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                                    while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                        $hitungan_pkdetail = $rowcekhitungan['hitungan_pk'];
                                                    }
                                                    if ($hitungan_pkdetail == '1') {
                                                        $sqlskortahunsekarang = "select sum(target) as target
                                                            from tb_data_pk_akhir
                                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                            and kdbalai = '" . $rowbalai['id_balai'] . "') 
                                                            and kode_tahun='" . $tahun . "'
                                                            and id_indikator = (select id from tb_indikator_akhir where deleted='0' and kode_unique = '" . $temprumuskolom4[$jumlahtambah] . "')
                                                            and tahun = '" . $x . "'";
                                                        //echo $sqlskortahunsekarang;
                                                        $resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
                                                        $numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
                                                        if ($numskortahunsekarang > 0) {
                                                            while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
                                                                $target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
                                                            }
                                                        } else {
                                                            $target_tahun_berjalan = 0;
                                                        }
                                                    }
                                                    $isikolom4 = $isikolom4 + $target_tahun_berjalan;
                                                }
                                            } else if (strstr($rumuskolom4, "ambildatasebelum")) {
                                                $ambildatasebelum = 0;
                                                if ($x == $tahun_awal) {
                                                    array_push($arraykolom4, (float) $baselinekolom4);
                                                    $ambildatasebelum = ((float) $baselinekolom4);
                                                } else {
                                                    array_push($arraykolom4, (float) $rowdata['kolom4'] + (float) $arraykolom4[$s - 1]);
                                                    $ambildatasebelum = ((float) $rowdata['kolom4'] + (float) $arraykolom4[$s - 1]);
                                                }
                                                $cekrumuskolom4 = str_replace("ambildatasebelumbaseline4", $ambildatasebelum, $rumuskolom4);
                                                //echo $cekrumuskolom4."<br>";
                                                $tempformula_string5 = explode("/", $cekrumuskolom4);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom4 = str_replace("0/0", "0", $cekrumuskolom4);
                                                        $cekrumuskolom4 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
                                                        $cekrumuskolom4 = str_replace("/0", "*0", $cekrumuskolom4);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom4 = ' . $cekrumuskolom4 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom4 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom4 = 0;');
                                                }
                                            } else if (strstr($rumuskolom4, "given")) {
                                                $cekrumuskolom4 = $rumuskolom4;
                                                for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                    $cekrumuskolom4 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom4);
                                                }
                                                try {
                                                    eval('$isikolom4 = ' . $cekrumuskolom4 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom4 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom4 = 0;');
                                                }
                                            } else if (strstr($rumuskolom4, "isikolom")) {
                                                $cekrumuskolom4 = $rumuskolom4;
                                                for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                    $cekrumuskolom4 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom4);
                                                }
                                                $tempformula_string5 = explode("/", $cekrumuskolom4);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom4 = str_replace("0/0", "0", $cekrumuskolom4);
                                                        $cekrumuskolom4 = str_replace("0/(0+0)", "0", $cekrumuskolom4);
                                                        $cekrumuskolom4 = str_replace("/0", "*0", $cekrumuskolom4);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom4 = ' . $cekrumuskolom4 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom4 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom4 = 0;');
                                                }
                                                //echo $isikolom4."<br>";
                                            } else if (strstr($rumuskolom4, "totalkolom")) {
                                                $cekrumuskolom4 = $rumuskolom4;
                                                for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                    $cekrumuskolom4 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom4);
                                                }
                                                $tempformula_string5 = explode("/", $cekrumuskolom4);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom4 = str_replace("0/0", "0", $cekrumuskolom4);
                                                        $cekrumuskolom4 = str_replace("0/(0+0)", "0", $cekrumuskolom4);
                                                        $cekrumuskolom4 = str_replace("/0", "*0", $cekrumuskolom4);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom4 = ' . $cekrumuskolom4 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom4 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom4 = 0;');
                                                }
                                                //echo $isikolom4."<br>";
                                            } else {
                                                $isikolom4 = (float) $rowdata['kolom4'];
                                            }
                                            $totalkolom4 = $totalkolom4 + (float) $isikolom4;
                                            array_push($arrayminuskolom4, (float) $isikolom4);
                                            #kolom4

                                            #kolom5
                                            if (strstr($rumuskolom5, "IKSK")) {
                                                $temprumuskolom5 = explode("+", $rumuskolom5);
                                                for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom5); $jumlahtambah++) {
                                                    $sqlcekhitungan = "select id,hitungan_pk from tb_indikator_akhir where deleted = '0'
                                                        and kode_unique = '" . $temprumuskolom5[$jumlahtambah] . "'";
                                                    $resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
                                                    while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
                                                        $hitungan_pkdetail = $rowcekhitungan['hitungan_pk'];
                                                    }
                                                    if ($hitungan_pkdetail == '1') {
                                                        $sqlskortahunsekarang = "select sum(target) as target
                                                            from tb_data_pk_akhir
                                                            where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
                                                            and kdbalai = '" . $rowbalai['id_balai'] . "') 
                                                            and kode_tahun='" . $tahun . "'
                                                            and id_indikator = (select id from tb_indikator_akhir where deleted='0' and kode_unique = '" . $temprumuskolom5[$jumlahtambah] . "')
                                                            and tahun = '" . $x . "'";
                                                        //echo $sqlskortahunsekarang;
                                                        $resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
                                                        $numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
                                                        if ($numskortahunsekarang > 0) {
                                                            while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
                                                                $target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
                                                            }
                                                        } else {
                                                            $target_tahun_berjalan = 0;
                                                        }
                                                    }
                                                    $isikolom5 = $isikolom5 + $target_tahun_berjalan;
                                                }
                                            } else if (strstr($rumuskolom5, "ambildatasebelum")) {
                                                $ambildatasebelum = 0;
                                                if ($x == $tahun_awal) {
                                                    array_push($arraykolom5, (float) $baselinekolom5);
                                                    $ambildatasebelum = ((float) $baselinekolom5);
                                                } else {
                                                    array_push($arraykolom5, (float) $rowdata['kolom5'] + (float) $arraykolom5[$s - 1]);
                                                    $ambildatasebelum = ((float) $rowdata['kolom5'] + (float) $arraykolom5[$s - 1]);
                                                }
                                                $cekrumuskolom5 = str_replace("ambildatasebelumbaseline5", $ambildatasebelum, $rumuskolom5);
                                                //echo $cekrumuskolom5."<br>";
                                                $tempformula_string5 = explode("/", $cekrumuskolom5);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
                                                        $cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
                                                        $cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom5 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom5 = 0;');
                                                }
                                            } else if (strstr($rumuskolom5, "ambildatamin")) {
                                                $cekrumuskolom5 = $rumuskolom5;
                                                if (strstr($rumuskolom5, "ambildatamin1")) {
                                                    $ambildatasebelum1 = 0;
                                                    if ($x == $tahun_awal) {
                                                        array_push($arrayisiminuskolom1, (float) $baselinekolom1);
                                                        $ambildatasebelum1 = ((float) $baselinekolom1);
                                                    } else {
                                                        array_push($arrayisiminuskolom1, (float) $arrayminuskolom1[$s - 1]);
                                                        $ambildatasebelum1 = ((float) $arrayminuskolom1[$s - 1]);
                                                    }
                                                    $cekrumuskolom5 = str_replace("ambildatamin1", $ambildatasebelum1, $cekrumuskolom5);
                                                    //echo $cekrumuskolom5."<br>";
                                                    $tempformula_string5 = explode("/", $cekrumuskolom5);
                                                    for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                        if ((float) $tempformula_string5[$ceknol] == 0) {
                                                            $cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
                                                            $cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
                                                            $cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
                                                        }
                                                    }
                                                }
                                                if (strstr($rumuskolom5, "ambildatamin2")) {
                                                    $ambildatasebelum2 = 0;
                                                    if ($x == $tahun_awal) {
                                                        array_push($arrayisiminuskolom2, (float) $baselinekolom2);
                                                        $ambildatasebelum2 = ((float) $baselinekolom2);
                                                    } else {
                                                        array_push($arrayisiminuskolom2, (float) $arrayminuskolom2[$s - 1]);
                                                        $ambildatasebelum2 = ((float) $arrayminuskolom2[$s - 1]);
                                                    }
                                                    $cekrumuskolom5 = str_replace("ambildatamin2", $ambildatasebelum2, $cekrumuskolom5);
                                                    //echo $cekrumuskolom5."<br>";
                                                    $tempformula_string5 = explode("/", $cekrumuskolom5);
                                                    for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                        if ((float) $tempformula_string5[$ceknol] == 0) {
                                                            $cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
                                                            $cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
                                                            $cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
                                                        }
                                                    }
                                                }
                                                if (strstr($rumuskolom5, "ambildatamin3")) {
                                                    $ambildatasebelum3 = 0;
                                                    if ($x == $tahun_awal) {
                                                        array_push($arrayisiminuskolom3, (float) $baselinekolom3);
                                                        $ambildatasebelum3 = ((float) $baselinekolom3);
                                                    } else {
                                                        array_push($arrayisiminuskolom3, (float) $arrayminuskolom3[$s - 1]);
                                                        $ambildatasebelum3 = ((float) $arrayminuskolom3[$s - 1]);
                                                    }
                                                    $cekrumuskolom5 = str_replace("ambildatamin3", $ambildatasebelum3, $cekrumuskolom5);
                                                    //echo $cekrumuskolom5."<br>";
                                                    $tempformula_string5 = explode("/", $cekrumuskolom5);
                                                    for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                        if ((float) $tempformula_string5[$ceknol] == 0) {
                                                            $cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
                                                            $cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
                                                            $cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
                                                        }
                                                    }
                                                }
                                                if (strstr($rumuskolom5, "ambildatamin4")) {
                                                    $ambildatasebelum4 = 0;
                                                    if ($x == $tahun_awal) {
                                                        array_push($arrayisiminuskolom4, (float) $baselinekolom4);
                                                        $ambildatasebelum4 = ((float) $baselinekolom4);
                                                    } else {
                                                        array_push($arrayisiminuskolom4, (float) $arrayminuskolom4[$s - 1]);
                                                        $ambildatasebelum4 = ((float) $arrayminuskolom4[$s - 1]);
                                                    }
                                                    $cekrumuskolom5 = str_replace("ambildatamin4", $ambildatasebelum4, $cekrumuskolom5);
                                                    //echo $cekrumuskolom5."<br>";
                                                    $tempformula_string5 = explode("/", $cekrumuskolom5);
                                                    for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                        if ((float) $tempformula_string5[$ceknol] == 0) {
                                                            $cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
                                                            $cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
                                                            $cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
                                                        }
                                                    }
                                                }
                                                if (strstr($rumuskolom5, "ambildatamin5")) {
                                                    $ambildatasebelum5 = 0;
                                                    if ($x == $tahun_awal) {
                                                        array_push($arrayisiminuskolom5, (float) $baselinekolom5);
                                                        $ambildatasebelum5 = ((float) $baselinekolom5);
                                                    } else {
                                                        array_push($arrayisiminuskolom5, (float) $arrayminuskolom5[$s - 1]);
                                                        $ambildatasebelum5 = ((float) $arrayminuskolom5[$s - 1]);
                                                    }
                                                    $cekrumuskolom5 = str_replace("ambildatamin5", $ambildatasebelum5, $cekrumuskolom5);
                                                    //echo $cekrumuskolom5."<br>";
                                                    $tempformula_string5 = explode("/", $cekrumuskolom5);
                                                    for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                        if ((float) $tempformula_string5[$ceknol] == 0) {
                                                            $cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
                                                            $cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
                                                            $cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
                                                        }
                                                    }
                                                }

                                                try {
                                                    eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom5 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom5 = 0;');
                                                }
                                            } else if (strstr($rumuskolom5, "given")) {
                                                $cekrumuskolom5 = $rumuskolom5;
                                                for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                    $cekrumuskolom5 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom5);
                                                }
                                                $tempformula_string5 = explode("/", $cekrumuskolom5);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
                                                        $cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
                                                        $cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
                                                    }
                                                }
                                                try {
                                                    eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom5 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom5 = 0;');
                                                }
                                            } else if (strstr($rumuskolom5, "isikolom")) {
                                                $cekrumuskolom5 = $rumuskolom5;
                                                for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                    $cekrumuskolom5 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom5);
                                                }
                                                $tempformula_string5 = explode("/", $cekrumuskolom5);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    //echo $tempformula_string5[$ceknol]."<br>";
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
                                                        $cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
                                                        $cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
                                                    }
                                                }
                                                //echo $cekrumuskolom5."<br>";
                                                try {
                                                    eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom5 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom5 = 0;');
                                                }
                                                //echo $isikolom5."<br>";
                                            } else if (strstr($rumuskolom5, "totalkolom")) {
                                                $cekrumuskolom5 = $rumuskolom5;
                                                for ($paramameter = 1; $paramameter < 10; $paramameter++) {
                                                    $cekrumuskolom5 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom5);
                                                }
                                                $tempformula_string5 = explode("/", $cekrumuskolom5);
                                                for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
                                                    //echo $tempformula_string5[$ceknol]."<br>";
                                                    if ((float) $tempformula_string5[$ceknol] == 0) {
                                                        $cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
                                                        $cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
                                                        $cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
                                                    }
                                                }
                                                //echo $cekrumuskolom5."<br>";
                                                try {
                                                    eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
                                                } catch (DivisionByZeroError $e) {
                                                    eval('$isikolom5 = 0;');
                                                } catch (ParseError $e) {
                                                    eval('$isikolom5 = 0;');
                                                }
                                                //echo $isikolom5."<br>";
                                            } else {
                                                $isikolom5 = (float) $rowdata['kolom5'];
                                            }
                                            $totalkolom5 = $totalkolom5 + (float) $isikolom5;
                                            array_push($arrayminuskolom5, (float) $isikolom5);
                                            #kolom5
                                            //echo $isikolom5."<br>";
                                            //echo $totalkolom1."<br>";
                                        }
                                    }
                                }
                            }
                            if ($rumuskolom5 <> '') {
                                $targetkumulatif = $isikolom5;
                            } else {
                                if ($rumuskolom4 <> '') {
                                    $targetkumulatif = $isikolom4;
                                } else {
                                    if ($rumuskolom3 <> '') {
                                        $targetkumulatif = $isikolom3;
                                    } else {
                                        if ($rumuskolom2 <> '') {
                                            $targetkumulatif = $isikolom2;
                                        } else {
                                            $targetkumulatif = $isikolom1;
                                        }
                                    }
                                }
                            }
                            $target = $targetkumulatif;
                        }
                        $s++;
                    }

                    if ($row['hitungan_pk'] == '2' || $row['hitungan_pk'] == '3') {
                        $sqlskor = "select nilai,rumus
                            from tb_data_pk_balai_akhir
                            where deleted = '0' and id_balai = '" . $rowbalai['id_balai'] . "' and kode_tahun='" . $tahun . "'
                            and tahun = '" . $tahunsekarang . "'
                            and id_indikator in (" . $row['grupid'] . ")";
                        //echo $sqlskor;
                        //die;
                        $resultskor = mysqli_query($link, $sqlskor);
                        $numskor = mysqli_num_rows($resultskor);
                        if ($numskor > 0) {
                            while ($rowskor = mysqli_fetch_assoc($resultskor)) {
                                $nilai = $rowskor['nilai'];
                                $rumus = $rowskor['rumus'];
                                $tempnilai = explode("|", $nilai);

                                for ($s = 0; $s < 10; $s++) {
                                    $rumus = str_replace("input" . $s, (float) ($tempnilai[$s]), $rumus);
                                }
                                //echo $rumus;
                                for ($s = 0; $s < 10; $s++) {
                                    $rumus = str_replace("nilai" . $s, (float) ($tempnilai[$s]), $rumus);
                                }

                                for ($s = 10; $s < 21; $s++) {
                                    $rumus = str_replace("entry" . $s, (float) ($tempnilai[$s]), $rumus);
                                }

                                for ($s = 10; $s < 21; $s++) {
                                    $rumus = str_replace("rumus" . $s, (float) ($tempnilai[$s]), $rumus);
                                }


                                //echo $rumus."<br>";
                                $rumus = str_replace("||", "|0|", $rumus);
                                $tempnilairumus = explode("|", $rumus);
                                $finalrumus = $tempnilairumus[count($tempnilairumus) - 1];

                                //$finalrumus = str_replace("/0", "*0", $finalrumus);
                                $tempformula_string5 = explode("/", $finalrumus);
                                for ($s = 0; $s < count($tempformula_string5); $s++) {
                                    if ((float) $tempformula_string5[$s] == 0) {
                                        $finalrumus = str_replace("0/0", "0", $finalrumus);
                                        $finalrumus = str_replace("0/(0+0)", "0", $finalrumus);
                                        $finalrumus = str_replace("/0", "*0", $finalrumus);
                                    }
                                }

                                //echo $finalrumus . "<br>" . $row['id'] . "<aa>" . $rumus . "<bb>";
                                //die;
                                try {
                                    eval('$nilaisekarang = ' . $finalrumus . ';');
                                } catch (DivisionByZeroError $e) {
                                    eval('$nilaisekarang = 0;');
                                } catch (ParseError $e) {
                                    eval('$nilaisekarang = 0;');
                                }
                                //die;
                                //echo $nilaisekarang."<br>";
                            }
                            $targetkumulatif = $nilaisekarang;
                            $target = $targetkumulatif;
                            //echo $target."<br>";
                        } else {
                            $targetkumulatif = 0;
                            $output_tahun_berjalan = 0;
                            $target_tahun_berjalan = 0;
                        }
                    }
                    $kinerja = 0;

                    if ($target == 0 && $capaian == 0) {
                        $kinerja = 0;
                    } else {
                        if ($target == 0) {
                            $kinerja = 0;
                        } else {
                            $tempcapaian = replace_comma(number_format($capaian, $belakangkoma, ",", "."));
                            $temptarget = replace_comma(number_format($target, $belakangkoma, ",", "."));
                            //$kinerja = $temptarget;
                            if ((float) $temptarget == 0) {
                                $kinerja = 0;
                            } else {
                                $kinerja = ((float) $tempcapaian / (float) $temptarget) * 100;
                            }
                            //$kinerja = $tempcapaian;
                        }
                    }

                    $myArraybalai[] = (object)
                    [
                        'id_balai' => $rowbalai['id_balai'],
                        'nama_balai' => $rowbalai['nama_kategori'],
                        'targetkumulatif' => number_format($target, $belakangkoma, ",", "."),
                        'output_tahun_berjalan' => $output_tahun_berjalan,
                        'target_tahun_berjalan' => $target,
                        'capaian' => number_format($capaian, $belakangkoma, ",", "."),
                        'kinerja' => number_format($kinerja, $belakangkoma, ",", "."),
                    ];
                }

                //IKSPSK
                $sqllevelIKSPSK = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
                    ,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
                    ,IF(a.satuan = '0', null, a.satuan) as satuan
                    ,IF(a.output = '0', null, a.output) as output
                    ,IF(a.outcome = '0', null, a.outcome) as outcome
                    , (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
                    , (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
                    , (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
                    , (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
                    ,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
                    and zz.kode_satker='" . $kodebalai . "') as terpilih
                    ,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk, a.belakangkoma
                    ,a.kolom1,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.rumuskolom1,a.rumuskolom2,a.rumuskolom3,a.rumuskolom4,a.rumuskolom5,a.isian_kolom
                    ,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
                    IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
                    IF(a.level = 'IKSP', 'levelsubsubkegiatan', 
                    IF(a.level = 'KEGIATAN', 'levelpaket', 
                    IF(a.level = 'SK', 'levelpekerjaan', 
                    IF(a.level = 'IKSK', 'levelakhir', 
                    IF(a.level = 'KRO', 'levelakhir', 
                    IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
                    from tb_indikator_akhir a 
                    left join master_unor b
                    on a.kdunor = b.id
                    where a.deleted='0' and a.id = '" . $row['id_parent'] . "'
                    order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
                //echo $sqllevelIKSPSK;
                //die;
                $resultlevelIKSPSK = mysqli_query($link, $sqllevelIKSPSK);
                while ($rowlevelIKSPSK = mysqli_fetch_assoc($resultlevelIKSPSK)) {
                    // KEGIATAN
                    $sqllevelKEGIATAN = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
                        ,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
                        ,IF(a.satuan = '0', null, a.satuan) as satuan
                        ,IF(a.output = '0', null, a.output) as output
                        ,IF(a.outcome = '0', null, a.outcome) as outcome
                        , (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
                        , (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
                        , (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
                        , (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
                        ,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
                        and zz.kode_satker='" . $kodebalai . "') as terpilih
                        ,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk, a.belakangkoma
                        ,a.kolom1,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.rumuskolom1,a.rumuskolom2,a.rumuskolom3,a.rumuskolom4,a.rumuskolom5,a.isian_kolom
                        ,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
                        IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
                        IF(a.level = 'IKSP', 'levelsubsubkegiatan', 
                        IF(a.level = 'KEGIATAN', 'levelpaket', 
                        IF(a.level = 'SK', 'levelpekerjaan', 
                        IF(a.level = 'IKSK', 'levelakhir', 
                        IF(a.level = 'KRO', 'levelakhir', 
                        IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
                        from tb_indikator_akhir a 
                        left join master_unor b
                        on a.kdunor = b.id
                        where a.deleted='0' and a.id = '" . $rowlevelIKSPSK['id_parent'] . "'
                        order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
                    //echo $sqllevelKEGIATAN;
                    //die;
                    $resultlevelKEGIATAN = mysqli_query($link, $sqllevelKEGIATAN);
                    while ($rowlevelKEGIATAN = mysqli_fetch_assoc($resultlevelKEGIATAN)) {
                        // PROGRAM
                        $sqllevelSASARANPROGRAM = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
                            ,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
                            ,IF(a.satuan = '0', null, a.satuan) as satuan
                            ,IF(a.output = '0', null, a.output) as output
                            ,IF(a.outcome = '0', null, a.outcome) as outcome
                            , (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
                            , (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
                            , (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
                            , (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
                            ,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
                            and zz.kode_satker='" . $kodebalai . "') as terpilih
                            ,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk, a.belakangkoma
                            ,a.kolom1,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.rumuskolom1,a.rumuskolom2,a.rumuskolom3,a.rumuskolom4,a.rumuskolom5,a.isian_kolom
                            ,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
                            IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
                            IF(a.level = 'IKSP', 'levelsubsubkegiatan', 
                            IF(a.level = 'KEGIATAN', 'levelpaket', 
                            IF(a.level = 'SK', 'levelpekerjaan', 
                            IF(a.level = 'IKSK', 'levelakhir', 
                            IF(a.level = 'KRO', 'levelakhir', 
                            IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
                            from tb_indikator_akhir a 
                            left join master_unor b
                            on a.kdunor = b.id
                            where a.deleted='0' and a.id = '" . $rowlevelKEGIATAN['id_parent'] . "'
                            order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
                        //echo $sqllevelSASARANPROGRAM;
                        //die;
                        $resultlevelSASARANPROGRAM = mysqli_query($link, $sqllevelSASARANPROGRAM);
                        while ($rowlevelSASARANPROGRAM = mysqli_fetch_assoc($resultlevelSASARANPROGRAM)) {

                            $sqllevelPROGRAM = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
                                ,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
                                ,IF(a.satuan = '0', null, a.satuan) as satuan
                                ,IF(a.output = '0', null, a.output) as output
                                ,IF(a.outcome = '0', null, a.outcome) as outcome
                                , (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
                                , (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
                                , (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
                                , (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
                                ,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
                                and zz.kode_satker='" . $kodebalai . "') as terpilih
                                ,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk, a.belakangkoma
                                ,a.kolom1,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.rumuskolom1,a.rumuskolom2,a.rumuskolom3,a.rumuskolom4,a.rumuskolom5,a.isian_kolom
                                ,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
                                IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
                                IF(a.level = 'IKSP', 'levelsubsubkegiatan', 
                                IF(a.level = 'KEGIATAN', 'levelpaket', 
                                IF(a.level = 'SK', 'levelpekerjaan', 
                                IF(a.level = 'IKSK', 'levelakhir', 
                                IF(a.level = 'KRO', 'levelakhir', 
                                IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
                                from tb_indikator_akhir a 
                                left join master_unor b
                                on a.kdunor = b.id
                                where a.deleted='0' and a.id = '" . $rowlevelSASARANPROGRAM['id_parent'] . "'
                                order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
                            //echo $sqllevelPROGRAM;
                            //die;
                            $resultlevelPROGRAM = mysqli_query($link, $sqllevelPROGRAM);
                            while ($rowlevelPROGRAM = mysqli_fetch_assoc($resultlevelPROGRAM)) {
                                $searchValue = (float) $rowlevelPROGRAM['id'];
                                $found = false;
                                foreach ($myArray as $obj) {
                                    if (isset($obj->id) && $obj->id === $searchValue) {
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $myArray[] = (object)
                                    [
                                        'id' => (float) $rowlevelPROGRAM['id'],
                                        'tahun' => (float) $rowlevelPROGRAM['tahun'],
                                        'id_parent' => $rowlevelPROGRAM['id_parent'],
                                        'kdunor' => $rowlevelPROGRAM['kdunor'],
                                        'nama_unor' => $rowlevelPROGRAM['nama_unor'],
                                        'urutlevel' => (float) $rowlevelPROGRAM['urutlevel'],
                                        'level' => $rowlevelPROGRAM['level'],
                                        'kode' => $rowlevelPROGRAM['kode'],
                                        'kode_unique' => $rowlevelPROGRAM['kode_unique'],
                                        'textindikator' => $rowlevelPROGRAM['textindikator'],
                                        'satuan' => $rowlevelPROGRAM['satuan'],
                                        'namasatuan' => $rowlevelPROGRAM['namasatuan'],
                                        'output' => $rowlevelPROGRAM['output'],
                                        'namaoutput' => $rowlevelPROGRAM['namaoutput'],
                                        'outcome' => $rowlevelPROGRAM['outcome'],
                                        'namaoutcome' => $rowlevelPROGRAM['namaoutcome'],
                                        'penanggungjawab' => $rowlevelPROGRAM['penanggungjawab'],
                                        'pelaksana' => $rowlevelPROGRAM['pelaksana'],
                                        'pelaksanacode' => $rowlevelPROGRAM['pelaksanacode'],
                                        'pelaksanalabel' => $rowlevelPROGRAM['pelaksanalabel'],
                                        'target' => $rowlevelPROGRAM['target'],
                                        'urut' => (float) $rowlevelPROGRAM['urut'],
                                        'hitungan_pk' => $rowlevelPROGRAM['hitungan_pk'],
                                        'namahitungan_pk' => $rowlevelPROGRAM['namahitungan_pk'],
                                        'kolom1' => $rowlevelPROGRAM['kolom1'],
                                        'kolom2' => $rowlevelPROGRAM['kolom2'],
                                        'kolom3' => $rowlevelPROGRAM['kolom3'],
                                        'kolom4' => $rowlevelPROGRAM['kolom4'],
                                        'kolom5' => $rowlevelPROGRAM['kolom5'],
                                        'rumuskolom1' => $rowlevelPROGRAM['rumuskolom1'],
                                        'rumuskolom2' => $rowlevelPROGRAM['rumuskolom2'],
                                        'rumuskolom3' => $rowlevelPROGRAM['rumuskolom3'],
                                        'rumuskolom4' => $rowlevelPROGRAM['rumuskolom4'],
                                        'rumuskolom5' => $rowlevelPROGRAM['rumuskolom5'],
                                        'isian_kolom' => $rowlevelPROGRAM['isian_kolom'],
                                        'terpilih' => $rowlevelPROGRAM['terpilih'],
                                        'targetkumulatif' => 0,
                                        'output_tahun_berjalan' => 0,
                                        'target_tahun_berjalan' => 0,
                                        'class' => $rowlevelPROGRAM['class'],
                                    ];
                                }
                                $sqllevelINDIKATORSASARANPROGRAM = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
                                    ,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
                                    ,IF(a.satuan = '0', null, a.satuan) as satuan
                                    ,IF(a.output = '0', null, a.output) as output
                                    ,IF(a.outcome = '0', null, a.outcome) as outcome
                                    , (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
                                    , (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
                                    , (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
                                    , (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
                                    ,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
                                    and zz.kode_satker='" . $kodebalai . "') as terpilih
                                    ,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk, a.belakangkoma
                                    ,a.kolom1,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.rumuskolom1,a.rumuskolom2,a.rumuskolom3,a.rumuskolom4,a.rumuskolom5,a.isian_kolom
                                    ,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
                                    IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
                                    IF(a.level = 'IKSP', 'levelsubsubkegiatan', 
                                    IF(a.level = 'KEGIATAN', 'levelpaket', 
                                    IF(a.level = 'SK', 'levelpekerjaan', 
                                    IF(a.level = 'IKSK', 'levelakhir', 
                                    IF(a.level = 'KRO', 'levelakhir', 
                                    IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
                                    from tb_indikator_akhir a 
                                    left join master_unor b
                                    on a.kdunor = b.id
                                    where a.deleted='0' and a.id_parent = '" . $rowlevelSASARANPROGRAM['id'] . "'
                                    and a.urutlevel = '6' and a.pelaksana = '32'
                                    order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
                            }

                            $searchValue = (float) $rowlevelSASARANPROGRAM['id'];
                            $found = false;
                            foreach ($myArray as $obj) {
                                if (isset($obj->id) && $obj->id === $searchValue) {
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) {
                                $myArray[] = (object)
                                [
                                    'id' => (float) $rowlevelSASARANPROGRAM['id'],
                                    'tahun' => (float) $rowlevelSASARANPROGRAM['tahun'],
                                    'id_parent' => $rowlevelSASARANPROGRAM['id_parent'],
                                    'kdunor' => $rowlevelSASARANPROGRAM['kdunor'],
                                    'nama_unor' => $rowlevelSASARANPROGRAM['nama_unor'],
                                    'urutlevel' => (float) $rowlevelSASARANPROGRAM['urutlevel'],
                                    'level' => $rowlevelSASARANPROGRAM['level'],
                                    'kode' => $rowlevelSASARANPROGRAM['kode'],
                                    'kode_unique' => $rowlevelSASARANPROGRAM['kode_unique'],
                                    'textindikator' => $rowlevelSASARANPROGRAM['textindikator'],
                                    'satuan' => $rowlevelSASARANPROGRAM['satuan'],
                                    'namasatuan' => $rowlevelSASARANPROGRAM['namasatuan'],
                                    'output' => $rowlevelSASARANPROGRAM['output'],
                                    'namaoutput' => $rowlevelSASARANPROGRAM['namaoutput'],
                                    'outcome' => $rowlevelSASARANPROGRAM['outcome'],
                                    'namaoutcome' => $rowlevelSASARANPROGRAM['namaoutcome'],
                                    'penanggungjawab' => $rowlevelSASARANPROGRAM['penanggungjawab'],
                                    'pelaksana' => $rowlevelSASARANPROGRAM['pelaksana'],
                                    'pelaksanacode' => $rowlevelSASARANPROGRAM['pelaksanacode'],
                                    'pelaksanalabel' => $rowlevelSASARANPROGRAM['pelaksanalabel'],
                                    'target' => $rowlevelSASARANPROGRAM['target'],
                                    'urut' => (float) $rowlevelSASARANPROGRAM['urut'],
                                    'hitungan_pk' => $rowlevelSASARANPROGRAM['hitungan_pk'],
                                    'namahitungan_pk' => $rowlevelSASARANPROGRAM['namahitungan_pk'],
                                    'kolom1' => $rowlevelSASARANPROGRAM['kolom1'],
                                    'kolom2' => $rowlevelSASARANPROGRAM['kolom2'],
                                    'kolom3' => $rowlevelSASARANPROGRAM['kolom3'],
                                    'kolom4' => $rowlevelSASARANPROGRAM['kolom4'],
                                    'rumuskolom1' => $rowlevelSASARANPROGRAM['rumuskolom1'],
                                    'rumuskolom2' => $rowlevelSASARANPROGRAM['rumuskolom2'],
                                    'rumuskolom3' => $rowlevelSASARANPROGRAM['rumuskolom3'],
                                    'rumuskolom4' => $rowlevelSASARANPROGRAM['rumuskolom4'],
                                    'isian_kolom' => $rowlevelSASARANPROGRAM['isian_kolom'],
                                    'terpilih' => $rowlevelSASARANPROGRAM['terpilih'],
                                    'targetkumulatif' => 0,
                                    'output_tahun_berjalan' => 0,
                                    'target_tahun_berjalan' => 0,
                                    'class' => $rowlevelSASARANPROGRAM['class'],
                                ];

                                $resultlevelINDIKATORSASARANPROGRAM = mysqli_query($link, $sqllevelINDIKATORSASARANPROGRAM);
                                while ($rowlevelINDIKATORSASARANPROGRAM = mysqli_fetch_assoc($resultlevelINDIKATORSASARANPROGRAM)) {
                                    $myArray[] = (object)
                                    [
                                        'id' => (float) $rowlevelINDIKATORSASARANPROGRAM['id'],
                                        'tahun' => (float) $rowlevelINDIKATORSASARANPROGRAM['tahun'],
                                        'id_parent' => $rowlevelINDIKATORSASARANPROGRAM['id_parent'],
                                        'kdunor' => $rowlevelINDIKATORSASARANPROGRAM['kdunor'],
                                        'nama_unor' => $rowlevelINDIKATORSASARANPROGRAM['nama_unor'],
                                        'urutlevel' => (float) $rowlevelINDIKATORSASARANPROGRAM['urutlevel'],
                                        'level' => $rowlevelINDIKATORSASARANPROGRAM['level'],
                                        'kode' => $rowlevelINDIKATORSASARANPROGRAM['kode'],
                                        'kode_unique' => $rowlevelINDIKATORSASARANPROGRAM['kode_unique'],
                                        'textindikator' => $rowlevelINDIKATORSASARANPROGRAM['textindikator'],
                                        'satuan' => $rowlevelINDIKATORSASARANPROGRAM['satuan'],
                                        'namasatuan' => $rowlevelINDIKATORSASARANPROGRAM['namasatuan'],
                                        'output' => $rowlevelINDIKATORSASARANPROGRAM['output'],
                                        'namaoutput' => $rowlevelINDIKATORSASARANPROGRAM['namaoutput'],
                                        'outcome' => $rowlevelINDIKATORSASARANPROGRAM['outcome'],
                                        'namaoutcome' => $rowlevelINDIKATORSASARANPROGRAM['namaoutcome'],
                                        'penanggungjawab' => $rowlevelINDIKATORSASARANPROGRAM['penanggungjawab'],
                                        'pelaksana' => $rowlevelINDIKATORSASARANPROGRAM['pelaksana'],
                                        'pelaksanacode' => $rowlevelINDIKATORSASARANPROGRAM['pelaksanacode'],
                                        'pelaksanalabel' => $rowlevelINDIKATORSASARANPROGRAM['pelaksanalabel'],
                                        'target' => $rowlevelINDIKATORSASARANPROGRAM['target'],
                                        'urut' => (float) $rowlevelINDIKATORSASARANPROGRAM['urut'],
                                        'hitungan_pk' => $rowlevelINDIKATORSASARANPROGRAM['hitungan_pk'],
                                        'namahitungan_pk' => $rowlevelINDIKATORSASARANPROGRAM['namahitungan_pk'],
                                        'kolom1' => $rowlevelINDIKATORSASARANPROGRAM['kolom1'],
                                        'kolom2' => $rowlevelINDIKATORSASARANPROGRAM['kolom2'],
                                        'kolom3' => $rowlevelINDIKATORSASARANPROGRAM['kolom3'],
                                        'kolom4' => $rowlevelINDIKATORSASARANPROGRAM['kolom4'],
                                        'rumuskolom1' => $rowlevelINDIKATORSASARANPROGRAM['rumuskolom1'],
                                        'rumuskolom2' => $rowlevelINDIKATORSASARANPROGRAM['rumuskolom2'],
                                        'rumuskolom3' => $rowlevelINDIKATORSASARANPROGRAM['rumuskolom3'],
                                        'rumuskolom4' => $rowlevelINDIKATORSASARANPROGRAM['rumuskolom4'],
                                        'isian_kolom' => $rowlevelINDIKATORSASARANPROGRAM['isian_kolom'],
                                        'terpilih' => $rowlevelINDIKATORSASARANPROGRAM['terpilih'],
                                        'targetkumulatif' => replace_dot_koma($target),
                                        'output_tahun_berjalan' => replace_dot_koma($output_tahun_berjalan),
                                        'target_tahun_berjalan' => replace_dot_koma($target_tahun_berjalan),
                                        'class' => $rowlevelINDIKATORSASARANPROGRAM['class'],
                                    ];
                                }
                                //echo $sqllevelINDIKATORSASARANPROGRAM;
                                //die;
                            }
                        }
                        // PROGRAM
                        $searchValue = (float) $rowlevelKEGIATAN['id'];
                        $found = false;
                        foreach ($myArray as $obj) {
                            if (isset($obj->id) && $obj->id === $searchValue) {
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $myArray[] = (object)
                            [
                                'id' => (float) $rowlevelKEGIATAN['id'],
                                'tahun' => (float) $rowlevelKEGIATAN['tahun'],
                                'id_parent' => $rowlevelKEGIATAN['id_parent'],
                                'kdunor' => $rowlevelKEGIATAN['kdunor'],
                                'nama_unor' => $rowlevelKEGIATAN['nama_unor'],
                                'urutlevel' => (float) $rowlevelKEGIATAN['urutlevel'],
                                'level' => $rowlevelKEGIATAN['level'],
                                'kode' => $rowlevelKEGIATAN['kode'],
                                'kode_unique' => $rowlevelKEGIATAN['kode_unique'],
                                'textindikator' => $rowlevelKEGIATAN['textindikator'],
                                'satuan' => $rowlevelKEGIATAN['satuan'],
                                'namasatuan' => $rowlevelKEGIATAN['namasatuan'],
                                'output' => $rowlevelKEGIATAN['output'],
                                'namaoutput' => $rowlevelKEGIATAN['namaoutput'],
                                'outcome' => $rowlevelKEGIATAN['outcome'],
                                'namaoutcome' => $rowlevelKEGIATAN['namaoutcome'],
                                'penanggungjawab' => $rowlevelKEGIATAN['penanggungjawab'],
                                'pelaksana' => $rowlevelKEGIATAN['pelaksana'],
                                'pelaksanacode' => $rowlevelKEGIATAN['pelaksanacode'],
                                'pelaksanalabel' => $rowlevelKEGIATAN['pelaksanalabel'],
                                'target' => $rowlevelKEGIATAN['target'],
                                'urut' => (float) $rowlevelKEGIATAN['urut'],
                                'hitungan_pk' => $rowlevelKEGIATAN['hitungan_pk'],
                                'namahitungan_pk' => $rowlevelKEGIATAN['namahitungan_pk'],
                                'kolom1' => $rowlevelKEGIATAN['kolom1'],
                                'kolom2' => $rowlevelKEGIATAN['kolom2'],
                                'kolom3' => $rowlevelKEGIATAN['kolom3'],
                                'kolom4' => $rowlevelKEGIATAN['kolom4'],
                                'rumuskolom1' => $rowlevelKEGIATAN['rumuskolom1'],
                                'rumuskolom2' => $rowlevelKEGIATAN['rumuskolom2'],
                                'rumuskolom3' => $rowlevelKEGIATAN['rumuskolom3'],
                                'rumuskolom4' => $rowlevelKEGIATAN['rumuskolom4'],
                                'isian_kolom' => $rowlevelKEGIATAN['isian_kolom'],
                                'terpilih' => $rowlevelKEGIATAN['terpilih'],
                                'targetkumulatif' => 0,
                                'output_tahun_berjalan' => 0,
                                'target_tahun_berjalan' => 0,
                                'class' => $rowlevelKEGIATAN['class'],
                            ];
                        }
                    }
                    // KEGIATAN

                    $searchValue = (float) $rowlevelIKSPSK['id'];
                    $found = false;
                    foreach ($myArray as $obj) {
                        if (isset($obj->id) && $obj->id === $searchValue) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $myArray[] = (object)
                        [
                            'id' => (float) $rowlevelIKSPSK['id'],
                            'tahun' => (float) $rowlevelIKSPSK['tahun'],
                            'id_parent' => $rowlevelIKSPSK['id_parent'],
                            'kdunor' => $rowlevelIKSPSK['kdunor'],
                            'nama_unor' => $rowlevelIKSPSK['nama_unor'],
                            'urutlevel' => (float) $rowlevelIKSPSK['urutlevel'],
                            'level' => $rowlevelIKSPSK['level'],
                            'kode' => $rowlevelIKSPSK['kode'],
                            'kode_unique' => $rowlevelIKSPSK['kode_unique'],
                            'textindikator' => $rowlevelIKSPSK['textindikator'],
                            'satuan' => $rowlevelIKSPSK['satuan'],
                            'namasatuan' => $rowlevelIKSPSK['namasatuan'],
                            'output' => $rowlevelIKSPSK['output'],
                            'namaoutput' => $rowlevelIKSPSK['namaoutput'],
                            'outcome' => $rowlevelIKSPSK['outcome'],
                            'namaoutcome' => $rowlevelIKSPSK['namaoutcome'],
                            'penanggungjawab' => $rowlevelIKSPSK['penanggungjawab'],
                            'pelaksana' => $rowlevelIKSPSK['pelaksana'],
                            'pelaksanacode' => $rowlevelIKSPSK['pelaksanacode'],
                            'pelaksanalabel' => $rowlevelIKSPSK['pelaksanalabel'],
                            'target' => $rowlevelIKSPSK['target'],
                            'urut' => (float) $rowlevelIKSPSK['urut'],
                            'hitungan_pk' => $rowlevelIKSPSK['hitungan_pk'],
                            'namahitungan_pk' => $rowlevelIKSPSK['namahitungan_pk'],
                            'kolom1' => $rowlevelIKSPSK['kolom1'],
                            'kolom2' => $rowlevelIKSPSK['kolom2'],
                            'kolom3' => $rowlevelIKSPSK['kolom3'],
                            'kolom4' => $rowlevelIKSPSK['kolom4'],
                            'rumuskolom1' => $rowlevelIKSPSK['rumuskolom1'],
                            'rumuskolom2' => $rowlevelIKSPSK['rumuskolom2'],
                            'rumuskolom3' => $rowlevelIKSPSK['rumuskolom3'],
                            'rumuskolom4' => $rowlevelIKSPSK['rumuskolom4'],
                            'isian_kolom' => $rowlevelIKSPSK['isian_kolom'],
                            'terpilih' => $rowlevelIKSPSK['terpilih'],
                            'targetkumulatif' => 0,
                            'output_tahun_berjalan' => 0,
                            'target_tahun_berjalan' => 0,
                            'class' => $rowlevelIKSPSK['class'],
                        ];
                    }
                }
                //IKSPSK
                $myArray[] = (object)
                [
                    'id' => (float) $row['id'],
                    'tahun' => (float) $row['tahun'],
                    'id_parent' => $row['id_parent'],
                    'kdunor' => $row['kdunor'],
                    'nama_unor' => $row['nama_unor'],
                    'urutlevel' => (float) $row['urutlevel'],
                    'level' => $row['level'],
                    'kode' => $row['kode'],
                    'kode_unique' => $row['kode_unique'],
                    'textindikator' => $row['textindikator'],
                    'satuan' => $row['satuan'],
                    'namasatuan' => $row['namasatuan'],
                    'output' => $row['output'],
                    'namaoutput' => $row['namaoutput'],
                    'outcome' => $row['outcome'],
                    'namaoutcome' => $row['namaoutcome'],
                    'penanggungjawab' => $row['penanggungjawab'],
                    'pelaksana' => $row['pelaksana'],
                    'pelaksanacode' => $row['pelaksanacode'],
                    'pelaksanalabel' => $row['pelaksanalabel'],
                    'target' => $row['target'],
                    'urut' => (float) $row['urut'],
                    'hitungan_pk' => $row['hitungan_pk'],
                    'namahitungan_pk' => $row['namahitungan_pk'],
                    'kolom1' => $row['kolom1'],
                    'kolom2' => $row['kolom2'],
                    'kolom3' => $row['kolom3'],
                    'kolom4' => $row['kolom4'],
                    'kolom5' => $row['kolom5'],
                    'rumuskolom1' => $row['rumuskolom1'],
                    'rumuskolom2' => $row['rumuskolom2'],
                    'rumuskolom3' => $row['rumuskolom3'],
                    'rumuskolom4' => $row['rumuskolom4'],
                    'rumuskolom5' => $row['rumuskolom5'],
                    'isian_kolom' => $row['isian_kolom'],
                    'terpilih' => $row['terpilih'],
                    'class' => $row['class'],
                    'balai' => $myArraybalai,
                ];
            }
        }
    }

    $sheet->mergeCells('A2:G2');
    $sheet->setCellValue('A2', 'Resume Capaian Balai ');
    $sheet->getStyle('A2')->getFont()->setBold(true);

    $sheet->mergeCells('A3:A4');
    $sheet->setCellValue('A3', 'TIPE');
    $sheet->getStyle('A3:A4')->getFont()->setBold(true);
    $sheet->getStyle('A3:A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A3:A4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A3:A4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('A3:A4')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('A3:A4')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);


    $sheet->mergeCells('B3:B4');
    $sheet->setCellValue('B3', 'PROGRAM/SASARAN PROGRAM/INDIKATOR SASARAN PROGRAM/KEGIATAN/SASARAN KEGIATAN/INDIKATOR SASARAN KEGIATAN');
    $sheet->getStyle('B3:B4')->getFont()->setBold(true);
    $sheet->getStyle('B3:B4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('B3:B4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('B3:B4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('B3:B4')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('B3:B4')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(100);
    $sheet->getStyle('B3:B4')->getAlignment()->setWrapText(true);

    $sheet->mergeCells('C3:D3');
    $sheet->setCellValue('C3', 'TARGET');
    $sheet->getStyle('C3:D3')->getFont()->setBold(true);
    $sheet->getStyle('C3:D3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('C3:D3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('C3:D3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('C3:D3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('C3:D3')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(40);

    $sheet->setCellValue('C4', 'Volume');
    $sheet->getStyle('C4')->getFont()->setBold(true);
    $sheet->getStyle('C4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('C4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('C4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('C4')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('C4')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);

    $sheet->setCellValue('D4', 'Satuan');
    $sheet->getStyle('D4')->getFont()->setBold(true);
    $sheet->getStyle('D4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('D4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('D4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('D4')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('D4')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);

    $sheet->mergeCells('E3:F3');
    $sheet->setCellValue('E3', 'CAPAIAN');
    $sheet->getStyle('E3:F3')->getFont()->setBold(true);
    $sheet->getStyle('E3:F3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E3:F3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('E3:F3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('E3:F3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('E3:F3')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(40);

    $sheet->setCellValue('E4', 'Volume');
    $sheet->getStyle('E4')->getFont()->setBold(true);
    $sheet->getStyle('E4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('E4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('E4')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('E4')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);

    $sheet->setCellValue('F4', 'Satuan');
    $sheet->getStyle('F4')->getFont()->setBold(true);
    $sheet->getStyle('F4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('F4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('F4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('F4')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('F4')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);

    $sheet->setCellValue('G3', 'KINERJA');
    $sheet->getStyle('G3')->getFont()->setBold(true);
    $sheet->getStyle('G3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('G3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('G3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('G3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('G3')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);

    $sheet->setCellValue('G4', '%');
    $sheet->getStyle('G4')->getFont()->setBold(true);
    $sheet->getStyle('G4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('G4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('G4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('G4')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('G4')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);

    $baris = 5;
    foreach ($myArray as $obj) {
        $sheet->setCellValue('A' . $baris, $obj->level . ' ' . $obj->kode);
        $sheet->getStyle('A' . $baris)->getFont()->setBold(true);
        $sheet->getStyle('A' . $baris)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $baris)->applyFromArray($styleRangeArray);

        $sheet->mergeCells('B' . $baris . ':G' . $baris);
        $sheet->setCellValue('B' . $baris, $obj->textindikator);
        $sheet->getStyle('B' . $baris . ':G' . $baris)->getFont()->setBold(true);
        $sheet->getStyle('B' . $baris . ':G' . $baris)->applyFromArray($styleRangeArray);
        $baris++;
        $urut = 1;
        foreach ($obj->balai as $balai) {
            $sheet->setCellValue('A' . $baris, $urut);
            $sheet->getStyle('A' . $baris)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A' . $baris)->applyFromArray($styleRangeArray);

            $sheet->setCellValue('B' . $baris, $balai->nama_balai);
            $sheet->getStyle('B' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('B' . $baris)->applyFromArray($styleRangeArray);

            $sheet->setCellValue('C' . $baris, replace_comma($balai->targetkumulatif));
            $sheet->getStyle('C' . $baris)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('C' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle('C' . $baris)->applyFromArray($styleRangeArray);

            $sheet->setCellValue('D' . $baris, html_entity_decode(str_replace("</sup>", "",str_replace("<sup>", "",$obj->namasatuan)), ENT_COMPAT, 'UTF-8'));
            $sheet->getStyle('D' . $baris)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('D' . $baris)->applyFromArray($styleRangeArray);

            $sheet->setCellValue('E' . $baris, replace_comma($balai->capaian));
            $sheet->getStyle('E' . $baris)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('E' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle('E' . $baris)->applyFromArray($styleRangeArray);

            $sheet->setCellValue('F' . $baris, html_entity_decode(str_replace("</sup>", "",str_replace("<sup>", "",$obj->namasatuan)), ENT_COMPAT, 'UTF-8'));
            $sheet->getStyle('F' . $baris)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('F' . $baris)->applyFromArray($styleRangeArray);

            $sheet->setCellValue('G' . $baris, replace_comma($balai->kinerja));
            $sheet->getStyle('G' . $baris)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('G' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle('G' . $baris)->applyFromArray($styleRangeArray);

            $urut++;
            $baris++;
        }
    }

    $writer = new Xlsx($spreadsheet);
    header("Content-Description: File Transfer");
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="my_spreadsheet.xlsx"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    header('Expires: 0');
    ob_end_clean();
    $writer->save('php://output');
    exit();
}

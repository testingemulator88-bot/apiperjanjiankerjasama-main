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

    $filterquery = "";
    $filterquerysatker = "";
    $id_indikator = fixup($_GET['id_indikator']);
    $kode_satker = fixup($_GET['kode_satker']);
    $kode_satkeremon = "";
    $id_satker = "";
    $sqlsatkeremon = "select kode_satker_pendek,kode_satker_old_pendek,level_piu from master_satker where kode_satker = '" . $kode_satker . "'";
    $resultsatkeremon = mysqli_query($link, $sqlsatkeremon);
    while ($rowsatkeremon = mysqli_fetch_assoc($resultsatkeremon)) {
        $kode_satkeremon = $rowsatkeremon['kode_satker_pendek'];
        $kode_satkeremon_old = $rowsatkeremon['kode_satker_old_pendek'];
        $id_satker = $rowsatkeremon['level_piu'];
    }

    if ($id_indikator <> '') {
        $filterquery = $filterquery . " and a.id = '" . $id_indikator . "'";
    }

    if ($kode_satker <> '') {
        $filterquerysatker = $filterquerysatker . " and b.kode_satker in ( select x.kode_satker from master_satker x where x.kode_satker = '" . $kode_satker . "')";
    }

    $myArray = array();
    $sql = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
        ,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
        ,IF(a.level = 'UNOR', b.nama_kategori, a.nama_awal) as textindikator_awal
        ,IF(a.level = 'UNOR', b.nama_kategori, a.nama_akhir) as textindikator_akhir
        ,IF(a.satuan = '0', null, a.satuan) as satuan
        ,IF(a.output = '0', null, a.output) as output
        ,IF(a.outcome = '0', null, a.outcome) as outcome
        ,IF(a.satuan_awal = '0', null, a.satuan_awal) as satuan_awal
        ,IF(a.output_awal = '0', null, a.output_awal) as output_awal
        ,IF(a.outcome_awal = '0', null, a.outcome_awal) as outcome_awal
        ,IF(a.satuan_akhir = '0', null, a.satuan_akhir) as satuan_akhir
        ,IF(a.output_akhir = '0', null, a.output_akhir) as output_akhir
        ,IF(a.outcome_akhir = '0', null, a.outcome_akhir) as outcome_akhir
        , (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
        , (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
        , (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
        , (select x.singkatan from master_satuan x where x.id=a.satuan_awal) as namasatuan_awal
        , (select x.singkatan from master_satuan x where x.id=a.output_awal) as namaoutput_awal
        , (select x.singkatan from master_satuan x where x.id=a.outcome_awal) as namaoutcome_awal
        , (select x.singkatan from master_satuan x where x.id=a.satuan_akhir) as namasatuan_akhir
        , (select x.singkatan from master_satuan x where x.id=a.output_akhir) as namaoutput_akhir
        , (select x.singkatan from master_satuan x where x.id=a.outcome_akhir) as namaoutcome_akhir
        , (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
        ,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
        ,a.kolom1,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.rumuskolom1,a.rumuskolom2,a.rumuskolom3,a.rumuskolom4,a.rumuskolom5,a.isian_kolom
        ,a.cetak,a.cetakcode,a.cetaklabel,a.verif,a.verifcode,a.veriflabel,a.verif2,a.verif2code,a.verif2label
        , a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
        ,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
        IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
        IF(a.level = 'ISP', 'levelsubsubkegiatan', 
        IF(a.level = 'KEGIATAN', 'levelpaket', 
        IF(a.level = 'SK', 'levelpekerjaan', 
        IF(a.level = 'IKSK', 'levelakhir', 
        IF(a.level = 'KRO', 'levelakhir2', 
        IF(a.level = 'RO', 'levelakhir3', 'levelakhir3'))))))))))) as class
        from tb_indikator_akhir a 
        left join master_unor b
        on a.kdunor = b.id
        where a.deleted='0'
        " . $filterquery . "
        order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
    //echo $sql;
    //die();
    $result = mysqli_query($link, $sql);
    $num = mysqli_num_rows($result);
    if ($num > 0) {
        if ($result) {
            $myArrayheader = array();
            while ($row = mysqli_fetch_assoc($result)) {
                $tempkolomkomponenparent = explode("|", $row['kolomkomponen']);
                for ($z = 0; $z < count($tempkolomkomponenparent); $z++) {
                    $myArrayheader[] = (object)[
                        'header' => $tempkolomkomponenparent[$z],
                    ];
                }
                $sqlceksatker = "select a.kode_satker,a.nama_satker 
                    , ifnull((select x.nilai from tb_data_pk_akhir x where x.deleted='0' and 
                    x.id_indikator='" . $id_indikator . "' and x.tahun=(select tahun
                    from master_pk where id='3') and x.kode_satker=a.kode_satker),0) as isiannilai
                    , ifnull((select x.volume from tb_data_pk_akhir x where x.deleted='0' and 
                    x.id_indikator='" . $id_indikator . "' and x.tahun=(select tahun
                    from master_pk where id='3') and x.kode_satker=a.kode_satker),0) as volume
                    , ifnull((select x.target from tb_data_pk_akhir x where x.deleted='0' and 
                    x.id_indikator='" . $id_indikator . "' and x.tahun=(select tahun
                    from master_pk where id='3') and x.kode_satker=a.kode_satker),0) as target
                    , ifnull((select x.capaian from tb_data_pk_akhir x where x.deleted='0' and 
                    x.id_indikator='" . $id_indikator . "' and x.tahun=(select tahun
                    from master_pk where id='3') and x.kode_satker=a.kode_satker),0) as capaian
                    , ifnull((select x.capaian_outcome from tb_data_pk_akhir x where x.deleted='0' and 
                    x.id_indikator='" . $id_indikator . "' and x.tahun=(select tahun
                    from master_pk where id='3') and x.kode_satker=a.kode_satker),0) as capaian_outcome
                    , ifnull((select SUBSTRING_INDEX(nilai, '|', -1) from tb_data_pk_akhir x where x.deleted='0' and 
                    x.id_indikator='" . $id_indikator . "' and x.tahun=(select tahun
                    from master_pk where id='3') and x.kode_satker=a.kode_satker),0) as nilai
                    , (select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator='" . $id_indikator . "'
                    and zz.deleted='0' and zz.kode_satker=a.kode_satker) as terpilih
                    from master_satker a
                    where a.deleted='0' and a.kode_satker in (select b.kode_satker
                    from tb_data_pk_akhir b where b.deleted='0' " . $filterquerysatker . "
                    and b.id_indikator='" . $id_indikator . "' and b.tahun=(select tahun
                    from master_pk where id='3'))
                    order by a.kode_satker";
                //echo $sqlceksatker;
                //die;
                $resultceksatker = mysqli_query($link, $sqlceksatker);
                while ($rowceksatker = mysqli_fetch_assoc($resultceksatker)) {
                    if ((float) replace_comma(number_format($rowceksatker['nilai'], $row['belakangkoma'], ",", ".")) == 0 && (float) replace_comma(number_format($rowceksatker['capaian'], $rowlevelIKSK['belakangkoma'], ",", ".")) == 0) {
                        $kinerja = 100;
                    } else {
                        if ((float) replace_comma(number_format($rowceksatker['nilai'], $row['belakangkoma'], ",", ".")) == 0) {
                            $kinerja = 0;
                        } else {
                            $kinerja = ((float) replace_comma(number_format($rowceksatker['capaian'], $row['belakangkoma'], ",", ".")) / (float) replace_comma(number_format($rowceksatker['nilai'], $rowlevelIKSK['belakangkoma'], ",", "."))) * 100;
                        }
                    }
                    $myArraytempisiannilai = array();
                    $kode_satker = $rowceksatker['kode_satker'];
                    $nama_satker = $rowceksatker['nama_satker'];
                    $volume = $rowceksatker['volume'];
                    $target = $rowceksatker['target'];
                    $capaian = $rowceksatker['capaian'];
                    $capaian_outcome = $rowceksatker['capaian_outcome'];
                    $nilai = $rowceksatker['nilai'];
                    $isiannilai = $rowceksatker['isiannilai'];
                    $tempisiannilai = explode("|", $isiannilai);
                    for ($z = 0; $z < count($tempisiannilai); $z++) {
                        $myArraytempisiannilai[] = (object)[
                            'isiannilai' => number_format($tempisiannilai[$z], $row['belakangkoma'], ",", "."),
                        ];
                    }
                }
                $myArrayemon = array();
                $sqlemon = "select a.id,a.tahun,a.kode_satker,a.kode_emon,a.nilai, a.rumus
                    ,x.kode,x.nmpaket,x.pgrupiah,x.vol,x.sat,a.latitude,a.longitude,a.outcome,a.target
                    ,a.skor_komponen_a,a.skor_komponen_b,a.skor_komponen_c,a.capaian,a.capaian_outcome
                    from tb_data_pk_emon_akhir a 
                    left join paket_pk_akhir x
                    on a.kode_emon = x.kode
                    where a.deleted='0' and a.tahun=(select tahun
                    from master_pk where id='3') and a.id_indikator = '" . $id_indikator . "'
                    and YEAR(x.tanggaldata)=(select tahun
                    from master_pk where id='3')
                    and a.kode_emon in (select z.kode from paket_pk_akhir z where YEAR(z.tanggaldata)=(select tahun
                    from master_pk where id='3')
                    and (z.kdsatker = '" . $kode_satkeremon . "' or z.kdsatker = '" . $kode_satkeremon_old . "'))
                    and a.kode_satker = '" . $kode_satker . "'";
                //echo $sqlemon;
                //die;
                $resultemon = mysqli_query($link, $sqlemon);
                while ($rowemon = mysqli_fetch_assoc($resultemon)) {
                    $myArraynilaiemon = array();
                    $capaiannya = number_format($rowemon['capaian'], $row['belakangkoma'], ",", ".");
                    $tempnilai = explode("|", $rowemon['nilai']);
                    $temprumus = explode("|", $rowemon['rumus']);
                    $tempkolomkomponen = explode("|", $row['kolomkomponen']);
                    for ($z = 0; $z < count($tempnilai); $z++) {
                        if (strstr($temprumus[$z], "input")) {
                            $nilai = (float) $tempnilai[$z];
                        } else if (strstr($temprumus[$z], "entry")) {
                            $nilai = (float) $tempnilai[$z];
                        } else {
                            $formula_string5 = $temprumus[$z];
                            for ($s = 0; $s < 10; $s++) {
                                $formula_string5 = str_replace("input" . $s, (float) ($tempnilai[$s]), $formula_string5);
                            }
                            //echo $rumus;
                            for ($s = 0; $s < 10; $s++) {
                                $formula_string5 = str_replace("nilai" . $s, (float) ($tempnilai[$s]), $formula_string5);
                            }

                            for ($s = 10; $s < 21; $s++) {
                                $formula_string5 = str_replace("entry" . $s, (float) ($tempnilai[$s]), $formula_string5);
                            }

                            for ($s = 10; $s < 21; $s++) {
                                $formula_string5 = str_replace("rumus" . $s, (float) ($tempnilai[$s]), $formula_string5);
                            }

                            //echo $formula_string5 . "<br>";
                            $formula_string5 = str_replace("0/0", "0", $formula_string5);
                            $formula_string5 = str_replace("0/(0+0)", "0", $formula_string5);
                            //$formula_string5 = str_replace("00", "0", $formula_string5);
                            $tempformula_string5 = explode("/", $formula_string5);
                            for ($s = 0; $s < count($tempformula_string5); $s++) {
                                if (strlen($tempformula_string5[$s] == 1) && $tempformula_string5[$s] == '0') {
                                    $formula_string5 = str_replace("0/0", "0", $formula_string5);
                                    $formula_string5 = str_replace("0/(0+0)", "0", $formula_string5);
                                    $formula_string5 = str_replace("/0", "*0", $formula_string5);
                                }
                            }


                            //echo $formula_string5 . "<br>";
                            //die;
                            try {
                                eval('$nilai = ' . $formula_string5 . ';');
                            } catch (DivisionByZeroError $e) {
                                eval('$nilai = 0;');
                            } catch (ParseError $e) {
                                eval('$nilai = 0;');
                            }

                            //$nilai = 1222;
                            //$disabled = true;
                        }
                        $myArraynilaiemon[] = (object)[
                            'header' => $tempkolomkomponen[$z],
                            'nilai' => number_format($nilai, $row['belakangkoma'], ",", "."),
                            'rumus' => $temprumus[$z],
                        ];
                    }
                    $myArrayemon[] = (object)[
                        'id' => (float) $rowemon['id'],
                        'tahun' => (float) $rowemon['tahun'],
                        'kode_satker' => $rowemon['kode_satker'],
                        'kode_emon' => $rowemon['kode_emon'],
                        'kode' => $rowemon['kode'],
                        'nmpaket' => $rowemon['nmpaket'],
                        'pgrupiah' => (float) $rowemon['pgrupiah'],
                        'vol' => $rowemon['vol'],
                        'sat' => $rowemon['sat'],
                        'latitude' => $rowemon['latitude'],
                        'longitude' => $rowemon['longitude'],
                        'nilai' => $myArraynilaiemon,
                        'capaian' => $capaiannya,
                    ];
                }
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
                    'textindikator_awal' => $row['textindikator_awal'],
                    'satuan_awal' => $row['satuan_awal'],
                    'namasatuan_awal' => $row['namasatuan_awal'],
                    'output_awal' => $row['output_awal'],
                    'namaoutput_awal' => $row['namaoutput_awal'],
                    'outcome_awal' => $row['outcome_awal'],
                    'namaoutcome_awal' => $row['namaoutcome_awal'],
                    'textindikator_akhir' => $row['textindikator_akhir'],
                    'satuan_akhir' => $row['satuan_akhir'],
                    'namasatuan_akhir' => $row['namasatuan_akhir'],
                    'output_akhir' => $row['output_akhir'],
                    'namaoutput_akhir' => $row['namaoutput_akhir'],
                    'outcome_akhir' => $row['outcome_akhir'],
                    'namaoutcome_akhir' => $row['namaoutcome_akhir'],
                    'penanggungjawab' => $row['penanggungjawab'],
                    'pelaksana' => $row['pelaksana'],
                    'pelaksanacode' => $row['pelaksanacode'],
                    'pelaksanalabel' => $row['pelaksanalabel'],
                    'cetak' => $row['cetak'],
                    'cetakcode' => $row['cetakcode'],
                    'cetaklabel' => $row['cetaklabel'],
                    'verif' => $row['verif'],
                    'verifcode' => $row['verifcode'],
                    'veriflabel' => $row['veriflabel'],
                    'verif2' => $row['verif2'],
                    'verif2code' => $row['verif2code'],
                    'verif2label' => $row['verif2label'],
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
                    'jumlahkomponen' => $row['jumlahkomponen'],
                    'kolomkomponen' => $row['kolomkomponen'],
                    'bobotkomponen' => $row['bobotkomponen'],
                    'rumuskomponen' => $row['rumuskomponen'],
                    'belakangkoma' => $row['belakangkoma'],
                    'class' => $row['class'],
                    'kode_satker' => $kode_satker,
                    'satker' => $nama_satker,
                    'isiannilai' => $myArraytempisiannilai,
                    'volume' => number_format($volume, $row['belakangkoma'], ",", "."),
                    'target' => number_format($target, $row['belakangkoma'], ",", "."),
                    'capaian' => number_format($capaian, $row['belakangkoma'], ",", "."),
                    'capaian_outcome' => number_format($capaian, $row['belakangkoma'], ",", "."),
                    'nilai' => number_format($nilai, $row['belakangkoma'], ",", "."),
                    'kinerja' => number_format($kinerja, 2, ",", "."),
                    'header' => $myArrayheader,
                    'emon' => $myArrayemon,
                ];
            }
        }
    }

    $sheet->mergeCells('A2:D2');
    $sheet->setCellValue('A2', 'Detail Indikator ');
    $sheet->getStyle('A2')->getFont()->setBold(true);

    $sheet->setCellValue('A3', 'Kode');
    $sheet->getStyle('A3')->getFont()->setBold(true);
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('A3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('A3')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(30);

    $sheet->setCellValue('B3', 'Paket Emon');
    $sheet->getStyle('B3')->getFont()->setBold(true);
    $sheet->getStyle('B3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('B3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('B3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('B3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('B3')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(100);

    $sheet->setCellValue('C3', 'Pagu');
    $sheet->getStyle('C3')->getFont()->setBold(true);
    $sheet->getStyle('C3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('C3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('C3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('C3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('C3')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);

    $sheet->setCellValue('D3', 'Volume Satuan');
    $sheet->getStyle('D3')->getFont()->setBold(true);
    $sheet->getStyle('D3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('D3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('D3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('D3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('D3')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);

    $huruf_lopping = 'E';
    foreach ($myArray as $obj) {
        foreach ($obj->header as $header) {
            $sheet->setCellValue($huruf_lopping . '3', $header->header);
            $sheet->getStyle($huruf_lopping . '3')->getFont()->setBold(true);
            $sheet->getStyle($huruf_lopping . '3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($huruf_lopping . '3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle($huruf_lopping . '3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
            $sheet->getStyle($huruf_lopping . '3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
            $sheet->getStyle($huruf_lopping . '3')->applyFromArray($styleRangeArray);
            $spreadsheet->getActiveSheet()->getColumnDimension($huruf_lopping)->setWidth(10);
            $sheet->getStyle($huruf_lopping . '3')->getAlignment()->setWrapText(true);
            $huruf_lopping++;
        }
    }

    $sheet->setCellValue($huruf_lopping . '3', 'Capaian (%)');
    $sheet->getStyle($huruf_lopping . '3')->getFont()->setBold(true);
    $sheet->getStyle($huruf_lopping . '3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle($huruf_lopping . '3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle($huruf_lopping . '3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle($huruf_lopping . '3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle($huruf_lopping . '3')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension($huruf_lopping)->setWidth(10);

    $baris = 4;
    foreach ($myArray as $obj) {
        $sheet->mergeCells('A' . $baris . ':D' . $baris);
        $sheet->setCellValue('A' . $baris, $obj->textindikator . ' ' . $obj->satker);
        $sheet->getStyle('A' . $baris . ':D' . $baris)->applyFromArray($styleRangeArray);
        $sheet->getStyle('A' . $baris . ':D' . $baris)->getFont()->setBold(true);
        $huruf_lopping = 'E';
        foreach ($obj->isiannilai as $isiannilai) {
            $sheet->setCellValue($huruf_lopping . $baris, replace_comma($isiannilai->isiannilai));
            $sheet->getStyle($huruf_lopping . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle($huruf_lopping . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle($huruf_lopping . $baris)->applyFromArray($styleRangeArray);
            $sheet->getStyle($huruf_lopping . $baris)->getFont()->setBold(true);
            $huruf_lopping++;
        }
        $sheet->setCellValue($huruf_lopping . $baris, replace_comma($obj->capaian));
        $sheet->getStyle($huruf_lopping . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle($huruf_lopping . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->getStyle($huruf_lopping . $baris)->applyFromArray($styleRangeArray);
        $sheet->getStyle($huruf_lopping . $baris)->getFont()->setBold(true);
        $baris++;
        foreach ($obj->emon as $emon) {
            $sheet->setCellValue('A' . $baris, $emon->kode);
            $sheet->getStyle('A' . $baris)->applyFromArray($styleRangeArray);
            $sheet->getStyle('A' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('B' . $baris, $emon->nmpaket);
            $sheet->getStyle('B' . $baris)->applyFromArray($styleRangeArray);
            $sheet->getStyle('B' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

            $sheet->setCellValue('C' . $baris, $emon->pgrupiah);
            $sheet->getStyle('C' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('C' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle('C' . $baris)->applyFromArray($styleRangeArray);

            $sheet->setCellValue('D' . $baris, $emon->vol . ' ' . $emon->sat);
            $sheet->getStyle('D' . $baris)->applyFromArray($styleRangeArray);
            $sheet->getStyle('D' . $baris)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $huruf_lopping = 'E';
            foreach ($emon->nilai as $nilai) {
                $sheet->setCellValue($huruf_lopping . $baris, replace_comma($nilai->nilai));
                $sheet->getStyle($huruf_lopping . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle($huruf_lopping . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle($huruf_lopping . $baris)->applyFromArray($styleRangeArray);
                $huruf_lopping++;
            }
            $sheet->setCellValue($huruf_lopping . $baris, replace_comma($emon->capaian));
            $sheet->getStyle($huruf_lopping . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle($huruf_lopping . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle($huruf_lopping . $baris)->applyFromArray($styleRangeArray);
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

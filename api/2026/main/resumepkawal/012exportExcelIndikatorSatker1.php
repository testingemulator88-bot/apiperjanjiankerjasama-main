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
    $filterquerybalai = "";
    $filterquerysatker = "";
    $id_indikator = fixup($_GET['id_indikator']);
    $kategorisatker = fixup($_GET['kategorisatker']);
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

    if ($kategorisatker <> '') {
        $filterquerybalai = $filterquerybalai . " and a.kode_emon in (select z.kode from paket_pk_awal z where YEAR(z.tanggaldata)=(select tahun
            from master_pk where id='1')
            and (z.kdsatker in (select s.kode_satker_pendek from master_satker s where kdbalai = '" . $kategorisatker . "')
            or z.kdsatker in (select s.kode_satker_old_pendek from master_satker s where kdbalai = '" . $kategorisatker . "')))
            and a.kode_satker in (select s.kode_satker from master_satker s where kdbalai = '" . $kategorisatker . "')";
    }

    if ($kode_satker <> '' && $kode_satker <> 'Semua Data') {
        $filterquerysatker = $filterquerysatker . " and a.kode_emon in (select z.kode from paket_pk_awal z where YEAR(z.tanggaldata)=(select tahun
            from master_pk where id='1')
            and (z.kdsatker = '" . $kode_satkeremon . "' or z.kdsatker = '" . $kode_satkeremon_old . "'))
            and a.kode_satker = '" . $kode_satker . "'";
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
        from tb_indikator_awal a 
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
                $myArrayemon = array();
                $sqlemon = "select a.id,a.tahun,a.kode_satker,a.kode_emon,a.nilai, a.rumus
                    ,x.kode,x.nmpaket,x.pgrupiah,x.vol,x.sat,a.latitude,a.longitude,a.outcome,a.target
                    ,a.skor_komponen_a,a.skor_komponen_b,a.skor_komponen_c,a.capaian,a.capaian_outcome
                    from tb_data_pk_emon_awal a 
                    left join paket_pk_awal x
                    on a.kode_emon = x.kode
                    where a.deleted='0' and a.tahun=(select tahun
                    from master_pk where id='1') and a.id_indikator = '" . $id_indikator . "'
                    and YEAR(x.tanggaldata)=(select tahun
                    from master_pk where id='1')
                    " . $filterquerybalai . "
                    " . $filterquerysatker . "
                    order by a.kode_satker";
                //echo $sqlemon;
                //die;
                $resultemon = mysqli_query($link, $sqlemon);
                while ($rowemon = mysqli_fetch_assoc($resultemon)) {
                    $myArraynilaiemon = array();
                    $targetnya = number_format($rowemon['target'], $row['belakangkoma'], ",", ".");
                    $outcomenya = number_format($rowemon['outcome'], $row['belakangkoma'], ",", ".");
                    $capaiannya = number_format($rowemon['capaian'], $row['belakangkoma'], ",", ".");
                    $capaian_outcomenya = number_format($rowemon['capaian_outcome'], $row['belakangkoma'], ",", ".");
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
                        'target' => $targetnya,
                        'outcome' => $outcomenya,
                        'capaian' => $capaiannya,
                        'capaian_outcome' => $capaian_outcomenya,
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
                    'volume' => number_format($volume, $row['belakangkoma'], ",", "."),
                    'target' => number_format($target, $row['belakangkoma'], ",", "."),
                    'capaian' => number_format($capaian, $row['belakangkoma'], ",", "."),
                    'capaian_outcome' => number_format($capaian, $row['belakangkoma'], ",", "."),
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

    foreach ($myArray as $obj) {
        $namaoutput = $obj->namaoutput;
        $namaoutcome = $obj->namaoutcome;
    }
    $sheet->setCellValue('E3', 'Target / Output (' . html_entity_decode(str_replace("</sup>", "", str_replace("<sup>", "", $namaoutput))) . ')');
    $sheet->getStyle('E3')->getFont()->setBold(true);
    $sheet->getStyle('E3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('E3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('E3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('E3')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    $sheet->getStyle('E3')->getAlignment()->setWrapText(true);

    $sheet->setCellValue('F3', 'Outcome (' . html_entity_decode(str_replace("</sup>", "", str_replace("<sup>", "", $namaoutcome))) . ')');
    $sheet->getStyle('F3')->getFont()->setBold(true);
    $sheet->getStyle('F3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('F3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('F3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('F3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('F3')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    $sheet->getStyle('F3')->getAlignment()->setWrapText(true);

    $sheet->setCellValue('G3', 'Capaian  Target / Output (' . html_entity_decode(str_replace("</sup>", "", str_replace("<sup>", "", $namaoutput))) . ')');
    $sheet->getStyle('G3')->getFont()->setBold(true);
    $sheet->getStyle('G3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('G3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('G3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('G3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('G3')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $sheet->getStyle('G3')->getAlignment()->setWrapText(true);

    $sheet->setCellValue('H3', 'Capaian  Outcome (' . html_entity_decode(str_replace("</sup>", "", str_replace("<sup>", "", $namaoutcome))) . ')');
    $sheet->getStyle('H3')->getFont()->setBold(true);
    $sheet->getStyle('H3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('H3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('H3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('H3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('H3')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    $sheet->getStyle('H3')->getAlignment()->setWrapText(true);


    $baris = 4;
    foreach ($myArray as $obj) {
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

            $sheet->setCellValue('E' . $baris, replace_comma($emon->outcome));
            $sheet->getStyle('E' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('E' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle('E' . $baris)->applyFromArray($styleRangeArray);

            $sheet->setCellValue('F' . $baris, replace_comma($emon->target));
            $sheet->getStyle('F' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('F' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle('F' . $baris)->applyFromArray($styleRangeArray);

            $sheet->setCellValue('G' . $baris, replace_comma($emon->capaian));
            $sheet->getStyle('G' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('G' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle('G' . $baris)->applyFromArray($styleRangeArray);

            $sheet->setCellValue('H' . $baris, replace_comma($emon->capaian_outcome));
            $sheet->getStyle('H' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('H' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
            $sheet->getStyle('H' . $baris)->applyFromArray($styleRangeArray);

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

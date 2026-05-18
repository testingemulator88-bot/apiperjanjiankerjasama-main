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
    $filterquerybalaiawal = "";
    $filterquerybalai = "";
    $filterquerysatker = "";
    $tahun = fixup($_GET['tahun']);
    $kategorisatker = fixup($_GET['kategorisatker']);
    $kode_satker = fixup($_GET['kode_satker']);
    if ($tahun <> '') {
        $filterquery = $filterquery . " and a.tahun = '" . $tahun . "'";
    }
    if ($kategorisatker <> '') {
        $filterquerybalai = $filterquerybalai . " and b.kode_satker in ( select x.kode_satker from master_satker x where x.kdbalai = '" . $kategorisatker . "')";
        $filterquerybalaiawal = $filterquerybalaiawal . " and c.id = '" . $kategorisatker . "'";
    }

    if ($kode_satker <> '' && $kode_satker <> 'Semua Data') {
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
        where a.deleted='0' and a.id_parent is null
        " . $filterquery . "
        order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
    //echo $sql;
    //die();
    $result = mysqli_query($link, $sql);
    $num = mysqli_num_rows($result);
    if ($num > 0) {
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
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
                ];

                // SS
                $sqllevelSS = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
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
                    where a.deleted='0' and a.id_parent = '" . $row['id'] . "'
                    " . $filterquery . "
                    order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";

                $resultlevelSS = mysqli_query($link, $sqllevelSS);
                while ($rowlevelSS = mysqli_fetch_assoc($resultlevelSS)) {
                    $myArray[] = (object)
                    [
                        'id' => (float) $rowlevelSS['id'],
                        'tahun' => (float) $rowlevelSS['tahun'],
                        'id_parent' => $rowlevelSS['id_parent'],
                        'kdunor' => $rowlevelSS['kdunor'],
                        'nama_unor' => $rowlevelSS['nama_unor'],
                        'urutlevel' => (float) $rowlevelSS['urutlevel'],
                        'level' => $rowlevelSS['level'],
                        'kode' => $rowlevelSS['kode'],
                        'kode_unique' => $rowlevelSS['kode_unique'],
                        'textindikator' => $rowlevelSS['textindikator'],
                        'satuan' => $rowlevelSS['satuan'],
                        'namasatuan' => $rowlevelSS['namasatuan'],
                        'output' => $rowlevelSS['output'],
                        'namaoutput' => $rowlevelSS['namaoutput'],
                        'outcome' => $rowlevelSS['outcome'],
                        'namaoutcome' => $rowlevelSS['namaoutcome'],
                        'textindikator_awal' => $rowlevelSS['textindikator_awal'],
                        'satuan_awal' => $rowlevelSS['satuan_awal'],
                        'namasatuan_awal' => $rowlevelSS['namasatuan_awal'],
                        'output_awal' => $rowlevelSS['output_awal'],
                        'namaoutput_awal' => $rowlevelSS['namaoutput_awal'],
                        'outcome_awal' => $rowlevelSS['outcome_awal'],
                        'namaoutcome_awal' => $rowlevelSS['namaoutcome_awal'],
                        'textindikator_akhir' => $rowlevelSS['textindikator_akhir'],
                        'satuan_akhir' => $rowlevelSS['satuan_akhir'],
                        'namasatuan_akhir' => $rowlevelSS['namasatuan_akhir'],
                        'output_akhir' => $rowlevelSS['output_akhir'],
                        'namaoutput_akhir' => $rowlevelSS['namaoutput_akhir'],
                        'outcome_akhir' => $rowlevelSS['outcome_akhir'],
                        'namaoutcome_akhir' => $rowlevelSS['namaoutcome_akhir'],
                        'penanggungjawab' => $rowlevelSS['penanggungjawab'],
                        'pelaksana' => $rowlevelSS['pelaksana'],
                        'pelaksanacode' => $rowlevelSS['pelaksanacode'],
                        'pelaksanalabel' => $rowlevelSS['pelaksanalabel'],
                        'cetak' => $rowlevelSS['cetak'],
                        'cetakcode' => $rowlevelSS['cetakcode'],
                        'cetaklabel' => $rowlevelSS['cetaklabel'],
                        'verif' => $rowlevelSS['verif'],
                        'verifcode' => $rowlevelSS['verifcode'],
                        'veriflabel' => $rowlevelSS['veriflabel'],
                        'verif2' => $rowlevelSS['verif2'],
                        'verif2code' => $rowlevelSS['verif2code'],
                        'verif2label' => $rowlevelSS['verif2label'],
                        'target' => $rowlevelSS['target'],
                        'urut' => (float) $rowlevelSS['urut'],
                        'hitungan_pk' => $rowlevelSS['hitungan_pk'],
                        'namahitungan_pk' => $rowlevelSS['namahitungan_pk'],
                        'kolom1' => $rowlevelSS['kolom1'],
                        'kolom2' => $rowlevelSS['kolom2'],
                        'kolom3' => $rowlevelSS['kolom3'],
                        'kolom4' => $rowlevelSS['kolom4'],
                        'kolom5' => $rowlevelSS['kolom5'],
                        'rumuskolom1' => $rowlevelSS['rumuskolom1'],
                        'rumuskolom2' => $rowlevelSS['rumuskolom2'],
                        'rumuskolom3' => $rowlevelSS['rumuskolom3'],
                        'rumuskolom4' => $rowlevelSS['rumuskolom4'],
                        'rumuskolom5' => $rowlevelSS['rumuskolom5'],
                        'isian_kolom' => $rowlevelSS['isian_kolom'],
                        'jumlahkomponen' => $rowlevelSS['jumlahkomponen'],
                        'kolomkomponen' => $rowlevelSS['kolomkomponen'],
                        'bobotkomponen' => $rowlevelSS['bobotkomponen'],
                        'rumuskomponen' => $rowlevelSS['rumuskomponen'],
                        'belakangkoma' => $rowlevelSS['belakangkoma'],
                        'class' => $rowlevelSS['class'],
                    ];
                    // ISS
                    $sqllevelISS = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
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
                        where a.deleted='0' and a.id_parent = '" . $rowlevelSS['id'] . "'
                        " . $filterquery . "
                        order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";

                    $resultlevelISS = mysqli_query($link, $sqllevelISS);
                    while ($rowlevelISS = mysqli_fetch_assoc($resultlevelISS)) {
                        $myArray[] = (object)
                        [
                            'id' => (float) $rowlevelISS['id'],
                            'tahun' => (float) $rowlevelISS['tahun'],
                            'id_parent' => $rowlevelISS['id_parent'],
                            'kdunor' => $rowlevelISS['kdunor'],
                            'nama_unor' => $rowlevelISS['nama_unor'],
                            'urutlevel' => (float) $rowlevelISS['urutlevel'],
                            'level' => $rowlevelISS['level'],
                            'kode' => $rowlevelISS['kode'],
                            'kode_unique' => $rowlevelISS['kode_unique'],
                            'textindikator' => $rowlevelISS['textindikator'],
                            'satuan' => $rowlevelISS['satuan'],
                            'namasatuan' => $rowlevelISS['namasatuan'],
                            'output' => $rowlevelISS['output'],
                            'namaoutput' => $rowlevelISS['namaoutput'],
                            'outcome' => $rowlevelISS['outcome'],
                            'namaoutcome' => $rowlevelISS['namaoutcome'],
                            'textindikator_awal' => $rowlevelISS['textindikator_awal'],
                            'satuan_awal' => $rowlevelISS['satuan_awal'],
                            'namasatuan_awal' => $rowlevelISS['namasatuan_awal'],
                            'output_awal' => $rowlevelISS['output_awal'],
                            'namaoutput_awal' => $rowlevelISS['namaoutput_awal'],
                            'outcome_awal' => $rowlevelISS['outcome_awal'],
                            'namaoutcome_awal' => $rowlevelISS['namaoutcome_awal'],
                            'textindikator_akhir' => $rowlevelISS['textindikator_akhir'],
                            'satuan_akhir' => $rowlevelISS['satuan_akhir'],
                            'namasatuan_akhir' => $rowlevelISS['namasatuan_akhir'],
                            'output_akhir' => $rowlevelISS['output_akhir'],
                            'namaoutput_akhir' => $rowlevelISS['namaoutput_akhir'],
                            'outcome_akhir' => $rowlevelISS['outcome_akhir'],
                            'namaoutcome_akhir' => $rowlevelISS['namaoutcome_akhir'],
                            'penanggungjawab' => $rowlevelISS['penanggungjawab'],
                            'pelaksana' => $rowlevelISS['pelaksana'],
                            'pelaksanacode' => $rowlevelISS['pelaksanacode'],
                            'pelaksanalabel' => $rowlevelISS['pelaksanalabel'],
                            'cetak' => $rowlevelISS['cetak'],
                            'cetakcode' => $rowlevelISS['cetakcode'],
                            'cetaklabel' => $rowlevelISS['cetaklabel'],
                            'verif' => $rowlevelISS['verif'],
                            'verifcode' => $rowlevelISS['verifcode'],
                            'veriflabel' => $rowlevelISS['veriflabel'],
                            'verif2' => $rowlevelISS['verif2'],
                            'verif2code' => $rowlevelISS['verif2code'],
                            'verif2label' => $rowlevelISS['verif2label'],
                            'target' => $rowlevelISS['target'],
                            'urut' => (float) $rowlevelISS['urut'],
                            'hitungan_pk' => $rowlevelISS['hitungan_pk'],
                            'namahitungan_pk' => $rowlevelISS['namahitungan_pk'],
                            'kolom1' => $rowlevelISS['kolom1'],
                            'kolom2' => $rowlevelISS['kolom2'],
                            'kolom3' => $rowlevelISS['kolom3'],
                            'kolom4' => $rowlevelISS['kolom4'],
                            'kolom5' => $rowlevelISS['kolom5'],
                            'rumuskolom1' => $rowlevelISS['rumuskolom1'],
                            'rumuskolom2' => $rowlevelISS['rumuskolom2'],
                            'rumuskolom3' => $rowlevelISS['rumuskolom3'],
                            'rumuskolom4' => $rowlevelISS['rumuskolom4'],
                            'rumuskolom5' => $rowlevelISS['rumuskolom5'],
                            'isian_kolom' => $rowlevelISS['isian_kolom'],
                            'jumlahkomponen' => $rowlevelISS['jumlahkomponen'],
                            'kolomkomponen' => $rowlevelISS['kolomkomponen'],
                            'bobotkomponen' => $rowlevelISS['bobotkomponen'],
                            'rumuskomponen' => $rowlevelISS['rumuskomponen'],
                            'belakangkoma' => $rowlevelISS['belakangkoma'],
                            'class' => $rowlevelISS['class'],
                        ];
                        // PROGRAM
                        $sqllevelPROGRAM = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
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
                            where a.deleted='0' and a.id_parent = '" . $rowlevelISS['id'] . "'
                            " . $filterquery . "
                            order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";

                        $resultlevelPROGRAM = mysqli_query($link, $sqllevelPROGRAM);
                        while ($rowlevelPROGRAM = mysqli_fetch_assoc($resultlevelPROGRAM)) {
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
                                'textindikator_awal' => $rowlevelPROGRAM['textindikator_awal'],
                                'satuan_awal' => $rowlevelPROGRAM['satuan_awal'],
                                'namasatuan_awal' => $rowlevelPROGRAM['namasatuan_awal'],
                                'output_awal' => $rowlevelPROGRAM['output_awal'],
                                'namaoutput_awal' => $rowlevelPROGRAM['namaoutput_awal'],
                                'outcome_awal' => $rowlevelPROGRAM['outcome_awal'],
                                'namaoutcome_awal' => $rowlevelPROGRAM['namaoutcome_awal'],
                                'textindikator_akhir' => $rowlevelPROGRAM['textindikator_akhir'],
                                'satuan_akhir' => $rowlevelPROGRAM['satuan_akhir'],
                                'namasatuan_akhir' => $rowlevelPROGRAM['namasatuan_akhir'],
                                'output_akhir' => $rowlevelPROGRAM['output_akhir'],
                                'namaoutput_akhir' => $rowlevelPROGRAM['namaoutput_akhir'],
                                'outcome_akhir' => $rowlevelPROGRAM['outcome_akhir'],
                                'namaoutcome_akhir' => $rowlevelPROGRAM['namaoutcome_akhir'],
                                'penanggungjawab' => $rowlevelPROGRAM['penanggungjawab'],
                                'pelaksana' => $rowlevelPROGRAM['pelaksana'],
                                'pelaksanacode' => $rowlevelPROGRAM['pelaksanacode'],
                                'pelaksanalabel' => $rowlevelPROGRAM['pelaksanalabel'],
                                'cetak' => $rowlevelPROGRAM['cetak'],
                                'cetakcode' => $rowlevelPROGRAM['cetakcode'],
                                'cetaklabel' => $rowlevelPROGRAM['cetaklabel'],
                                'verif' => $rowlevelPROGRAM['verif'],
                                'verifcode' => $rowlevelPROGRAM['verifcode'],
                                'veriflabel' => $rowlevelPROGRAM['veriflabel'],
                                'verif2' => $rowlevelPROGRAM['verif2'],
                                'verif2code' => $rowlevelPROGRAM['verif2code'],
                                'verif2label' => $rowlevelPROGRAM['verif2label'],
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
                                'jumlahkomponen' => $rowlevelPROGRAM['jumlahkomponen'],
                                'kolomkomponen' => $rowlevelPROGRAM['kolomkomponen'],
                                'bobotkomponen' => $rowlevelPROGRAM['bobotkomponen'],
                                'rumuskomponen' => $rowlevelPROGRAM['rumuskomponen'],
                                'belakangkoma' => $rowlevelPROGRAM['belakangkoma'],
                                'class' => $rowlevelPROGRAM['class'],
                            ];
                            // SP KEGIATAN
                            $sqllevelSPKEGIATAN = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
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
                                where a.deleted='0' and a.id_parent = '" . $rowlevelPROGRAM['id'] . "'
                                " . $filterquery . "
                                order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";

                            $resultlevelSPKEGIATAN = mysqli_query($link, $sqllevelSPKEGIATAN);
                            while ($rowlevelSPKEGIATAN = mysqli_fetch_assoc($resultlevelSPKEGIATAN)) {
                                $myArray[] = (object)
                                [
                                    'id' => (float) $rowlevelSPKEGIATAN['id'],
                                    'tahun' => (float) $rowlevelSPKEGIATAN['tahun'],
                                    'id_parent' => $rowlevelSPKEGIATAN['id_parent'],
                                    'kdunor' => $rowlevelSPKEGIATAN['kdunor'],
                                    'nama_unor' => $rowlevelSPKEGIATAN['nama_unor'],
                                    'urutlevel' => (float) $rowlevelSPKEGIATAN['urutlevel'],
                                    'level' => $rowlevelSPKEGIATAN['level'],
                                    'kode' => $rowlevelSPKEGIATAN['kode'],
                                    'kode_unique' => $rowlevelSPKEGIATAN['kode_unique'],
                                    'textindikator' => $rowlevelSPKEGIATAN['textindikator'],
                                    'satuan' => $rowlevelSPKEGIATAN['satuan'],
                                    'namasatuan' => $rowlevelSPKEGIATAN['namasatuan'],
                                    'output' => $rowlevelSPKEGIATAN['output'],
                                    'namaoutput' => $rowlevelSPKEGIATAN['namaoutput'],
                                    'outcome' => $rowlevelSPKEGIATAN['outcome'],
                                    'namaoutcome' => $rowlevelSPKEGIATAN['namaoutcome'],
                                    'textindikator_awal' => $rowlevelSPKEGIATAN['textindikator_awal'],
                                    'satuan_awal' => $rowlevelSPKEGIATAN['satuan_awal'],
                                    'namasatuan_awal' => $rowlevelSPKEGIATAN['namasatuan_awal'],
                                    'output_awal' => $rowlevelSPKEGIATAN['output_awal'],
                                    'namaoutput_awal' => $rowlevelSPKEGIATAN['namaoutput_awal'],
                                    'outcome_awal' => $rowlevelSPKEGIATAN['outcome_awal'],
                                    'namaoutcome_awal' => $rowlevelSPKEGIATAN['namaoutcome_awal'],
                                    'textindikator_akhir' => $rowlevelSPKEGIATAN['textindikator_akhir'],
                                    'satuan_akhir' => $rowlevelSPKEGIATAN['satuan_akhir'],
                                    'namasatuan_akhir' => $rowlevelSPKEGIATAN['namasatuan_akhir'],
                                    'output_akhir' => $rowlevelSPKEGIATAN['output_akhir'],
                                    'namaoutput_akhir' => $rowlevelSPKEGIATAN['namaoutput_akhir'],
                                    'outcome_akhir' => $rowlevelSPKEGIATAN['outcome_akhir'],
                                    'namaoutcome_akhir' => $rowlevelSPKEGIATAN['namaoutcome_akhir'],
                                    'penanggungjawab' => $rowlevelSPKEGIATAN['penanggungjawab'],
                                    'pelaksana' => $rowlevelSPKEGIATAN['pelaksana'],
                                    'pelaksanacode' => $rowlevelSPKEGIATAN['pelaksanacode'],
                                    'pelaksanalabel' => $rowlevelSPKEGIATAN['pelaksanalabel'],
                                    'cetak' => $rowlevelSPKEGIATAN['cetak'],
                                    'cetakcode' => $rowlevelSPKEGIATAN['cetakcode'],
                                    'cetaklabel' => $rowlevelSPKEGIATAN['cetaklabel'],
                                    'verif' => $rowlevelSPKEGIATAN['verif'],
                                    'verifcode' => $rowlevelSPKEGIATAN['verifcode'],
                                    'veriflabel' => $rowlevelSPKEGIATAN['veriflabel'],
                                    'verif2' => $rowlevelSPKEGIATAN['verif2'],
                                    'verif2code' => $rowlevelSPKEGIATAN['verif2code'],
                                    'verif2label' => $rowlevelSPKEGIATAN['verif2label'],
                                    'target' => $rowlevelSPKEGIATAN['target'],
                                    'urut' => (float) $rowlevelSPKEGIATAN['urut'],
                                    'hitungan_pk' => $rowlevelSPKEGIATAN['hitungan_pk'],
                                    'namahitungan_pk' => $rowlevelSPKEGIATAN['namahitungan_pk'],
                                    'kolom1' => $rowlevelSPKEGIATAN['kolom1'],
                                    'kolom2' => $rowlevelSPKEGIATAN['kolom2'],
                                    'kolom3' => $rowlevelSPKEGIATAN['kolom3'],
                                    'kolom4' => $rowlevelSPKEGIATAN['kolom4'],
                                    'kolom5' => $rowlevelSPKEGIATAN['kolom5'],
                                    'rumuskolom1' => $rowlevelSPKEGIATAN['rumuskolom1'],
                                    'rumuskolom2' => $rowlevelSPKEGIATAN['rumuskolom2'],
                                    'rumuskolom3' => $rowlevelSPKEGIATAN['rumuskolom3'],
                                    'rumuskolom4' => $rowlevelSPKEGIATAN['rumuskolom4'],
                                    'rumuskolom5' => $rowlevelSPKEGIATAN['rumuskolom5'],
                                    'isian_kolom' => $rowlevelSPKEGIATAN['isian_kolom'],
                                    'jumlahkomponen' => $rowlevelSPKEGIATAN['jumlahkomponen'],
                                    'kolomkomponen' => $rowlevelSPKEGIATAN['kolomkomponen'],
                                    'bobotkomponen' => $rowlevelSPKEGIATAN['bobotkomponen'],
                                    'rumuskomponen' => $rowlevelSPKEGIATAN['rumuskomponen'],
                                    'belakangkoma' => $rowlevelSPKEGIATAN['belakangkoma'],
                                    'class' => $rowlevelSPKEGIATAN['class'],
                                ];
                                //ISPSK
                                $sqllevelISPSK = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
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
                                    where a.deleted='0' and a.id_parent = '" . $rowlevelSPKEGIATAN['id'] . "'
                                    " . $filterquery . "
                                    order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";

                                $resultlevelISPSK = mysqli_query($link, $sqllevelISPSK);
                                while ($rowlevelISPSK = mysqli_fetch_assoc($resultlevelISPSK)) {
                                    // IKSK
                                    $sqllevelIKSK = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
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
                                        where a.deleted='0' and a.id_parent = '" . $rowlevelISPSK['id'] . "'
                                        " . $filterquery . "
                                        order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";

                                    $resultlevelIKSK = mysqli_query($link, $sqllevelIKSK);
                                    while ($rowlevelIKSK = mysqli_fetch_assoc($resultlevelIKSK)) {
                                        $myArraybalai = array();
                                        $sqlcekbalai = "select a.kdbalai,c.nama_kategori, count(a.kode_satker) as jmlsatker
                                            , (select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator='" . $rowlevelIKSK['id'] . "'
                                            and zz.deleted='0' and zz.kode_satker 
                                            in (select b.kode_satker
                                            from tb_data_pk_akhir b where b.deleted='0' 
                                            and b.id_indikator='" . $rowlevelIKSK['id'] . "' and b.tahun=(select tahun
                                            from master_pk where id='3'))) as terpilih
                                            from master_satker a
                                            left join master_kategori_satker c
                                            on a.kdbalai = c.id
                                            where a.deleted='0' " . $filterquerybalaiawal . " and a.kode_satker in (select b.kode_satker
                                            from tb_data_pk_akhir b where b.deleted='0' " . $filterquerybalai . " " . $filterquerysatker . "
                                            and b.id_indikator='" . $rowlevelIKSK['id'] . "' and b.tahun=(select tahun
                                            from master_pk where id='3'))
                                            group by a.kdbalai order by a.kdbalai";
                                        //echo $sqlcekbalai;
                                        //die;
                                        $resultcekbalai = mysqli_query($link, $sqlcekbalai);
                                        while ($rowcekbalai = mysqli_fetch_assoc($resultcekbalai)) {
                                            $myArraysatker = array();
                                            $sqlceksatker = "select a.kode_satker,a.nama_satker 
                                                , ifnull((select x.volume from tb_data_pk_akhir x where x.deleted='0' and 
                                                x.id_indikator='" . $rowlevelIKSK['id'] . "' and x.tahun=(select tahun
                                                from master_pk where id='3') and x.kode_satker=a.kode_satker),0) as volume
                                                , ifnull((select x.target from tb_data_pk_akhir x where x.deleted='0' and 
                                                x.id_indikator='" . $rowlevelIKSK['id'] . "' and x.tahun=(select tahun
                                                from master_pk where id='3') and x.kode_satker=a.kode_satker),0) as target
                                                , ifnull((select x.capaian from tb_data_pk_akhir x where x.deleted='0' and 
                                                x.id_indikator='" . $rowlevelIKSK['id'] . "' and x.tahun=(select tahun
                                                from master_pk where id='3') and x.kode_satker=a.kode_satker),0) as capaian
                                                , ifnull((select x.capaian_outcome from tb_data_pk_akhir x where x.deleted='0' and 
                                                x.id_indikator='" . $rowlevelIKSK['id'] . "' and x.tahun=(select tahun
                                                from master_pk where id='3') and x.kode_satker=a.kode_satker),0) as capaian_outcome
                                                , ifnull((select SUBSTRING_INDEX(nilai, '|', -1) from tb_data_pk_akhir x where x.deleted='0' and 
                                                x.id_indikator='" . $rowlevelIKSK['id'] . "' and x.tahun=(select tahun
                                                from master_pk where id='3') and x.kode_satker=a.kode_satker),0) as nilai
                                                , (select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator='" . $rowlevelIKSK['id'] . "'
                                                and zz.deleted='0' and zz.kode_satker=a.kode_satker) as terpilih
                                                from master_satker a
                                                where a.deleted='0' and a.kdbalai = '" . $rowcekbalai['kdbalai'] . "' and a.kode_satker in (select b.kode_satker
                                                from tb_data_pk_akhir b where b.deleted='0' " . $filterquerybalai . " " . $filterquerysatker . "
                                                and b.id_indikator='" . $rowlevelIKSK['id'] . "' and b.tahun=(select tahun
                                                from master_pk where id='3'))
                                                order by a.kode_satker";
                                            $resultceksatker = mysqli_query($link, $sqlceksatker);
                                            while ($rowceksatker = mysqli_fetch_assoc($resultceksatker)) {
                                                $angka = (float) $rowceksatker['terpilih'];
                                                if ((float) $rowceksatker['terpilih'] == 0) {
                                                    if ($rowlevelIKSK['hitungan_pk'] == '1') {
                                                        if ((float) replace_comma(number_format($rowceksatker['volume'], $rowlevelIKSK['belakangkoma'], ",", ".")) == 0 && (float) replace_comma(number_format($rowceksatker['capaian'], $rowlevelIKSK['belakangkoma'], ",", ".")) == 0) {
                                                            $kinerja = 100;
                                                        } else {
                                                            if ((float) replace_comma(number_format($rowceksatker['volume'], $rowlevelIKSK['belakangkoma'], ",", ".")) == 0) {
                                                                $kinerja = 0;
                                                            } else {
                                                                $kinerja = ((float) replace_comma(number_format($rowceksatker['capaian'], $rowlevelIKSK['belakangkoma'], ",", ".")) / (float) replace_comma(number_format($rowceksatker['volume'], $rowlevelIKSK['belakangkoma'], ",", "."))) * 100;
                                                            }
                                                        }

                                                        if ((float) replace_comma(number_format($rowceksatker['target'], $rowlevelIKSK['belakangkoma'], ",", ".")) == 0 && (float) replace_comma(number_format($rowceksatker['capaian_outcome'], $rowlevelIKSK['belakangkoma'], ",", ".")) == 0) {
                                                            $kinerja_outcome = 100;
                                                        } else {
                                                            if ((float) replace_comma(number_format($rowceksatker['target'], $rowlevelIKSK['belakangkoma'], ",", ".")) == 0) {
                                                                $kinerja_outcome = 0;
                                                            } else {
                                                                $kinerja_outcome = ((float) replace_comma(number_format($rowceksatker['capaian_outcome'], $rowlevelIKSK['belakangkoma'], ",", ".")) / (float) replace_comma(number_format($rowceksatker['target'], $rowlevelIKSK['belakangkoma'], ",", "."))) * 100;
                                                            }
                                                        }
                                                    } else {
                                                        if ((float) replace_comma(number_format($rowceksatker['nilai'], $rowlevelIKSK['belakangkoma'], ",", ".")) == 0 && (float) replace_comma(number_format($rowceksatker['capaian'], $rowlevelIKSK['belakangkoma'], ",", ".")) == 0) {
                                                            $kinerja = 100;
                                                        } else {
                                                            if ((float) replace_comma(number_format($rowceksatker['nilai'], $rowlevelIKSK['belakangkoma'], ",", ".")) == 0) {
                                                                $kinerja = 0;
                                                            } else {
                                                                $kinerja = ((float) replace_comma(number_format($rowceksatker['capaian'], $rowlevelIKSK['belakangkoma'], ",", ".")) / (float) replace_comma(number_format($rowceksatker['nilai'], $rowlevelIKSK['belakangkoma'], ",", "."))) * 100;
                                                            }
                                                        }

                                                        $kinerja_outcome = $kinerja;
                                                    }

                                                    $myArrayemonsatker = array();
                                                    $sqlcekemonsatker = "select rumus,pagu,kode_emon,nmpaket,target, outcome, nilai, capaian, capaian_outcome from 
                                                        tb_data_pk_emon_akhir where deleted='0' and tahun = (select tahun
                                                        from master_pk where id='3') and id_indikator = '" . $rowlevelIKSK['id'] . "'
                                                        and kode_satker = '" . $rowceksatker['kode_satker'] . "'";

                                                    //echo $sqlcekemonsatker."<br>";
                                                    $resultcekemonsatker = mysqli_query($link, $sqlcekemonsatker);
                                                    $mynumnya = mysqli_num_rows($resultcekemonsatker);
                                                    if ($mynumnya > 0) {
                                                        if ($resultcekemonsatker) {
                                                            while ($rowcekemonsatker = mysqli_fetch_assoc($resultcekemonsatker)) {
                                                                if ($rowlevelIKSK['hitungan_pk'] == '1') {
                                                                    $targetnya_emon = number_format($rowcekemonsatker['target'], $rowlevelIKSK['belakangkoma'], ",", ".");
                                                                    $outcomenya_emon = number_format($rowcekemonsatker['outcome'], $rowlevelIKSK['belakangkoma'], ",", ".");
                                                                    $capaiannya_emon = number_format($rowcekemonsatker['capaian'], $rowlevelIKSK['belakangkoma'], ",", ".");
                                                                    $capaian_outcomenya_emon = number_format($rowcekemonsatker['capaian_outcome'], $rowlevelIKSK['belakangkoma'], ",", ".");
                                                                } else {
                                                                    if ($rowcekemonsatker['nilai'] <> '') {
                                                                        $tempnilai = explode("|", $rowcekemonsatker['nilai']);
                                                                        $temprumus = explode("|", $rowcekemonsatker['rumus']);
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
                                                                            $targetnya_emon = number_format($nilai, $row['belakangkoma'], ",", ".");
                                                                            $outcomenya_emon = number_format($nilai, $row['belakangkoma'], ",", ".");
                                                                            $capaiannya_emon = number_format($rowcekemonsatker['capaian'], $rowlevelIKSK['belakangkoma'], ",", ".");
                                                                            $capaian_outcomenya_emon = number_format($rowcekemonsatker['capaian'], $rowlevelIKSK['belakangkoma'], ",", ".");
                                                                        }
                                                                    } else {
                                                                        $outcomenya_emon = number_format(0, $rowlevelIKSK['belakangkoma'], ",", ".");
                                                                        $targetnya_emon = number_format(0, $rowlevelIKSK['belakangkoma'], ",", ".");
                                                                        $capaiannya_emon = number_format(0, $rowlevelIKSK['belakangkoma'], ",", ".");
                                                                        $capaian_outcomenya_emon = number_format(0, $rowlevelIKSK['belakangkoma'], ",", ".");
                                                                    }
                                                                }

                                                                $kinerja_emon = 0;
                                                                if (((float) replace_comma($outcomenya_emon) == 0) && ((float) replace_comma($capaiannya_emon) == 0)) {
                                                                    $kinerja_emon = 0;
                                                                } else {
                                                                    if ((float) replace_comma($outcomenya_emon) == 0) {
                                                                        $kinerja_emon = 0;
                                                                    } else {
                                                                        $kinerja_emon = ((float) replace_comma($capaiannya_emon) / (float) replace_comma($outcomenya_emon)) * 100;
                                                                    }
                                                                }


                                                                $kinerja_emon_outcome = 0;
                                                                if (((float) replace_comma($targetnya_emon) == 0) && ((float) replace_comma($capaian_outcomenya_emon) == 0)) {
                                                                    $kinerja_emon_outcome = 0;
                                                                } else {
                                                                    if ((float) replace_comma($targetnya_emon) == 0) {
                                                                        $kinerja_emon_outcome = 0;
                                                                    } else {
                                                                        $kinerja_emon_outcome = ((float) replace_comma($capaian_outcomenya_emon) / (float) replace_comma($targetnya_emon)) * 100;
                                                                    }
                                                                }


                                                                $myArrayemonsatker[] = (object)
                                                                [
                                                                    'kode_emon' => $rowcekemonsatker['kode_emon'],
                                                                    'nmpaket' => $rowcekemonsatker['nmpaket'],
                                                                    'pagu' => number_format($rowcekemonsatker['pagu'], $rowlevelIKSK['belakangkoma'], ",", "."),
                                                                    'target' => $outcomenya_emon,
                                                                    'outcome' => $targetnya_emon,
                                                                    'capaian' => $capaiannya_emon,
                                                                    'capaian_outcome' => $capaian_outcomenya_emon,
                                                                    'kinerja' => number_format($kinerja_emon, 2, ",", "."),
                                                                    'kinerja_outcome' => number_format($kinerja_emon_outcome, 2, ",", "."),
                                                                ];
                                                            }
                                                        }
                                                    }

                                                    $myArraysatker[] = (object)
                                                    [
                                                        'kode_satker' => $rowceksatker['kode_satker'],
                                                        'satker' => $rowceksatker['nama_satker'],
                                                        'volume' => number_format($rowceksatker['volume'], $rowlevelIKSK['belakangkoma'], ",", "."),
                                                        'target' => number_format($rowceksatker['target'], $rowlevelIKSK['belakangkoma'], ",", "."),
                                                        'capaian' => number_format($rowceksatker['capaian'], $rowlevelIKSK['belakangkoma'], ",", "."),
                                                        'capaian_outcome' => number_format($rowceksatker['capaian_outcome'], $rowlevelIKSK['belakangkoma'], ",", "."),
                                                        'nilai' => number_format($rowceksatker['nilai'], $rowlevelIKSK['belakangkoma'], ",", "."),
                                                        'kinerja' => number_format($kinerja, 2, ",", "."),
                                                        'kinerja_outcome' => number_format($kinerja_outcome, 2, ",", "."),
                                                        'emon' => $myArrayemonsatker,
                                                    ];
                                                }
                                            }
                                            if ((float) $rowcekbalai['jmlsatker'] > $angka) {
                                                $myArraybalai[] = (object)
                                                [
                                                    'balai' => $rowcekbalai['nama_kategori'],
                                                    'satker' => $myArraysatker,
                                                ];
                                            }
                                        }
                                        $myArray[] = (object)
                                        [
                                            'id' => (float) $rowlevelIKSK['id'],
                                            'tahun' => (float) $rowlevelIKSK['tahun'],
                                            'id_parent' => $rowlevelIKSK['id_parent'],
                                            'kdunor' => $rowlevelIKSK['kdunor'],
                                            'nama_unor' => $rowlevelIKSK['nama_unor'],
                                            'urutlevel' => (float) $rowlevelIKSK['urutlevel'],
                                            'level' => $rowlevelIKSK['level'],
                                            'kode' => $rowlevelIKSK['kode'],
                                            'kode_unique' => $rowlevelIKSK['kode_unique'],
                                            'textindikator' => $rowlevelIKSK['textindikator'],
                                            'satuan' => $rowlevelIKSK['satuan'],
                                            'namasatuan' => $rowlevelIKSK['namasatuan'],
                                            'output' => $rowlevelIKSK['output'],
                                            'namaoutput' => $rowlevelIKSK['namaoutput'],
                                            'outcome' => $rowlevelIKSK['outcome'],
                                            'namaoutcome' => $rowlevelIKSK['namaoutcome'],
                                            'textindikator_awal' => $rowlevelIKSK['textindikator_awal'],
                                            'satuan_awal' => $rowlevelIKSK['satuan_awal'],
                                            'namasatuan_awal' => $rowlevelIKSK['namasatuan_awal'],
                                            'output_awal' => $rowlevelIKSK['output_awal'],
                                            'namaoutput_awal' => $rowlevelIKSK['namaoutput_awal'],
                                            'outcome_awal' => $rowlevelIKSK['outcome_awal'],
                                            'namaoutcome_awal' => $rowlevelIKSK['namaoutcome_awal'],
                                            'textindikator_akhir' => $rowlevelIKSK['textindikator_akhir'],
                                            'satuan_akhir' => $rowlevelIKSK['satuan_akhir'],
                                            'namasatuan_akhir' => $rowlevelIKSK['namasatuan_akhir'],
                                            'output_akhir' => $rowlevelIKSK['output_akhir'],
                                            'namaoutput_akhir' => $rowlevelIKSK['namaoutput_akhir'],
                                            'outcome_akhir' => $rowlevelIKSK['outcome_akhir'],
                                            'namaoutcome_akhir' => $rowlevelIKSK['namaoutcome_akhir'],
                                            'penanggungjawab' => $rowlevelIKSK['penanggungjawab'],
                                            'pelaksana' => $rowlevelIKSK['pelaksana'],
                                            'pelaksanacode' => $rowlevelIKSK['pelaksanacode'],
                                            'pelaksanalabel' => $rowlevelIKSK['pelaksanalabel'],
                                            'cetak' => $rowlevelIKSK['cetak'],
                                            'cetakcode' => $rowlevelIKSK['cetakcode'],
                                            'cetaklabel' => $rowlevelIKSK['cetaklabel'],
                                            'verif' => $rowlevelIKSK['verif'],
                                            'verifcode' => $rowlevelIKSK['verifcode'],
                                            'veriflabel' => $rowlevelIKSK['veriflabel'],
                                            'verif2' => $rowlevelIKSK['verif2'],
                                            'verif2code' => $rowlevelIKSK['verif2code'],
                                            'verif2label' => $rowlevelIKSK['verif2label'],
                                            'target' => $rowlevelIKSK['target'],
                                            'urut' => (float) $rowlevelIKSK['urut'],
                                            'hitungan_pk' => $rowlevelIKSK['hitungan_pk'],
                                            'namahitungan_pk' => $rowlevelIKSK['namahitungan_pk'],
                                            'kolom1' => $rowlevelIKSK['kolom1'],
                                            'kolom2' => $rowlevelIKSK['kolom2'],
                                            'kolom3' => $rowlevelIKSK['kolom3'],
                                            'kolom4' => $rowlevelIKSK['kolom4'],
                                            'kolom5' => $rowlevelIKSK['kolom5'],
                                            'rumuskolom1' => $rowlevelIKSK['rumuskolom1'],
                                            'rumuskolom2' => $rowlevelIKSK['rumuskolom2'],
                                            'rumuskolom3' => $rowlevelIKSK['rumuskolom3'],
                                            'rumuskolom4' => $rowlevelIKSK['rumuskolom4'],
                                            'rumuskolom5' => $rowlevelIKSK['rumuskolom5'],
                                            'isian_kolom' => $rowlevelIKSK['isian_kolom'],
                                            'jumlahkomponen' => $rowlevelIKSK['jumlahkomponen'],
                                            'kolomkomponen' => $rowlevelIKSK['kolomkomponen'],
                                            'bobotkomponen' => $rowlevelIKSK['bobotkomponen'],
                                            'rumuskomponen' => $rowlevelIKSK['rumuskomponen'],
                                            'belakangkoma' => $rowlevelIKSK['belakangkoma'],
                                            'class' => $rowlevelIKSK['class'],
                                            'balai' => $myArraybalai,
                                        ];
                                        // RO
                                        $sqllevelRO = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
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
                                            where a.deleted='0' and a.id_parent = '" . $rowlevelIKSK['id'] . "'
                                            " . $filterquery . "
                                            order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";

                                        $resultlevelRO = mysqli_query($link, $sqllevelRO);
                                        while ($rowlevelRO = mysqli_fetch_assoc($resultlevelRO)) {
                                            $myArray[] = (object)
                                            [
                                                'id' => (float) $rowlevelRO['id'],
                                                'tahun' => (float) $rowlevelRO['tahun'],
                                                'id_parent' => $rowlevelRO['id_parent'],
                                                'kdunor' => $rowlevelRO['kdunor'],
                                                'nama_unor' => $rowlevelRO['nama_unor'],
                                                'urutlevel' => (float) $rowlevelRO['urutlevel'],
                                                'level' => $rowlevelRO['level'],
                                                'kode' => $rowlevelRO['kode'],
                                                'kode_unique' => $rowlevelRO['kode_unique'],
                                                'textindikator' => $rowlevelRO['textindikator'],
                                                'satuan' => $rowlevelRO['satuan'],
                                                'namasatuan' => $rowlevelRO['namasatuan'],
                                                'output' => $rowlevelRO['output'],
                                                'namaoutput' => $rowlevelRO['namaoutput'],
                                                'outcome' => $rowlevelRO['outcome'],
                                                'namaoutcome' => $rowlevelRO['namaoutcome'],
                                                'textindikator_awal' => $rowlevelRO['textindikator_awal'],
                                                'satuan_awal' => $rowlevelRO['satuan_awal'],
                                                'namasatuan_awal' => $rowlevelRO['namasatuan_awal'],
                                                'output_awal' => $rowlevelRO['output_awal'],
                                                'namaoutput_awal' => $rowlevelRO['namaoutput_awal'],
                                                'outcome_awal' => $rowlevelRO['outcome_awal'],
                                                'namaoutcome_awal' => $rowlevelRO['namaoutcome_awal'],
                                                'textindikator_akhir' => $rowlevelRO['textindikator_akhir'],
                                                'satuan_akhir' => $rowlevelRO['satuan_akhir'],
                                                'namasatuan_akhir' => $rowlevelRO['namasatuan_akhir'],
                                                'output_akhir' => $rowlevelRO['output_akhir'],
                                                'namaoutput_akhir' => $rowlevelRO['namaoutput_akhir'],
                                                'outcome_akhir' => $rowlevelRO['outcome_akhir'],
                                                'namaoutcome_akhir' => $rowlevelRO['namaoutcome_akhir'],
                                                'penanggungjawab' => $rowlevelRO['penanggungjawab'],
                                                'pelaksana' => $rowlevelRO['pelaksana'],
                                                'pelaksanacode' => $rowlevelRO['pelaksanacode'],
                                                'pelaksanalabel' => $rowlevelRO['pelaksanalabel'],
                                                'cetak' => $rowlevelRO['cetak'],
                                                'cetakcode' => $rowlevelRO['cetakcode'],
                                                'cetaklabel' => $rowlevelRO['cetaklabel'],
                                                'verif' => $rowlevelRO['verif'],
                                                'verifcode' => $rowlevelRO['verifcode'],
                                                'veriflabel' => $rowlevelRO['veriflabel'],
                                                'verif2' => $rowlevelRO['verif2'],
                                                'verif2code' => $rowlevelRO['verif2code'],
                                                'verif2label' => $rowlevelRO['verif2label'],
                                                'target' => $rowlevelRO['target'],
                                                'urut' => (float) $rowlevelRO['urut'],
                                                'hitungan_pk' => $rowlevelRO['hitungan_pk'],
                                                'namahitungan_pk' => $rowlevelRO['namahitungan_pk'],
                                                'kolom1' => $rowlevelRO['kolom1'],
                                                'kolom2' => $rowlevelRO['kolom2'],
                                                'kolom3' => $rowlevelRO['kolom3'],
                                                'kolom4' => $rowlevelRO['kolom4'],
                                                'kolom5' => $rowlevelRO['kolom5'],
                                                'rumuskolom1' => $rowlevelRO['rumuskolom1'],
                                                'rumuskolom2' => $rowlevelRO['rumuskolom2'],
                                                'rumuskolom3' => $rowlevelRO['rumuskolom3'],
                                                'rumuskolom4' => $rowlevelRO['rumuskolom4'],
                                                'rumuskolom5' => $rowlevelRO['rumuskolom5'],
                                                'isian_kolom' => $rowlevelRO['isian_kolom'],
                                                'jumlahkomponen' => $rowlevelRO['jumlahkomponen'],
                                                'kolomkomponen' => $rowlevelRO['kolomkomponen'],
                                                'bobotkomponen' => $rowlevelRO['bobotkomponen'],
                                                'rumuskomponen' => $rowlevelRO['rumuskomponen'],
                                                'belakangkoma' => $rowlevelRO['belakangkoma'],
                                                'class' => $rowlevelRO['class'],
                                            ];
                                            // IRO
                                            $sqllevelIRO = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
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
                                                where a.deleted='0' and a.id_parent = '" . $rowlevelRO['id'] . "'
                                                " . $filterquery . "
                                                order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";

                                            $resultlevelIRO = mysqli_query($link, $sqllevelIRO);
                                            while ($rowlevelIRO = mysqli_fetch_assoc($resultlevelIRO)) {
                                                $myArray[] = (object)
                                                [
                                                    'id' => (float) $rowlevelIRO['id'],
                                                    'tahun' => (float) $rowlevelIRO['tahun'],
                                                    'id_parent' => $rowlevelIRO['id_parent'],
                                                    'kdunor' => $rowlevelIRO['kdunor'],
                                                    'nama_unor' => $rowlevelIRO['nama_unor'],
                                                    'urutlevel' => (float) $rowlevelIRO['urutlevel'],
                                                    'level' => $rowlevelIRO['level'],
                                                    'kode' => $rowlevelIRO['kode'],
                                                    'kode_unique' => $rowlevelIRO['kode_unique'],
                                                    'textindikator' => $rowlevelIRO['textindikator'],
                                                    'satuan' => $rowlevelIRO['satuan'],
                                                    'namasatuan' => $rowlevelIRO['namasatuan'],
                                                    'output' => $rowlevelIRO['output'],
                                                    'namaoutput' => $rowlevelIRO['namaoutput'],
                                                    'outcome' => $rowlevelIRO['outcome'],
                                                    'namaoutcome' => $rowlevelIRO['namaoutcome'],
                                                    'textindikator_awal' => $rowlevelIRO['textindikator_awal'],
                                                    'satuan_awal' => $rowlevelIRO['satuan_awal'],
                                                    'namasatuan_awal' => $rowlevelIRO['namasatuan_awal'],
                                                    'output_awal' => $rowlevelIRO['output_awal'],
                                                    'namaoutput_awal' => $rowlevelIRO['namaoutput_awal'],
                                                    'outcome_awal' => $rowlevelIRO['outcome_awal'],
                                                    'namaoutcome_awal' => $rowlevelIRO['namaoutcome_awal'],
                                                    'textindikator_akhir' => $rowlevelIRO['textindikator_akhir'],
                                                    'satuan_akhir' => $rowlevelIRO['satuan_akhir'],
                                                    'namasatuan_akhir' => $rowlevelIRO['namasatuan_akhir'],
                                                    'output_akhir' => $rowlevelIRO['output_akhir'],
                                                    'namaoutput_akhir' => $rowlevelIRO['namaoutput_akhir'],
                                                    'outcome_akhir' => $rowlevelIRO['outcome_akhir'],
                                                    'namaoutcome_akhir' => $rowlevelIRO['namaoutcome_akhir'],
                                                    'penanggungjawab' => $rowlevelIRO['penanggungjawab'],
                                                    'pelaksana' => $rowlevelIRO['pelaksana'],
                                                    'pelaksanacode' => $rowlevelIRO['pelaksanacode'],
                                                    'pelaksanalabel' => $rowlevelIRO['pelaksanalabel'],
                                                    'cetak' => $rowlevelIRO['cetak'],
                                                    'cetakcode' => $rowlevelIRO['cetakcode'],
                                                    'cetaklabel' => $rowlevelIRO['cetaklabel'],
                                                    'verif' => $rowlevelIRO['verif'],
                                                    'verifcode' => $rowlevelIRO['verifcode'],
                                                    'veriflabel' => $rowlevelIRO['veriflabel'],
                                                    'verif2' => $rowlevelIRO['verif2'],
                                                    'verif2code' => $rowlevelIRO['verif2code'],
                                                    'verif2label' => $rowlevelIRO['verif2label'],
                                                    'target' => $rowlevelIRO['target'],
                                                    'urut' => (float) $rowlevelIRO['urut'],
                                                    'hitungan_pk' => $rowlevelIRO['hitungan_pk'],
                                                    'namahitungan_pk' => $rowlevelIRO['namahitungan_pk'],
                                                    'kolom1' => $rowlevelIRO['kolom1'],
                                                    'kolom2' => $rowlevelIRO['kolom2'],
                                                    'kolom3' => $rowlevelIRO['kolom3'],
                                                    'kolom4' => $rowlevelIRO['kolom4'],
                                                    'kolom5' => $rowlevelIRO['kolom5'],
                                                    'rumuskolom1' => $rowlevelIRO['rumuskolom1'],
                                                    'rumuskolom2' => $rowlevelIRO['rumuskolom2'],
                                                    'rumuskolom3' => $rowlevelIRO['rumuskolom3'],
                                                    'rumuskolom4' => $rowlevelIRO['rumuskolom4'],
                                                    'rumuskolom5' => $rowlevelIRO['rumuskolom5'],
                                                    'isian_kolom' => $rowlevelIRO['isian_kolom'],
                                                    'jumlahkomponen' => $rowlevelIRO['jumlahkomponen'],
                                                    'kolomkomponen' => $rowlevelIRO['kolomkomponen'],
                                                    'bobotkomponen' => $rowlevelIRO['bobotkomponen'],
                                                    'rumuskomponen' => $rowlevelIRO['rumuskomponen'],
                                                    'belakangkoma' => $rowlevelIRO['belakangkoma'],
                                                    'class' => $rowlevelIRO['class'],
                                                ];
                                            }
                                            // IRO
                                        }
                                        // RO
                                    }
                                    // IKSK
                                }
                                //ISPSK
                            }
                            // SP KEGIATAN
                        }
                        // PROGRAM
                    }
                    // ISS
                }
                // SS
            }
        }
    }

    $sheet->mergeCells('A2:L2');
    $sheet->setCellValue('A2', 'Resume Capaian Indikator ');
    $sheet->getStyle('A2')->getFont()->setBold(true);

    $sheet->mergeCells('A3:A5');
    $sheet->setCellValue('A3', 'TIPE');
    $sheet->getStyle('A3:A5')->getFont()->setBold(true);
    $sheet->getStyle('A3:A5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A3:A5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('A3:A5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('A3:A5')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('A3:A5')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(30);


    $sheet->mergeCells('B3:B5');
    $sheet->setCellValue('B3', 'PROGRAM/SASARAN PROGRAM/INDIKATOR SASARAN PROGRAM/KEGIATAN/SASARAN KEGIATAN/INDIKATOR SASARAN KEGIATAN');
    $sheet->getStyle('B3:B5')->getFont()->setBold(true);
    $sheet->getStyle('B3:B5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('B3:B5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('B3:B5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('B3:B5')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('B3:B5')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(100);
    $sheet->getStyle('B3:B5')->getAlignment()->setWrapText(true);

    $sheet->mergeCells('C3:F3');
    $sheet->setCellValue('C3', 'OUTPUT');
    $sheet->getStyle('C3:F3')->getFont()->setBold(true);
    $sheet->getStyle('C3:F3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('C3:F3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('C3:F3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('C3:F3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('C3:F3')->applyFromArray($styleRangeArray);

    $sheet->mergeCells('C4:D4');
    $sheet->setCellValue('C4', 'Target');
    $sheet->getStyle('C4:D4')->getFont()->setBold(true);
    $sheet->getStyle('C4:D4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('C4:D4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('C4:D4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('C4:D4')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('C4:D4')->applyFromArray($styleRangeArray);

    $sheet->mergeCells('E4:F4');
    $sheet->setCellValue('E4', 'Capaian');
    $sheet->getStyle('E4:F4')->getFont()->setBold(true);
    $sheet->getStyle('E4:F4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E4:F4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('E4:F4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('E4:F4')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('E4:F4')->applyFromArray($styleRangeArray);

    $sheet->setCellValue('C5', 'Volume');
    $sheet->getStyle('C5')->getFont()->setBold(true);
    $sheet->getStyle('C5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('C5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('C5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('C5')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('C5')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);

    $sheet->setCellValue('D5', 'Satuan');
    $sheet->getStyle('D5')->getFont()->setBold(true);
    $sheet->getStyle('D5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('D5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('D5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('D5')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('D5')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);

    $sheet->setCellValue('E5', 'Volume');
    $sheet->getStyle('E5')->getFont()->setBold(true);
    $sheet->getStyle('E5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('E5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('E5')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('E5')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);

    $sheet->setCellValue('F5', 'Satuan');
    $sheet->getStyle('F5')->getFont()->setBold(true);
    $sheet->getStyle('F5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('F5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('F5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('F5')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('F5')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);

    $sheet->mergeCells('G3:G4');
    $sheet->setCellValue('G3', 'KINERJA');
    $sheet->getStyle('G3:G4')->getFont()->setBold(true);
    $sheet->getStyle('G3:G4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('G3:G4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('G3:G4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('G3:G4')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('G3:G4')->applyFromArray($styleRangeArray);

    $sheet->setCellValue('G5', '%');
    $sheet->getStyle('G5')->getFont()->setBold(true);
    $sheet->getStyle('G5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('G5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('G5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('G5')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('G5')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(15);

    ///////////
    $sheet->mergeCells('H3:K3');
    $sheet->setCellValue('H3', 'OUTCOME');
    $sheet->getStyle('H3:K3')->getFont()->setBold(true);
    $sheet->getStyle('H3:K3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('H3:K3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('H3:K3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('H3:K3')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('H3:K3')->applyFromArray($styleRangeArray);

    $sheet->mergeCells('H4:I4');
    $sheet->setCellValue('H4', 'Target');
    $sheet->getStyle('H4:I4')->getFont()->setBold(true);
    $sheet->getStyle('H4:I4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('H4:I4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('H4:I4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('H4:I4')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('H4:I4')->applyFromArray($styleRangeArray);

    $sheet->mergeCells('J4:K4');
    $sheet->setCellValue('J4', 'Capaian');
    $sheet->getStyle('J4:K4')->getFont()->setBold(true);
    $sheet->getStyle('J4:K4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('J4:K4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('J4:K4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('J4:K4')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('J4:K4')->applyFromArray($styleRangeArray);

    $sheet->setCellValue('H5', 'Volume');
    $sheet->getStyle('H5')->getFont()->setBold(true);
    $sheet->getStyle('H5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('H5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('H5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('H5')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('H5')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);

    $sheet->setCellValue('I5', 'Satuan');
    $sheet->getStyle('I5')->getFont()->setBold(true);
    $sheet->getStyle('I5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('I5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('I5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('I5')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('I5')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);

    $sheet->setCellValue('J5', 'Volume');
    $sheet->getStyle('J5')->getFont()->setBold(true);
    $sheet->getStyle('J5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('J5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('J5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('J5')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('J5')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);

    $sheet->setCellValue('K5', 'Satuan');
    $sheet->getStyle('K5')->getFont()->setBold(true);
    $sheet->getStyle('K5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('K5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('K5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('K5')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('K5')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);

    $sheet->mergeCells('L3:L4');
    $sheet->setCellValue('L3', 'KINERJA');
    $sheet->getStyle('L3:L4')->getFont()->setBold(true);
    $sheet->getStyle('L3:L4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('L3:L4')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('L3:L4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('L3:L4')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('L3:L4')->applyFromArray($styleRangeArray);

    $sheet->setCellValue('L5', '%');
    $sheet->getStyle('L5')->getFont()->setBold(true);
    $sheet->getStyle('L5')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('L5')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('L5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('005c95');
    $sheet->getStyle('L5')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
    $sheet->getStyle('L5')->applyFromArray($styleRangeArray);
    $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(15);


    $baris = 6;
    foreach ($myArray as $obj) {
        $warna = "FFFFFF";
        if ($obj->class == 'levelkomponen') {
            $warna = "1E90FF";
        }
        if ($obj->class == 'levelsubkomponen') {
            $warna = "FFD700";
        }
        if ($obj->class == 'levelprogram') {
            $warna = "FFB6C1";
        }
        if ($obj->class == 'levelkegiatan') {
            $warna = "90EE90";
        }
        if ($obj->class == 'levelsubkegiatan') {
            $warna = "B0E0E6";
        }
        if ($obj->class == 'levelpaket') {
            $warna = "F5DEB3";
        }
        if ($obj->class == 'levelakhir') {
            $warna = "D3D3D3";
        }
        $sheet->setCellValue('A' . $baris, $obj->kode_unique);
        $sheet->getStyle('A' . $baris)->getFont()->setBold(true);
        $sheet->getStyle('A' . $baris)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A' . $baris)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($warna);
        $sheet->getStyle('A' . $baris)->applyFromArray($styleRangeArray);

        $sheet->mergeCells('B' . $baris . ':L' . $baris);
        $sheet->setCellValue('B' . $baris, $obj->textindikator);
        $sheet->getStyle('B' . $baris . ':L' . $baris)->getFont()->setBold(true);
        $sheet->getStyle('B' . $baris . ':L' . $baris)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($warna);
        $sheet->getStyle('B' . $baris . ':L' . $baris)->applyFromArray($styleRangeArray);

        $baris++;
        $urut = 1;
        foreach ($obj->balai as $balai) {
            $sheet->setCellValue('A' . $baris, $urut);
            $sheet->getStyle('A' . $baris)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle('A' . $baris)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE);
            $sheet->getStyle('A' . $baris)->getFont()->setBold(true);
            $sheet->getStyle('A' . $baris)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("E0F20D");
            $sheet->getStyle('A' . $baris)->applyFromArray($styleRangeArray);

            $sheet->mergeCells('B' . $baris . ':L' . $baris);
            $sheet->setCellValue('B' . $baris, $balai->balai);
            $sheet->getStyle('B' . $baris . ':L' . $baris)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE);
            $sheet->getStyle('B' . $baris . ':L' . $baris)->getFont()->setBold(true);
            $sheet->getStyle('B' . $baris . ':L' . $baris)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("E0F20D");
            $sheet->getStyle('B' . $baris . ':L' . $baris)->applyFromArray($styleRangeArray);

            $baris++;

            $urutsatker = 1;
            foreach ($balai->satker as $satker) {
                $sheet->setCellValue('A' . $baris, $urut . '-' . $urutsatker);
                $sheet->getStyle('A' . $baris)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle('A' . $baris)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("0DE0F2");
                $sheet->getStyle('A' . $baris)->applyFromArray($styleRangeArray);

                $sheet->setCellValue('B' . $baris, $satker->satker);
                $sheet->getStyle('B' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle('B' . $baris)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("0DE0F2");
                $sheet->getStyle('B' . $baris)->applyFromArray($styleRangeArray);

                if ($obj->hitungan_pk == '1') {
                    $sheet->setCellValue('C' . $baris, replace_comma($satker->volume));
                } else {
                    $sheet->setCellValue('C' . $baris, replace_comma($satker->nilai));
                }
                $sheet->getStyle('C' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle('C' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle('C' . $baris)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("0DE0F2");
                $sheet->getStyle('C' . $baris)->applyFromArray($styleRangeArray);

                $sheet->setCellValue('D' . $baris, str_replace("&#37;", "%", str_replace("</sup>", "", str_replace("<sup>", "", $obj->namaoutput))));
                $sheet->getStyle('D' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle('D' . $baris)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("0DE0F2");
                $sheet->getStyle('D' . $baris)->applyFromArray($styleRangeArray);

                $sheet->setCellValue('E' . $baris, replace_comma($satker->capaian));
                $sheet->getStyle('E' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle('E' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle('E' . $baris)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("0DE0F2");
                $sheet->getStyle('E' . $baris)->applyFromArray($styleRangeArray);

                $sheet->setCellValue('F' . $baris, str_replace("&#37;", "%", str_replace("</sup>", "", str_replace("<sup>", "", $obj->namaoutput))));
                $sheet->getStyle('F' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle('F' . $baris)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("0DE0F2");
                $sheet->getStyle('F' . $baris)->applyFromArray($styleRangeArray);

                $sheet->setCellValue('G' . $baris, replace_comma($satker->kinerja));
                $sheet->getStyle('G' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle('G' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle('G' . $baris)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("0DE0F2");
                $sheet->getStyle('G' . $baris)->applyFromArray($styleRangeArray);

                //////
                if ($obj->hitungan_pk == '1') {
                    $sheet->setCellValue('H' . $baris, replace_comma($satker->target));
                } else {
                    $sheet->setCellValue('H' . $baris, replace_comma($satker->nilai));
                }
                $sheet->getStyle('H' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle('H' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle('H' . $baris)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("0DE0F2");
                $sheet->getStyle('H' . $baris)->applyFromArray($styleRangeArray);

                $sheet->setCellValue('I' . $baris, str_replace("&#37;", "%", str_replace("</sup>", "", str_replace("<sup>", "", $obj->namaoutcome))));
                $sheet->getStyle('I' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle('I' . $baris)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("0DE0F2");
                $sheet->getStyle('I' . $baris)->applyFromArray($styleRangeArray);

                if ($obj->hitungan_pk == '1') {
                    $sheet->setCellValue('J' . $baris, replace_comma($satker->capaian_outcome));
                } else {
                    $sheet->setCellValue('J' . $baris, replace_comma($satker->capaian));
                }
                $sheet->getStyle('J' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle('J' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle('J' . $baris)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("0DE0F2");
                $sheet->getStyle('J' . $baris)->applyFromArray($styleRangeArray);

                $sheet->setCellValue('K' . $baris, str_replace("&#37;", "%", str_replace("</sup>", "", str_replace("<sup>", "", $obj->namaoutcome))));
                $sheet->getStyle('K' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle('K' . $baris)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("0DE0F2");
                $sheet->getStyle('K' . $baris)->applyFromArray($styleRangeArray);

                $sheet->setCellValue('L' . $baris, replace_comma($satker->kinerja_outcome));
                $sheet->getStyle('L' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle('L' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                $sheet->getStyle('L' . $baris)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB("0DE0F2");
                $sheet->getStyle('L' . $baris)->applyFromArray($styleRangeArray);
                $urutsatker++;
                $baris++;

                foreach ($satker->emon as $emon) {
                    $sheet->setCellValue('A' . $baris, $emon->kode_emon);
                    $sheet->getStyle('A' . $baris)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('A' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    $sheet->getStyle('A' . $baris)->applyFromArray($styleRangeArray);

                    $sheet->setCellValue('B' . $baris, $emon->nmpaket);
                    $sheet->getStyle('B' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    $sheet->getStyle('B' . $baris)->applyFromArray($styleRangeArray);

                    $sheet->setCellValue('C' . $baris, replace_comma($emon->target));
                    $sheet->getStyle('C' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    $sheet->getStyle('C' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    $sheet->getStyle('C' . $baris)->applyFromArray($styleRangeArray);

                    $sheet->setCellValue('D' . $baris, str_replace("&#37;", "%", str_replace("</sup>", "", str_replace("<sup>", "", $obj->namaoutput))));
                    $sheet->getStyle('D' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    $sheet->getStyle('D' . $baris)->applyFromArray($styleRangeArray);


                    $sheet->setCellValue('E' . $baris, replace_comma($emon->capaian));
                    $sheet->getStyle('E' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    $sheet->getStyle('E' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    $sheet->getStyle('E' . $baris)->applyFromArray($styleRangeArray);

                    $sheet->setCellValue('F' . $baris, str_replace("&#37;", "%", str_replace("</sup>", "", str_replace("<sup>", "", $obj->namaoutput))));
                    $sheet->getStyle('F' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    $sheet->getStyle('F' . $baris)->applyFromArray($styleRangeArray);

                    $sheet->setCellValue('G' . $baris, replace_comma($emon->kinerja));
                    $sheet->getStyle('G' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    $sheet->getStyle('G' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    $sheet->getStyle('G' . $baris)->applyFromArray($styleRangeArray);

                    $sheet->setCellValue('H' . $baris, replace_comma($emon->outcome));
                    $sheet->getStyle('H' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    $sheet->getStyle('H' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    $sheet->getStyle('H' . $baris)->applyFromArray($styleRangeArray);

                    $sheet->setCellValue('I' . $baris, str_replace("&#37;", "%", str_replace("</sup>", "", str_replace("<sup>", "", $obj->namaoutcome))));
                    $sheet->getStyle('I' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    $sheet->getStyle('I' . $baris)->applyFromArray($styleRangeArray);

                    $sheet->setCellValue('J' . $baris, replace_comma($emon->capaian_outcome));
                    $sheet->getStyle('J' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    $sheet->getStyle('J' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    $sheet->getStyle('J' . $baris)->applyFromArray($styleRangeArray);

                    $sheet->setCellValue('K' . $baris, str_replace("&#37;", "%", str_replace("</sup>", "", str_replace("<sup>", "", $obj->namaoutcome))));
                    $sheet->getStyle('K' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    $sheet->getStyle('K' . $baris)->applyFromArray($styleRangeArray);

                    $sheet->setCellValue('L' . $baris, replace_comma($emon->kinerja_outcome));
                    $sheet->getStyle('L' . $baris)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                    $sheet->getStyle('L' . $baris)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
                    $sheet->getStyle('L' . $baris)->applyFromArray($styleRangeArray);

                    $baris++;
                }
            }
            $urut++;
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

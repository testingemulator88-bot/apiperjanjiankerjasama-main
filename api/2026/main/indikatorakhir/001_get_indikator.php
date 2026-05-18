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
        $tahun = fixup($_GET['tahun']);
        if ($tahun <> '') {
            $filterquery = $filterquery . " and a.tahun = '" . $tahun . "'";
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
        , a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma, a.kumulatif
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
                        'kumulatif' => $row['kumulatif'],
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
                    , a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma, a.kumulatif
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
                            'kumulatif' => $rowlevelSS['kumulatif'],
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
                        , a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma, a.kumulatif
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
                                'kumulatif' => $rowlevelISS['kumulatif'],
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
                            , a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma, a.kumulatif
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
                                    'kumulatif' => $rowlevelPROGRAM['kumulatif'],
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
                                , a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma, a.kumulatif
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
                                        'kumulatif' => $rowlevelSPKEGIATAN['kumulatif'],
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
                                    , a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma, a.kumulatif
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
                                        $myArray[] = (object)
                                        [
                                            'id' => (float) $rowlevelISPSK['id'],
                                            'tahun' => (float) $rowlevelISPSK['tahun'],
                                            'id_parent' => $rowlevelISPSK['id_parent'],
                                            'kdunor' => $rowlevelISPSK['kdunor'],
                                            'nama_unor' => $rowlevelISPSK['nama_unor'],
                                            'urutlevel' => (float) $rowlevelISPSK['urutlevel'],
                                            'level' => $rowlevelISPSK['level'],
                                            'kode' => $rowlevelISPSK['kode'],
                                            'kode_unique' => $rowlevelISPSK['kode_unique'],
                                            'textindikator' => $rowlevelISPSK['textindikator'],
                                            'satuan' => $rowlevelISPSK['satuan'],
                                            'namasatuan' => $rowlevelISPSK['namasatuan'],
                                            'output' => $rowlevelISPSK['output'],
                                            'namaoutput' => $rowlevelISPSK['namaoutput'],
                                            'outcome' => $rowlevelISPSK['outcome'],
                                            'namaoutcome' => $rowlevelISPSK['namaoutcome'],
                                            'textindikator_awal' => $rowlevelISPSK['textindikator_awal'],
                                            'satuan_awal' => $rowlevelISPSK['satuan_awal'],
                                            'namasatuan_awal' => $rowlevelISPSK['namasatuan_awal'],
                                            'output_awal' => $rowlevelISPSK['output_awal'],
                                            'namaoutput_awal' => $rowlevelISPSK['namaoutput_awal'],
                                            'outcome_awal' => $rowlevelISPSK['outcome_awal'],
                                            'namaoutcome_awal' => $rowlevelISPSK['namaoutcome_awal'],
                                            'textindikator_akhir' => $rowlevelISPSK['textindikator_akhir'],
                                            'satuan_akhir' => $rowlevelISPSK['satuan_akhir'],
                                            'namasatuan_akhir' => $rowlevelISPSK['namasatuan_akhir'],
                                            'output_akhir' => $rowlevelISPSK['output_akhir'],
                                            'namaoutput_akhir' => $rowlevelISPSK['namaoutput_akhir'],
                                            'outcome_akhir' => $rowlevelISPSK['outcome_akhir'],
                                            'namaoutcome_akhir' => $rowlevelISPSK['namaoutcome_akhir'],
                                            'penanggungjawab' => $rowlevelISPSK['penanggungjawab'],
                                            'pelaksana' => $rowlevelISPSK['pelaksana'],
                                            'pelaksanacode' => $rowlevelISPSK['pelaksanacode'],
                                            'pelaksanalabel' => $rowlevelISPSK['pelaksanalabel'],
                                            'cetak' => $rowlevelISPSK['cetak'],
                                            'cetakcode' => $rowlevelISPSK['cetakcode'],
                                            'cetaklabel' => $rowlevelISPSK['cetaklabel'],
                                            'verif' => $rowlevelISPSK['verif'],
                                            'verifcode' => $rowlevelISPSK['verifcode'],
                                            'veriflabel' => $rowlevelISPSK['veriflabel'],
                                            'verif2' => $rowlevelISPSK['verif2'],
                                            'verif2code' => $rowlevelISPSK['verif2code'],
                                            'verif2label' => $rowlevelISPSK['verif2label'],
                                            'target' => $rowlevelISPSK['target'],
                                            'urut' => (float) $rowlevelISPSK['urut'],
                                            'hitungan_pk' => $rowlevelISPSK['hitungan_pk'],
                                            'namahitungan_pk' => $rowlevelISPSK['namahitungan_pk'],
                                            'kolom1' => $rowlevelISPSK['kolom1'],
                                            'kolom2' => $rowlevelISPSK['kolom2'],
                                            'kolom3' => $rowlevelISPSK['kolom3'],
                                            'kolom4' => $rowlevelISPSK['kolom4'],
                                            'kolom5' => $rowlevelISPSK['kolom5'],
                                            'rumuskolom1' => $rowlevelISPSK['rumuskolom1'],
                                            'rumuskolom2' => $rowlevelISPSK['rumuskolom2'],
                                            'rumuskolom3' => $rowlevelISPSK['rumuskolom3'],
                                            'rumuskolom4' => $rowlevelISPSK['rumuskolom4'],
                                            'rumuskolom5' => $rowlevelISPSK['rumuskolom5'],
                                            'isian_kolom' => $rowlevelISPSK['isian_kolom'],
                                            'jumlahkomponen' => $rowlevelISPSK['jumlahkomponen'],
                                            'kolomkomponen' => $rowlevelISPSK['kolomkomponen'],
                                            'bobotkomponen' => $rowlevelISPSK['bobotkomponen'],
                                            'rumuskomponen' => $rowlevelISPSK['rumuskomponen'],
                                            'belakangkoma' => $rowlevelISPSK['belakangkoma'],
                                            'kumulatif' => $rowlevelISPSK['kumulatif'],
                                            'class' => $rowlevelISPSK['class'],
                                        ];
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
                                        , a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma, a.kumulatif
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
                                                'kumulatif' => $rowlevelIKSK['kumulatif'],
                                                'class' => $rowlevelIKSK['class'],
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
                                            , a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma, a.kumulatif
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
                                                    'kumulatif' => $rowlevelRO['kumulatif'],
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
                                                , a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma, a.kumulatif
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
                                                        'kumulatif' => $rowlevelIRO['kumulatif'],
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

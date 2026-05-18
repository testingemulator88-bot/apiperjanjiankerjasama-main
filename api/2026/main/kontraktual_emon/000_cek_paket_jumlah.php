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


    $tahun = fixup($_GET['tahun']);
    $filterquery = $filterquery . " and a.tanggaldata = (select MAX(nama) from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "') and YEAR(a.tanggaldata) ='" . $tahun . "'";

    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($tahun <> '')) {
        $myArray = array();
        $myArraysatker = array();
        $tanggalemon = date("Y-m-d H:i:s");
        $sqltanggalemon = "select MAX(nama) as tanggalemon from tb_data_emon where deleted='0' and YEAR(nama) ='" . $tahun . "'";
        //echo $sqltanggalemon;
        //die;

        $resulttanggalemon = mysqli_query($link, $sqltanggalemon);
        while ($rowtanggalemon = mysqli_fetch_assoc($resulttanggalemon)) {
            $tanggalemon = $rowtanggalemon['tanggalemon'];
        }

        $urut = 1;
        $sqldata = "select x.nama_satker,a.kdsatker,a.tahapan_skrg
        ,a.pgrupiah,a.pgpln, a.pgsbsn,a.pg57,a.ket_lanjut
        ,a.pg,a.blokir,a.pfis,a.nkon,a.rkn_nama,a.kdspaket
        ,a.rtot,a.tanggaldata,a.status,a.kode,a.nmpaket
        from d_penyedia a 
        left join master_satker x on a.kdsatker = x.kode_satker_pendek
        where a.nmpaket <> '' and (a.metode = '1' or a.metode = '2' or a.metode = '6' or a.metode = '13')
        and a.kdprogram ='FC' and a.tgl_lelang<>'' and a.tgl_mulai<>'' and a.tgl_selesai<>''
        and a.kdprogram ='FC'
        and (a.kdoutput = 'ABF' or a.kdoutput = 'BMA' or a.kdoutput = 'CBG' or a.kdoutput = 'CBH' or a.kdoutput = 'CBR' or a.kdoutput = 'CBS'
        or a.kdoutput = 'CDH' or a.kdoutput = 'FAD' or a.kdoutput = 'QMA' or a.kdoutput = 'RBG'or a.kdoutput = 'RBH'or a.kdoutput = 'RBR'or a.kdoutput = 'RBS') 
        " . $filterquery . "
        group by a.kode";

        //echo $sqldata;
        //die;
        $resultdata = mysqli_query($link, $sqldata);
        $jumlahtotal = 0;

        while ($rowdata = mysqli_fetch_assoc($resultdata)) {
            if (((float) $rowdata['pg'] - (float) $rowdata['blokir'] > 0)) {
                if ((float) $rowdata['pg57'] == 0) {
                    $tempkode = explode(".", $rowdata['kode']);
                    if (count($tempkode) > 8) {
                        $myArray[] = (object)
                        [
                            'urut' => $urut,
                            'nama' => $rowdata['nama_satker'],
                            'kdsatker' => $rowdata['kdsatker'],
                            'kode' => $rowdata['kode'],
                            'nmpaket' => $rowdata['nmpaket'],
                            'class'  => '',
                        ];
                    }
                } else {
                    $myArray[] = (object)
                    [
                        'urut' => $urut,
                        'nama' => $rowdata['nama_satker'],
                        'kdsatker' => $rowdata['kdsatker'],
                        'kode' => $rowdata['kode'],
                        'nmpaket' => $rowdata['nmpaket'],
                        'class'  => '',
                    ];
                }
                $urut++;
            } else {
                if (
                    strstr($rowdata['tahapan_skrg'], 'Tender Sudah Selesai') && ($rowdata['status'] == 'Sudah Penetapan Pemenang')
                    && ((float) $rowdata['pg'] - (float) $rowdata['blokir'] == 0) && ($rowdata['rkn_nama'] <> '')
                ) {
                    $myArray[] = (object)
                    [
                        'urut' => $urut,
                        'nama' => $rowdata['nama_satker'],
                        'kdsatker' => $rowdata['kdsatker'],
                        'kode' => $rowdata['kode'],
                        'nmpaket' => $rowdata['nmpaket'],
                        'class'  => '',
                    ];
                }
                if ($rowdata['status'] == 'Sudah Penetapan Pemenang' && ((strstr($rowdata['tahapan_skrg'], 'Prakualifikasi')
                    || strstr($rowdata['tahapan_skrg'], 'Masa Sanggah') || strstr($rowdata['tahapan_skrg'], 'Berita Acara Hasil Pelelangan') || $rowdata['tahapan_skrg'] == '')) && ((float) $rowdata['pfis'] > 0)) {
                    if (($rowdata['rkn_nama'] <> '') && (strstr($rowdata['tahapan_skrg'], 'Berita Acara Hasil Pelelangan') || strstr($rowdata['tahapan_skrg'], 'Tender Sudah Selesai'))) {
                        $myArray[] = (object)
                        [
                            'urut' => $urut,
                            'nama' => $rowdata['nama_satker'],
                            'kdsatker' => $rowdata['kdsatker'],
                            'kode' => $rowdata['kode'],
                            'nmpaket' => $rowdata['nmpaket'],
                            'class'  => '',
                        ];
                    } else if (($rowdata['rkn_nama'] == '') && (strstr($rowdata['tahapan_skrg'], 'Berita Acara Hasil Pelelangan')
                            || strstr($rowdata['tahapan_skrg'], 'Prakualifikasi')) && ($rowdata['kdspaket'] <> '')
                        && $rowdata['status'] == 'Sudah Penetapan Pemenang'
                    ) {
                        $myArray[] = (object)
                        [
                            'urut' => $urut,
                            'nama' => $rowdata['nama_satker'],
                            'kdsatker' => $rowdata['kdsatker'],
                            'kode' => $rowdata['kode'],
                            'nmpaket' => $rowdata['nmpaket'],
                            'class'  => '',
                        ];
                    } else if (((float) $rowdata['pg'] - (float) $rowdata['blokir'] == 0)
                        && $rowdata['status'] == 'Sudah Penetapan Pemenang' && $rowdata['tahapan_skrg'] == ''
                    ) {
                        $myArray[] = (object)
                        [
                            'urut' => $urut,
                            'nama' => $rowdata['nama_satker'],
                            'kdsatker' => $rowdata['kdsatker'],
                            'kode' => $rowdata['kode'],
                            'nmpaket' => $rowdata['nmpaket'],
                            'class'  => '',
                        ];
                    } else if (((float) $rowdata['pg'] - (float) $rowdata['blokir'] == 0)
                        && $rowdata['status'] == 'Sudah Penetapan Pemenang' && $rowdata['tahapan_skrg'] == 'Masa Sanggah'
                        && (!strstr(strtoupper($rowdata['ket_lanjut']), 'BLOKIR'))
                    ) {
                        $myArray[] = (object)
                        [
                            'urut' => $urut,
                            'nama' => $rowdata['nama_satker'],
                            'kdsatker' => $rowdata['kdsatker'],
                            'kode' => $rowdata['kode'],
                            'nmpaket' => $rowdata['nmpaket'],
                            'class'  => '',
                        ];
                    }
                }
            }
        }
        //die;
        foreach ($myArray as $satker) {
            //echo $satker->kdsatker;
            $cekarray = array_column($myArraysatker, 'kdsatker');
            if (in_array($satker->kdsatker, $cekarray)) {
                $jumlah = $jumlah++;
                foreach ($myArraysatker as $satkernya) {
                    if ($satkernya->kdsatker === $satker->kdsatker) {
                        $satkernya->jumlah = $satkernya->jumlah + 1; // New price
                        break; // Exit loop after finding and updating
                    }
                }
            } else {
                $jumlah = 1;
                //echo "tidal";
                $myArraysatker[] = (object)
                [
                    'kdsatker' => $satker->kdsatker,
                    'nama' => $satker->nama,
                    'jumlah' => $jumlah,
                    'class'  => '',
                ];
                //echo "The value '{$searchValue}' does not exist in the '{$propertyName}' property of any object in the array.";
            }
        }

        usort($myArraysatker, fn($a, $b) => strcmp($a->kdsatker, $b->kdsatker));

        $response         = [];
        $response['tanggalemon'] =  $tanggalemon;
        $response['data'] =  $myArraysatker;
        mysqli_close($link);
        echo json_encode(array('response' => 'success', 'message' => 'data diketemukan', 'content' => $response), JSON_PRETTY_PRINT);
        die;
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

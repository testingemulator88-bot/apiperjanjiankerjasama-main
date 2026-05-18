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
//echo $Bearer;
//die;

$sqlcek = "select id as jumlah from tb_user where token='" . str_replace('"', '', $tempBearer[1]) . "' and deleted='0'";
//echo $sqlcek;
//die;
$resultsqlcek = mysqli_query($link, $sqlcek);
$numcek = mysqli_num_rows($resultsqlcek);
if ($numcek > 0) {
    $kduser=fixup($_GET['kduser']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($kduser <> '')) {
        $myArray = array();
        $sql = " select a.id,a.username,md5(a.id) as md5nya, a.nama, a.level as level,a.password
        , a.levelcode, a.levellabel, a.hakaksesevaluatorlke, a.hakaksesevaluatorlkecode, a.hakaksesevaluatorlkelabel
        , b.nama as namaleveluser,a.unor,e.nama_kategori as nama_unor, a.kategori_satker, c.nama_kategori as nama_kategori_satker
        , a.satker,a.kode_satker, d.nama_satker,d.status_piu,d.level_piu,c.level_piu as level_piu_balai
        , a.jabatan,a.pangkat,a.nip, a.notelp, a.alamat, a.email,a.jenis_piu, i.nama as namajenis_piu
        , a.verif,a.verifcode,a.veriflabel
        , a.verif2,a.verif2code,a.verif2label
        , a.verif3,a.verif3code,a.verif3label
        , null as evaluasi
        ,ifnull(a.ikon,'3177440.png') as foto
        from tb_user a 
        left join master_level b
        on a.level=b.id
        left join master_unor e
        on a.unor=e.id
        left join master_kategori_satker c
        on a.kategori_satker=c.id
        left join master_satker d
        on a.satker = d.id
        left join master_level_piu i
        on a.jenis_piu=i.id
        where a.deleted='0' 
        and a.id='" . $kduser . "'
        order by a.id asc ";
        //echo $sql;
        //die();
        $result = mysqli_query($link, $sql);
        $num = mysqli_num_rows($result);
        if ($num > 0) {
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $myArray[] = (object)
                    [
                        'id' => $row['id'],
                        'unor' => $row['unor'],
                        'nama_unor' => $row['nama_unor'],
                        'kategori_satker' => $row['kategori_satker'],
                        'nama_kategori_satker' => $row['nama_kategori_satker'],
                        'level_piu_balai' => $row['level_piu_balai'],
                        'satker' => $row['satker'],
                        'kode_satker' => $row['kode_satker'],
                        'nama_satker' => $row['nama_satker'],
                        'status_piu' => $row['status_piu'],
                        'level_piu' => $row['level_piu'],
                        'nama' => $row['nama'],
                        'username' => $row['username'],
                        'password' => decrypt($row['password']),
                        'level' => $row['level'],
                        'levelcode' => $row['levelcode'],
                        'levellabel' => $row['levellabel'],
                        'hakaksesevaluatorlke' => $row['hakaksesevaluatorlke'],
                        'hakaksesevaluatorlkecode' => $row['hakaksesevaluatorlkecode'],
                        'hakaksesevaluatorlkelabel' => $row['hakaksesevaluatorlkelabel'],
                        'namaleveluser' => $row['namaleveluser'],
                        'verif' => $row['verif'],
                        'veriflabel' => $row['veriflabel'],
                        'verifcode' => $row['verifcode'],
                        'verif2' => $row['verif2'],
                        'verif2label' => $row['verif2label'],
                        'verif2code' => $row['verif2code'],
                        'verif3' => $row['verif3'],
                        'verif3label' => $row['verif3label'],
                        'verif3code' => $row['verif3code'],
                        'evaluasi' => $row['evaluasi'],
                        'jenis_piu' => $row['jenis_piu'],
                        'namajenis_piu' => $row['namajenis_piu'],
                        'jabatan' => $row['jabatan'],
                        'pangkat' => $row['pangkat'],
                        'nip' => $row['nip'],
                        'notelp' => $row['notelp'],
                        'alamat' => $row['alamat'],
                        'email' => $row['email'],
                        'foto' => $row['foto'],
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

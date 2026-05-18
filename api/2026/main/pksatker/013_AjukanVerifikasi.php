<?php
include '../../library/config.php';
error_reporting(0);
check_injection();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
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
$resultsqlcek = mysqli_query($link, $sqlcek);
$numcek = mysqli_num_rows($resultsqlcek);
if ($numcek > 0) {
    $dari = fixup($_POST['dari']);
    $kepada = fixup($_POST['kepada']);
    $kepada2 = fixup($_POST['kepada2']);
    $evaluasi = fixup($_POST['evaluasi']);

    $kode_tahun = fixup($_POST['kode_tahun']);
    $tahun = get_Isi_Field1('tahun','master_pk','id','2');
    $id_pelaksana = fixup($_POST['id_pelaksana']);
    $kode_pelaksana = fixup($_POST['kode_pelaksana']);
    $verifikasi_ke = fixup($_POST['verifikasi_ke']);
    $updateby = fixup($_POST['updateby']);
    if (($Bearer <> '') && ($tempBearer[1] <> '') && ($kode_pelaksana <> '')) {

        $sqljumlahverif = "select verif,verif2,verif3 from tb_user where deleted='0' and jenis_piu = '" . $id_pelaksana . "'
        and kode_satker = '" . $kode_pelaksana . "'";
        $resultjumlahverif = mysqli_query($link, $sqljumlahverif);
        while ($rowjumlahverif = mysqli_fetch_assoc($resultjumlahverif)) {
            $jumlahtotalverifikasi = (float) $rowjumlahverif['id_parent'];
            $verif = $rowjumlahverif['verif'];
            $verif2 = $rowjumlahverif['verif2'];
            $verif3 = $rowjumlahverif['verif3'];
        }

        $sqlpkaktif = "select id from master_pk where deleted = '0' and is_active = '1'";
        $resultpkaktif  = mysqli_query($link, $sqlpkaktif);
        while ($rowpkaktif  = mysqli_fetch_assoc($resultpkaktif)) {
            $pk_aktif = $rowpkaktif['id'];
        }
        $pk_aktif = '2';
        $id = '';
        $sqlnya = "select max(id) as idnya, verifikasi_ke, verifikasi_ke
        from tb_data_pk_verifikasi a 
        where a.deleted='0' and a.id_pelaksana = '" . $id_pelaksana . "'
        and a.kode_pelaksana = '" . $kode_pelaksana . "' and a.kode_tahun = '" . $kode_tahun . "'
        and a.jenis_pk = '" . $pk_aktif . "'
        order by a.id ASC ";
        //echo $sqlnya;
        //die;
        $resultnya = mysqli_query($link, $sqlnya);
        $numcekverifikasi_ke = mysqli_num_rows($resultnya);
        while ($rownya = mysqli_fetch_assoc($resultnya)) {
            $id = $rownya['idnya'];
            $cekverifikasi_ke = $rownya['verifikasi_ke'];
            if ((float) $verifikasi_ke > (float) $cekverifikasi_ke) {
                $verifikasi_ke = $verifikasi_ke;
            } else {
                $verifikasi_ke = $cekverifikasi_ke;
            }
        }

        if ($id <> "") {
            $sqlnya2 = "select * from tb_data_pk_verifikasi a where a.deleted='0' and a.id = '" . $id . "'
            order by a.id ASC ";
            $resultnya2 = mysqli_query($link, $sqlnya2);
            while ($rownya2 = mysqli_fetch_assoc($resultnya2)) {
                $hasil_verif = $rownya2['hasil_verif'];
            }

            $status_ajuan = "Pengajuan PK ke-" . $verifikasi_ke;
            $status_verifikasi = "Proses Verifikasi PK ke-" . $verifikasi_ke;
            if ($hasil_verif == 1) {
                $tempkepada = explode(",", $kepada2);
                for ($i = 0; $i < count($tempkepada); $i++) {
                    $sql = " insert into tb_data_pk_verifikasi (id, dari, kepada, kepada2, evaluasi, kode_tahun, tahun
                    , id_pelaksana, kode_pelaksana, verifikasi_ke, readed, status_ajuan, status_verifikasi, jenis_pk
                    , createddate, createdby, deleted) VALUES (null, '" . $dari . "', '" . $verif2 . "', '0', '" . $evaluasi . "'
                    , '" . $kode_tahun . "', '" . $tahun . "', '" . $id_pelaksana . "', '" . $kode_pelaksana . "', '" . $verifikasi_ke . "'
                    ,'0', '" . $status_ajuan . "', '" . $status_verifikasi . "', '" . $pk_aktif . "',  SYSDATE(), '" . $updateby . "','0')";
                    $result = mysqli_query($link, $sql);
                }
            } else {
                if ($verifikasi_ke > 1) {
                    $tempkepada = explode(",", $kepada2);
                    if ($dari == '2' && $kepada2 == '42' && $verifikasi_ke == '2') {
                        $sql = " insert into tb_data_pk_verifikasi (id, dari, kepada, kepada2, evaluasi, kode_tahun, tahun
                        , id_pelaksana, kode_pelaksana, verifikasi_ke, readed, status_ajuan, status_verifikasi, jenis_pk
                        , createddate, createdby, deleted) VALUES (null, '" . $dari . "', '" . $kepada2 . "', '0', '" . $evaluasi . "'
                        , '" . $kode_tahun . "', '" . $tahun . "', '" . $id_pelaksana . "', '" . $kode_pelaksana . "', '" . $verifikasi_ke . "'
                        ,'0', '" . $status_ajuan . "', '" . $status_verifikasi . "', '" . $pk_aktif . "',  SYSDATE(), '" . $updateby . "','0')";
                    $result = mysqli_query($link, $sql);
                    } else {
                        $sql = " insert into tb_data_pk_verifikasi (id, dari, kepada, kepada2, evaluasi, kode_tahun, tahun
                        , id_pelaksana, kode_pelaksana, verifikasi_ke, readed, status_ajuan, status_verifikasi, jenis_pk
                        , createddate, createdby, deleted) VALUES (null, '" . $dari . "', '" . $verif2 . "', '0', '" . $evaluasi . "'
                        , '" . $kode_tahun . "', '" . $tahun . "', '" . $id_pelaksana . "', '" . $kode_pelaksana . "', '" . $verifikasi_ke . "'
                        ,'0', '" . $status_ajuan . "', '" . $status_verifikasi . "', '" . $pk_aktif . "',  SYSDATE(), '" . $updateby . "','0')";
                        $result = mysqli_query($link, $sql);
                    }
                } else {
                    $tempkepada = explode(",", $kepada);
                    $sql = " insert into tb_data_pk_verifikasi (id, dari, kepada, kepada2, evaluasi, kode_tahun, tahun
                        , id_pelaksana, kode_pelaksana, verifikasi_ke, readed, status_ajuan, status_verifikasi, jenis_pk
                        , createddate, createdby, deleted) VALUES (null, '" . $dari . "', '" . $verif . "', '0', '" . $evaluasi . "'
                        , '" . $kode_tahun . "', '" . $tahun . "', '" . $id_pelaksana . "', '" . $kode_pelaksana . "', '" . $verifikasi_ke . "'
                        ,'0', '" . $status_ajuan . "', '" . $status_verifikasi . "', '" . $pk_aktif . "',  SYSDATE(), '" . $updateby . "','0')";
                    $result = mysqli_query($link, $sql);
                }
            }
        } else {
            $tempkepada = explode(",", $kepada);
            for ($i = 0; $i < count($tempkepada); $i++) {
                $status_ajuan = "Pengajuan PK ke-" . $verifikasi_ke;
                $status_verifikasi = "Proses Verifikasi PK ke-" . $verifikasi_ke;
                $sql = " insert into tb_data_pk_verifikasi (id, dari, kepada, kepada2, evaluasi, kode_tahun, tahun
                , id_pelaksana, kode_pelaksana, verifikasi_ke, readed, status_ajuan, status_verifikasi, jenis_pk
                , createddate, createdby, deleted) VALUES (null, '" . $dari . "', '" . $verif . "', '" . $verif2 . "', '" . $evaluasi . "'
                , '" . $kode_tahun . "', '" . $tahun . "', '" . $dari . "', '" . $kode_pelaksana . "', '" . $verifikasi_ke . "'
                ,'0', '" . $status_ajuan . "', '" . $status_verifikasi . "', '" . $pk_aktif . "',  SYSDATE(), '" . $updateby . "','0')";
                $result = mysqli_query($link, $sql);
            }
        }
        //echo $sql;
        //die;


        if ($result) {
            mysqli_close($link);
            echo json_encode(array('response' => 'success', 'message' => 'Data Telah Diupdate'));
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
} else {
    mysqli_close($link);
    echo json_encode(array('response' => 'error', 'message' => 'Autentikasi tidak valid'));
    die;
}

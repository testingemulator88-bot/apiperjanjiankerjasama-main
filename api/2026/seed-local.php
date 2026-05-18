#!/usr/bin/env php
<?php
declare(strict_types=1);

$host = '127.0.0.1';
$port = 3307;
$user = 'root';
$pass = '';
$dbName = 'pars9694_monika_2026';

function connectMySql(string $host, int $port, string $user, string $pass, ?string $dbName = null): mysqli
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $dbName = $dbName ?? '';
    $link = @new mysqli($host, $user, $pass, $dbName, $port);
    $link->set_charset('utf8mb4');
    return $link;
}

function runSql(mysqli $link, string $sql): void
{
    if (!$link->query($sql)) {
        throw new RuntimeException($link->error . ' while executing: ' . $sql);
    }
}

function encryptLocal(string $str): string
{
    $kunci = 'AIzaSyDsiwLbEcjMOzXceMQ7-vJh21icjaHmlJE"';
    $hasil = '';
    $length = strlen($kunci);

    for ($i = 0; $i < strlen($str); $i++) {
        $karakter = substr($str, $i, 1);
        $kuncikarakter = substr($kunci, ($i % $length) - 1, 1);
        $karakter = chr(ord($karakter) + ord($kuncikarakter));
        $hasil .= $karakter;
    }

    return urlencode(base64_encode($hasil));
}

try {
    $server = connectMySql($host, $port, $user, $pass, null);
    runSql($server, "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $server->close();

    $db = connectMySql($host, $port, $user, $pass, $dbName);

    $schemaStatements = [
        "CREATE TABLE IF NOT EXISTS master_level (
            id INT UNSIGNED NOT NULL PRIMARY KEY,
            nama VARCHAR(100) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS master_unor (
            id INT UNSIGNED NOT NULL PRIMARY KEY,
            nama_kategori VARCHAR(150) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS master_level_piu (
            id INT UNSIGNED NOT NULL PRIMARY KEY,
            nama VARCHAR(150) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS master_pk (
            id INT UNSIGNED NOT NULL PRIMARY KEY,
            tahun VARCHAR(10) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS master_kategori_satker (
            id INT UNSIGNED NOT NULL PRIMARY KEY,
            nama_kategori VARCHAR(150) NOT NULL,
            level_piu INT DEFAULT 0,
            deleted TINYINT(1) NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS master_satker (
            id INT UNSIGNED NOT NULL PRIMARY KEY,
            kode_satker VARCHAR(20) NOT NULL UNIQUE,
            kdbalai VARCHAR(10) NOT NULL,
            nama_satker VARCHAR(255) NOT NULL,
            status_piu VARCHAR(50) DEFAULT NULL,
            level_piu INT DEFAULT 0,
            deleted TINYINT(1) NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS ztable_detail_log (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(150) NOT NULL,
            ipnya VARCHAR(100) NOT NULL,
            tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS tb_user (
            id INT UNSIGNED NOT NULL PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            nama VARCHAR(150) NOT NULL,
            level INT NOT NULL,
            levelcode VARCHAR(50) DEFAULT NULL,
            levellabel VARCHAR(100) DEFAULT NULL,
            unor INT DEFAULT NULL,
            kategori_satker INT DEFAULT NULL,
            satker INT DEFAULT NULL,
            kode_satker VARCHAR(20) DEFAULT NULL,
            jabatan VARCHAR(150) DEFAULT NULL,
            pangkat VARCHAR(150) DEFAULT NULL,
            nip VARCHAR(50) DEFAULT NULL,
            notelp VARCHAR(50) DEFAULT NULL,
            alamat TEXT DEFAULT NULL,
            email VARCHAR(150) DEFAULT NULL,
            evaluasi VARCHAR(50) DEFAULT NULL,
            evaluasicode VARCHAR(50) DEFAULT NULL,
            evaluasilabel VARCHAR(100) DEFAULT NULL,
            verif VARCHAR(50) DEFAULT NULL,
            verifcode VARCHAR(50) DEFAULT NULL,
            veriflabel VARCHAR(100) DEFAULT NULL,
            verif2 VARCHAR(50) DEFAULT NULL,
            verif2code VARCHAR(50) DEFAULT NULL,
            verif2label VARCHAR(100) DEFAULT NULL,
            verif3 VARCHAR(50) DEFAULT NULL,
            verif3code VARCHAR(50) DEFAULT NULL,
            verif3label VARCHAR(100) DEFAULT NULL,
            jenis_piu INT DEFAULT NULL,
            hakaksesevaluatorlke TEXT DEFAULT NULL,
            hakaksesevaluatorlkecode TEXT DEFAULT NULL,
            hakaksesevaluatorlkelabel TEXT DEFAULT NULL,
            token VARCHAR(255) NOT NULL UNIQUE,
            ikon VARCHAR(255) DEFAULT '3177440.png',
            createddate DATETIME DEFAULT CURRENT_TIMESTAMP,
            createdby VARCHAR(100) DEFAULT NULL,
            deleted TINYINT(1) NOT NULL DEFAULT 0
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS komponen (
            id_komponen INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            kode_komponen VARCHAR(50) DEFAULT NULL,
            nama_komponen VARCHAR(255) NOT NULL,
            bobot_komponen DECIMAL(10,4) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS subkomponen (
            id_subkomponen INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_komponen INT UNSIGNED NOT NULL,
            kode_subkomponen VARCHAR(50) DEFAULT NULL,
            nama_subkomponen VARCHAR(255) NOT NULL,
            bobot_subkomponen DECIMAL(10,4) NOT NULL DEFAULT 0,
            deskripsi_rekomendasi TEXT DEFAULT NULL,
            status_tindak_lanjut VARCHAR(50) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS kriteria_penilaian (
            id_kriteria INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_subkomponen INT UNSIGNED NOT NULL,
            kode_kriteria VARCHAR(50) DEFAULT NULL,
            nama_kriteria VARCHAR(255) NOT NULL,
            langkah_kerja TEXT NOT NULL,
            daftar_evidence TEXT DEFAULT NULL,
            deskripsi_rekomendasi TEXT DEFAULT NULL,
            status_tindak_lanjut VARCHAR(50) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS pilihan_jawaban (
            id_jawaban INT UNSIGNED NOT NULL PRIMARY KEY,
            pilihan_jawaban VARCHAR(20) NOT NULL,
            nilai DECIMAL(10,4) NOT NULL DEFAULT 0,
            penjelasan_jawaban VARCHAR(255) DEFAULT NULL,
            kategori_jawaban VARCHAR(100) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS hasil_lke (
            id_hasil_lke INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_kriteria INT UNSIGNED NOT NULL,
            id_user INT UNSIGNED NOT NULL,
            kode_satker VARCHAR(20) NOT NULL,
            level INT NOT NULL,
            tahun VARCHAR(10) NOT NULL,
            kriteria_penilaian1 DECIMAL(10,4) NOT NULL DEFAULT 0,
            kriteria_penilaian2 DECIMAL(10,4) NOT NULL DEFAULT 0,
            kriteria_penilaian3 DECIMAL(10,4) NOT NULL DEFAULT 0,
            kriteria_penilaian4 DECIMAL(10,4) NOT NULL DEFAULT 0,
            catatan_evaluator TEXT DEFAULT NULL,
            tanggapan_evaluator TEXT DEFAULT NULL,
            deskripsi_rekomendasi TEXT DEFAULT NULL,
            status_tindak_lanjut VARCHAR(50) DEFAULT NULL,
            status_lke VARCHAR(10) DEFAULT NULL,
            upload_dok_satker TEXT DEFAULT NULL,
            nama_file_dok TEXT DEFAULT NULL,
            jawaban_satker TEXT DEFAULT NULL,
            link_bukti_dukung TEXT DEFAULT NULL,
            tanggal_evaluasi DATETIME DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS data_ttd_lke (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            kode_satker VARCHAR(20) NOT NULL,
            tahun VARCHAR(10) NOT NULL,
            tempat_ttd VARCHAR(150) DEFAULT NULL,
            tgl_surat DATE DEFAULT NULL,
            nip_pejabat VARCHAR(50) DEFAULT NULL,
            nama_pejabat VARCHAR(150) DEFAULT NULL,
            jabatan VARCHAR(150) DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS evaluatan (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            kode_satker VARCHAR(20) NOT NULL,
            level INT NOT NULL,
            tim VARCHAR(150) DEFAULT NULL,
            nip VARCHAR(50) DEFAULT NULL,
            jabatan VARCHAR(150) DEFAULT NULL,
            wilayah VARCHAR(150) DEFAULT NULL,
            tahun VARCHAR(10) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        "CREATE TABLE IF NOT EXISTS evaluator (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            kode_satker VARCHAR(20) NOT NULL,
            tim VARCHAR(150) DEFAULT NULL,
            nip VARCHAR(50) DEFAULT NULL,
            jabatan VARCHAR(150) DEFAULT NULL,
            wilayah VARCHAR(150) DEFAULT NULL,
            tahun_evaluasi VARCHAR(10) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    ];

    foreach ($schemaStatements as $sql) {
        runSql($db, $sql);
    }

    $demoPassword = encryptLocal('Monika2026!');

    $seedStatements = [
        "INSERT IGNORE INTO master_level (id, nama) VALUES
            (1, 'Administrator'),
            (2, 'Evaluator Pusat'),
            (5, 'Satker Operator'),
            (7, 'Satker'),
            (8, 'Evaluator Lapangan'),
            (9, 'Reviewer')",
        "INSERT IGNORE INTO master_unor (id, nama_kategori) VALUES
            (1, 'UNOR Demo')",
        "INSERT IGNORE INTO master_level_piu (id, nama) VALUES
            (6, 'PIU Balai'),
            (9, 'PIU Pusat'),
            (21, 'Pusat'),
            (31, 'Pusat 31')",
        "INSERT IGNORE INTO master_pk (id, tahun) VALUES
            (1, '2025'),
            (2, '2026'),
            (3, '2027')",
        "INSERT IGNORE INTO master_kategori_satker (id, nama_kategori, level_piu, deleted) VALUES
            (1, 'Demo Balai', 21, 0)",
        "INSERT IGNORE INTO master_satker (id, kode_satker, kdbalai, nama_satker, status_piu, level_piu, deleted) VALUES
            (1, '01010101', '01', 'SATKER DEMO', 'AKTIF', 21, 0)",
        "INSERT IGNORE INTO tb_user
            (id, username, password, nama, level, levelcode, levellabel, unor, kategori_satker, satker, kode_satker, jabatan, pangkat, nip, notelp, alamat, email, evaluasi, evaluasicode, evaluasilabel, verif, verifcode, veriflabel, verif2, verif2code, verif2label, verif3, verif3code, verif3label, jenis_piu, hakaksesevaluatorlke, hakaksesevaluatorlkecode, hakaksesevaluatorlkelabel, token, ikon, deleted)
            VALUES
            (1, 'admin.demo', '{$demoPassword}', 'Admin Demo', 1, '1', 'Administrator', 1, 1, 1, '01010101', 'Administrator', '-', '-', '-', '-', 'admin.demo@sisda.local', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 21, '', '', '', 'demo-token-admin', '3177440.png', 0),
            (2, 'satker.demo', '{$demoPassword}', 'Satker Demo', 7, '7', 'Satker', 1, 1, 1, '01010101', 'Operator Satker', '-', '-', '-', '-', 'satker.demo@sisda.local', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 21, '', '', '', 'demo-token-satker', '3177440.png', 0),
            (3, 'evaluator.demo', '{$demoPassword}', 'Evaluator Demo', 2, '2', 'Evaluator Pusat', 1, 1, 1, '01010101', 'Evaluator', '-', '-', '-', '-', 'evaluator.demo@sisda.local', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', 21, '01010101', '01010101', 'Demo Access', 'demo-token-evaluator', '3177440.png', 0)",
        "INSERT IGNORE INTO komponen (id_komponen, kode_komponen, nama_komponen, bobot_komponen, is_active) VALUES
            (1, 'K1', 'Komponen Demo', 1.0000, 1)",
        "INSERT IGNORE INTO subkomponen (id_subkomponen, id_komponen, kode_subkomponen, nama_subkomponen, bobot_subkomponen, deskripsi_rekomendasi, status_tindak_lanjut, is_active) VALUES
            (1, 1, 'SK1', 'Sub Komponen Demo', 1.0000, 'Rekomendasi demo', '0', 1)",
        "INSERT IGNORE INTO kriteria_penilaian (id_kriteria, id_subkomponen, kode_kriteria, nama_kriteria, langkah_kerja, daftar_evidence, deskripsi_rekomendasi, status_tindak_lanjut, is_active) VALUES
            (1, 1, 'KR1', 'Kriteria Demo', 'Langkah kerja demo', 'Evidence demo', 'Rekomendasi demo', '0', 1)",
        "INSERT IGNORE INTO pilihan_jawaban (id_jawaban, pilihan_jawaban, nilai, penjelasan_jawaban, kategori_jawaban, is_active) VALUES
            (1, 'AA', 100.0000, 'Sangat Baik', 'A', 1),
            (2, 'A', 90.0000, 'Baik Sekali', 'A', 1),
            (3, 'BB', 80.0000, 'Baik', 'B', 1),
            (4, 'B', 70.0000, 'Cukup Baik', 'B', 1),
            (5, 'CC', 60.0000, 'Cukup', 'C', 1),
            (6, 'C', 50.0000, 'Kurang', 'C', 1),
            (7, 'D', 30.0000, 'Buruk', 'D', 1),
            (8, 'E', 0.0000, 'Sangat Buruk', 'E', 1)",
        "INSERT IGNORE INTO hasil_lke
            (id_hasil_lke, id_kriteria, id_user, kode_satker, level, tahun, kriteria_penilaian1, kriteria_penilaian2, kriteria_penilaian3, kriteria_penilaian4, catatan_evaluator, tanggapan_evaluator, deskripsi_rekomendasi, status_tindak_lanjut, status_lke, upload_dok_satker, nama_file_dok, jawaban_satker, link_bukti_dukung, tanggal_evaluasi, is_active)
            VALUES
            (1, 1, 2, '01010101', 7, '2026', 0, 0, 0, 0, '-', '-', '-', '-', '1', '', '', '', '', NULL, 1)",
        "INSERT IGNORE INTO data_ttd_lke (id, kode_satker, tahun, tempat_ttd, tgl_surat, nip_pejabat, nama_pejabat, jabatan, is_active) VALUES
            (1, '01010101', '2026', 'Jakarta', '2026-01-01', '198001012006041001', 'Pejabat Demo', 'Kepala Demo', 1)",
        "INSERT IGNORE INTO evaluator (id, kode_satker, tim, nip, jabatan, wilayah, tahun_evaluasi) VALUES
            (1, '01010101', 'Tim Demo', '198001012006041001', 'Evaluator Demo', 'Demo Balai', '2026')",
        "INSERT IGNORE INTO evaluatan (id, kode_satker, level, tim, nip, jabatan, wilayah, tahun) VALUES
            (1, '01010101', 7, 'Tim Demo', '198001012006041001', 'Evaluator Demo', 'Demo Balai', '2026')",
        "INSERT IGNORE INTO ztable_detail_log (id, nama, ipnya, tanggal) VALUES
            (1, 'admin.demo', '127.0.0.1', NOW())",
    ];

    foreach ($seedStatements as $sql) {
        runSql($db, $sql);
    }

    $tables = [
        'master_level',
        'master_unor',
        'master_level_piu',
        'master_pk',
        'master_kategori_satker',
        'master_satker',
        'tb_user',
        'komponen',
        'subkomponen',
        'kriteria_penilaian',
        'pilihan_jawaban',
        'hasil_lke',
        'data_ttd_lke',
        'evaluatan',
        'evaluator',
        'ztable_detail_log',
    ];

    echo "Seed complete for database: {$dbName}" . PHP_EOL;
    foreach ($tables as $table) {
        $result = $db->query("SELECT COUNT(*) AS total FROM `{$table}`");
        $row = $result ? $result->fetch_assoc() : ['total' => 0];
        echo sprintf("%s: %d", $table, (int) $row['total']) . PHP_EOL;
    }

    $db->close();
} catch (Throwable $e) {
    fwrite(STDERR, '[ERROR] ' . $e->getMessage() . PHP_EOL);
    exit(1);
}

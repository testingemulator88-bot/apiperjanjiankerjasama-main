<?php
error_reporting(0);
//phpinfo();
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include 'library/config.php';
date_default_timezone_set("Asia/Jakarta");
check_injection();
?>
<!doctype html>
<title>App</title>
<style>
    body {
        text-align: center;
        padding: 150px;
    }

    h1 {
        font-size: 50px;
    }

    body {
        font: 20px Helvetica, sans-serif;
        color: #333;
        height: 250px;
        background-color: #1fc8db;
        background-image: linear-gradient(140deg, #003c76 0%, #6495ED 50%, #BFD641 75%);
        background-repeat: no-repeat;
        background-attachment: fixed;
        color: white;
        opacity: 0.95;
    }

    article {
        text-align: left;
    }

    a {
        color: #111;
        text-decoration: none;
    }

    a:hover {
        color: #dc8100;
        text-decoration: none;
    }
</style>

<article>
    <div>
        <p><a href="#">Selamat datang di Application Programming Interface</a></p>
    </div>
    <h1>Direktorat Jenderal Sumber Daya Air</h1>
    <h2>Kementerian Pekerjaan Umum</h2>
    <div>
        <p>Ini merupakan layanan api dari sistem informasi Monitoring Kegiatan Direktorat Jenderal Sumber Daya Air</p>
        <p>&mdash; <a href="#"> Team Hore &copy; <?php echo date('Y'); ?></a></p>
    </div>
</article>
<?php
session_destroy();
?>

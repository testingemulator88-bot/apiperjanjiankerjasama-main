<?php
//error_reporting(-1);
session_start();
include 'library/config.php';
date_default_timezone_set("Asia/Jakarta");
check_injection();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-type: application/json");
?>
<!doctype html>
<title>Site Maintenance</title>
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
    }

    article {
        display: block;
        text-align: left;
        width: 650px;
        margin: 0 auto;
    }

    a {
        color: #dc8100;
        text-decoration: none;
    }

    a:hover {
        color: #333;
        text-decoration: none;
    }
</style>

<article>
    <h1>We&rsquo;ll say sorry!</h1>
    <div>
        <p>A script/sql injection attempt has been detected, system has logged all of your data!!! Sorry for the inconvenience but we&rsquo;re performing some
            security procedures at the moment. If you need to you can always contact us, otherwise we&rsquo;ll be back online shortly!</p>
        <p>&mdash; The Team</p>
        <p><a href="#">Back to the system</a></p>
    </div>
</article>
<?php
unset($_SESSION['pmc_adi']);
unset($_SESSION['pmc_username']);
unset($_SESSION['pmc_password']);
unset($_SESSION['pmc_level']);
unset($_SESSION['pmc_nama']);
unset($_SESSION['pmc_foto']);
unset($_SESSION['pmc_tahun']);
session_destroy();
?>
<?php
include '../../library/config.php';
error_reporting(0);
function check_injection3()
{
	foreach ($_GET as $name => $value) {
		if (($name != 'f')) {
?>
			<script>
				document.location = "index.php";
			</script>
<?php
		}
	}
}
check_injection3();
date_default_timezone_set("Asia/Jakarta");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-type: application/json");
$files = urlencode($_GET['f']);
readfile($files);
die();
?>
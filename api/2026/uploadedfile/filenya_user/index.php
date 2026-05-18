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
$files = urlencode($_GET['f']);
header('Content-Type: image/jpeg');
readfile($files);
?>
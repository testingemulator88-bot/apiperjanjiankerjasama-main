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
$cekeksist=file_exists(urlencode($_GET['f']));
//echo $cekeksist;
if ($cekeksist) {
	$files = $_GET['f'];
}
else {
	$files = 'default.jpg';
}
//echo $files;
header('Content-Type: image/jpeg');
readfile($files);
?>
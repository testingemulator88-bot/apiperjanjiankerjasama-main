<?php
error_reporting(0);
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "dbmonikanew_2025";
$link = new mysqli($dbhost, $dbuser, $dbpass, $dbname) or die("Connect failed: %s\n" . $conn->error);

$db = "dbmonikanew_2025";
$dbbackup = "dbemon";
$judul = "API";
$webRoot = "http://localhost/apiperjanjiankerjasama/api/2025/";
$docRootLKE = "../../backoffice/upload/";
$docRoot = "../../uploadedfile/";

//parameters
$allowedExtensions = array("gif", "jpeg", "png", "jpg", "GIF", "JPEG", "PNG", "JPG", "pdf", "PDF", "doc", "docx", "DOC", "DOCX", "odt", "xls", "xlsx", "ppt", "pptx", "zip", "ZIP", "rar", "RAR");
$fileMaxSize = 50000000;



// setting up the web root and server root for
// this shopping cart application
$thisFile = str_replace('\\', '/', __FILE__);
//$docRoot = $_SERVER['DOCUMENT_ROOT'];
$srvRoot  = str_replace('library/config.php', '', $thisFile);

function fixup2($str)
{
	$nama = $str;
	if ((strpos($nama, "<script") !== false) || (strpos(strtoupper($nama), "PASSWORD") !== false) || (strpos(strtoupper($nama), "USERNAME") !== false) || (strpos(strtoupper($nama), "VERSION") !== false) || (strpos(strtoupper($nama), "SELECT") !== false) || (strpos(strtoupper($nama), "ALL") !== false)  || (strpos(strtoupper($nama), "UNION") !== false) || (strpos(strtoupper($nama), "ORDER BY") !== false) || (strpos(strtoupper($nama), "%27") !== false) || (strpos(strtoupper($nama), "SCRIPT") !== false) || (strpos(strtoupper($nama), "<IMG") !== false) || (strpos(strtoupper($nama), "</IMG") !== false) || (strpos(strtoupper($nama), "SRC") !== false) || (strpos(strtoupper($nama), "%20") !== false) || (strpos(strtoupper($nama), "%27") !== false) || (strpos(strtoupper($nama), "<SCRIPT") !== false) || (strpos(strtoupper($nama), "</SCRIPT") !== false) || (strpos($str, "Scri") !== false) || (strpos($str, "scri") !== false) || (strpos($str, "</script") !== false) || (strpos($str, "<SCRIPT") !== false) || (strpos($str, "</SCRIPT") !== false) || (strpos($str, ";") !== false) || (strpos($str, ":") !== false) || (strpos($str, "*") !== false) || (strpos($str, "http") !== false) || (strpos($str, "/") !== false) || (strpos($str, ".php") !== false) || (strpos($str, " union") !== false) || (strpos($str, " unison") !== false) || (strpos($str, " or") !== false) || (strpos($str, ".js") !== false) || (strpos($str, ".asp") !== false) || (strpos($str, ".htm") !== false) || (strpos($str, "www.") !== false) || (strpos($str, "ookie") !== false) || (strpos($str, "[") !== false) || (strpos($str, "]") !== false) || (strpos($str, "PHPSESSID") !== false) || (strpos($str, "PAYLOAD") !== false) || (strpos($str, " OR") !== false) || (strpos($str, "OR ") !== false) || (strpos($str, "or ") !== false) || (strpos($str, "= ") !== false) || (strpos($str, " =") !== false) || (strpos($str, " %") !== false) || (strpos($str, "% ") !== false) || (strpos($str, " %27;)") !== false) || (strpos($str, "%27; )") !== false) || (strpos($str, " % ") !== false) || (strpos($str, "alert") !== false) || (strpos($str, ");(") !== false)) {
		$nama = "ada";
		//echo $nama."<br>";
		header("Location: warning-error");
		exit;
	}
	//echo "aaaa1";
	return $nama;
}


//fungsi untuk mereplace karakter tertentu, menghindari injection
function fixup($str)
{
	$str2 = $str;
	$str2 = preg_replace('/\\\\/', '', $str2);
	$str2 = str_replace("<script", "scri", $str2);
	$str2 = str_replace("</script", "scri", $str2);
	$str2 = str_replace("<SCRIPT", "scri", $str2);
	$str2 = str_replace("</SCRIPT", "scri", $str2);
	//$str2 = str_replace(";", ":", $str2);
	$str2 = str_replace("*", "&#42;", $str2);
	//$str2 = str_replace("-", "&#45;", $str2);
	$str2 = str_replace("%", "&#37;", $str2);
	//$str2 = str_replace("union", "unison", $str2);
	$str2 = str_replace("' or", "or", $str2);
	$str2 = str_replace("'", "&#39;", $str2);
	$str2 = str_replace('"', "", $str2);
	return $str2;
}

//fungsi untuk cek injection pada method post atau get
function check_injection()
{
	foreach ($_GET as $name => $value) {
		$nama = $value;
		if ((strpos($nama, "<script") !== false) || (strpos(strtoupper($nama), "PASSWORD") !== false) || (strpos(strtoupper($nama), "USERNAME") !== false) || (strpos(strtoupper($nama), "VERSION") !== false) || (strpos(strtoupper($nama), "SELECT ") !== false) || (strpos(strtoupper($nama), "ALL") !== false) || (strpos(strtoupper($nama), "UNION") !== false) || (strpos(strtoupper($nama), "ORDER BY") !== false) || (strpos(strtoupper($nama), "%27") !== false) || (strpos(strtoupper($nama), "SCRIPT") !== false) || (strpos(strtoupper($nama), "<IMG") !== false) || (strpos(strtoupper($nama), "</IMG") !== false) || (strpos(strtoupper($nama), "SRC") !== false) || (strpos(strtoupper($nama), "%20") !== false) || (strpos(strtoupper($nama), "%27") !== false) || (strpos(strtoupper($nama), "<SCRIPT") !== false) || (strpos(strtoupper($nama), "</SCRIPT") !== false) || (strpos($nama, "Scri") !== false) || (strpos($nama, "scri") !== false) || (strpos($nama, "</script") !== false) || (strpos($nama, "<SCRIPT") !== false) || (strpos($nama, "</SCRIPT") !== false) || (strpos($nama, ";") !== false) || (strpos($nama, ":") !== false) || (strpos($nama, "*") !== false) || (strpos($nama, "http") !== false) || (strpos($nama, ".php") !== false) || (strpos($nama, " union") !== false) || (strpos($nama, " unison") !== false) || (strpos($nama, " or ") !== false) || (strpos($nama, ".js") !== false) || (strpos($nama, ".asp") !== false) || (strpos($nama, ".htm") !== false) || (strpos($nama, "www.") !== false) || (strpos($nama, "ookie") !== false) || (strpos($nama, "[") !== false) || (strpos($nama, "]") !== false) || (strpos($nama, "PHPSESSID") !== false) || (strpos($nama, "PAYLOAD") !== false) || (strpos($nama, " OR ") !== false) || (strpos($nama, " OR ") !== false) || (strpos($nama, " or ") !== false) || (strpos($nama, "= ") !== false) || (strpos($nama, " =") !== false) || (strpos($nama, " %") !== false) || (strpos($nama, "% ") !== false) || (strpos($nama, " %27;)") !== false) || (strpos($nama, "%27; )") !== false) || (strpos($nama, " % ") !== false) || (strpos($nama, "alert") !== false) || (strpos($nama, ");(") !== false)) {
			$nama = "ada";
			header("Location: warning-error");
			exit;
		}
	}

	foreach ($_POST as $name => $value) {
		$nama = $value;
		if ((strpos($nama, "<script") !== false) || (strpos(strtoupper($nama), "PASSWORD") !== false) || (strpos(strtoupper($nama), "USERNAME") !== false) || (strpos(strtoupper($nama), "VERSION") !== false) || (strpos(strtoupper($nama), "SELECT ") !== false) || (strpos(strtoupper($nama), "ALL") !== false) || (strpos(strtoupper($nama), "UNION") !== false) || (strpos(strtoupper($nama), "ORDER BY") !== false) || (strpos(strtoupper($nama), "%27") !== false) || (strpos(strtoupper($nama), "SCRIPT") !== false) || (strpos(strtoupper($nama), "<IMG") !== false) || (strpos(strtoupper($nama), "</IMG") !== false) || (strpos(strtoupper($nama), "SRC") !== false) || (strpos(strtoupper($nama), "%20") !== false) || (strpos(strtoupper($nama), "%27") !== false)  || (strpos(strtoupper($nama), "<SCRIPT") !== false)  || (strpos(strtoupper($nama), "</SCRIPT") !== false) || (strpos($nama, "Scri") !== false) || (strpos($nama, "scri") !== false) || (strpos($nama, "</script") !== false) || (strpos($nama, "<SCRIPT") !== false) || (strpos($nama, "</SCRIPT") !== false) || (strpos($nama, ";") !== false) || (strpos($nama, ":") !== false) || (strpos($nama, "*") !== false) || (strpos($nama, "http") !== false) || (strpos($nama, ".php") !== false) || (strpos($nama, " union") !== false) || (strpos($nama, " unison") !== false) || (strpos($nama, " or ") !== false) || (strpos($nama, ".js") !== false) || (strpos($nama, ".asp") !== false) || (strpos($nama, ".htm") !== false) || (strpos($nama, "www.") !== false) || (strpos($nama, "ookie") !== false) || (strpos($nama, "[") !== false) || (strpos($nama, "]") !== false) || (strpos($nama, "PHPSESSID") !== false) || (strpos($nama, "PAYLOAD") !== false) || (strpos($nama, " OR ") !== false) || (strpos($nama, " OR ") !== false) || (strpos($nama, " or ") !== false) || (strpos($nama, "= ") !== false) || (strpos($nama, " =") !== false) || (strpos($nama, " %") !== false) || (strpos($nama, "% ") !== false) || (strpos($nama, " %27;)") !== false) || (strpos($nama, "%27; )") !== false) || (strpos($nama, " % ") !== false) || (strpos($nama, "alert") !== false) || (strpos($nama, ");(") !== false)) {
			$nama = "ada";
			header("Location: warning-error");
			exit;
		}
	}

	foreach ($_REQUEST as $name => $value) {
		$nama = $value;
		if ((strpos($nama, "<script") !== false) || (strpos(strtoupper($nama), "PASSWORD") !== false) || (strpos(strtoupper($nama), "USERNAME") !== false) || (strpos(strtoupper($nama), "VERSION") !== false) || (strpos(strtoupper($nama), "SELECT ") !== false) || (strpos(strtoupper($nama), "ALL") !== false) || (strpos(strtoupper($nama), "UNION") !== false) || (strpos(strtoupper($nama), "ORDER BY") !== false) || (strpos(strtoupper($nama), "%27") !== false) || (strpos(strtoupper($nama), "SCRIPT") !== false) || (strpos(strtoupper($nama), "<IMG") !== false) || (strpos(strtoupper($nama), "</IMG") !== false) || (strpos(strtoupper($nama), "SRC") !== false) || (strpos(strtoupper($nama), "%20") !== false) || (strpos(strtoupper($nama), "%27") !== false) || (strpos(strtoupper($nama), "<SCRIPT") !== false)  || (strpos(strtoupper($nama), "</SCRIPT") !== false) || (strpos($nama, "Scri") !== false) || (strpos($nama, "scri") !== false) || (strpos($nama, "</script") !== false) || (strpos($nama, "<SCRIPT") !== false) || (strpos($nama, "</SCRIPT") !== false) || (strpos($nama, ";") !== false) || (strpos($nama, ":") !== false) || (strpos($nama, "*") !== false) || (strpos($nama, "http") !== false) || (strpos($nama, ".php") !== false) || (strpos($nama, " union") !== false) || (strpos($nama, " unison") !== false) || (strpos($nama, " or ") !== false) || (strpos($nama, ".js") !== false) || (strpos($nama, ".asp") !== false) || (strpos($nama, ".htm") !== false) || (strpos($nama, "www.") !== false) || (strpos($nama, "ookie") !== false) || (strpos($nama, "[") !== false) || (strpos($nama, "]") !== false) || (strpos($nama, "PHPSESSID") !== false) || (strpos($nama, "PAYLOAD") !== false) || (strpos($nama, " OR ") !== false) || (strpos($nama, " OR ") !== false) || (strpos($nama, " or ") !== false) || (strpos($nama, "= ") !== false) || (strpos($nama, " =") !== false) || (strpos($nama, " %") !== false) || (strpos($nama, "% ") !== false) || (strpos($nama, " %27;)") !== false) || (strpos($nama, "%27; )") !== false) || (strpos($nama, " % ") !== false) || (strpos($nama, "alert") !== false) || (strpos($nama, ");(") !== false)) {
			$nama = "ada";
			header("Location: warning-error");
			exit;
		}
	}
}



//fungsi untuk cek injection pada method post atau get multiple
function check_injection2()
{
	foreach ($_GET as $name => $value) {
		$str = $value;
		if ((strpos($str, ";") !== false) || (strpos($str, ":") !== false) || (strpos($str, "*") !== false) || (strpos($str, "http") !== false) || (strpos($str, "/") !== false) || (strpos($str, ".php") !== false) || (strpos($str, " union") !== false) || (strpos($str, " unison") !== false) || (strpos($str, " or") !== false) || (strpos($str, ".js") !== false) || (strpos($str, ".asp") !== false) || (strpos($str, ".htm") !== false) || (strpos($str, "www.") !== false) || (strpos($str, "ookie") !== false) || (strpos($str, "[") !== false) || (strpos($str, "]") !== false)  || (strpos($str, "PHPSESSID") !== false) || (strpos($str, "PAYLOAD") !== false) || (strpos($str, " OR") !== false) || (strpos($str, "OR ") !== false) || (strpos($str, "or ") !== false) || (strpos($str, "= ") !== false) || (strpos($str, " =") !== false) || (strpos($str, " %") !== false) || (strpos($str, "% ") !== false) || (strpos($str, " %27;)") !== false) || (strpos($str, "%27; )") !== false) || (strpos($str, " % ") !== false) || (strpos($str, "alert") !== false) || (strpos($str, ");(") !== false)) {
			$nama = "ada";
			header("Location: warning-error");
			exit;
		}
	}

	foreach ($_POST as $name => $value) {
		$str = $value;
		if ((strpos($str, ";") !== false) || (strpos($str, ":") !== false) || (strpos($str, "*") !== false) || (strpos($str, "http") !== false) || (strpos($str, "/") !== false) || (strpos($str, ".php") !== false) || (strpos($str, " union") !== false) || (strpos($str, " unison") !== false) || (strpos($str, " or") !== false) || (strpos($str, ".js") !== false) || (strpos($str, ".asp") !== false) || (strpos($str, ".htm") !== false) || (strpos($str, "www.") !== false) || (strpos($str, "ookie") !== false) || (strpos($str, "[") !== false) || (strpos($str, "]") !== false)  || (strpos($str, "PHPSESSID") !== false) || (strpos($str, "PAYLOAD") !== false) || (strpos($str, " OR") !== false) || (strpos($str, "OR ") !== false) || (strpos($str, "or ") !== false) || (strpos($str, "= ") !== false) || (strpos($str, " =") !== false) || (strpos($str, " %") !== false) || (strpos($str, "% ") !== false) || (strpos($str, " %27;)") !== false) || (strpos($str, "%27; )") !== false) || (strpos($str, " % ") !== false) || (strpos($str, "alert") !== false) || (strpos($str, ");(") !== false)) {
			$nama = "ada";
			header("Location: warning-error");
			exit;
		}
	}

	foreach ($_REQUEST as $name => $value) {
		if ((strpos($str, ";") !== false) || (strpos($str, ":") !== false) || (strpos($str, "*") !== false) || (strpos($str, "http") !== false) || (strpos($str, "/") !== false) || (strpos($str, ".php") !== false) || (strpos($str, " union") !== false) || (strpos($str, " unison") !== false) || (strpos($str, " or") !== false) || (strpos($str, ".js") !== false) || (strpos($str, ".asp") !== false) || (strpos($str, ".htm") !== false) || (strpos($str, "www.") !== false) || (strpos($str, "ookie") !== false) || (strpos($str, "[") !== false) || (strpos($str, "]") !== false)  || (strpos($str, "PHPSESSID") !== false) || (strpos($str, "PAYLOAD") !== false) || (strpos($str, " OR") !== false) || (strpos($str, "OR ") !== false) || (strpos($str, "or ") !== false) || (strpos($str, "= ") !== false) || (strpos($str, " =") !== false) || (strpos($str, " %") !== false) || (strpos($str, "% ") !== false) || (strpos($str, " %27;)") !== false) || (strpos($str, "%27; )") !== false) || (strpos($str, " % ") !== false) || (strpos($str, "alert") !== false) || (strpos($str, ");(") !== false)) {
			$nama = "ada";
			header("Location: warning-error");
			exit;
		}
	}
}


function get_Isi_Field1($id, $id1, $id2, $id3)
{
	$link = mysqli_connect("localhost", "root", "", "dbmonikanew_2025");
	$nama = "";
	$sqlg = "select " . $id . " from " . $id1 . " where " . $id2 . " = '" . $id3 . "'";
	//echo $sqlg."<br>";
	$resultg = mysqli_query($link, $sqlg);
	$numrows = mysqli_num_rows($resultg);
	if ($numrows == 0) {
		$nama = "0";
	} else {
		if ($resultg) {
			//echo $sqlg."<br>";
			while ($rowg = mysqli_fetch_array($resultg)) {
				$nama = $rowg[$id];
			}
		} else {
			$nama = "";
		}
	}


	return $nama;
}


function get_COUNT_Field1($id, $id1, $id2, $id3)
{
	$link = mysqli_connect("localhost", "root", "", "dbmonikanew_2025");
	$nama = "";
	$sqlg = "select " . $id . " from " . $id1 . " where " . $id2 . " = '" . $id3 . "'";
	$resultg = mysqli_query($link, $sqlg);
	$numrows = mysqli_num_rows($resultg);
	if ($numrows == 0) {
		$nama = 0;
	} else {
		$nama = $numrows;
	}


	return $nama;
}


function get_COUNT_Field2($id, $id1, $id2, $id3, $id4, $id5)
{
	$link = mysqli_connect("localhost", "root", "", "dbmonikanew_2025");
	$nama = "";
	$sqlg = "select " . $id . " from " . $id1 . " where " . $id2 . " = '" . $id3 . "' and " . $id4 . " = '" . $id5 . "' ";
	//echo $sqlg."<br>";
	$resultg = mysqli_query($link, $sqlg);
	$numrows = mysqli_num_rows($resultg);
	if ($numrows == 0) {
		$nama = 0;
	} else {
		$nama = $numrows;
	}


	return $nama;
}

function get_COUNT_Field3($id, $id1, $id2, $id3)
{
	$link = mysqli_connect("localhost", "root", "", "dbmonikanew_2025");
	$nama = "";
	$sqlg = "select " . $id . " from " . $id1 . " where " . $id2 . " = '" . $id3 . "' limit 1";
	//echo $sqlg."<br>";
	$resultg = mysqli_query($link, $sqlg);
	$numrows = mysqli_num_rows($resultg);
	if ($numrows == 0) {
		$nama = 0;
	} else {
		$nama = $numrows;
	}


	return $nama;
}

function get_COUNT_Field4($id, $id1, $id2, $id3)
{
	$link = mysqli_connect("localhost", "root", "", "dbmonikanew_2025");
	$nama = "";
	$sqlg = "select " . $id . " from " . $id1 . " where " . $id2 . " = '" . $id3 . "' and deleted='0' limit 1";
	//echo $sqlg."<br>";
	$resultg = mysqli_query($link, $sqlg);
	$numrows = mysqli_num_rows($resultg);
	if ($numrows == 0) {
		$nama = 0;
	} else {
		$nama = $numrows;
	}


	return $nama;
}

function get_COUNT_Field5($id, $id1, $id2, $id3, $id4, $id5, $id6, $id7)
{
	$link = mysqli_connect("localhost", "root", "", "dbmonikanew_2025");
	$nama = "";
	$sqlg = "select " . $id . " from " . $id1 . " where " . $id2 . " = '" . $id3 . "' and " . $id4 . " = '" . $id5 . "' and " . $id6 . " = '" . $id7 . "' ";
	//echo $sqlg."<br>";
	$resultg = mysqli_query($link, $sqlg);
	$numrows = mysqli_num_rows($resultg);
	if ($numrows == 0) {
		$nama = 0;
	} else {
		$nama = $numrows;
	}


	return $nama;
}

function get_COUNT_Field6($id, $id1, $id2, $id3, $id4, $id5, $id6, $id7, $id8, $id9)
{
	$link = mysqli_connect("localhost", "root", "", "dbmonikanew_2025");
	$nama = "";
	$sqlg = "select " . $id . " from " . $id1 . " where " . $id2 . " = '" . $id3 . "' and " . $id4 . " = '" . $id5 . "' and " . $id6 . " = '" . $id7 . "' and " . $id8 . " = '" . $id9 . "' ";
	//echo $sqlg."<br>";
	$resultg = mysqli_query($link, $sqlg);
	$numrows = mysqli_num_rows($resultg);
	if ($numrows == 0) {
		$nama = 0;
	} else {
		$nama = $numrows;
	}


	return $nama;
}


function get_Isi_Field2($id, $id1, $id2, $id3)
{
	$link = mysqli_connect("localhost", "root", "", "dbmonikanew_2025");
	$nama = "";
	$sqlg = "select " . $id . " from " . $id1 . " where " . $id2 . " = '" . $id3 . "' and deleted='0' ";
	//echo $sqlg."<br>";
	$resultg = mysqli_query($link, $sqlg);
	if ($resultg) {
		$numrows = mysqli_num_rows($resultg);
		if ($numrows == 0) {
			$nama = $id3;
		} else {
			if ($resultg) {
				//echo $sqlg."<br>";
				while ($rowg = mysqli_fetch_array($resultg)) {
					$nama = $rowg[$id];
				}
			} else {
				$nama = $id3;
			}
		}
	} else {
		$nama = '';
	}
	return $nama;
}

function get_Isi_FieldKosong($id, $id1, $id2, $id3)
{
	$link = mysqli_connect("localhost", "root", "", "dbmonikanew_2025");
	$nama = "";
	$sqlg = "select " . $id . " from " . $id1 . " where " . $id2 . " = '" . $id3 . "' and deleted='0' ";
	//echo $sqlg."<br>";
	$resultg = mysqli_query($link, $sqlg);
	if ($resultg) {
		$numrows = mysqli_num_rows($resultg);
		if ($numrows == 0) {
			$nama = 'kosong';
		} else {
			if ($resultg) {
				//echo $sqlg."<br>";
				while ($rowg = mysqli_fetch_array($resultg)) {
					$nama = $rowg[$id];
				}
			} else {
				$nama = $id3;
			}
		}
	} else {
		$nama = '';
	}
	return $nama;
}


function get_Isi_Field3($id, $id1, $id2, $id3, $id4, $id5)
{
	$link = mysqli_connect("localhost", "root", "", "dbmonikanew_2025");
	$nama = "";
	$sqlg = "select ifnull(" . $id . ",0) as " . $id . "  from " . $id1 . " where " . $id2 . " = '" . $id3 . "' and " . $id4 . " = '" . $id5 . "'";
	//echo $sqlg."<br>";
	$resultg = mysqli_query($link, $sqlg);
	$numrows = mysqli_num_rows($resultg);
	if ($numrows == 0) {
		$nama = 0;
	} else {
		if ($resultg) {
			//echo $sqlg."<br>";
			while ($rowg = mysqli_fetch_array($resultg)) {
				$nama = $rowg[$id];
			}
		} else {
			$nama = $id3;
		}
	}


	return $nama;
}

function get_Isi_FieldAlias($id, $id1, $id2, $id3, $id4, $id5)
{
	$link = mysqli_connect("localhost", "root", "", "dbmonikanew_2025");
	$nama = "";
	$sqlg = "select ifnull(" . $id . ",0) as " . $id . "  from " . $id1 . " where " . $id2 . " = '" . $id3 . "' and " . $id4 . " = '" . $id5 . "'";
	//echo $sqlg."<br>";
	$resultg = mysqli_query($link, $sqlg);
	if ($resultg) {
		$numrows = mysqli_num_rows($resultg);
		if ($numrows == 0) {
			$nama = $id3;
		} else {
			if ($resultg) {
				//echo $sqlg."<br>";
				while ($rowg = mysqli_fetch_array($resultg)) {
					$nama = $rowg[$id];
				}
			} else {
				$nama = $id3;
			}
		}
	} else {
		$nama = '';
	}
	return $nama;
}

function get_SUM_Field1($id, $id1, $id2, $id3, $id4, $id5)
{
	$link = mysqli_connect("localhost", "root", "", "dbmonikanew_2025");
	$nama = "";
	$sqlg = "select sum(" . $id . ") as " . $id . " from " . $id1 . " where " . $id2 . " = '" . $id3 . "' and " . $id4 . " = '" . $id5 . "' and deleted='0'";
	//echo $sqlg."<br>";
	$resultg = mysqli_query($link, $sqlg);
	$numrows = mysqli_num_rows($resultg);
	if ($numrows == 0) {
		$nama = 0;
	} else {
		if ($resultg) {
			//echo $sqlg."<br>";
			while ($rowg = mysqli_fetch_array($resultg)) {
				$nama = $rowg[$id];
			}
		} else {
			$nama = 0;
		}
	}


	return $nama;
}



function encrypt($str)
{
	$kunci = 'AIzaSyDsiwLbEcjMOzXceMQ7-vJh21icjaHmlJE"';
	$hasil = '';
	for ($i = 0; $i < strlen($str); $i++) {
		$karakter = substr($str, $i, 1);
		$kuncikarakter = substr($kunci, ($i % strlen($kunci)) - 1, 1);
		$karakter = chr(ord($karakter) + ord($kuncikarakter));
		$hasil .= $karakter;
	}
	return urlencode(base64_encode($hasil));
}

function decrypt($str)
{
	$str = base64_decode(urldecode($str));
	$hasil = '';
	$kunci = 'AIzaSyDsiwLbEcjMOzXceMQ7-vJh21icjaHmlJE"';
	for ($i = 0; $i < strlen($str); $i++) {
		$karakter = substr($str, $i, 1);
		$kuncikarakter = substr($kunci, ($i % strlen($kunci)) - 1, 1);
		$karakter = chr(ord($karakter) - ord($kuncikarakter));
		$hasil .= $karakter;
	}
	return $hasil;
}

function tanggal_indo($tanggal)
{
	$bulan = array(
		1 =>   'Januari',
		'Februari',
		'Maret',
		'April',
		'Mei',
		'Juni',
		'Juli',
		'Agustus',
		'September',
		'Oktober',
		'November',
		'Desember'
	);
	$pecahkan = explode('-', $tanggal);

	// variabel pecahkan 0 = tanggal
	// variabel pecahkan 1 = bulan
	// variabel pecahkan 2 = tahun

	$adit = explode(' ', $pecahkan[2]);


	return $adit[0] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0] . ' ' . $adit[1] . ' WIB';
}

function tanggal_inggris($tanggal)
{
	$bulan = array(
		1 =>   'January',
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December'
	);
	$pecahkan = explode('-', $tanggal);

	// variabel pecahkan 0 = tanggal
	// variabel pecahkan 1 = bulan
	// variabel pecahkan 2 = tahun

	$adit = explode(' ', $pecahkan[2]);


	return $adit[0] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0] . ' ' . $adit[1] . ' WIB';
}


function tanggal_indodoank($tanggal)
{
	$bulan = array(
		1 =>   'Januari',
		'Februari',
		'Maret',
		'April',
		'Mei',
		'Juni',
		'Juli',
		'Agustus',
		'September',
		'Oktober',
		'November',
		'Desember'
	);
	$pecahkan = explode('-', $tanggal);

	// variabel pecahkan 0 = tanggal
	// variabel pecahkan 1 = bulan
	// variabel pecahkan 2 = tahun

	$adit = explode(' ', $pecahkan[2]);


	return $adit[0] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

function tanggal_inggrisdoank($tanggal)
{
	$bulan = array(
		1 =>   'January',
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December'
	);
	$pecahkan = explode('-', $tanggal);

	// variabel pecahkan 0 = tanggal
	// variabel pecahkan 1 = bulan
	// variabel pecahkan 2 = tahun

	$adit = explode(' ', $pecahkan[2]);


	return $adit[0] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}


function tgl_indo($tanggal)
{
	$bulan = array(
		1 =>   'Januari',
		'Februari',
		'Maret',
		'April',
		'Mei',
		'Juni',
		'Juli',
		'Agustus',
		'September',
		'Oktober',
		'November',
		'Desember'
	);
	$pecahkan = explode('-', $tanggal);

	// variabel pecahkan 0 = tanggal
	// variabel pecahkan 1 = bulan
	// variabel pecahkan 2 = tahun

	$adit = explode(' ', $pecahkan[2]);


	return $adit[0] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0] . ' ' . $adit[1] . ' WIB';
}

function tgl_indo2($tanggal)
{
	$bulan = array(
		1 =>   'Januari',
		'Februari',
		'Maret',
		'April',
		'Mei',
		'Juni',
		'Juli',
		'Agustus',
		'September',
		'Oktober',
		'November',
		'Desember'
	);
	$pecahkan = explode('-', $tanggal);

	// variabel pecahkan 0 = tanggal
	// variabel pecahkan 1 = bulan
	// variabel pecahkan 2 = tahun

	$adit = explode(' ', $pecahkan[2]);


	return $adit[0] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0];
}

//fungsi untuk mengganti koma menjadi titik
function replace_comma($text)
{
	$text = str_replace(".", "", $text);
	$text2 = str_replace(",", ".", $text);
	return $text2;
}

//fungsi untuk mengganti titik menjadi koma
function replace_dot($text)
{
	$text2 = number_format($text, 0, ",", ".");
	return $text2;
}

//fungsi untuk mengganti titik menjadi koma
function replace_dot_koma($text)
{
	$text2 = number_format($text, 2, ",", ".");
	return $text2;
}

function replace_dot_koma_tiga($text)
{
	$text2 = number_format($text, 3, ",", ".");
	return $text2;
}

//fungsi untuk memformat angka ke format dua digit di belakang koma
function format_point($text)
{
	$text2 = number_format($text, 2);
	return $text2;
}


function kekata($x)
{
	$x = abs($x);
	$angka = array(
		"",
		"satu",
		"dua",
		"tiga",
		"empat",
		"lima",
		"enam",
		"tujuh",
		"delapan",
		"sembilan",
		"sepuluh",
		"sebelas"
	);
	$temp = "";
	if ($x < 12) {
		$temp = " " . $angka[$x];
	} else if ($x < 20) {
		$temp = kekata($x - 10) . " belas";
	} else if ($x < 100) {
		$temp = kekata($x / 10) . " puluh" . kekata($x % 10);
	} else if ($x < 200) {
		$temp = " seratus" . kekata($x - 100);
	} else if ($x < 1000) {
		$temp = kekata($x / 100) . " ratus" . kekata($x % 100);
	} else if ($x < 2000) {
		$temp = " seribu" . kekata($x - 1000);
	} else if ($x < 1000000) {
		$temp = kekata($x / 1000) . " ribu" . kekata($x % 1000);
	} else if ($x < 1000000000) {
		$temp = kekata($x / 1000000) . " juta" . kekata($x % 1000000);
	} else if ($x < 1000000000000) {
		$temp = kekata($x / 1000000000) . " milyar" . kekata(fmod($x, 1000000000));
	} else if ($x < 1000000000000000) {
		$temp = kekata($x / 1000000000000) . " trilyun" . kekata(fmod($x, 1000000000000));
	}
	return $temp;
}
function terbilang($x, $style = 4)
{
	if ($x < 0) {
		$hasil = "minus " . trim(kekata($x));
	} else {
		$hasil = trim(kekata($x));
	}
	switch ($style) {
		case 1:
			$hasil = strtoupper($hasil);
			break;
		case 2:
			$hasil = strtolower($hasil);
			break;
		case 3:
			$hasil = ucwords($hasil);
			break;
		default:
			$hasil = ucfirst($hasil);
			break;
	}
	return $hasil;
}

function get_Pencarian_Tabel($id, $str)
{
	$link = mysqli_connect("localhost", "root", "", "dbmonikanew_2025");
	$nama = "";
	$caritabel = explode(",", $id);
	$jumlahcaritabel = count($caritabel);
	for ($i = 0; $i <= $jumlahcaritabel - 1; $i++) {
		if ($i == 0) {
			$nama = $nama . " " . $caritabel[$i] . " like '%" . $str . "%'";
		} else {
			$nama = $nama . " or " . $caritabel[$i] . " like '%" . $str . "%'";
		}
	}
	return "and (" . $nama . ")";
}


//fungsi untuk cek status login dengan error dan redirect ke halaman login
function check_login()
{
	if (!isset($_SESSION['pmc_adi']) && !isset($_SESSION['pmc_username'])) {
		//$loc = "Location: ".$root;
		//echo $loc;
?>
		<script>
			document.location = "./index.php";
		</script>
<?php
	}
}


function tomysqldate($date)
{

	$day = substr($date, 0, 2);
	$month = substr($date, 3, 2);
	$year = substr($date, 6, 4);
	$hours = substr($date, 10, 2);
	$minutes = substr($date, 13, 2);
	$seconds = substr($date, 16, 2);

	$date = $year . $month . $day . $hours . $minutes . $seconds;
	return $date;
}

function tomysqldateminus($date)
{

	$day = substr($date, 0, 2);
	$month = substr($date, 3, 2);
	$year = substr($date, 6, 4);
	$hours = substr($date, 10, 2);
	$minutes = substr($date, 13, 2);
	$seconds = substr($date, 16, 2);

	$date = $year . "-" . $month . "-" . $day;
	if ($date = "--") {
		$date = "";
	}
	return $date;
}



function frommysqldate($date, $format = '')
{
	global $CONFIG;
	global $timeoffset;
	$year = substr($date, 0, 4);
	$month = substr($date, 5, 2);
	$day = substr($date, 8, 2);
	$hours = substr($date, 11, 2);
	$minutes = substr($date, 14, 2);
	$seconds = substr($date, 17, 2);
	if ($timeoffset) {
		$hours = $hours + $timeoffset;
		$new_time = mktime($hours, $minutes, $seconds, $month, $day, $year);
		$year = date('Y', $new_time);
		$month = date('m', $new_time);
		$day = date('d', $new_time);
		$hours = date('H', $new_time);
		$minutes = date('i', $new_time);
		$seconds = date('s', $new_time);
	}

	$date = '' . $day . '/' . $month . '/' . $year;

	if ($format == 'time') {
		$date .= '' . ' ' . $hours . ':' . $minutes;
	}

	return $date;
}

function potongkalimat($texts, $jmlh_karakter, $tambahan = '...')
{
	if (strlen($texts) > $jmlh_karakter || $texts == '') {
		$kata = preg_split('/\s/', $texts);
		$hasilakhir = '';
		$i = 0;
		while (1) {
			$panjang = strlen($hasilakhir) + strlen($kata[$i]);
			if ($panjang > $jmlh_karakter) { //pengambilan sesuai jumlah karakter
				break;
			} else {
				$hasilakhir .= " " . $kata[$i]; //Menyatukan text
				++$i;
			}
		}
		$hasilakhir .= $tambahan; //Tambahan setelah kalimat dipotong
	} else {
		$hasilakhir = preg_split('/\s/', $texts);
	}
	return $hasilakhir;
}

function getRomawi($bln)
{
	switch ($bln) {
		case 1:
			return "I";
			break;
		case 2:
			return "II";
			break;
		case 3:
			return "III";
			break;
		case 4:
			return "IV";
			break;
		case 5:
			return "V";
			break;
		case 6:
			return "VI";
			break;
		case 7:
			return "VII";
			break;
		case 8:
			return "VIII";
			break;
		case 9:
			return "IX";
			break;
		case 10:
			return "X";
			break;
		case 11:
			return "XI";
			break;
		case 12:
			return "XII";
			break;
	}
}

function cekangka_atobukan($id)
{
	$nama = "";
	if (is_numeric($id)) {
		$nama = $id;
	} else {
		$nama = '0';
	}


	return $nama;
}


function randomkata($panjang)
{

	$karakter = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
	$string = '';

	for ($i = 0; $i < $panjang; $i++) {
		$pos = rand(0, strlen($karakter) - 1);
		$string .= $karakter[$pos];
	}
	return $string;
}

function haripadaTanggal($tanggal)
{
	$day = date('l', strtotime($tanggal));
	switch ($day) {
		case 'Sunday':
			return 'Minggu';
			break;
		case 'Monday':
			return 'Senin';
			break;
		case 'Tuesday':
			return 'Selasa';
			break;
		case 'Wednesday':
			return 'Rabu';
			break;
		case 'Thursday':
			return 'Kamis';
			break;
		case 'Friday':
			return "Jumat";
			break;
		case 'Saturday':
			return 'Sabtu';
			break;
	}
}

function getLastWorkingDayOfYear($year)
{
	// Start with December 31st of the specified year
	$date = new DateTime($year . "-12-31");

	// Get the numeric representation of the day of the week (1=Mon, ..., 6=Sat, 7=Sun)
	$dayOfWeek = (int)$date->format('N');

	// If it's Saturday (6) or Sunday (7), subtract days until it's a weekday
	while ($dayOfWeek >= 6) {
		$date->modify('-1 day');
		$dayOfWeek = (int)$date->format('N');
	}

	return $date->format('Y-m-d');
}

?>
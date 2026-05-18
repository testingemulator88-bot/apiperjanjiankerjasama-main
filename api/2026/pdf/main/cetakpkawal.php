<?php
error_reporting(0);
set_time_limit(-1);
session_start();
include '../../library/config.php';
$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
//check_login();

function tgl_indoku($tanggal)
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


	return $adit[0] . ' ' . $bulan[(int)$pecahkan[1]] . ' ' . $pecahkan[0] . ' ' . $adit[1] . '';
}

check_injection();
$myArrayIKU = array();
$kode_satker = fixup(base64_decode($_GET['kode_satker']));
$tahun = fixup(base64_decode($_GET['tahun']));
$pelaksana = fixup(base64_decode($_GET['pelaksana']));

$tahunsekarang = get_Isi_Field1('tahun', 'master_pk', 'id', '1');
$filterquery = "";

if ($tahun <> '') {
	$filterquery = $filterquery . " and a.tahun = '" . $tahun . "'";
}

if ($pelaksana <> '') {
	$filterquery = $filterquery . " and (pelaksana in ('" . $pelaksana . "') or pelaksana in (" . $pelaksana . ")) or pelaksana like ('%," . $pelaksana . ",%') or pelaksana like ('" . $pelaksana . ",%') or pelaksana like ('%," . $pelaksana . "')";
} else {
	$filterquery = $filterquery . " and a.pelaksana in ('---')";
}

$sqlsatkeremon = "select nama_satker,kdbalai 
from master_satker where kode_satker = '" . $kode_satker . "' and deleted='0'";
//echo $sqlsatkeremon;
//die;
$resultsatkeremon = mysqli_query($link, $sqlsatkeremon);
while ($rowsatkeremon = mysqli_fetch_assoc($resultsatkeremon)) {
	$nama_satker = $rowsatkeremon['nama_satker'];
	$kdbalai = $rowsatkeremon['kdbalai'];
}

$sqlsatkeremonbalai = "select nama_kategori 
from master_kategori_satker where id = '" . $kdbalai . "' and deleted='0'";
//echo $sqlsatkeremon;
//die;
$resultsatkeremonbalai = mysqli_query($link, $sqlsatkeremonbalai);
while ($rowsatkeremonbalai = mysqli_fetch_assoc($resultsatkeremonbalai)) {
	$nama_balai = $rowsatkeremonbalai['nama_kategori'];
}

if ($nama_balai == 'PUSAT') {
	$nama_balai = 'DIREKTORAT JENDERAL SUMBER DAYA AIR';
}
if ($nama_balai == 'DINAS') {
	$nama_balai = 'DIREKTORAT JENDERAL SUMBER DAYA AIR';
}

$sqlsatkerttd = "select nama_pejabat, jabatan_pejabat, filenya_ttd, lokasi_pejabat, tanggal_pejabat
from tb_data_ttd_awal where kode_satker_pejabat = '" . $kode_satker . "' and level_pejabat='Satker'";
//echo $sqlsatkerttd;
//die;
$resultsatkerttd = mysqli_query($link, $sqlsatkerttd);
while ($rowsatkerttd = mysqli_fetch_assoc($resultsatkerttd)) {
	$nama_pejabat = $rowsatkerttd['nama_pejabat'];
	$jabatan_pejabat = $rowsatkerttd['jabatan_pejabat'];
	$lokasi_pejabat = $rowsatkerttd['lokasi_pejabat'];
	$tanggal_pejabat = tgl_indoku($rowsatkerttd['tanggal_pejabat']);
	$ttd_pejabat = $rowsatkerttd['filenya_ttd'];
}


$sqlbalaittd = "select nama_pejabat, jabatan_pejabat, filenya_ttd
from tb_data_ttd_awal where kode_satker_pejabat = '" . $kdbalai . "' and level_pejabat='Balai'";
//echo $sqlbalaittd;
//die;
$resultbalaittd = mysqli_query($link, $sqlbalaittd);
while ($rowbalaittd = mysqli_fetch_assoc($resultbalaittd)) {
	$nama_pejabat_balai = $rowbalaittd['nama_pejabat'];
	$jabatan_pejabat_balai = $rowbalaittd['jabatan_pejabat'];
	$ttd_pejabat_balai = $rowbalaittd['filenya_ttd'];
}

$sqlceklevelnya = "select level_piu from master_satker where kode_satker = '" . $kode_satker . "' and deleted='0'";
//echo $sqlceklevelnya;
//die;
$resultceklevelnya = mysqli_query($link, $sqlceklevelnya);
while ($rowceklevelnya = mysqli_fetch_assoc($resultceklevelnya)) {
	$levelpiu = $rowceklevelnya['level_piu'];
}

if (((float) $levelpiu > 7) && ((float) $levelpiu <> 31) && ((float) $levelpiu <> 41) && ((float) $levelpiu <> 9) && ((float) $levelpiu <> 47)) {
	$sqlbalaittd = "select nama_pejabat, jabatan_pejabat, filenya_ttd
	from tb_data_ttd_awal where level_pejabat='Dirjen'";
	//echo $sqlbalaittd;
	//die;
	$resultbalaittd = mysqli_query($link, $sqlbalaittd);
	while ($rowbalaittd = mysqli_fetch_assoc($resultbalaittd)) {
		$nama_pejabat_balai = $rowbalaittd['nama_pejabat'];
		$jabatan_pejabat_balai = $rowbalaittd['jabatan_pejabat'];
		$ttd_pejabat_balai = $rowbalaittd['filenya_ttd'];
	}
}

if (((float) $levelpiu == 6)) {
	$sqlbalaittd = "select nama_pejabat, jabatan_pejabat, filenya_ttd
	from tb_data_ttd_awal where kode_satker_pejabat='03694117'";
	//echo $sqlbalaittd;
	//die;
	$resultbalaittd = mysqli_query($link, $sqlbalaittd);
	while ($rowbalaittd = mysqli_fetch_assoc($resultbalaittd)) {
		$nama_pejabat_balai = $rowbalaittd['nama_pejabat'];
		$jabatan_pejabat_balai = $rowbalaittd['jabatan_pejabat'];
		$ttd_pejabat_balai = $rowbalaittd['filenya_ttd'];
	}
}

if (((float) $levelpiu == 31)) {
	$sqlbalaittd = "select nama_pejabat, jabatan_pejabat, filenya_ttd
	from tb_data_ttd_awal where level_pejabat='DirSupan'";
	//echo $sqlbalaittd;
	//die;
	$resultbalaittd = mysqli_query($link, $sqlbalaittd);
	while ($rowbalaittd = mysqli_fetch_assoc($resultbalaittd)) {
		$nama_pejabat_balai = $rowbalaittd['nama_pejabat'];
		$jabatan_pejabat_balai = $rowbalaittd['jabatan_pejabat'];
		$ttd_pejabat_balai = $rowbalaittd['filenya_ttd'];
	}
}

if (((float) $levelpiu == 31)) {
	$nama_balai = 'DIREKTORAT SUNGAI DAN PANTAI';
}

if (((float) $levelpiu == 9)) {
	$sqlbalaittd = "select nama_pejabat, jabatan_pejabat, filenya_ttd
	from tb_data_ttd_awal where level_pejabat='DirSSPSDA'";
	//echo $sqlbalaittd;
	//die;
	$resultbalaittd = mysqli_query($link, $sqlbalaittd);
	while ($rowbalaittd = mysqli_fetch_assoc($resultbalaittd)) {
		$nama_pejabat_balai = $rowbalaittd['nama_pejabat'];
		$jabatan_pejabat_balai = $rowbalaittd['jabatan_pejabat'];
		$ttd_pejabat_balai = $rowbalaittd['filenya_ttd'];
	}
}

if (((float) $levelpiu == 39)) {
	$sqlbalaittd = "select nama_pejabat, jabatan_pejabat, filenya_ttd
	from tb_data_ttd_awal where level_pejabat='DirBenda'";
	//echo $sqlbalaittd;
	//die;
	$resultbalaittd = mysqli_query($link, $sqlbalaittd);
	while ($rowbalaittd = mysqli_fetch_assoc($resultbalaittd)) {
		$nama_pejabat_balai = $rowbalaittd['nama_pejabat'];
		$jabatan_pejabat_balai = $rowbalaittd['jabatan_pejabat'];
		$ttd_pejabat_balai = $rowbalaittd['filenya_ttd'];
	}
}

if (((float) $levelpiu == 39)) {
	$sqlbalaittd = "select nama_pejabat, jabatan_pejabat, filenya_ttd
	from tb_data_ttd_awal where level_pejabat='DirBenda'";
	//echo $sqlbalaittd;
	//die;
	$resultbalaittd = mysqli_query($link, $sqlbalaittd);
	while ($rowbalaittd = mysqli_fetch_assoc($resultbalaittd)) {
		$nama_pejabat_balai = $rowbalaittd['nama_pejabat'];
		$jabatan_pejabat_balai = $rowbalaittd['jabatan_pejabat'];
		$ttd_pejabat_balai = $rowbalaittd['filenya_ttd'];
	}
}

if (((float) $levelpiu == 8) || ((float) $levelpiu == 12) || ((float) $levelpiu == 13) || ((float) $levelpiu == 14) || ((float) $levelpiu == 26) || ((float) $levelpiu == 28) || ((float) $levelpiu == 29) || ((float) $levelpiu == 30)) {
	$sqlbalaittd = "select nama_pejabat, jabatan_pejabat, filenya_ttd
	from tb_data_ttd_awal where level_pejabat='DirBintek'";
	//echo $sqlbalaittd;
	//die;
	$resultbalaittd = mysqli_query($link, $sqlbalaittd);
	while ($rowbalaittd = mysqli_fetch_assoc($resultbalaittd)) {
		$nama_pejabat_balai = $rowbalaittd['nama_pejabat'];
		$jabatan_pejabat_balai = $rowbalaittd['jabatan_pejabat'];
		$ttd_pejabat_balai = $rowbalaittd['filenya_ttd'];
	}
}

if (((float) $levelpiu == 9)) {
	$nama_balai = 'DIREKTORAT SISTEM DAN STRATEGI PENGELOLAAN SUMBER DAYA AIR';
}

$sqlceklevelnyajenis = "select id_parent from master_level_piu where id = '" . $levelpiu . "' and deleted='0'";
$resultceklevelnyajenis = mysqli_query($link, $sqlceklevelnyajenis);
while ($rowceklevelnyajenis = mysqli_fetch_assoc($resultceklevelnyajenis)) {
	$id_parent = $rowceklevelnyajenis['id_parent'];
}

$pk_aktif = '1';
$sqlttd = "select ttd from master_pk where deleted = '0' and id = '" . $pk_aktif . "'";
$resultttd  = mysqli_query($link, $sqlttd);
while ($rowttd  = mysqli_fetch_assoc($resultttd)) {
	$ttdinput = $rowttd['ttd'];
}

if ($ttd_pejabat == '') {
	$ttd_pejabat = 'default.jpg';
}
if ($ttd_pejabat_balai == '') {
	$ttd_pejabat_balai = 'default.jpg';
}

$sqlcekversi = "select a.verifikasi_ke, a.hasil_verif,b.nama
from tb_data_pk_verifikasi a 
left join master_verifikasi b
on a.hasil_verif=b.id
where a.kode_pelaksana = '" . $kode_satker . "' and a.deleted='0'
and a.jenis_pk = '" . $pk_aktif . "' 
and a.id_pelaksana not in (45,32) order by a.id DESC limit 1";
//echo $sqlcekversi;
//die;
$resultcekversi = mysqli_query($link, $sqlcekversi);
$numversi = mysqli_num_rows($resultcekversi);
if ($numversi > 0) {
	while ($rowcekversi = mysqli_fetch_assoc($resultcekversi)) {
		$verifikasi_ke = $rowcekversi['verifikasi_ke'];
		$namaverifikasi = $rowcekversi['nama'];
	}
} else {
	$versinya = "- ";
}

//echo $versinya;
//die;
$versinya = $namaverifikasi . "- " . $verifikasi_ke;
//echo $versinya;
//die;
if ($versinya == "- ") {
	$versinya = "  KONSEP PK";
} else if ($versinya == "- " . $verifikasi_ke) {
	//echo (float) $id_parent;
	//die;
	if ((float) $id_parent < (float) $verifikasi_ke) {
		$versinya = " VERIFIKASI " . $verifikasi_ke;
	} else {
		if ((float) $verifikasi_ke == 1) {
			$versinya = "  KONSEP PK";
		} else if ((float) $verifikasi_ke == 2) {
			$versinya = "  VERIFIKASI " . $verifikasi_ke;
		} else {
			$versinya = "       ";
		}
	}
} else if (strstr($versinya, "Diterima")) {
	//echo (float) $id_parent;
	//echo (float) $verifikasi_ke;
	if ($verifikasi_ke == "1") {
		if ((float) $verifikasi_ke < (float) $id_parent) {
			$versinya = " VERIFIKASI " . $verifikasi_ke;
		} else {
			$versinya = "       ";
		}
	} else {
		$versinya = "       ";
	}
}


//============================================================+
// File name   : example_003.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Example 003 for TCPDF class
//               Custom Header and Footer
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Custom Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 */

// Include the main TCPDF library (search for installation path).

require_once('tcpdf_include.php');


// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF
{

	public $myCustomHeaderversi;
	//Page header
	public function Header() {}

	// Page footer
	// Page footer
	public function Footer()
	{
		// Position at 15 mm from bottom
		$style = array(
			'border' => 4,
			'vpadding' => 'auto',
			'hpadding' => 'auto',
			'fgcolor' => array(0, 0, 0),
			'bgcolor' => false, //array(255,255,255)
			'module_width' => 1, // width of a single module in points
			'module_height' => 1 // height of a single module in points
		);
		$this->setY(-15);
		$this->setFont('helvetica', 'B', 12);
		// Page number
		$this->Text(276, 10, $this->myCustomHeaderversi);
		if ((strstr(trim($this->myCustomHeaderversi), 'KONSEP')) || (strstr(trim($this->myCustomHeaderversi), 'VERIFIKASI')) || (strstr(trim($this->myCustomHeaderversi), '-'))) {
			$this->Line(275, 10, 308, 10);
			$this->Line(275, 16, 308, 16);
			$this->Line(275, 10, 275, 16);
			$this->Line(308, 10, 308, 16);
		} else {
			$actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			$this->write2DBarcode($actual_link, 'QRCODE,Q', 285, 5, 30, 30, $style, 'N');
			$this->setFont('helvetica', '', 5);
			$this->Text(10, 208, 'Generate by MONIKA SDA KEMENTERIAN PU ©' . date("Y"));
		}
	}
}

// create new PDF document  
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->myCustomHeaderversi = $versinya;
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('DITJEN SDA');
$pdf->SetTitle('PERJANJIAN KINERJA');
$pdf->SetSubject('Adi Nuryono');
$pdf->SetKeywords('TCPDF, PDF, Parastapa, V, 2');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins('25', '10', '25');
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, '15');

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
	require_once(dirname(__FILE__) . '/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('dejavusans', '', 11);

// add a page
$resolution = array(215, 330);
$pdf->AddPage('L', $resolution);

// set style for barcode


$style1 = array('width' => 0.8, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
$style2 = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 5);
if (strlen($nama_satker) < 120) {
	$html = '
	<table border="0"  cellpadding="2" style="width:100%;" >
	<thead>
	';
	$html = $html . '	
	</thead>  
	<tbody>
	<tr>
	<td align="center" width="1000px">	
	<img src="images/logopu.jpg" height="50">
	</td>
	</tr>
	<tr>
	<td align="center" width="1000px">	
	<b>PERJANJIAN KINERJA TAHUN ' . $tahunsekarang . '</b>
	</td>
	</tr>
	<tr>
	<td align="center" width="1000px">	
	<b>' . $nama_satker . '</b>
	</td>
	</tr>
	<tr>
	<td align="center" width="1000px">	
	<b>' . $nama_balai . '</b>
	</td>
	</tr>
	<tr>
	<td align="center" width="1000px">	
	&nbsp;
	</td>
	</tr>
	';

	$pdf->SetFont('dejavusans', '', 11);

	$html = $html . '
	<tr>
	<td align="justify" width="1000px">	
	Dalam rangka mewujudkan manajemen pemerintahan yang efektif, transparan dan akuntabel serta berorientasi pada hasil, kami yang bertandatangan dibawah ini:
	</td>
	</tr>
	<tr>
	<td align="justify" width="50px">	
	</td>
	<td align="justify" width="100px">	
	Nama
	</td>
	<td align="center" width="10px">	
	:
	</td>
	<td align="justify" width="840px">	
	' . strtoupper($nama_pejabat) . '
	</td>
	</tr>
	<tr>
	<td align="justify" width="50px">	
	</td>
	<td align="justify" width="100px">	
	Jabatan
	</td>
	<td align="center" width="10px">	
	:
	</td>
	<td align="justify" width="840px">	
	' . $jabatan_pejabat . '
	</td>
	</tr>
	<tr>
	<td align="justify" width="1000px">	
	Selanjutnya disebut <b>PIHAK PERTAMA</b>
	</td>
	</tr>
	<tr>
	<td align="center" width="1000px">	
	&nbsp;
	</td>
	</tr>
	<tr>
	<td align="justify" width="50px">	
	</td>
	<td align="justify" width="100px">	
	Nama
	</td>
	<td align="center" width="10px">	
	:
	</td>
	<td align="justify" width="840px">	
	' . strtoupper($nama_pejabat_balai) . '
	</td>
	</tr>
	<tr>
	<td align="justify" width="50px">	
	</td>
	<td align="justify" width="100px">	
	Jabatan
	</td>
	<td align="center" width="10px">	
	:
	</td>
	<td align="justify" width="840px">	
	' . $jabatan_pejabat_balai . '
	</td>
	</tr>
	<tr>
	<td align="justify" width="1000px">	
	Selaku atasan langsung pihak pertama, selanjutnya disebut <b>PIHAK KEDUA</b>
	</td>
	</tr>
	<tr>
	<td align="center" width="1000px">	
	&nbsp;
	</td>
	</tr>
	<tr>
	<td align="justify" width="1000px">	
	<b>PIHAK PERTAMA</b> dan <b>PIHAK KEDUA</b> sepakat untuk membuat Perjanjian Kinerja dengan ketentuan sebagai berikut:
	</td>
	</tr>
	<tr>
	<td align="justify" width="30px">
	1.	
	</td>
	<td align="justify" width="970px">	
	Pihak pertama pada tahun ' . $tahunsekarang . ' ini berjanji akan mewujudkan target kinerja yang seharusnya sesuai dengan lampiran perjanjian ini, dalam rangka mencapai target kinerja jangka menengah seperti yang telah ditetapkan dalam dokumen perencanaan. Keberhasilan dan kegagalan pencapaian target kinerja tersebut menjadi tanggungjawab pihak pertama.
	</td>
	</tr>
	<tr>
	<td align="justify" width="30px">
	2.	
	</td>
	<td align="justify" width="970px">	
	Pihak kedua akan melakukan supervisi yang diperlukan serta akan melakukan evaluasi terhadap capaian kinerja dari perjanjian ini dan mengambil tindakan yang diperlukan dalam rangka pemberian penghargaan dan sanksi.
	</td>
	</tr>
	<tr>
	<td align="center" width="300px">
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="300px">
	' . ucfirst($lokasi_pejabat) . ', ' . $tanggal_pejabat . '
	</td>
	</tr>
	<tr>
	<td align="center" width="300px">
	<b>Pihak Kedua</b>
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="300px">
	<b>Pihak Pertama</b>
	</td>
	</tr>
	';
	if ($ttdinput == '0') {
		$html = $html . '
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	';
	} else {
		$html = $html . '
	<tr>
	<td align="center" width="300px">
	<img src="../../uploadedfile/filenya_ttd/' . $ttd_pejabat_balai . '" height="100">
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="300px">
	<img src="../../uploadedfile/filenya_ttd/' . $ttd_pejabat . '" height="100">
	</td>
	</tr>
	';
	}
	$html = $html . '
	<tr>
	<td align="center" width="300px">
	<b>' . strtoupper($nama_pejabat_balai) . '</b>
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="300px">
	<b>' . strtoupper($nama_pejabat) . '</b>
	</td>
	</tr>
	';

	$html = $html . '
	</tbody>
	</table>
	';
} else {
	$html = '
	<table border="0"  cellpadding="2" style="width:100%;" >
	<thead>
	';
	$html = $html . '	
	</thead>  
	<tbody>
	<tr>
	<td align="center" width="1000px">	
	<img src="images/logopu.jpg" height="50">
	</td>
	</tr>
	<tr>
	<td align="center" width="1000px">	
	<b>PERJANJIAN KINERJA TAHUN ' . $tahunsekarang . '</b>
	</td>
	</tr>
	<tr>
	<td align="center" width="100px">
	</td>
	<td align="center" width="800px">	
	<b>' . $nama_satker . '</b>
	</td>
	<td align="center" width="100px">
	</td>
	</tr>
	<tr>
	<td align="center" width="1000px">	
	<b>' . $nama_balai . '</b>
	</td>
	</tr>
	<tr>
	<td align="center" width="1000px">	
	&nbsp;
	</td>
	</tr>
	';

	$pdf->SetFont('dejavusans', '', 11);

	$html = $html . '
	<tr>
	<td align="justify" width="1000px">	
	Dalam rangka mewujudkan manajemen pemerintahan yang efektif, transparan dan akuntabel serta berorientasi pada hasil, kami yang bertandatangan dibawah ini:
	</td>
	</tr>
	<tr>
	<td align="justify" width="50px">	
	</td>
	<td align="justify" width="100px">	
	Nama
	</td>
	<td align="center" width="10px">	
	:
	</td>
	<td align="justify" width="840px">	
	' . strtoupper($nama_pejabat) . '
	</td>
	</tr>
	<tr>
	<td align="justify" width="50px">	
	</td>
	<td align="justify" width="100px">	
	Jabatan
	</td>
	<td align="center" width="10px">	
	:
	</td>
	<td align="justify" width="840px">	
	' . $jabatan_pejabat . '
	</td>
	</tr>
	<tr>
	<td align="justify" width="1000px">	
	Selanjutnya disebut <b>PIHAK PERTAMA</b>
	</td>
	</tr>
	<tr>
	<td align="center" width="1000px">	
	&nbsp;
	</td>
	</tr>
	<tr>
	<td align="justify" width="50px">	
	</td>
	<td align="justify" width="100px">	
	Nama
	</td>
	<td align="center" width="10px">	
	:
	</td>
	<td align="justify" width="840px">	
	' . strtoupper($nama_pejabat_balai) . '
	</td>
	</tr>
	<tr>
	<td align="justify" width="50px">	
	</td>
	<td align="justify" width="100px">	
	Jabatan
	</td>
	<td align="center" width="10px">	
	:
	</td>
	<td align="justify" width="840px">	
	' . $jabatan_pejabat_balai . '
	</td>
	</tr>
	<tr>
	<td align="justify" width="1000px">	
	Selaku atasan langsung pihak pertama, selanjutnya disebut <b>PIHAK KEDUA</b>
	</td>
	</tr>
	<tr>
	<td align="center" width="1000px">	
	&nbsp;
	</td>
	</tr>
	<tr>
	<td align="justify" width="1000px">	
	<b>PIHAK PERTAMA</b> dan <b>PIHAK KEDUA</b> sepakat untuk membuat Perjanjian Kinerja dengan ketentuan sebagai berikut:
	</td>
	</tr>
	<tr>
	<td align="justify" width="30px">
	1.	
	</td>
	<td align="justify" width="970px">	
	Pihak pertama pada tahun ' . $tahunsekarang . ' ini berjanji akan mewujudkan target kinerja yang seharusnya sesuai dengan lampiran perjanjian ini, dalam rangka mencapai target kinerja jangka menengah seperti yang telah ditetapkan dalam dokumen perencanaan. Keberhasilan dan kegagalan pencapaian target kinerja tersebut menjadi tanggungjawab pihak pertama.
	</td>
	</tr>
	<tr>
	<td align="justify" width="30px">
	2.	
	</td>
	<td align="justify" width="970px">	
	Pihak kedua akan melakukan supervisi yang diperlukan serta akan melakukan evaluasi terhadap capaian kinerja dari perjanjian ini dan mengambil tindakan yang diperlukan dalam rangka pemberian penghargaan dan sanksi.
	</td>
	</tr>
	<tr>
	<td align="center" width="300px">
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="300px">
	' . ucfirst($lokasi_pejabat) . ', ' . $tanggal_pejabat . '
	</td>
	</tr>
	<tr>
	<td align="center" width="300px">
	<b>Pihak Kedua</b>
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="300px">
	<b>Pihak Pertama</b>
	</td>
	</tr>
	<tr>';
	if ($ttdinput == '0') {
		$html = $html . '<td align="center" width="300px">
		</td>';
	} else {
		$html = $html . '<td align="center" width="300px">
		<img src="../../uploadedfile/filenya_ttd/' . $ttd_pejabat_balai . '" height="100">
		</td>';
	}
	$html = $html . '
	<td align="center" width="175px">
	</td>
	<td align="center" width="175px">
	</td>
	';
	if ($ttdinput == '0') {
		$html = $html . '<td align="center" width="300px">
		<br><br><br><br><br><br>
		</td>';
	} else {
		$html = $html . '<td align="center" width="300px">
		<img src="../../uploadedfile/filenya_ttd/' . $ttd_pejabat . '" height="100">
		</td>';
	}
	$html = $html . '
	</tr>
	<tr>
	<td align="center" width="300px">
	<b>' . strtoupper($nama_pejabat_balai) . '</b>
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="300px">
	<b>' . strtoupper($nama_pejabat) . '</b>
	</td>
	</tr>
	';

	$html = $html . '
	</tbody>
	</table>
	';
}

$pdf->writeHTML($html, true, false, true, false, '');
// reset pointer to the last page
$pdf->SetFont('dejavusans', '', 9);
$pdf->AddPage('L', $resolution);
$html = '

<table border="0"  cellpadding="2" style="width:100%;" >
<tr>
<td align="center" width="1000px">	
<b>PERJANJIAN KINERJA TAHUN ' . $tahunsekarang . '</b>
</td>
</tr>';
if (strlen($nama_satker) < 120) {
	$html = $html . '
	<tr>
	<td align="center" width="1000px">	
	<b>' . $nama_satker . '</b>
	</td>
	</tr>';
} else {
	$html = $html . '<tr>
	<td align="center" width="150px">
	</td>
	<td align="center" width="700px">	
	<b>' . $nama_satker . '</b>
	</td>
	<td align="center" width="150px">
	</td>
	</tr>';
}

$html = $html . '
<tr>
<td align="center" width="1000px">	
<b>' . $nama_balai . '</b>
</td>
</tr>
<tr>
<td align="center" width="250px">
</td>
<td align="center" width="250px">
</td>
<td align="center" width="250px">
</td>
<td align="right" width="250px">
<b>Lampiran 1</b>
</td>
</tr>
</table>
';

$pdf->writeHTML($html, true, false, true, false, '');
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 5);
$pdf->SetFont('dejavusans', '', 8);
$html = '
<table border="1"  cellpadding="2" style="width:100%;" >
';
$html = $html . '
<tr>
<td align="justify" width="800px" style="background-color: #D3D3D3;">
<b>SASARAN PROGRAM / INDIKATOR SASARAN PROGRAM</b>
</td>
<td align="center" width="200px" style="background-color: #D3D3D3;">
<b>TARGET</b>
</td>
</tr>
';
$html = $html . '
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 5);
$myArray = array();
// IKSK
$sql = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
,IF(a.satuan = '0', null, a.satuan) as satuan
,IF(a.output = '0', null, a.output) as output
,IF(a.outcome = '0', null, a.outcome) as outcome
, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma, a.kumulatif
,(select count(zz.id) from tb_data_pk_tidak_cetak_awal zz where zz.id_indikator=a.id and zz.deleted='0'
and zz.kode_satker='" . $kode_satker . "') as terpilih
,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
IF(a.level = 'ISP', 'levelsubsubkegiatan', 
IF(a.level = 'KEGIATAN', 'levelpaket', 
IF(a.level = 'SK', 'levelpekerjaan', 
IF(a.level = 'IKSK', 'levelakhir', 
IF(a.level = 'KRO', 'levelakhir', 
IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
from tb_indikator_awal a 
left join master_unor b
on a.kdunor = b.id
where a.deleted='0' and a.level = 'IKSK'
" . $filterquery . "
order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";

//echo $sql;
//die();
$result = mysqli_query($link, $sql);
$num = mysqli_num_rows($result);
if ($num > 0) {
	if ($result) {
		while ($row = mysqli_fetch_assoc($result)) {
			$belakangkoma = (float) $row['belakangkoma'];
			$baseline = 0;
			$target = 0;
			$target_komponen = 0;
			$target_tahun_berjalan = 0;
			$target_komponen_tahun_berjalan = 0;
			$skor_komponen_a = 0;
			$skor_komponen_b = 0;
			$skor_komponen_c = 0;
			$output_tahun_berjalan = 0;
			$output_komponen_tahun_berjalan = 0;
			$skor_komponen_a_tahun_berjalan = 0;
			$skor_komponen_b_tahun_berjalan = 0;
			$skor_komponen_c_tahun_berjalan = 0;
			$sqlbaseline = "select baseline
			from tb_data_baseline_awal
			where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
			and id_indikator = '" . $row['id'] . "'";
			//echo $sqlbaseline;
			$resultbaseline = mysqli_query($link, $sqlbaseline);
			$numbaseline = mysqli_num_rows($resultbaseline);
			if ($numbaseline > 0) {
				while ($rowbaseline = mysqli_fetch_assoc($resultbaseline)) {
					$baseline = (float) $rowbaseline['baseline'];
				}
			}
			if ($row['kumulatif'] == '0') {
				$sqlskor = "select sum(target) as target
				from tb_data_pk_awal
				where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
				and id_indikator = '" . $row['id'] . "' and tahun = '" . $tahunsekarang . "'";
				$resultskor = mysqli_query($link, $sqlskor);
				$numskor = mysqli_num_rows($resultskor);
				if ($numskor > 0) {
					while ($rowskor = mysqli_fetch_assoc($resultskor)) {
						$target = (float) $rowskor['target'] + $baseline;
					}
				}
			} else {
				if ($row['kode_unique']  == 'IKSK7693SK101') {
					$sqlskor = "select sum(volume) as target
					from tb_data_pk_awal
					where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
					and id_indikator = '" . $row['id'] . "' and tahun = '" . $tahunsekarang . "'";

					$resultskor = mysqli_query($link, $sqlskor);
					$numskor = mysqli_num_rows($resultskor);
					if ($numskor > 0) {
						while ($rowskor = mysqli_fetch_assoc($resultskor)) {
							$target = (float) $rowskor['target'] + $baseline;
						}
					}
				} else {
					$sqlskor = "select sum(target) as target
					from tb_data_pk_awal
					where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
					and id_indikator = '" . $row['id'] . "' and tahun = '" . $tahunsekarang . "'";

					$resultskor = mysqli_query($link, $sqlskor);
					$numskor = mysqli_num_rows($resultskor);
					if ($numskor > 0) {
						while ($rowskor = mysqli_fetch_assoc($resultskor)) {
							$target = (float) $rowskor['target'] + $baseline;
						}
					}
				}
			}

			//echo $sqlskor;

			if ($row['kode_unique']  == 'IKSK7693SK101') {
				$sqlskortahunsekarang = "select sum(volume) as target
				from tb_data_pk_awal
				where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
				and id_indikator = '" . $row['id'] . "' and tahun = '" . $tahunsekarang . "'";
				//echo $sqlskortahunsekarang;
				$resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
				$numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
				if ($numskortahunsekarang > 0) {
					while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
						$target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
					}
				}
			} else {
				$sqlskortahunsekarang = "select sum(target) as target
				from tb_data_pk_awal
				where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
				and id_indikator = '" . $row['id'] . "' and tahun = '" . $tahunsekarang . "'";
				//echo $sqlskortahunsekarang;
				$resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
				$numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
				if ($numskortahunsekarang > 0) {
					while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
						$target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
					}
				}
			}


			$sqloutputtahunsekarang = "select sum(volume) as volume
			from tb_data_pk_awal
			where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
			and id_indikator = '" . $row['id'] . "' and tahun = '" . $tahunsekarang . "'";
			//echo $sqloutputtahunsekarang;
			$resultoutputtahunsekarang = mysqli_query($link, $sqloutputtahunsekarang);
			$numoutputtahunsekarang = mysqli_num_rows($resultoutputtahunsekarang);
			if ($numoutputtahunsekarang > 0) {
				while ($rowoutputtahunsekarang = mysqli_fetch_assoc($resultoutputtahunsekarang)) {
					$output_tahun_berjalan = (float) $rowoutputtahunsekarang['volume'];
				}
			}

			if ($row['hitungan_pk'] == '2' || $row['hitungan_pk'] == '3') {
				$sqlskorsemua = "select nilai,rumus
				from tb_data_pk_awal
				where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
				and id_indikator = '" . $row['id'] . "'";
				//echo $sqlskor;
				//die;
				//die;
				$sqlskor = "select nilai,rumus
				from tb_data_pk_awal
				where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
				and tahun = '" . $tahunsekarang . "'
				and id_indikator = '" . $row['id'] . "'";
				//echo $sqlskor;
				//die;
				$resultskor = mysqli_query($link, $sqlskor);
				$numskor = mysqli_num_rows($resultskor);
				if ($numskor > 0) {
					while ($rowskor = mysqli_fetch_assoc($resultskor)) {
						$nilai = $rowskor['nilai'];
						$rumus = $rowskor['rumus'];
						$tempnilai = explode("|", $nilai);
						$temprumus = explode("|", $rumus);

						$nilaisekarang = $tempnilai[count($tempnilai) - 1];

						//echo $nilai;
						//die;

					}
					$target = $nilaisekarang;
					$output_tahun_berjalan = $nilaisekarang;
					$target_tahun_berjalan = $nilaisekarang;
				} else {
					$target = 0;
					$output_tahun_berjalan = 0;
					$target_tahun_berjalan = 0;
				}
			} else {
				$target = $target;
				$output_tahun_berjalan = $output_tahun_berjalan;
				$target_tahun_berjalan = $target_tahun_berjalan;
			}


			if ($row['hitungan_pk'] == '1') {
				$target = number_format($target, $belakangkoma, ",", ".");
				$output_tahun_berjalan = number_format($output_tahun_berjalan, $belakangkoma, ",", ".");
				$target_tahun_berjalan = number_format($target_tahun_berjalan, $belakangkoma, ",", ".");
			} else {
				$target = number_format($target, $belakangkoma, ",", ".");
				$output_tahun_berjalan = number_format($output_tahun_berjalan, $belakangkoma, ",", ".");
				$target_tahun_berjalan = number_format($target_tahun_berjalan, $belakangkoma, ",", ".");
			}
			//echo (float) $row['terpilih'];
			//die;

			$sqllevelISPSK = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
			,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
			,IF(a.satuan = '0', null, a.satuan) as satuan
			,IF(a.output = '0', null, a.output) as output
			,IF(a.outcome = '0', null, a.outcome) as outcome
			, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
			, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
			, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
			, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
			,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk, a.belakangkoma, a.kumulatif
			,(select count(zz.id) from tb_data_pk_tidak_cetak_awal zz where zz.id_indikator=a.id and zz.deleted='0'
        	and zz.kode_satker='" . $kode_satker . "') as terpilih
			,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
			IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
			IF(a.level = 'ISP', 'levelsubsubkegiatan', 
			IF(a.level = 'KEGIATAN', 'levelpaket', 
			IF(a.level = 'SK', 'levelpekerjaan', 
			IF(a.level = 'IKSK', 'levelakhir', 
			IF(a.level = 'KRO', 'levelakhir', 
			IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
			from tb_indikator_awal a 
			left join master_unor b
			on a.kdunor = b.id
			where a.deleted='0' and a.id = '" . $row['id_parent'] . "'
			order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
			//echo $sqllevelISPSK;
			//die;
			$resultlevelISPSK = mysqli_query($link, $sqllevelISPSK);
			while ($rowlevelISPSK = mysqli_fetch_assoc($resultlevelISPSK)) {
				$sqllevelKEGIATAN = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
				,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
				,IF(a.satuan = '0', null, a.satuan) as satuan
				,IF(a.output = '0', null, a.output) as output
				,IF(a.outcome = '0', null, a.outcome) as outcome
				, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
				, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
				, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
				, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
				,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk, a.belakangkoma, a.kumulatif
				,(select count(zz.id) from tb_data_pk_tidak_cetak_awal zz where zz.id_indikator=a.id and zz.deleted='0'
        		and zz.kode_satker='" . $kode_satker . "') as terpilih
				,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
				IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
				IF(a.level = 'ISP', 'levelsubsubkegiatan', 
				IF(a.level = 'KEGIATAN', 'levelpaket', 
				IF(a.level = 'SK', 'levelpekerjaan', 
				IF(a.level = 'IKSK', 'levelakhir', 
				IF(a.level = 'KRO', 'levelakhir', 
				IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
				from tb_indikator_awal a 
				left join master_unor b
				on a.kdunor = b.id
				where a.deleted='0' and a.id = '" . $rowlevelISPSK['id_parent'] . "'
				order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
				//echo $sqllevelKEGIATAN;
				//die;
				$resultlevelKEGIATAN = mysqli_query($link, $sqllevelKEGIATAN);
				while ($rowlevelKEGIATAN = mysqli_fetch_assoc($resultlevelKEGIATAN)) {
					$sqllevelSASARANPROGRAM = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
					,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
					,IF(a.satuan = '0', null, a.satuan) as satuan
					,IF(a.output = '0', null, a.output) as output
					,IF(a.outcome = '0', null, a.outcome) as outcome
					, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
					, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
					, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
					, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
					,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk, a.belakangkoma, a.kumulatif
					,(select count(zz.id) from tb_data_pk_tidak_cetak_awal zz where zz.id_indikator=a.id and zz.deleted='0'
        			and zz.kode_satker='" . $kode_satker . "') as terpilih
					,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
					IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
					IF(a.level = 'ISP', 'levelsubsubkegiatan', 
					IF(a.level = 'KEGIATAN', 'levelpaket', 
					IF(a.level = 'SK', 'levelpekerjaan', 
					IF(a.level = 'IKSK', 'levelakhir', 
					IF(a.level = 'KRO', 'levelakhir', 
					IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
					from tb_indikator_awal a 
					left join master_unor b
					on a.kdunor = b.id
					where a.deleted='0' and a.id = '" . $rowlevelKEGIATAN['id_parent'] . "'
					order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
					//\\\\\echo $sqllevelPROGRAM;
					//die;
					$resultlevelSASARANPROGRAM = mysqli_query($link, $sqllevelSASARANPROGRAM);
					while ($rowlevelSASARANPROGRAM = mysqli_fetch_assoc($resultlevelSASARANPROGRAM)) {
						$searchValue = (float) $rowlevelSASARANPROGRAM['id'];
						$found = false;
						foreach ($myArray as $obj) {
							if (isset($obj->id) && $obj->id === $searchValue) {
								$found = true;
								break;
							}
						}
						if (!$found) {
							$currentY = $pdf->GetY();
							if ($rowlevelSASARANPROGRAM['textindikator'] == 'DUKUNGAN MANAJEMEN') {
								$pdf->AddPage('L', $resolution);
								$pdf->SetFont('dejavusans', '', 9);
								$html = '
								<table border="0"  cellpadding="2" style="width:100%;" >
								<tr>
								<td align="center" width="1000px">	
								<b>PERJANJIAN KINERJA TAHUN ' . $tahunsekarang . '</b>
								</td>
								</tr>';
								if (strlen($nama_satker) < 120) {
									$html = $html . '
									<tr>
									<td align="center" width="1000px">	
									<b>' . $nama_satker . '</b>
									</td>
									</tr>';
								} else {
									$html = $html . '<tr>
									<td align="center" width="150px">
									</td>
									<td align="center" width="700px">	
									<b>' . $nama_satker . '</b>
									</td>
									<td align="center" width="150px">
									</td>
									</tr>';
								}

								$html = $html . '
								<tr>
								<td align="center" width="1000px">	
								<b>' . $nama_balai . '</b>
								</td>
								</tr>
								<tr>
								<td align="center" width="250px">
								</td>
								<td align="center" width="250px">
								</td>
								<td align="center" width="250px">
								</td>
								<td align="right" width="250px">
								<b>Lampiran 1</b>
								</td>
								</tr>
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 5);
								$pdf->SetFont('dejavusans', '', 8);
								$html = '
								<table border="1"  cellpadding="2" style="width:100%;" >
								';
								$html = $html . '
								<tr>
								<td align="justify" width="800px" style="background-color: #D3D3D3;">
								<b>SASARAN PROGRAM / INDIKATOR SASARAN PROGRAM</b>
								</td>
								<td align="center" width="200px" style="background-color: #D3D3D3;">
								<b>TARGET</b>
								</td>
								</tr>
								';
								$html = $html . '
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 5);
								$html = '
								<table border="1"  cellpadding="2" style="width:100%;" >
								<tr>
								<td align="justify" width="1000px">
								<b>PROGRAM: ' . $rowlevelSASARANPROGRAM['textindikator'] . '</b>
								</td>
								</tr>
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 4.7);
								$currentY = $pdf->GetY();
							} else {
								$html = '
								<table border="1"  cellpadding="2" style="width:100%;" >
								<tr>
								<td align="justify" width="1000px">
								<b>PROGRAM: ' . $rowlevelSASARANPROGRAM['textindikator'] . '</b>
								</td>
								</tr>
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 4.7);
								$currentY = $pdf->GetY();
							}
						}
						$myArray[] = (object)
						[
							'id' => (float) $rowlevelSASARANPROGRAM['id'],
							'tahun' => (float) $rowlevelSASARANPROGRAM['tahun'],
							'id_parent' => $rowlevelSASARANPROGRAM['id_parent'],
							'kdunor' => $rowlevelSASARANPROGRAM['kdunor'],
							'nama_unor' => $rowlevelSASARANPROGRAM['nama_unor'],
							'urutlevel' => (float) $rowlevelSASARANPROGRAM['urutlevel'],
							'level' => $rowlevelSASARANPROGRAM['level'],
							'kode' => $rowlevelSASARANPROGRAM['kode'],
							'kode_unique' => $rowlevelSASARANPROGRAM['kode_unique'],
							'textindikator' => $rowlevelSASARANPROGRAM['textindikator'],
							'satuan' => $rowlevelSASARANPROGRAM['satuan'],
							'namasatuan' => $rowlevelSASARANPROGRAM['namasatuan'],
							'output' => $rowlevelSASARANPROGRAM['output'],
							'namaoutput' => $rowlevelSASARANPROGRAM['namaoutput'],
							'outcome' => $rowlevelSASARANPROGRAM['outcome'],
							'namaoutcome' => $rowlevelSASARANPROGRAM['namaoutcome'],
							'penanggungjawab' => $rowlevelSASARANPROGRAM['penanggungjawab'],
							'pelaksana' => $rowlevelSASARANPROGRAM['pelaksana'],
							'pelaksanacode' => $rowlevelSASARANPROGRAM['pelaksanacode'],
							'pelaksanalabel' => $rowlevelSASARANPROGRAM['pelaksanalabel'],
							'target' => $rowlevelSASARANPROGRAM['target'],
							'urut' => (float) $rowlevelSASARANPROGRAM['urut'],
							'hitungan_pk' => $rowlevelSASARANPROGRAM['hitungan_pk'],
							'namahitungan_pk' => $rowlevelSASARANPROGRAM['namahitungan_pk'],
							'targetkumulatif' => 0,
							'output_tahun_berjalan' => 0,
							'target_tahun_berjalan' => 0,
							'class' => $rowlevelSASARANPROGRAM['class'],
						];
					}

					$searchValue = (float) $rowlevelISPSK['id'];
					$found = false;
					foreach ($myArray as $obj) {
						if (isset($obj->id) && $obj->id === $searchValue) {
							$found = true;
							break;
						}
					}
					if (!$found) {
						if ((float) $rowlevelISPSK['terpilih'] == 0) {
							array_push($myArrayIKU, $rowlevelISPSK['kode_unique']);
							$html = '
							<table border="1"  cellpadding="2" style="width:100%;" >
							<tr>
							<td align="justify" width="1000px">
							<b>SK: ' . $rowlevelISPSK['textindikator'] . '</b>
							</td>
							</tr>
							</table>
							';
							$pdf->writeHTML($html, true, false, true, false, '');
							$currentY = $pdf->GetY();
							$pdf->SetY($currentY - 4.7);
							$currentY = $pdf->GetY();
						}
					}

					$myArray[] = (object)
					[
						'id' => (float) $rowlevelISPSK['id'],
						'tahun' => (float) $rowlevelISPSK['tahun'],
						'id_parent' => $rowlevelISPSK['id_parent'],
						'kdunor' => $rowlevelISPSK['kdunor'],
						'nama_unor' => $rowlevelISPSK['nama_unor'],
						'urutlevel' => (float) $rowlevelISPSK['urutlevel'],
						'level' => $rowlevelISPSK['level'],
						'kode' => $rowlevelISPSK['kode'],
						'kode_unique' => $rowlevelISPSK['kode_unique'],
						'textindikator' => $rowlevelISPSK['textindikator'],
						'satuan' => $rowlevelISPSK['satuan'],
						'namasatuan' => $rowlevelISPSK['namasatuan'],
						'output' => $rowlevelISPSK['output'],
						'namaoutput' => $rowlevelISPSK['namaoutput'],
						'outcome' => $rowlevelISPSK['outcome'],
						'namaoutcome' => $rowlevelISPSK['namaoutcome'],
						'penanggungjawab' => $rowlevelISPSK['penanggungjawab'],
						'pelaksana' => $rowlevelISPSK['pelaksana'],
						'pelaksanacode' => $rowlevelISPSK['pelaksanacode'],
						'pelaksanalabel' => $rowlevelISPSK['pelaksanalabel'],
						'target' => $rowlevelISPSK['target'],
						'urut' => (float) $rowlevelISPSK['urut'],
						'hitungan_pk' => $rowlevelISPSK['hitungan_pk'],
						'namahitungan_pk' => $rowlevelISPSK['namahitungan_pk'],
						'targetkumulatif' => 0,
						'output_tahun_berjalan' => 0,
						'target_tahun_berjalan' => 0,
						'class' => $rowlevelISPSK['class'],
					];
				}
			}

			if ((float) $row['terpilih'] == 0) {
				array_push($myArrayIKU, $row['kode_unique']);
				$html = '
				<table border="1"  cellpadding="2" style="width:100%;" >
				<tr>
				<td align="justify" width="800px">
				' . $row['textindikator'] . '
				</td>
				<td align="center" width="200px">
				' . $target . ' ' . $row['namasatuan'] . '
				</td>
				</tr>
				</table>
				';
				$pdf->writeHTML($html, true, false, true, false, '');
				$currentY = $pdf->GetY();
				$pdf->SetY($currentY - 4.7);
				$currentY = $pdf->GetY();

				$myArray[] = (object)
				[
					'id' => (float) $row['id'],
					'tahun' => (float) $row['tahun'],
					'id_parent' => $row['id_parent'],
					'kdunor' => $row['kdunor'],
					'nama_unor' => $row['nama_unor'],
					'urutlevel' => (float) $row['urutlevel'],
					'level' => $row['level'],
					'kode' => $row['kode'],
					'kode_unique' => $row['kode_unique'],
					'textindikator' => $row['textindikator'],
					'satuan' => $row['satuan'],
					'namasatuan' => $row['namasatuan'],
					'output' => $row['output'],
					'namaoutput' => $row['namaoutput'],
					'outcome' => $row['outcome'],
					'namaoutcome' => $row['namaoutcome'],
					'penanggungjawab' => $row['penanggungjawab'],
					'pelaksana' => $row['pelaksana'],
					'pelaksanacode' => $row['pelaksanacode'],
					'pelaksanalabel' => $row['pelaksanalabel'],
					'target' => $row['target'],
					'urut' => (float) $row['urut'],
					'hitungan_pk' => $row['hitungan_pk'],
					'namahitungan_pk' => $row['namahitungan_pk'],
					'targetkumulatif' => $target,
					'output_tahun_berjalan' => $target_tahun_berjalan,
					'target_tahun_berjalan' => $output_tahun_berjalan,
					'class' => $row['class'],
				];
			}
		}
	}
}

$currentY = $pdf->GetY();
$pdf->SetY($currentY + 5);
$html = '<tr>
<td align="left" colspan="3" width="820px">
<b>PROGRAM / KEGIATAN</b>
</td>
<td align="center" width="170px">
<b>ANGGARAN</b>
</td>
</tr>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->SetFont('dejavusans', '', 9);
$kode_satkeremon = "";
$kode_satkeremon2 = "";
$sqlsatkeremon = "select kode_satker_old_pendek,kode_satker_pendek 
from master_satker where kode_satker = '" . $kode_satker . "'";
//echo $sqlsatkeremon;
//die;
$resultsatkeremon = mysqli_query($link, $sqlsatkeremon);
while ($rowsatkeremon = mysqli_fetch_assoc($resultsatkeremon)) {
	$kode_satkeremon = $rowsatkeremon['kode_satker_old_pendek'];
	$kode_satkeremon2 = $rowsatkeremon['kode_satker_pendek'];
}
$sqlpagu = "select a.kdprogram,a.nmprogram 
,ifnull((select sum(b.pg) from paket_pk_awal b where b.kdprogram=a.kdprogram
and b.kdsatker in (" . $kode_satkeremon2 . "," . $kode_satkeremon . ")),0) as pagu
from tprogram a
where a.kdprogram in ('FC','WA')
order by a.kdprogram";

//echo $sqlpagu;

$resultpagu = mysqli_query($link, $sqlpagu);
$html = '';
$urut = 1;
$totalpagu = 0;
while ($rowpagu = mysqli_fetch_assoc($resultpagu)) {
	$totalpagu = $totalpagu + (float) $rowpagu['pagu'];
	$html = $html . '<tr>
	<td align="left" colspan="2" width="740px">
	<b>' . $urut . '.' . $rowpagu['nmprogram'] . '</b>
	</td>
	<td align="center" width="50px">
	<b>Rp.</b>
	</td>
	<td align="right" width="200px">
	<b>' . replace_dot_koma($rowpagu['pagu']) . '</b>
	</td>
	</tr>';
	$sqlpagukegiatan = "select sum(b.pg) as pagukegiatan 
	, (select x.nmgiat from tgiat x where x.kdgiat=b.kdgiat) as nmgiat
	, (select x.kdgiat from tgiat x where x.kdgiat=b.kdgiat) as kdgiat
	from paket_pk_awal b where b.kdprogram='" . $rowpagu['kdprogram'] . "'
	and b.kdsatker in (" . $kode_satkeremon2 . "," . $kode_satkeremon . ") group by b.kdgiat order by b.kdgiat";
	$resultpagukegiatan = mysqli_query($link, $sqlpagukegiatan);
	$urutkegiatan = 'a';
	while ($rowpagukegiatan = mysqli_fetch_assoc($resultpagukegiatan)) {
		$html = $html . '<tr>
		<td align="left" width="10px">
		</td>
		<td align="left" width="730px">
		' . $rowpagukegiatan['kdgiat'] . ' - ' . $rowpagukegiatan['nmgiat'] . '
		</td>
		<td align="center" width="50px">
		Rp.
		</td>
		<td align="right" width="200px">
		' . replace_dot_koma($rowpagukegiatan['pagukegiatan']) . '
		</td>
		</tr>';
		$urutkegiatan++;
	}
	$urut++;
}
$html = $html . '<tr>
<td align="right" colspan="3" width="740px">
<b>TOTAL</b>
</td>
<td align="center" width="50px">
<b>Rp.</b>
</td>
<td align="right" width="200px">
<b>' . replace_dot_koma($totalpagu) . '</b>
</td>
</tr>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->SetFont('dejavusans', '', 11);
$html = '
<tr>
<td align="center" width="300px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
' . ucfirst($lokasi_pejabat) . ', ' . $tanggal_pejabat . '
</td>
</tr>
<tr>
<td align="center" width="300px">
<b>' . $jabatan_pejabat_balai . '</b>
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
<b>' . $jabatan_pejabat . '</b>
</td>
</tr>
';
if ($ttdinput == '0') {
	$html = $html . '
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	';
} else {
	$html = $html . '
	<tr>
	<td align="center" width="300px">
	<img src="../../uploadedfile/filenya_ttd/' . $ttd_pejabat_balai . '" height="100">
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="300px">
	<img src="../../uploadedfile/filenya_ttd/' . $ttd_pejabat . '" height="100">
	</td>
	</tr>
	';
}
$html = $html . '
<tr>
<td align="center" width="300px">
<b>' . strtoupper($nama_pejabat_balai) . '</b>
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
<b>' . strtoupper($nama_pejabat) . '</b>
</td>
</tr>
';

$html = $html . '
</tbody>
</table>
';

$pdf->writeHTML($html, true, false, true, false, '');

$pdf->AddPage('L', $resolution);
$pdf->SetFont('dejavusans', '', 9);
$currentY = $pdf->GetY();

$html = '
<table border="0"  cellpadding="2" style="width:100%;" >
<tr>
<td align="center" width="1000px">	
<b>PERJANJIAN KINERJA TAHUN ' . $tahunsekarang . '</b>
</td>
</tr>';
if (strlen($nama_satker) < 120) {
	$html = $html . '
	<tr>
	<td align="center" width="1000px">	
	<b>' . $nama_satker . '</b>
	</td>
	</tr>';
} else {
	$html = $html . '<tr>
	<td align="center" width="150px">
	</td>
	<td align="center" width="700px">	
	<b>' . $nama_satker . '</b>
	</td>
	<td align="center" width="150px">
	</td>
	</tr>';
}

$html = $html . '
<tr>
<td align="center" width="1000px">	
<b>' . $nama_balai . '</b>
</td>
</tr>
<tr>
<td align="center" width="250px">
</td>
<td align="center" width="250px">
</td>
<td align="center" width="250px">
</td>
<td align="right" width="250px">
<b>Lampiran 2</b>
</td>
</tr>
</table>
';

$pdf->writeHTML($html, true, false, true, false, '');
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 5);
$pdf->SetFont('dejavusans', '', 8);
$html = '
<table border="1"  cellpadding="2" style="width:100%;" >
';
$html = $html . '
<tr>
<td align="justify" width="520px" style="background-color: #D3D3D3;">
<b>SASARAN PROGRAM / INDIKATOR SASARAN PROGRAM</b>
</td>
<td align="center" width="160px" style="background-color: #D3D3D3;">
<b>TARGET KUMULATIF</b>
</td>
<td align="center" width="160px" style="background-color: #D3D3D3;">
<b>OUTPUT (Tahun Berjalan)</b>
</td>
<td align="center" width="160px" style="background-color: #D3D3D3;">
<b>OUTCOME (Tahun Berjalan)</b>
</td>
</tr>
';
$html = $html . '
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 5);
$myArray = array();
// IKSK
$sql = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
,IF(a.satuan = '0', null, a.satuan) as satuan
,IF(a.output = '0', null, a.output) as output
,IF(a.outcome = '0', null, a.outcome) as outcome
, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma, a.kumulatif
,(select count(zz.id) from tb_data_pk_tidak_cetak_awal zz where zz.id_indikator=a.id and zz.deleted='0'
and zz.kode_satker='" . $kode_satker . "') as terpilih
,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
IF(a.level = 'ISP', 'levelsubsubkegiatan', 
IF(a.level = 'KEGIATAN', 'levelpaket', 
IF(a.level = 'SK', 'levelpekerjaan', 
IF(a.level = 'IKSK', 'levelakhir', 
IF(a.level = 'KRO', 'levelakhir', 
IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
from tb_indikator_awal a 
left join master_unor b
on a.kdunor = b.id
where a.deleted='0' and a.level = 'IKSK'
" . $filterquery . "
order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";

//echo $sql;
//die();
$result = mysqli_query($link, $sql);
$num = mysqli_num_rows($result);
if ($num > 0) {
	if ($result) {
		while ($row = mysqli_fetch_assoc($result)) {
			$belakangkoma = (float) $row['belakangkoma'];
			$baseline = 0;
			$target = 0;
			$target_komponen = 0;
			$target_tahun_berjalan = 0;
			$target_komponen_tahun_berjalan = 0;
			$skor_komponen_a = 0;
			$skor_komponen_b = 0;
			$skor_komponen_c = 0;
			$output_tahun_berjalan = 0;
			$output_komponen_tahun_berjalan = 0;
			$skor_komponen_a_tahun_berjalan = 0;
			$skor_komponen_b_tahun_berjalan = 0;
			$skor_komponen_c_tahun_berjalan = 0;
			$sqlbaseline = "select baseline
			from tb_data_baseline_awal
			where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
			and id_indikator = '" . $row['id'] . "'";
			//echo $sqlbaseline;
			$resultbaseline = mysqli_query($link, $sqlbaseline);
			$numbaseline = mysqli_num_rows($resultbaseline);
			if ($numbaseline > 0) {
				while ($rowbaseline = mysqli_fetch_assoc($resultbaseline)) {
					$baseline = (float) $rowbaseline['baseline'];
				}
			}
			if ($row['kumulatif'] == '0') {
				$sqlskor = "select sum(target) as target
				from tb_data_pk_awal
				where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
				and id_indikator = '" . $row['id'] . "' and tahun = '" . $tahunsekarang . "'";
				$resultskor = mysqli_query($link, $sqlskor);
				$numskor = mysqli_num_rows($resultskor);
				if ($numskor > 0) {
					while ($rowskor = mysqli_fetch_assoc($resultskor)) {
						$target = (float) $rowskor['target'] + $baseline;
					}
				}
			} else {

				if ($row['kode_unique']  == 'IKSK7693SK101') {
					$sqlskor = "select sum(volume) as target
					from tb_data_pk_awal
					where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
					and id_indikator = '" . $row['id'] . "' and tahun = '" . $tahunsekarang . "'";

					$resultskor = mysqli_query($link, $sqlskor);
					$numskor = mysqli_num_rows($resultskor);
					if ($numskor > 0) {
						while ($rowskor = mysqli_fetch_assoc($resultskor)) {
							$target = (float) $rowskor['target'] + $baseline;
						}
					}
				} else {
					$sqlskor = "select sum(target) as target
					from tb_data_pk_awal
					where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
					and id_indikator = '" . $row['id'] . "' and tahun = '" . $tahunsekarang . "'";

					$resultskor = mysqli_query($link, $sqlskor);
					$numskor = mysqli_num_rows($resultskor);
					if ($numskor > 0) {
						while ($rowskor = mysqli_fetch_assoc($resultskor)) {
							$target = (float) $rowskor['target'] + $baseline;
						}
					}
				}


				$sqlskor = "select sum(capaian_outcome) as capaian_outcome
				from tb_data_pk_akhir
				where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
				and id_indikator = '" . $row['id'] . "' and tahun < '" . $tahunsekarang . "'";
				$resultskor = mysqli_query($link, $sqlskor);
				$numskor = mysqli_num_rows($resultskor);
				if ($numskor > 0) {
					while ($rowskor = mysqli_fetch_assoc($resultskor)) {
						$target = (float) $rowskor['capaian_outcome'] + $target;
					}
				}
			}

			//echo $sqlskor;

			$sqlskortahunsekarang = "select sum(target) as target
			from tb_data_pk_awal
			where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
			and id_indikator = '" . $row['id'] . "' and tahun = '" . $tahunsekarang . "'";
			//echo $sqlskortahunsekarang;
			$resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
			$numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
			if ($numskortahunsekarang > 0) {
				while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
					$target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
				}
			}

			$sqloutputtahunsekarang = "select sum(volume) as volume
			from tb_data_pk_awal
			where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
			and id_indikator = '" . $row['id'] . "' and tahun = '" . $tahunsekarang . "'";
			//echo $sqloutputtahunsekarang;
			$resultoutputtahunsekarang = mysqli_query($link, $sqloutputtahunsekarang);
			$numoutputtahunsekarang = mysqli_num_rows($resultoutputtahunsekarang);
			if ($numoutputtahunsekarang > 0) {
				while ($rowoutputtahunsekarang = mysqli_fetch_assoc($resultoutputtahunsekarang)) {
					$output_tahun_berjalan = (float) $rowoutputtahunsekarang['volume'];
				}
			}

			if ($row['hitungan_pk'] == '2' || $row['hitungan_pk'] == '3') {

				$capaian = 0;
				$sqlskor = "select sum(capaian) as capaian
				from tb_data_pk_akhir
				where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
				and id_indikator = '" . $row['id'] . "' and tahun < '" . $tahunsekarang . "'";
				$resultskor = mysqli_query($link, $sqlskor);
				$numskor = mysqli_num_rows($resultskor);
				if ($numskor > 0) {
					while ($rowskor = mysqli_fetch_assoc($resultskor)) {
						$capaian = (float) $rowskor['capaian'];
					}
				}

				$sqlskorsemua = "select nilai,rumus
				from tb_data_pk_awal
				where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
				and id_indikator = '" . $row['id'] . "'";
				//echo $sqlskor;
				//die;
				//die;
				$sqlskor = "select nilai,rumus
				from tb_data_pk_awal
				where deleted = '0' and kode_satker = '" . $kode_satker . "' and kode_tahun='" . $tahun . "'
				and tahun = '" . $tahunsekarang . "'
				and id_indikator = '" . $row['id'] . "'";
				//echo $sqlskor;
				//die;
				$resultskor = mysqli_query($link, $sqlskor);
				$numskor = mysqli_num_rows($resultskor);
				if ($numskor > 0) {
					while ($rowskor = mysqli_fetch_assoc($resultskor)) {
						$nilai = $rowskor['nilai'];
						$rumus = $rowskor['rumus'];
						$tempnilai = explode("|", $nilai);
						$temprumus = explode("|", $rumus);
						//echo $nilai;
						//die;
						$nilaisekarang = $tempnilai[count($tempnilai) - 1];
						//echo $nilai;
						//die;
					}
					if ($row['kumulatif'] == '0') {
						$target = $nilaisekarang;
					} else {
						$target = $nilaisekarang + $capaian;
					}
					$output_tahun_berjalan = $nilaisekarang;
					$target_tahun_berjalan = $nilaisekarang;
				} else {
					$target = 0;
					$output_tahun_berjalan = 0;
					$target_tahun_berjalan = 0;
				}
			} else {
				$target = $target;
				$output_tahun_berjalan = $output_tahun_berjalan;
				$target_tahun_berjalan = $target_tahun_berjalan;
			}
			$sqllevelISPSK = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
			,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
			,IF(a.satuan = '0', null, a.satuan) as satuan
			,IF(a.output = '0', null, a.output) as output
			,IF(a.outcome = '0', null, a.outcome) as outcome
			, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
			, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
			, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
			, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
			,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk, a.belakangkoma, a.kumulatif
			,(select count(zz.id) from tb_data_pk_tidak_cetak_awal zz where zz.id_indikator=a.id and zz.deleted='0'
        	and zz.kode_satker='" . $kode_satker . "') as terpilih
			,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
			IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
			IF(a.level = 'ISP', 'levelsubsubkegiatan', 
			IF(a.level = 'KEGIATAN', 'levelpaket', 
			IF(a.level = 'SK', 'levelpekerjaan', 
			IF(a.level = 'IKSK', 'levelakhir', 
			IF(a.level = 'KRO', 'levelakhir', 
			IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
			from tb_indikator_awal a 
			left join master_unor b
			on a.kdunor = b.id
			where a.deleted='0' and a.id = '" . $row['id_parent'] . "'
			order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
			//echo $sqllevelISPSK;
			//die;
			$resultlevelISPSK = mysqli_query($link, $sqllevelISPSK);
			while ($rowlevelISPSK = mysqli_fetch_assoc($resultlevelISPSK)) {
				$sqllevelKEGIATAN = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
				,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
				,IF(a.satuan = '0', null, a.satuan) as satuan
				,IF(a.output = '0', null, a.output) as output
				,IF(a.outcome = '0', null, a.outcome) as outcome
				, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
				, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
				, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
				, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
				,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk, a.belakangkoma, a.kumulatif
				,(select count(zz.id) from tb_data_pk_tidak_cetak_awal zz where zz.id_indikator=a.id and zz.deleted='0'
        		and zz.kode_satker='" . $kode_satker . "') as terpilih
				,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
				IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
				IF(a.level = 'ISP', 'levelsubsubkegiatan', 
				IF(a.level = 'KEGIATAN', 'levelpaket', 
				IF(a.level = 'SK', 'levelpekerjaan', 
				IF(a.level = 'IKSK', 'levelakhir', 
				IF(a.level = 'KRO', 'levelakhir', 
				IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
				from tb_indikator_awal a 
				left join master_unor b
				on a.kdunor = b.id
				where a.deleted='0' and a.id = '" . $rowlevelISPSK['id_parent'] . "'
				order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
				//echo $sqllevelKEGIATAN;
				//die;
				$resultlevelKEGIATAN = mysqli_query($link, $sqllevelKEGIATAN);
				while ($rowlevelKEGIATAN = mysqli_fetch_assoc($resultlevelKEGIATAN)) {
					$sqllevelSASARANPROGRAM = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
					,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
					,IF(a.satuan = '0', null, a.satuan) as satuan
					,IF(a.output = '0', null, a.output) as output
					,IF(a.outcome = '0', null, a.outcome) as outcome
					, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
					, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
					, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
					, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
					,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk, a.belakangkoma, a.kumulatif
					,(select count(zz.id) from tb_data_pk_tidak_cetak_awal zz where zz.id_indikator=a.id and zz.deleted='0'
        			and zz.kode_satker='" . $kode_satker . "') as terpilih
					,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
					IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
					IF(a.level = 'ISP', 'levelsubsubkegiatan', 
					IF(a.level = 'KEGIATAN', 'levelpaket', 
					IF(a.level = 'SK', 'levelpekerjaan', 
					IF(a.level = 'IKSK', 'levelakhir', 
					IF(a.level = 'KRO', 'levelakhir', 
					IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
					from tb_indikator_awal a 
					left join master_unor b
					on a.kdunor = b.id
					where a.deleted='0' and a.id = '" . $rowlevelKEGIATAN['id_parent'] . "'
					order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
					//\\\\\echo $sqllevelPROGRAM;
					//die;
					$resultlevelSASARANPROGRAM = mysqli_query($link, $sqllevelSASARANPROGRAM);
					while ($rowlevelSASARANPROGRAM = mysqli_fetch_assoc($resultlevelSASARANPROGRAM)) {
						$searchValue = (float) $rowlevelSASARANPROGRAM['id'];
						$found = false;
						foreach ($myArray as $obj) {
							if (isset($obj->id) && $obj->id === $searchValue) {
								$found = true;
								break;
							}
						}
						if (!$found) {
							$currentY = $pdf->GetY();
							if ($rowlevelSASARANPROGRAM['textindikator'] == 'DUKUNGAN MANAJEMEN') {
								$pdf->AddPage('L', $resolution);
								$pdf->SetFont('dejavusans', '', 9);
								$html = '
								<table border="0"  cellpadding="2" style="width:100%;" >
								<tr>
								<td align="center" width="1000px">	
								<b>PERJANJIAN KINERJA TAHUN ' . $tahunsekarang . '</b>
								</td>
								</tr>';
								if (strlen($nama_satker) < 120) {
									$html = $html . '
									<tr>
									<td align="center" width="1000px">	
									<b>' . $nama_satker . '</b>
									</td>
									</tr>';
								} else {
									$html = $html . '<tr>
									<td align="center" width="150px">
									</td>
									<td align="center" width="700px">	
									<b>' . $nama_satker . '</b>
									</td>
									<td align="center" width="150px">
									</td>
									</tr>';
								}

								$html = $html . '
								<tr>
								<td align="center" width="1000px">	
								<b>' . $nama_balai . '</b>
								</td>
								</tr>
								<tr>
								<td align="center" width="250px">
								</td>
								<td align="center" width="250px">
								</td>
								<td align="center" width="250px">
								</td>
								<td align="right" width="250px">
								<b>Lampiran 2</b>
								</td>
								</tr>
								</table>
								';

								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 5);
								$pdf->SetFont('dejavusans', '', 8);
								$html = '
								<table border="1"  cellpadding="2" style="width:100%;" >
								';
								$html = $html . '
								<tr>
								<td align="justify" width="520px" style="background-color: #D3D3D3;">
								<b>SASARAN PROGRAM / INDIKATOR SASARAN PROGRAM</b>
								</td>
								<td align="center" width="160px" style="background-color: #D3D3D3;">
								<b>TARGET KUMULATIF</b>
								</td>
								<td align="center" width="160px" style="background-color: #D3D3D3;">
								<b>OUTPUT (Tahun Berjalan)</b>
								</td>
								<td align="center" width="160px" style="background-color: #D3D3D3;">
								<b>OUTCOME (Tahun Berjalan)</b>
								</td>
								</tr>
								';
								$html = $html . '
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 5);
								$html = '
								<table border="1"  cellpadding="2" style="width:100%;" >
								<tr>
								<td align="justify" width="1000px">
								<b>PROGRAM: ' . $rowlevelSASARANPROGRAM['textindikator'] . '</b>
								</td>
								</tr>
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 4.7);
								$currentY = $pdf->GetY();
							} else {
								$html = '
								<table border="1"  cellpadding="2" style="width:100%;" >
								<tr>
								<td align="justify" width="1000px">
								<b>PROGRAM: ' . $rowlevelSASARANPROGRAM['textindikator'] . '</b>
								</td>
								</tr>
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 4.7);
								$currentY = $pdf->GetY();
								if ($currentY > 120) {
									$pdf->AddPage('L', $resolution);
									$pdf->SetFont('dejavusans', '', 9);
									$html = '<tr>
									<td align="left" colspan="4" width="990px">
									&nbsp;
									</td>
									</tr>
									<tr>
									<td align="left" colspan="4" width="990px">
									&nbsp;
									</td>
									</tr>
									<tr>
									<td align="left" colspan="4" width="990px">
									&nbsp;
									</td>
									</tr>
									<tr>
									<td align="right" colspan="4" width="990px">
									<b>Lampiran 2</b>
									</td>
									</tr>';
									$pdf->writeHTML($html, true, false, true, false, '');
									$pdf->SetFont('dejavusans', '', 8);
								}
							}
						}
						$myArray[] = (object)
						[
							'id' => (float) $rowlevelSASARANPROGRAM['id'],
							'tahun' => (float) $rowlevelSASARANPROGRAM['tahun'],
							'id_parent' => $rowlevelSASARANPROGRAM['id_parent'],
							'kdunor' => $rowlevelSASARANPROGRAM['kdunor'],
							'nama_unor' => $rowlevelSASARANPROGRAM['nama_unor'],
							'urutlevel' => (float) $rowlevelSASARANPROGRAM['urutlevel'],
							'level' => $rowlevelSASARANPROGRAM['level'],
							'kode' => $rowlevelSASARANPROGRAM['kode'],
							'kode_unique' => $rowlevelSASARANPROGRAM['kode_unique'],
							'textindikator' => $rowlevelSASARANPROGRAM['textindikator'],
							'satuan' => $rowlevelSASARANPROGRAM['satuan'],
							'namasatuan' => $rowlevelSASARANPROGRAM['namasatuan'],
							'output' => $rowlevelSASARANPROGRAM['output'],
							'namaoutput' => $rowlevelSASARANPROGRAM['namaoutput'],
							'outcome' => $rowlevelSASARANPROGRAM['outcome'],
							'namaoutcome' => $rowlevelSASARANPROGRAM['namaoutcome'],
							'penanggungjawab' => $rowlevelSASARANPROGRAM['penanggungjawab'],
							'pelaksana' => $rowlevelSASARANPROGRAM['pelaksana'],
							'pelaksanacode' => $rowlevelSASARANPROGRAM['pelaksanacode'],
							'pelaksanalabel' => $rowlevelSASARANPROGRAM['pelaksanalabel'],
							'target' => $rowlevelSASARANPROGRAM['target'],
							'urut' => (float) $rowlevelSASARANPROGRAM['urut'],
							'hitungan_pk' => $rowlevelSASARANPROGRAM['hitungan_pk'],
							'namahitungan_pk' => $rowlevelSASARANPROGRAM['namahitungan_pk'],
							'targetkumulatif' => 0,
							'output_tahun_berjalan' => 0,
							'target_tahun_berjalan' => 0,
							'class' => $rowlevelSASARANPROGRAM['class'],
						];
					}

					$searchValue = (float) $rowlevelISPSK['id'];
					$found = false;
					foreach ($myArray as $obj) {
						if (isset($obj->id) && $obj->id === $searchValue) {
							$found = true;
							break;
						}
					}
					if (!$found) {
						if ((float) $rowlevelISPSK['terpilih'] == 0) {
							array_push($myArrayIKU, $rowlevelISPSK['kode_unique']);
							$html = '
							<table border="1"  cellpadding="2" style="width:100%;" >
							<tr>
							<td align="justify" width="1000px">
							<b>SK: ' . $rowlevelISPSK['textindikator'] . '</b>
							</td>
							</tr>
							</table>
							';
							$pdf->writeHTML($html, true, false, true, false, '');
							$currentY = $pdf->GetY();
							$pdf->SetY($currentY - 4.7);
							$currentY = $pdf->GetY();
						}
					}

					$myArray[] = (object)
					[
						'id' => (float) $rowlevelISPSK['id'],
						'tahun' => (float) $rowlevelISPSK['tahun'],
						'id_parent' => $rowlevelISPSK['id_parent'],
						'kdunor' => $rowlevelISPSK['kdunor'],
						'nama_unor' => $rowlevelISPSK['nama_unor'],
						'urutlevel' => (float) $rowlevelISPSK['urutlevel'],
						'level' => $rowlevelISPSK['level'],
						'kode' => $rowlevelISPSK['kode'],
						'kode_unique' => $rowlevelISPSK['kode_unique'],
						'textindikator' => $rowlevelISPSK['textindikator'],
						'satuan' => $rowlevelISPSK['satuan'],
						'namasatuan' => $rowlevelISPSK['namasatuan'],
						'output' => $rowlevelISPSK['output'],
						'namaoutput' => $rowlevelISPSK['namaoutput'],
						'outcome' => $rowlevelISPSK['outcome'],
						'namaoutcome' => $rowlevelISPSK['namaoutcome'],
						'penanggungjawab' => $rowlevelISPSK['penanggungjawab'],
						'pelaksana' => $rowlevelISPSK['pelaksana'],
						'pelaksanacode' => $rowlevelISPSK['pelaksanacode'],
						'pelaksanalabel' => $rowlevelISPSK['pelaksanalabel'],
						'target' => $rowlevelISPSK['target'],
						'urut' => (float) $rowlevelISPSK['urut'],
						'hitungan_pk' => $rowlevelISPSK['hitungan_pk'],
						'namahitungan_pk' => $rowlevelISPSK['namahitungan_pk'],
						'targetkumulatif' => 0,
						'output_tahun_berjalan' => 0,
						'target_tahun_berjalan' => 0,
						'class' => $rowlevelISPSK['class'],
					];
				}
			}
			//ISPSK
			if ($row['hitungan_pk'] == '1') {
				$target = number_format($target, $belakangkoma, ",", ".");
				$output_tahun_berjalan = number_format($output_tahun_berjalan, $belakangkoma, ",", ".");
				$target_tahun_berjalan = number_format($target_tahun_berjalan, $belakangkoma, ",", ".");
			} else {
				$target = number_format($target, $belakangkoma, ",", ".");
				$output_tahun_berjalan = number_format($output_tahun_berjalan, $belakangkoma, ",", ".");
				$target_tahun_berjalan = number_format($target_tahun_berjalan, $belakangkoma, ",", ".");
			}
			if ((float) $row['terpilih'] == 0) {
				array_push($myArrayIKU, $row['kode_unique']);
				$html = '
				<table border="1"  cellpadding="2" style="width:100%;" >
				<tr>
				<td align="justify" width="520px">
				' . $row['textindikator'] . '
				</td>
				<td align="center" width="160px">
				' . $target . ' ' . $row['namasatuan'] . '
				</td>
				<td align="center" width="160px">
				' . $output_tahun_berjalan . ' ' . $row['namaoutput'] . '
				</td>
				<td align="center" width="160px">
				' . $target_tahun_berjalan . ' ' . $row['namaoutcome'] . '
				</td>
				</tr>
				</table>
				';
				$pdf->writeHTML($html, true, false, true, false, '');
				$currentY = $pdf->GetY();
				$pdf->SetY($currentY - 4.7);
				$currentY = $pdf->GetY();
				$myArray[] = (object)
				[
					'id' => (float) $row['id'],
					'tahun' => (float) $row['tahun'],
					'id_parent' => $row['id_parent'],
					'kdunor' => $row['kdunor'],
					'nama_unor' => $row['nama_unor'],
					'urutlevel' => (float) $row['urutlevel'],
					'level' => $row['level'],
					'kode' => $row['kode'],
					'kode_unique' => $row['kode_unique'],
					'textindikator' => $row['textindikator'],
					'satuan' => $row['satuan'],
					'namasatuan' => $row['namasatuan'],
					'output' => $row['output'],
					'namaoutput' => $row['namaoutput'],
					'outcome' => $row['outcome'],
					'namaoutcome' => $row['namaoutcome'],
					'penanggungjawab' => $row['penanggungjawab'],
					'pelaksana' => $row['pelaksana'],
					'pelaksanacode' => $row['pelaksanacode'],
					'pelaksanalabel' => $row['pelaksanalabel'],
					'target' => $row['target'],
					'urut' => (float) $row['urut'],
					'hitungan_pk' => $row['hitungan_pk'],
					'namahitungan_pk' => $row['namahitungan_pk'],
					'targetkumulatif' => $target,
					'output_tahun_berjalan' => $output_tahun_berjalan,
					'target_tahun_berjalan' => $target_tahun_berjalan,
					'class' => $row['class'],
				];
			}
		}
	}
}

$currentY = $pdf->GetY();
$pdf->SetY($currentY + 5);
$currentY = $pdf->GetY();
$html = '<tr>
<td align="left" colspan="3" width="820px">
<b>PROGRAM / KEGIATAN</b>
</td>
<td align="center" width="170px">
<b>ANGGARAN</b>
</td>
</tr>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->SetFont('dejavusans', '', 9);
$kode_satkeremon = "";
$kode_satkeremon2 = "";
$sqlsatkeremon = "select kode_satker_old_pendek,kode_satker_pendek 
from master_satker where kode_satker = '" . $kode_satker . "'";
//echo $sqlsatkeremon;
//die;
$resultsatkeremon = mysqli_query($link, $sqlsatkeremon);
while ($rowsatkeremon = mysqli_fetch_assoc($resultsatkeremon)) {
	$kode_satkeremon = $rowsatkeremon['kode_satker_old_pendek'];
	$kode_satkeremon2 = $rowsatkeremon['kode_satker_pendek'];
}
$sqlpagu = "select a.kdprogram,a.nmprogram 
,ifnull((select sum(b.pg) from paket_pk_awal b where b.kdprogram=a.kdprogram
and b.kdsatker in (" . $kode_satkeremon2 . "," . $kode_satkeremon . ")),0) as pagu
from tprogram a
where a.kdprogram in ('FC','WA')
order by a.kdprogram";

//echo $sqlpagu;

$resultpagu = mysqli_query($link, $sqlpagu);
$html = '';
$urut = 1;
$totalpagu = 0;
while ($rowpagu = mysqli_fetch_assoc($resultpagu)) {
	$totalpagu = $totalpagu + (float) $rowpagu['pagu'];
	$html = $html . '<tr>
	<td align="left" colspan="2" width="740px">
	<b>' . $urut . '.' . $rowpagu['nmprogram'] . '</b>
	</td>
	<td align="center" width="50px">
	<b>Rp.</b>
	</td>
	<td align="right" width="200px">
	<b>' . replace_dot_koma($rowpagu['pagu']) . '</b>
	</td>
	</tr>';
	$sqlpagukegiatan = "select sum(b.pg) as pagukegiatan 
	, (select x.nmgiat from tgiat x where x.kdgiat=b.kdgiat) as nmgiat
	, (select x.kdgiat from tgiat x where x.kdgiat=b.kdgiat) as kdgiat
	from paket_pk_awal b where b.kdprogram='" . $rowpagu['kdprogram'] . "'
	and b.kdsatker in (" . $kode_satkeremon2 . "," . $kode_satkeremon . ") group by b.kdgiat order by b.kdgiat";
	$resultpagukegiatan = mysqli_query($link, $sqlpagukegiatan);
	$urutkegiatan = 'a';
	while ($rowpagukegiatan = mysqli_fetch_assoc($resultpagukegiatan)) {
		$html = $html . '<tr>
		<td align="left" width="10px">
		</td>
		<td align="left" width="730px">
		' . $rowpagukegiatan['kdgiat'] . ' - ' . $rowpagukegiatan['nmgiat'] . '
		</td>
		<td align="center" width="50px">
		Rp.
		</td>
		<td align="right" width="200px">
		' . replace_dot_koma($rowpagukegiatan['pagukegiatan']) . '
		</td>
		</tr>';
		$urutkegiatan++;
	}
	$urut++;
}
$html = $html . '<tr>
<td align="right" colspan="3" width="740px">
<b>TOTAL</b>
</td>
<td align="center" width="50px">
<b>Rp.</b>
</td>
<td align="right" width="200px">
<b>' . replace_dot_koma($totalpagu) . '</b>
</td>
</tr>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->SetFont('dejavusans', '', 11);
$html = '
<tr>
<td align="center" width="300px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
' . ucfirst($lokasi_pejabat) . ', ' . $tanggal_pejabat . '
</td>
</tr>
<tr>
<td align="center" width="300px">
<b>' . $jabatan_pejabat_balai . '</b>
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
<b>' . $jabatan_pejabat . '</b>
</td>
</tr>
';
if ($ttdinput == '0') {
	$html = $html . '
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	<tr>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	<td align="center" width="250px">
	</td>
	</tr>
	';
} else {
	$html = $html . '
	<tr>
	<td align="center" width="300px">
	<img src="../../uploadedfile/filenya_ttd/' . $ttd_pejabat_balai . '" height="100">
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="175px">
	</td>
	<td align="center" width="300px">
	<img src="../../uploadedfile/filenya_ttd/' . $ttd_pejabat . '" height="100">
	</td>
	</tr>
	';
}
$html = $html . '
<tr>
<td align="center" width="300px">
<b>' . strtoupper($nama_pejabat_balai) . '</b>
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
<b>' . strtoupper($nama_pejabat) . '</b>
</td>
</tr>
';

$html = $html . '
</tbody>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');
$iku = 0;
$variku = "";
foreach ($myArrayIKU as $value) {
	// code to be executed
	if ($iku == 0) {
		$variku = $variku . "'" . $value . "'";
	} else {
		$variku = $variku . "," . "'" . $value . "'";
	}
	$iku++;
}

$pdf->SetFont('dejavusans', '', 9);
$pdf->AddPage('L', $resolution);
$html = '
<table border="0"  cellpadding="2" style="width:100%;">
<tr>
<td align="center" width="1000px">
</td>
</tr>
<tr>
<td align="center" width="1000px">
<b>MANUAL INDIKATOR KINERJA UTAMA</b>
</td>
</tr>
<tr>
<td align="center" width="1000px">	
<b>DIREKTORAT JENDERAL SUMBER DAYA AIR, KEMENTERIAN PEKERJAAN UMUM</b>
</td>
</tr>';
$pdf->SetFont('dejavusans', '', 9);
$html = $html . '
<tr>
<td align="center" width="250px">
</td>
<td align="center" width="250px">
</td>
<td align="center" width="250px">
</td>
<td align="right" width="250px">
<b>Lampiran 3</b>
</td>
</tr>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 5);
$pdf->SetFont('dejavusans', '', 8);
$html = '
<table border="1"  cellpadding="2" style="width:100%;" >
';
$html = $html . '
<tr>
<td align="center" width="100px" style="background-color: #D3D3D3;">
<b>NO</b>
</td>
<td align="center" width="200px" style="background-color: #D3D3D3;">
<b>SASARAN PROGRAM</b>
</td>
<td align="center" width="250px" style="background-color: #D3D3D3;">
<b>INDIKATOR KINERJA</b>
</td>
<td align="center" width="450px" style="background-color: #D3D3D3;">
<b>METODE PERHITUNGAN INDIKATOR KINERJA</b>
</td>
</tr>
';
$html = $html . '
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');
$arrayiku = array();
$iku = 0;
$sqliku = "select * from tb_iku where deleted='0' and REPLACE(KODE, ' ', '') in (" . $variku . ")
order by REPLACE(KODE, ' ', '')";
$resultiku = mysqli_query($link, $sqliku);
while ($rowiku = mysqli_fetch_assoc($resultiku)) {
	$tulissasaran = $rowiku['SASARANPROGRAM'];
	array_push($arrayiku, $rowiku['SASARANPROGRAM']);
	$currentY = $pdf->GetY();
	$pdf->SetY($currentY - 4.7);
	$currentY = $pdf->GetY();
	if ($currentY > 115) {
		$pdf->AddPage('L', $resolution);
		$pdf->SetFont('dejavusans', '', 9);
		$html = '
		<table border="0"  cellpadding="2" style="width:100%;">
		<tr>
		<td align="center" width="1000px">
		</td>
		</tr>
		<tr>
		<td align="center" width="1000px">
		<b>MANUAL INDIKATOR KINERJA UTAMA</b>
		</td>
		</tr>
		<tr>
		<td align="center" width="1000px">	
		<b>DIREKTORAT JENDERAL SUMBER DAYA AIR, KEMENTERIAN PEKERJAAN UMUM</b>
		</td>
		</tr>';
		$pdf->SetFont('dejavusans', '', 9);
		$html = $html . '
		<tr>
		<td align="center" width="250px">
		</td>
		<td align="center" width="250px">
		</td>
		<td align="center" width="250px">
		</td>
		<td align="right" width="250px">
		<b>Lampiran 3</b>
		</td>
		</tr>
		</table>
		';
		$pdf->writeHTML($html, true, false, true, false, '');
		$currentY = $pdf->GetY();
		$pdf->SetY($currentY - 5);
		$pdf->SetFont('dejavusans', '', 8);

		$html = '
		<table border="1"  cellpadding="2" style="width:100%;" >
		';
		$html = $html . '
		<tr>
		<td align="center" width="100px" style="background-color: #D3D3D3;">
		<b>NO</b>
		</td>
		<td align="center" width="200px" style="background-color: #D3D3D3;">
		<b>SASARAN PROGRAM</b>
		</td>
		<td align="center" width="250px" style="background-color: #D3D3D3;">
		<b>INDIKATOR KINERJA</b>
		</td>
		<td align="center" width="450px" style="background-color: #D3D3D3;">
		<b>METODE PERHITUNGAN INDIKATOR KINERJA</b>
		</td>
		</tr>
		';
		$html = $html . '
		</table>
		';
		$pdf->writeHTML($html, true, false, true, false, '');
		$currentY = $pdf->GetY();
		$pdf->SetY($currentY - 5);
	}
	$html = '
	<table border="1"  cellpadding="2" style="width:100%;" >
	';
	$html = $html . '
	<tr>
	<td align="center" width="100px">
	' . $rowiku['KODE'] . '
	</td>
	<td align="justify" width="200px">
	' . $tulissasaran . '
	</td>
	<td align="justify" width="250px">
	' . $rowiku['INDIKATORKINERJA'] . '
	</td>
	<td align="justify" width="450px">
	' . $rowiku['METODE'] . '
	</td>
	</tr>
';
	$html = $html . '
	</table>
	';
	$pdf->writeHTML($html, true, false, true, false, '');
	$iku++;
}

$pdf->SetFont('dejavusans', '', 11);
$html = '
<table border="0"  cellpadding="2" style="width:100%;">
<tbody>
<tr>
<td align="center" width="300px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
' . ucfirst($lokasi_pejabat) . ', ' . $tanggal_pejabat . '
</td>
</tr>
<tr>
<td align="center" width="300px">
<b>' . $jabatan_pejabat_balai . '</b>
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
<b>' . $jabatan_pejabat . '</b>
</td>
</tr>
<tr>
<td align="center" width="300px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
</td>
</tr>
<tr>
<td align="center" width="300px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
</td>
</tr>
<tr>
<td align="center" width="300px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
</td>
</tr>
<tr>
<td align="center" width="300px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
</td>
</tr>
<tr>
<td align="center" width="300px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
</td>
</tr>
<tr>
<tr>
<td align="center" width="300px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
</td>
</tr>
<tr>
<td align="center" width="300px">
<b>' . strtoupper($nama_pejabat_balai) . '</b>
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
<b>' . strtoupper($nama_pejabat) . '</b>
</td>
</tr>
';

$html = $html . '
</tbody>
</table>
';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->lastPage();

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output('Cetak_Perjanjian_Kinerja Satker.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+

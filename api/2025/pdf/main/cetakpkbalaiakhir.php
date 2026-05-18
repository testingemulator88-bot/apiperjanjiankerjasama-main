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
$kodebalai = fixup(base64_decode($_GET['kodebalai']));
$tempkodebalai = explode("_", $kodebalai);
$kodebalai = $tempkodebalai[0];
$tahun = fixup(base64_decode($_GET['tahun']));


$tahunsekarang = get_Isi_Field1('tahun','master_pk','id','3');
$filterquery = "";
$filterqueryiksk = "";
if ($tahun <> '') {
	$filterquery = $filterquery . " and a.tahun = '" . $tahun . "'";
}

$sqlkodebalai = "select GROUP_CONCAT(kdbalai,',',kdbalai_old) as  kdbalai
from master_kategori_satker where id = '" . $kodebalai . "' and deleted='0'";
//echo $sqlsatkeremon;
//die;
$resultkodebalai = mysqli_query($link, $sqlkodebalai);
while ($rowresultkodebalai = mysqli_fetch_assoc($resultkodebalai)) {
	$kodebalaipendek = $rowresultkodebalai['kdbalai'];
}

$sqlsatker = "select GROUP_CONCAT(kode_satker,',',kode_satker_old) as  kode_satker
, GROUP_CONCAT(kode_satker_pendek,',',kode_satker_old_pendek) as  kode_satker_pendek
from master_satker where kdbalai = '" . $kodebalai . "' and deleted='0'";
//echo $sqlsatker;
//die;
$resultsqlsatker = mysqli_query($link, $sqlsatker);
while ($rowsqlsatker = mysqli_fetch_assoc($resultsqlsatker)) {
	$groupkode_satker = $rowsqlsatker['kode_satker'];
	$groupkode_satker_pendek = $rowsqlsatker['kode_satker_pendek'];
}


$tempgroupkode_satker = explode(',', $groupkode_satker);
$countgroupkode_satker = count($tempgroupkode_satker);

//echo $countgroupkode_satker;
//die;

if ($kodebalai <> '') {
	$filterquery = $filterquery . " and a.pelaksana in (select level_piu from master_satker where kdbalai = '" . $kodebalai . "')";
	$filterqueryiksk = $filterqueryiksk . " and (a.cetak like CONCAT('%,',(select level_piu from master_kategori_satker where id = '" . $kodebalai . "'))
	or a.cetak like CONCAT((select level_piu from master_kategori_satker where id = '" . $kodebalai . "'),',%')
	or a.cetak like CONCAT('%,',(select level_piu from master_kategori_satker where id = '" . $kodebalai . "'),',%')
	or a.cetak like (select level_piu from master_kategori_satker where id = '" . $kodebalai . "'))";
} else {
	$filterquery = $filterquery . " and a.pelaksana in ('---')";
}


$sqlsatkeremonbalai = "select nama_kategori 
from master_kategori_satker where id = '" . $kodebalai . "'";
//echo $sqlsatkeremon;
//die;
$resultsatkeremonbalai = mysqli_query($link, $sqlsatkeremonbalai);
while ($rowsatkeremonbalai = mysqli_fetch_assoc($resultsatkeremonbalai)) {
	$nama_balai = $rowsatkeremonbalai['nama_kategori'];
}

$sqlsatkerttd = "select nama_pejabat, jabatan_pejabat, filenya_ttd, lokasi_pejabat, tanggal_pejabat
from tb_data_ttd_akhir where kode_satker_pejabat = '" . $kodebalai . "' and level_pejabat='Balai'";
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
from tb_data_ttd_akhir where level_pejabat='Dirjen'";
//echo $sqlbalaittd;
//die;
$resultbalaittd = mysqli_query($link, $sqlbalaittd);
while ($rowbalaittd = mysqli_fetch_assoc($resultbalaittd)) {
	$nama_pejabat_balai = $rowbalaittd['nama_pejabat'];
	$jabatan_pejabat_balai = $rowbalaittd['jabatan_pejabat'];
	$ttd_pejabat_balai = $rowbalaittd['filenya_ttd'];
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

	//Page header
	public $myCustomHeadertahun;
	public $myCustomHeadertahunlength;
	public $myCustomHeadernamabalai;
	public $myCustomHeadernamabalailength;

	public function Header() {}

	// Page footer
	public function Footer()
	{
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

$pk_aktif = '3';
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
where a.kode_pelaksana in (" . $kodebalaipendek . ") and a.deleted='0' and a.id_pelaksana in (32,45)
and a.jenis_pk = '" . $pk_aktif . "'
and a.id_pelaksana in (45,32,46) order by a.id DESC limit 1";
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


// create new PDF document  
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->myCustomHeaderversi = $versinya;
$pdf->myCustomHeadertahun = $tahunsekarang;
$pdf->myCustomHeadertahunlength = strlen($tahunsekarang);
$pdf->myCustomHeadernamabalai = $nama_balai;
$pdf->myCustomHeadernamabalailength = strlen($nama_balai);
// set document information

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
$pdf->SetMargins('25', '15', '25');
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, '25');

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
	require_once(dirname(__FILE__) . '/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set font
$pdf->SetFont('helvetica', '', 11);

// add a page
$resolution = array(215, 330);
$pdf->AddPage('L', $resolution);
// set style for barcode


$style1 = array('width' => 0.8, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
$style2 = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));


$currentY = $pdf->GetY();
$pdf->SetY($currentY - 15);
$pdf->SetFont('helvetica', '', 11);
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
<b>' . $nama_balai . '</b>
</td>
</tr>
<tr>
<td align="center" width="1000px">	
<b>DIREKTORAT JENDERAL SUMBER DAYA AIR</b>
</td>
</tr>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');
$html = '
<table border="0"  cellpadding="2" style="width:100%;" >
<thead>
';
$pdf->SetFont('helvetica', '', 11);

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
//echo "aaaa";
$pdf->writeHTML($html, true, false, true, false, '');
// reset pointer to the last page
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage('L', $resolution);
$currentY = $pdf->GetY();
$html = '
<table border="0"  cellpadding="2" style="width:100%;" >
<thead>
';
$html = $html . '	
</thead>  
<tbody>
<tr>
<td align="center" width="1000px">	
<b>PERJANJIAN KINERJA TAHUN ' . $tahunsekarang . '</b>
</td>
</tr>
<tr>
<td align="center" width="1000px">	
<b>' . $nama_balai . '</b>
</td>
</tr>
<tr>
<td align="center" width="1000px">	
<b>DIREKTORAT JENDERAL SUMBER DAYA AIR</b>
</td>
</tr>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 5);
$pdf->SetFont('dejavusans', '', 9);
$html = '

<table border="0"  cellpadding="2" style="width:100%;" >
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
$pdf->SetFont('helvetica', '', 9);
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
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');

$currentY = $pdf->GetY();
$pdf->SetY($currentY - 5);
$myArrayIKU = array();
$myArray = array();
$myArrayLAMPIRAN2 = array();
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
, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
and zz.kode_satker='" . $kodebalai . "') as terpilih
,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
IF(a.level = 'ISP', 'levelsubsubkegiatan', 
IF(a.level = 'KEGIATAN', 'levelpaket', 
IF(a.level = 'SK', 'levelpekerjaan', 
IF(a.level = 'IKSK', 'levelakhir', 
IF(a.level = 'KRO', 'levelakhir', 
IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
from tb_indikator_akhir a 
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
			$baseline = 0;
			$target = 0;
			$target_komponen = 0;
			$target_tahun_berjalan = 0;
			$target_komponen_tahun_berjalan = 0;
			$output_tahun_berjalan = 0;
			$output_komponen_tahun_berjalan = 0;

			//ISPSK
			$sqllevelISPSK = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
			,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
			,IF(a.satuan = '0', null, a.satuan) as satuan
			,IF(a.output = '0', null, a.output) as output
			,IF(a.outcome = '0', null, a.outcome) as outcome
			, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
			, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
			, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
			, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
			,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
			, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
			,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
			and zz.kode_satker='" . $kodebalai . "') as terpilih
			,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
			IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
			IF(a.level = 'ISP', 'levelsubsubkegiatan', 
			IF(a.level = 'KEGIATAN', 'levelpaket', 
			IF(a.level = 'SK', 'levelpekerjaan', 
			IF(a.level = 'IKSK', 'levelakhir', 
			IF(a.level = 'KRO', 'levelakhir', 
			IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
			from tb_indikator_akhir a 
			left join master_unor b
			on a.kdunor = b.id
			where a.deleted='0' and a.id = '" . $row['id_parent'] . "'
			order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
			//echo $sqllevelISPSK;
			//die;
			$resultlevelISPSK = mysqli_query($link, $sqllevelISPSK);
			while ($rowlevelISPSK = mysqli_fetch_assoc($resultlevelISPSK)) {
				// KEGIATAN
				$sqllevelKEGIATAN = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
				,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
				,IF(a.satuan = '0', null, a.satuan) as satuan
				,IF(a.output = '0', null, a.output) as output
				,IF(a.outcome = '0', null, a.outcome) as outcome
				, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
				, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
				, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
				, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
				,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
				, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
				,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
				and zz.kode_satker='" . $kodebalai . "') as terpilih
				,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
				IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
				IF(a.level = 'ISP', 'levelsubsubkegiatan', 
				IF(a.level = 'KEGIATAN', 'levelpaket', 
				IF(a.level = 'SK', 'levelpekerjaan', 
				IF(a.level = 'IKSK', 'levelakhir', 
				IF(a.level = 'KRO', 'levelakhir', 
				IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
				from tb_indikator_akhir a 
				left join master_unor b
				on a.kdunor = b.id
				where a.deleted='0' and a.id = '" . $rowlevelISPSK['id_parent'] . "'
				order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
				//echo $sqllevelKEGIATAN;
				//die;
				$resultlevelKEGIATAN = mysqli_query($link, $sqllevelKEGIATAN);
				while ($rowlevelKEGIATAN = mysqli_fetch_assoc($resultlevelKEGIATAN)) {
					// TAMBAHAN
					$sqllevelINDIKATORSASARANPROGRAM = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
					,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
					,IF(a.satuan = '0', null, a.satuan) as satuan
					,IF(a.output = '0', null, a.output) as output
					,IF(a.outcome = '0', null, a.outcome) as outcome
					, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
					, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
					, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
					, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
					,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
					, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
					,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
					and zz.kode_satker='" . $kodebalai . "') as terpilih
					,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
					IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
					IF(a.level = 'ISP', 'levelsubsubkegiatan', 
					IF(a.level = 'KEGIATAN', 'levelpaket', 
					IF(a.level = 'SK', 'levelpekerjaan', 
					IF(a.level = 'IKSK', 'levelakhir', 
					IF(a.level = 'KRO', 'levelakhir', 
					IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
					from tb_indikator_akhir a 
					left join master_unor b
					on a.kdunor = b.id
					where a.deleted='0' and a.id = '" . $rowlevelKEGIATAN['id_parent'] . "'
					and a.level = 'PROGRAM'
					order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
					// TAMBAHAN
					//echo $sqllevelINDIKATORSASARANPROGRAM;
					//die;
					$resultlevelINDIKATORSASARANPROGRAM = mysqli_query($link, $sqllevelINDIKATORSASARANPROGRAM);
					while ($rowlevelINDIKATORSASARANPROGRAM = mysqli_fetch_assoc($resultlevelINDIKATORSASARANPROGRAM)) {
						// PROGRAM
						$searchValue = (float) $rowlevelINDIKATORSASARANPROGRAM['id'];
						$found = false;
						foreach ($myArray as $obj) {
							if (isset($obj->id) && $obj->id === $searchValue) {
								$found = true;
								break;
							}
						}
						if (!$found) {
							$currentY = $pdf->GetY();
							if ($rowlevelINDIKATORSASARANPROGRAM['textindikator'] == 'DUKUNGAN MANAJEMEN' && (float) $currentY > 90) {
								$pdf->AddPage('L', $resolution);
								$pdf->SetFont('dejavusans', '', 11);

								$html = '
								<table border="0"  cellpadding="2" style="width:100%;" >
								<thead>
								';
								$html = $html . '	
								</thead>  
								<tbody>
								<tr>
								<td align="center" width="1000px">	
								<b>PERJANJIAN KINERJA TAHUN ' . $tahunsekarang . '</b>
								</td>
								</tr>
								<tr>
								<td align="center" width="1000px">	
								<b>' . $nama_balai . '</b>
								</td>
								</tr>
								<tr>
								<td align="center" width="1000px">	
								<b>DIREKTORAT JENDERAL SUMBER DAYA AIR</b>
								</td>
								</tr>
								</table>
								';
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 5);
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 5);
								$pdf->SetFont('dejavusans', '', 9);
								$html = '

								<table border="0"  cellpadding="2" style="width:100%;" >
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
								<b>PROGRAM: ' . $rowlevelINDIKATORSASARANPROGRAM['textindikator'] . '</b>
								</td>
								</tr>
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 4.7);
							} else {
								$html = '
								<table border="1"  cellpadding="2" style="width:100%;" >
								<tr>
								<td align="justify" width="1000px">
								<b>PROGRAM: ' . $rowlevelINDIKATORSASARANPROGRAM['textindikator'] . '</b>
								</td>
								</tr>
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 5);
							}
						}
						$myArray[] = (object)
						[
							'id' => (float) $rowlevelINDIKATORSASARANPROGRAM['id'],
							'tahun' => (float) $rowlevelINDIKATORSASARANPROGRAM['tahun'],
							'id_parent' => $rowlevelINDIKATORSASARANPROGRAM['id_parent'],
							'kdunor' => $rowlevelINDIKATORSASARANPROGRAM['kdunor'],
							'nama_unor' => $rowlevelINDIKATORSASARANPROGRAM['nama_unor'],
							'urutlevel' => (float) $rowlevelINDIKATORSASARANPROGRAM['urutlevel'],
							'level' => $rowlevelINDIKATORSASARANPROGRAM['level'],
							'kode' => $rowlevelINDIKATORSASARANPROGRAM['kode'],
							'kode_unique' => $rowlevelINDIKATORSASARANPROGRAM['kode_unique'],
							'textindikator' => $rowlevelINDIKATORSASARANPROGRAM['textindikator'],
							'satuan' => $rowlevelINDIKATORSASARANPROGRAM['satuan'],
							'namasatuan' => $rowlevelINDIKATORSASARANPROGRAM['namasatuan'],
							'output' => $rowlevelINDIKATORSASARANPROGRAM['output'],
							'namaoutput' => $rowlevelINDIKATORSASARANPROGRAM['namaoutput'],
							'outcome' => $rowlevelINDIKATORSASARANPROGRAM['outcome'],
							'namaoutcome' => $rowlevelINDIKATORSASARANPROGRAM['namaoutcome'],
							'penanggungjawab' => $rowlevelINDIKATORSASARANPROGRAM['penanggungjawab'],
							'pelaksana' => $rowlevelINDIKATORSASARANPROGRAM['pelaksana'],
							'pelaksanacode' => $rowlevelINDIKATORSASARANPROGRAM['pelaksanacode'],
							'pelaksanalabel' => $rowlevelINDIKATORSASARANPROGRAM['pelaksanalabel'],
							'target' => $rowlevelINDIKATORSASARANPROGRAM['target'],
							'urut' => (float) $rowlevelINDIKATORSASARANPROGRAM['urut'],
							'hitungan_pk' => $rowlevelINDIKATORSASARANPROGRAM['hitungan_pk'],
							'namahitungan_pk' => $rowlevelINDIKATORSASARANPROGRAM['namahitungan_pk'],
							'targetkumulatif' => 0,
							'output_tahun_berjalan' => 0,
							'target_tahun_berjalan' => 0,
							'class' => $rowlevelINDIKATORSASARANPROGRAM['class'],
						];
						$sqllevelSASARANPROGRAM = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
						,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
						,IF(a.satuan = '0', null, a.satuan) as satuan
						,IF(a.output = '0', null, a.output) as output
						,IF(a.outcome = '0', null, a.outcome) as outcome
						, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
						, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
						, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
						, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
						,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
						, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
						,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
						and zz.kode_satker='" . $kodebalai . "') as terpilih
						,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
						IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
						IF(a.level = 'ISP', 'levelsubsubkegiatan', 
						IF(a.level = 'KEGIATAN', 'levelpaket', 
						IF(a.level = 'SK', 'levelpekerjaan', 
						IF(a.level = 'IKSK', 'levelakhir', 
						IF(a.level = 'KRO', 'levelakhir', 
						IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
						from tb_indikator_akhir a 
						left join master_unor b
						on a.kdunor = b.id
						where a.deleted='0' and a.id_parent = '" . $rowlevelINDIKATORSASARANPROGRAM['id'] . "'
						and a.level = 'SP'
						order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
						//echo $sqllevelSASARANPROGRAM;
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
								if ((float) $rowlevelSASARANPROGRAM['terpilih'] == 0) {
									array_push($myArrayIKU, $rowlevelSASARANPROGRAM['kode_unique']);
									$html = '
									<table border="1"  cellpadding="2" style="width:100%;" >
									<tr>
									<td align="justify" width="1000px">
									<b>SASARAN PROGRAM: ' . $rowlevelSASARANPROGRAM['textindikator'] . '</b>
									</td>
									</tr>
									</table>
									';
									$pdf->writeHTML($html, true, false, true, false, '');
									$currentY = $pdf->GetY();
									$pdf->SetY($currentY - 4.85);
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
							$sqllevelPROGRAM = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
							,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
							,IF(a.satuan = '0', null, a.satuan) as satuan
							,IF(a.output = '0', null, a.output) as output
							,IF(a.outcome = '0', null, a.outcome) as outcome
							, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
							, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
							, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
							, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
							,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk, a.belakangkoma
							,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
							and zz.kode_satker='" . $kodebalai . "') as terpilih
        					,a.kolom1,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.rumuskolom1,a.rumuskolom2,a.rumuskolom3,a.rumuskolom4,a.rumuskolom5,a.isian_kolom
							,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
							IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
							IF(a.level = 'ISP', 'levelsubsubkegiatan', 
							IF(a.level = 'KEGIATAN', 'levelpaket', 
							IF(a.level = 'SK', 'levelpekerjaan', 
							IF(a.level = 'IKSK', 'levelakhir', 
							IF(a.level = 'KRO', 'levelakhir', 
							IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
							from tb_indikator_akhir a 
							left join master_unor b
							on a.kdunor = b.id
							where a.deleted='0' and a.id_parent = '" . $rowlevelSASARANPROGRAM['id'] . "'
							" . $filterqueryiksk . "
							order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";

							//echo $sqllevelPROGRAM;
							//die;
							$resultlevelPROGRAM = mysqli_query($link, $sqllevelPROGRAM);
							while ($rowlevelPROGRAM = mysqli_fetch_assoc($resultlevelPROGRAM)) {
								$searchValue = (float) $rowlevelPROGRAM['id'];
								$found = false;
								foreach ($myArray as $obj) {
									if (isset($obj->id) && $obj->id === $searchValue) {
										$found = true;
										break;
									}
								}
								if (!$found) {
									$belakangkoma = (float) $rowlevelPROGRAM['belakangkoma'];
									$rumuskolom1 = $rowlevelPROGRAM['rumuskolom1'];
									$rumuskolom2 = $rowlevelPROGRAM['rumuskolom2'];
									$rumuskolom3 = $rowlevelPROGRAM['rumuskolom3'];
									$rumuskolom4 = $rowlevelPROGRAM['rumuskolom4'];
									$rumuskolom5 = $rowlevelPROGRAM['rumuskolom5'];
									$targetkumulatif = 0;
									if ($rowlevelPROGRAM['hitungan_pk'] == '1') {
										$baselinekolom1 = 0;
										$baselinekolom2 = 0;
										$baselinekolom3 = 0;
										$baselinekolom4 = 0;
										$baselinekolom5 = 0;
										$sqlbaseline = " select a.id,a.kode_tahun,a.id_indikator,a.id_balai,a.kode_balai,a.kolom1 
										,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.isian_kolom, b.nama as tahun
										from tb_data_baseline_pk_balai_akhir a 
										left join master_tahun b
										on a.kode_tahun = b.id
										where a.deleted='0' 
										and a.kode_tahun='" . $tahun . "'
										and a.id_indikator = '" . $rowlevelPROGRAM['id'] . "'
										and a.id_balai = '" . $kodebalai . "'
										order by a.id";
										//echo $sqlbaseline;
										//die;
										$resultbaseline = mysqli_query($link, $sqlbaseline);
										$numbaseline = mysqli_num_rows($resultbaseline);
										if ($numbaseline > 0) {
											while ($rowbaseline = mysqli_fetch_assoc($resultbaseline)) {
												$baselinekolom1 = (float) $rowbaseline['kolom1'];
												$baselinekolom2 = (float) $rowbaseline['kolom2'];
												$baselinekolom3 = (float) $rowbaseline['kolom3'];
												$baselinekolom4 = (float) $rowbaseline['kolom4'];
												$baselinekolom5 = (float) $rowbaseline['kolom5'];
											}
										}
										$sqltahun = "select a.tahun_awal,a.tahun_akhir from master_tahun a where a.deleted='0' and a.id = '" . $tahun . "'";
										//echo $sqltahun;
										//die;
										$resulttahun = mysqli_query($link, $sqltahun);
										while ($rowtahun = mysqli_fetch_assoc($resulttahun)) {
											$tahun_awal = (float) $rowtahun['tahun_awal'];
											$tahun_akhir = (float) $rowtahun['tahun_akhir'];
										}
										$arraytahun = array();
										$arraykolom1 = array();
										$arraykolom2 = array();
										$arraykolom3 = array();
										$arraykolom4 = array();
										$arraykolom5 = array();
										$arrayminuskolom1 = array();
										$arrayminuskolom2 = array();
										$arrayminuskolom3 = array();
										$arrayminuskolom4 = array();
										$arrayminuskolom5 = array();

										$arrayisiminuskolom1 = array();
										$arrayisiminuskolom2 = array();
										$arrayisiminuskolom3 = array();
										$arrayisiminuskolom4 = array();
										$arrayisiminuskolom5 = array();
										$s = 0;
										$totalkolom1 = 0;
										$totalkolom2 = 0;
										$totalkolom3 = 0;
										$totalkolom4 = 0;
										$totalkolom5 = 0;
										for ($x = $tahun_awal; $x <= $tahunsekarang; $x++) {
											array_push($arraytahun, $x);
											$myArraydetail = array();
											$sqldata = "select a.id,a.id_balai,a.kode_balai,a.kolom1,a.kolom2,a.kolom3
											,a.kolom4,a.kolom5,a.isian_kolom
											from tb_data_pk_balai_akhir a where a.deleted='0' and a.tahun='" . $x . "' 
											and a.id_balai = '" . $kodebalai . "' and a.id_indikator = '" . $rowlevelPROGRAM['id'] . "'";
											//echo $sqldata;
											//echo $rowlevelPROGRAM['hitungan_pk'];
											//die;
											$resultdata = mysqli_query($link, $sqldata);
											$numdata = mysqli_num_rows($resultdata);
											if ($numdata > 0) {
												if ($resultdata) {
													while ($rowdata = mysqli_fetch_assoc($resultdata)) {
														if ($rowlevelPROGRAM['hitungan_pk'] == '1') {
															//echo $sqldata;
															//die;
															$isikolom1 = 0;
															$isikolom2 = 0;
															$isikolom3 = 0;
															$isikolom4 = 0;
															$isikolom5 = 0;
															#kolom1
															if (strstr($rumuskolom1, "IKSK")) {
																$temprumuskolom1 = explode("+", $rumuskolom1);
																for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom1); $jumlahtambah++) {
																	$sqlcekhitungan = "select id,hitungan_pk from tb_indikator_akhir where deleted = '0'
                                                    				and kode_unique = '" . $temprumuskolom1[$jumlahtambah] . "'";
																	//echo $sqlcekhitungan;
																	//die;
																	$resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
																	while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
																		$hitungan_pkdetail = $rowcekhitungan['hitungan_pk'];
																	}
																	if ($hitungan_pkdetail == '1') {
																		$sqlskortahunsekarang = "select sum(target) as target
																		from tb_data_pk_akhir
																		where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
																		and kdbalai = '" . $kodebalai . "') 
																		and kode_tahun='" . $tahun . "'
																		and id_indikator = (select id from tb_indikator_akhir where deleted='0' and kode_unique = '" . $temprumuskolom1[$jumlahtambah] . "')
																		and tahun = '" . $x . "'";
																		//echo $sqlskortahunsekarang;
																		//die;
																		$resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
																		$numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
																		if ($numskortahunsekarang > 0) {
																			while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
																				$target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
																			}
																		} else {
																			$target_tahun_berjalan = 0;
																		}
																	}
																	$isikolom1 = $isikolom1 + $target_tahun_berjalan;
																	//echo $isikolom1;
																	//echo $rumuskolom1;
																}
															} else if (strstr($rumuskolom1, "ambildatasebelum")) {
																$ambildatasebelum = 0;
																if ($x == $tahun_awal) {
																	array_push($arraykolom1, (float) $baselinekolom1);
																	$ambildatasebelum = ((float) $baselinekolom1);
																} else {
																	array_push($arraykolom1, (float) $rowdata['kolom1'] + (float) $arraykolom1[$s - 1]);
																	$ambildatasebelum = ((float) $rowdata['kolom1'] + (float) $arraykolom1[$s - 1]);
																}
																$cekrumuskolom1 = str_replace("ambildatasebelumbaseline1", $ambildatasebelum, $rumuskolom1);
																//echo $cekrumuskolom1."<br>";
																$tempformula_string5 = explode("/", $cekrumuskolom1);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom1 = str_replace("0/0", "0", $cekrumuskolom1);
																		$cekrumuskolom1 = str_replace("0/(0+0)", "0", $cekrumuskolom1);
																		$cekrumuskolom1 = str_replace("/0", "*0", $cekrumuskolom1);
																	}
																}
																try {
																	eval('$isikolom1 = ' . $cekrumuskolom1 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom1 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom1 = 0;');
																}
															} else if (strstr($rumuskolom1, "given")) {
																$cekrumuskolom1 = $rumuskolom1;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom1 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom1);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom1);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom1 = str_replace("0/0", "0", $cekrumuskolom1);
																		$cekrumuskolom1 = str_replace("0/(0+0)", "0", $cekrumuskolom1);
																		$cekrumuskolom1 = str_replace("/0", "*0", $cekrumuskolom1);
																	}
																}
																try {
																	eval('$isikolom1 = ' . $cekrumuskolom1 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom1 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom1 = 0;');
																}
															} else if (strstr($rumuskolom1, "isikolom")) {
																$cekrumuskolom1 = $rumuskolom1;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom1 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom1);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom1);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom1 = str_replace("0/0", "0", $cekrumuskolom1);
																		$cekrumuskolom1 = str_replace("0/(0+0)", "0", $cekrumuskolom1);
																		$cekrumuskolom1 = str_replace("/0", "*0", $cekrumuskolom1);
																	}
																}
																try {
																	eval('$isikolom1 = ' . $cekrumuskolom1 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom1 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom1 = 0;');
																}
																//echo $isikolom1."<br>";
															} else if (strstr($rumuskolom1, "totalkolom")) {
																$cekrumuskolom1 = $rumuskolom1;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom1 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom1);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom1);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom1 = str_replace("0/0", "0", $cekrumuskolom1);
																		$cekrumuskolom1 = str_replace("0/(0+0)", "0", $cekrumuskolom1);
																		$cekrumuskolom1 = str_replace("/0", "*0", $cekrumuskolom1);
																	}
																}
																try {
																	eval('$isikolom1 = ' . $cekrumuskolom1 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom1 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom1 = 0;');
																}
																//echo $isikolom1."<br>";
															} else {
																$isikolom1 = (float) $rowdata['kolom1'];
															}
															$totalkolom1 = $totalkolom1 + (float) $isikolom1;
															array_push($arrayminuskolom1, (float) $isikolom1);
															#kolom1

															#kolom2
															if (strstr($rumuskolom2, "IKSK")) {
																$temprumuskolom2 = explode("+", $rumuskolom2);
																for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom2); $jumlahtambah++) {
																	$sqlcekhitungan = "select id,hitungan_pk from tb_indikator_akhir where deleted = '0'
                                                    				and kode_unique = '" . $temprumuskolom2[$jumlahtambah] . "'";
																	$resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
																	while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
																		$hitungan_pkdetail = $rowcekhitungan['hitungan_pk'];
																	}
																	if ($hitungan_pkdetail == '1') {
																		$sqlskortahunsekarang = "select sum(target) as target
																		from tb_data_pk_akhir
																		where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
																		and kdbalai = '" . $kodebalai . "') 
																		and kode_tahun='" . $tahun . "'
																		and id_indikator = (select id from tb_indikator_akhir where deleted='0' and kode_unique = '" . $temprumuskolom2[$jumlahtambah] . "')
																		and tahun = '" . $x . "'";
																		//echo $sqlskortahunsekarang;
																		$resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
																		$numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
																		if ($numskortahunsekarang > 0) {
																			while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
																				$target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
																			}
																		} else {
																			$target_tahun_berjalan = 0;
																		}
																	}
																	$isikolom2 = $isikolom2 + $target_tahun_berjalan;
																}
															} else if (strstr($rumuskolom2, "ambildatasebelum")) {
																$ambildatasebelum = 0;
																if ($x == $tahun_awal) {
																	array_push($arraykolom2, (float) $baselinekolom2);
																	$ambildatasebelum = ((float) $baselinekolom2);
																} else {
																	array_push($arraykolom2, (float) $rowdata['kolom2'] + (float) $arraykolom2[$s - 1]);
																	$ambildatasebelum = ((float) $rowdata['kolom2'] + (float) $arraykolom2[$s - 1]);
																}
																$cekrumuskolom2 = str_replace("ambildatasebelumbaseline2", $ambildatasebelum, $rumuskolom2);
																//echo $cekrumuskolom2."<br>";
																$tempformula_string5 = explode("/", $cekrumuskolom2);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom2 = str_replace("0/0", "0", $cekrumuskolom2);
																		$cekrumuskolom2 = str_replace("0/(0+0)", "0", $cekrumuskolom2);
																		$cekrumuskolom2 = str_replace("/0", "*0", $cekrumuskolom2);
																	}
																}
																try {
																	eval('$isikolom2 = ' . $cekrumuskolom2 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom2 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom2 = 0;');
																}
															} else if (strstr($rumuskolom2, "given")) {
																$cekrumuskolom2 = $rumuskolom2;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom2 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom2);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom2);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom2 = str_replace("0/0", "0", $cekrumuskolom2);
																		$cekrumuskolom2 = str_replace("0/(0+0)", "0", $cekrumuskolom2);
																		$cekrumuskolom2 = str_replace("/0", "*0", $cekrumuskolom2);
																	}
																}
																try {
																	eval('$isikolom2 = ' . $cekrumuskolom2 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom2 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom2 = 0;');
																}
															} else if (strstr($rumuskolom2, "isikolom")) {
																$cekrumuskolom2 = $rumuskolom2;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom2 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom2);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom2);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom2 = str_replace("0/0", "0", $cekrumuskolom2);
																		$cekrumuskolom2 = str_replace("0/(0+0)", "0", $cekrumuskolom2);
																		$cekrumuskolom2 = str_replace("/0", "*0", $cekrumuskolom2);
																	}
																}
																try {
																	eval('$isikolom2 = ' . $cekrumuskolom2 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom2 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom2 = 0;');
																}
																//echo $isikolom2."<br>";
															} else if (strstr($rumuskolom2, "totalkolom")) {
																$cekrumuskolom2 = $rumuskolom2;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom2 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom2);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom2);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom2 = str_replace("0/0", "0", $cekrumuskolom2);
																		$cekrumuskolom2 = str_replace("0/(0+0)", "0", $cekrumuskolom2);
																		$cekrumuskolom2 = str_replace("/0", "*0", $cekrumuskolom2);
																	}
																}
																try {
																	eval('$isikolom2 = ' . $cekrumuskolom2 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom2 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom2 = 0;');
																}
																//echo $isikolom2."<br>";
															} else {
																$isikolom2 = (float) $rowdata['kolom2'];
															}
															$totalkolom2 = $totalkolom2 + (float) $isikolom2;
															array_push($arrayminuskolom2, (float) $isikolom2);
															#kolom2

															#kolom3
															if (strstr($rumuskolom3, "IKSK")) {
																$temprumuskolom3 = explode("+", $rumuskolom3);
																for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom3); $jumlahtambah++) {
																	$sqlcekhitungan = "select id,hitungan_pk from tb_indikator_akhir where deleted = '0'
                                                    				and kode_unique = '" . $temprumuskolom3[$jumlahtambah] . "'";
																	$resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
																	while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
																		$hitungan_pkdetail = $rowcekhitungan['hitungan_pk'];
																	}
																	if ($hitungan_pkdetail == '1') {
																		$sqlskortahunsekarang = "select sum(target) as target
																		from tb_data_pk_akhir
																		where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
																		and kdbalai = '" . $kodebalai . "') 
																		and kode_tahun='" . $tahun . "'
																		and id_indikator = (select id from tb_indikator_akhir where deleted='0' and kode_unique = '" . $temprumuskolom3[$jumlahtambah] . "')
																		and tahun = '" . $x . "'";
																		//echo $sqlskortahunsekarang;
																		$resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
																		$numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
																		if ($numskortahunsekarang > 0) {
																			while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
																				$target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
																			}
																		} else {
																			$target_tahun_berjalan = 0;
																		}
																	}
																	$isikolom3 = $isikolom3 + $target_tahun_berjalan;
																}
															} else if (strstr($rumuskolom3, "ambildatasebelum")) {
																$ambildatasebelum = 0;
																if ($x == $tahun_awal) {
																	array_push($arraykolom3, (float) $baselinekolom3);
																	$ambildatasebelum = ((float) $baselinekolom3);
																} else {
																	array_push($arraykolom3, (float) $rowdata['kolom3'] + (float) $arraykolom3[$s - 1]);
																	$ambildatasebelum = ((float) $rowdata['kolom3'] + (float) $arraykolom3[$s - 1]);
																}
																$cekrumuskolom3 = str_replace("ambildatasebelumbaseline3", $ambildatasebelum, $rumuskolom3);
																//echo $cekrumuskolom3."<br>";
																$tempformula_string5 = explode("/", $cekrumuskolom3);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom3 = str_replace("0/0", "0", $cekrumuskolom3);
																		$cekrumuskolom3 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
																		$cekrumuskolom3 = str_replace("/0", "*0", $cekrumuskolom3);
																	}
																}
																try {
																	eval('$isikolom3 = ' . $cekrumuskolom3 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom3 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom3 = 0;');
																}
															} else if (strstr($rumuskolom3, "given")) {
																$cekrumuskolom3 = $rumuskolom3;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom3 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom3);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom3);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom3 = str_replace("0/0", "0", $cekrumuskolom3);
																		$cekrumuskolom3 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
																		$cekrumuskolom3 = str_replace("/0", "*0", $cekrumuskolom3);
																	}
																}
																try {
																	eval('$isikolom3 = ' . $cekrumuskolom3 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom3 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom3 = 0;');
																}
															} else if (strstr($rumuskolom3, "isikolom")) {
																$cekrumuskolom3 = $rumuskolom3;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom3 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom3);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom3);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom3 = str_replace("0/0", "0", $cekrumuskolom3);
																		$cekrumuskolom3 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
																		$cekrumuskolom3 = str_replace("/0", "*0", $cekrumuskolom3);
																	}
																}
																try {
																	eval('$isikolom3 = ' . $cekrumuskolom3 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom3 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom3 = 0;');
																}
																//echo $isikolom3."<br>";
															} else if (strstr($rumuskolom3, "totalkolom")) {
																$cekrumuskolom3 = $rumuskolom3;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom3 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom3);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom3);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom3 = str_replace("0/0", "0", $cekrumuskolom3);
																		$cekrumuskolom3 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
																		$cekrumuskolom3 = str_replace("/0", "*0", $cekrumuskolom3);
																	}
																}
																try {
																	eval('$isikolom3 = ' . $cekrumuskolom3 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom3 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom3 = 0;');
																}
																//echo $isikolom3."<br>";
															} else {
																$isikolom3 = (float) $rowdata['kolom3'];
															}
															$totalkolom3 = $totalkolom3 + (float) $isikolom3;
															array_push($arrayminuskolom3, (float) $isikolom3);
															#kolom3

															#kolom4
															if (strstr($rumuskolom4, "IKSK")) {
																$temprumuskolom4 = explode("+", $rumuskolom4);
																for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom4); $jumlahtambah++) {
																	$sqlcekhitungan = "select id,hitungan_pk from tb_indikator_akhir where deleted = '0'
                                                    				and kode_unique = '" . $temprumuskolom4[$jumlahtambah] . "'";
																	$resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
																	while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
																		$hitungan_pkdetail = $rowcekhitungan['hitungan_pk'];
																	}
																	if ($hitungan_pkdetail == '1') {
																		$sqlskortahunsekarang = "select sum(target) as target
																		from tb_data_pk_akhir
																		where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
																		and kdbalai = '" . $kodebalai . "') 
																		and kode_tahun='" . $tahun . "'
																		and id_indikator = (select id from tb_indikator_akhir where deleted='0' and kode_unique = '" . $temprumuskolom4[$jumlahtambah] . "')
																		and tahun = '" . $x . "'";
																		//echo $sqlskortahunsekarang;
																		$resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
																		$numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
																		if ($numskortahunsekarang > 0) {
																			while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
																				$target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
																			}
																		} else {
																			$target_tahun_berjalan = 0;
																		}
																	}
																	$isikolom4 = $isikolom4 + $target_tahun_berjalan;
																}
															} else if (strstr($rumuskolom4, "ambildatasebelum")) {
																$ambildatasebelum = 0;
																if ($x == $tahun_awal) {
																	array_push($arraykolom4, (float) $baselinekolom4);
																	$ambildatasebelum = ((float) $baselinekolom4);
																} else {
																	array_push($arraykolom4, (float) $rowdata['kolom4'] + (float) $arraykolom4[$s - 1]);
																	$ambildatasebelum = ((float) $rowdata['kolom4'] + (float) $arraykolom4[$s - 1]);
																}
																$cekrumuskolom4 = str_replace("ambildatasebelumbaseline4", $ambildatasebelum, $rumuskolom4);
																//echo $cekrumuskolom4."<br>";
																$tempformula_string5 = explode("/", $cekrumuskolom4);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom4 = str_replace("0/0", "0", $cekrumuskolom4);
																		$cekrumuskolom4 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
																		$cekrumuskolom4 = str_replace("/0", "*0", $cekrumuskolom4);
																	}
																}
																try {
																	eval('$isikolom4 = ' . $cekrumuskolom4 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom4 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom4 = 0;');
																}
															} else if (strstr($rumuskolom4, "given")) {
																$cekrumuskolom4 = $rumuskolom4;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom4 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom4);
																}
																try {
																	eval('$isikolom4 = ' . $cekrumuskolom4 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom4 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom4 = 0;');
																}
															} else if (strstr($rumuskolom4, "isikolom")) {
																$cekrumuskolom4 = $rumuskolom4;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom4 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom4);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom4);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom4 = str_replace("0/0", "0", $cekrumuskolom4);
																		$cekrumuskolom4 = str_replace("0/(0+0)", "0", $cekrumuskolom4);
																		$cekrumuskolom4 = str_replace("/0", "*0", $cekrumuskolom4);
																	}
																}
																try {
																	eval('$isikolom4 = ' . $cekrumuskolom4 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom4 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom4 = 0;');
																}
																//echo $isikolom4."<br>";
															} else if (strstr($rumuskolom4, "totalkolom")) {
																$cekrumuskolom4 = $rumuskolom4;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom4 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom4);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom4);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom4 = str_replace("0/0", "0", $cekrumuskolom4);
																		$cekrumuskolom4 = str_replace("0/(0+0)", "0", $cekrumuskolom4);
																		$cekrumuskolom4 = str_replace("/0", "*0", $cekrumuskolom4);
																	}
																}
																try {
																	eval('$isikolom4 = ' . $cekrumuskolom4 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom4 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom4 = 0;');
																}
																//echo $isikolom4."<br>";
															} else {
																$isikolom4 = (float) $rowdata['kolom4'];
															}
															$totalkolom4 = $totalkolom4 + (float) $isikolom4;
															array_push($arrayminuskolom4, (float) $isikolom4);
															#kolom4

															#kolom5
															if (strstr($rumuskolom5, "IKSK")) {
																$temprumuskolom5 = explode("+", $rumuskolom5);
																for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom5); $jumlahtambah++) {
																	$sqlcekhitungan = "select id,hitungan_pk from tb_indikator_akhir where deleted = '0'
                                                    				and kode_unique = '" . $temprumuskolom5[$jumlahtambah] . "'";
																	$resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
																	while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
																		$hitungan_pkdetail = $rowcekhitungan['hitungan_pk'];
																	}
																	if ($hitungan_pkdetail == '1') {
																		$sqlskortahunsekarang = "select sum(target) as target
																		from tb_data_pk_akhir
																		where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
																		and kdbalai = '" . $kodebalai . "') 
																		and kode_tahun='" . $tahun . "'
																		and id_indikator = (select id from tb_indikator_akhir where deleted='0' and kode_unique = '" . $temprumuskolom5[$jumlahtambah] . "')
																		and tahun = '" . $x . "'";
																		//echo $sqlskortahunsekarang;
																		$resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
																		$numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
																		if ($numskortahunsekarang > 0) {
																			while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
																				$target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
																			}
																		} else {
																			$target_tahun_berjalan = 0;
																		}
																	}
																	$isikolom5 = $isikolom5 + $target_tahun_berjalan;
																}
															} else if (strstr($rumuskolom5, "ambildatasebelum")) {
																$ambildatasebelum = 0;
																if ($x == $tahun_awal) {
																	array_push($arraykolom5, (float) $baselinekolom5);
																	$ambildatasebelum = ((float) $baselinekolom5);
																} else {
																	array_push($arraykolom5, (float) $rowdata['kolom5'] + (float) $arraykolom5[$s - 1]);
																	$ambildatasebelum = ((float) $rowdata['kolom5'] + (float) $arraykolom5[$s - 1]);
																}
																$cekrumuskolom5 = str_replace("ambildatasebelumbaseline5", $ambildatasebelum, $rumuskolom5);
																//echo $cekrumuskolom5."<br>";
																$tempformula_string5 = explode("/", $cekrumuskolom5);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																		$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																		$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																	}
																}
																try {
																	eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom5 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom5 = 0;');
																}
															} else if (strstr($rumuskolom5, "ambildatamin")) {
																$cekrumuskolom5 = $rumuskolom5;
																if (strstr($rumuskolom5, "ambildatamin1")) {
																	$ambildatasebelum1 = 0;
																	if ($x == $tahun_awal) {
																		array_push($arrayisiminuskolom1, (float) $baselinekolom1);
																		$ambildatasebelum1 = ((float) $baselinekolom1);
																	} else {
																		array_push($arrayisiminuskolom1, (float) $arrayminuskolom1[$s - 1]);
																		$ambildatasebelum1 = ((float) $arrayminuskolom1[$s - 1]);
																	}
																	$cekrumuskolom5 = str_replace("ambildatamin1", $ambildatasebelum1, $cekrumuskolom5);
																	//echo $cekrumuskolom5."<br>";
																	$tempformula_string5 = explode("/", $cekrumuskolom5);
																	for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																		if ((float) $tempformula_string5[$ceknol] == 0) {
																			$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																		}
																	}
																}
																if (strstr($rumuskolom5, "ambildatamin2")) {
																	$ambildatasebelum2 = 0;
																	if ($x == $tahun_awal) {
																		array_push($arrayisiminuskolom2, (float) $baselinekolom2);
																		$ambildatasebelum2 = ((float) $baselinekolom2);
																	} else {
																		array_push($arrayisiminuskolom2, (float) $arrayminuskolom2[$s - 1]);
																		$ambildatasebelum2 = ((float) $arrayminuskolom2[$s - 1]);
																	}
																	$cekrumuskolom5 = str_replace("ambildatamin2", $ambildatasebelum2, $cekrumuskolom5);
																	//echo $cekrumuskolom5."<br>";
																	$tempformula_string5 = explode("/", $cekrumuskolom5);
																	for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																		if ((float) $tempformula_string5[$ceknol] == 0) {
																			$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																		}
																	}
																}
																if (strstr($rumuskolom5, "ambildatamin3")) {
																	$ambildatasebelum3 = 0;
																	if ($x == $tahun_awal) {
																		array_push($arrayisiminuskolom3, (float) $baselinekolom3);
																		$ambildatasebelum3 = ((float) $baselinekolom3);
																	} else {
																		array_push($arrayisiminuskolom3, (float) $arrayminuskolom3[$s - 1]);
																		$ambildatasebelum3 = ((float) $arrayminuskolom3[$s - 1]);
																	}
																	$cekrumuskolom5 = str_replace("ambildatamin3", $ambildatasebelum3, $cekrumuskolom5);
																	//echo $cekrumuskolom5."<br>";
																	$tempformula_string5 = explode("/", $cekrumuskolom5);
																	for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																		if ((float) $tempformula_string5[$ceknol] == 0) {
																			$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																		}
																	}
																}
																if (strstr($rumuskolom5, "ambildatamin4")) {
																	$ambildatasebelum4 = 0;
																	if ($x == $tahun_awal) {
																		array_push($arrayisiminuskolom4, (float) $baselinekolom4);
																		$ambildatasebelum4 = ((float) $baselinekolom4);
																	} else {
																		array_push($arrayisiminuskolom4, (float) $arrayminuskolom4[$s - 1]);
																		$ambildatasebelum4 = ((float) $arrayminuskolom4[$s - 1]);
																	}
																	$cekrumuskolom5 = str_replace("ambildatamin4", $ambildatasebelum4, $cekrumuskolom5);
																	//echo $cekrumuskolom5."<br>";
																	$tempformula_string5 = explode("/", $cekrumuskolom5);
																	for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																		if ((float) $tempformula_string5[$ceknol] == 0) {
																			$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																		}
																	}
																}
																if (strstr($rumuskolom5, "ambildatamin5")) {
																	$ambildatasebelum5 = 0;
																	if ($x == $tahun_awal) {
																		array_push($arrayisiminuskolom5, (float) $baselinekolom5);
																		$ambildatasebelum5 = ((float) $baselinekolom5);
																	} else {
																		array_push($arrayisiminuskolom5, (float) $arrayminuskolom5[$s - 1]);
																		$ambildatasebelum5 = ((float) $arrayminuskolom5[$s - 1]);
																	}
																	$cekrumuskolom5 = str_replace("ambildatamin5", $ambildatasebelum5, $cekrumuskolom5);
																	//echo $cekrumuskolom5."<br>";
																	$tempformula_string5 = explode("/", $cekrumuskolom5);
																	for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																		if ((float) $tempformula_string5[$ceknol] == 0) {
																			$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																		}
																	}
																}

																try {
																	eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom5 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom5 = 0;');
																}
															} else if (strstr($rumuskolom5, "given")) {
																$cekrumuskolom5 = $rumuskolom5;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom5 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom5);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom5);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																		$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																		$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																	}
																}
																try {
																	eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom5 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom5 = 0;');
																}
															} else if (strstr($rumuskolom5, "isikolom")) {
																$cekrumuskolom5 = $rumuskolom5;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom5 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom5);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom5);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	//echo $tempformula_string5[$ceknol]."<br>";
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																		$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																		$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																	}
																}
																//echo $cekrumuskolom5."<br>";
																try {
																	eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom5 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom5 = 0;');
																}
																//echo $isikolom5."<br>";
															} else if (strstr($rumuskolom5, "totalkolom")) {
																$cekrumuskolom5 = $rumuskolom5;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom5 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom5);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom5);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	//echo $tempformula_string5[$ceknol]."<br>";
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																		$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																		$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																	}
																}
																//echo $cekrumuskolom5."<br>";
																try {
																	eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom5 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom5 = 0;');
																}
																//echo $isikolom5."<br>";
															} else {
																$isikolom5 = (float) $rowdata['kolom5'];
															}
															$totalkolom5 = $totalkolom5 + (float) $isikolom5;
															array_push($arrayminuskolom5, (float) $isikolom5);
															#kolom5
															//echo $isikolom5."<br>";
															//echo $totalkolom1."<br>";
														}
													}
												}
											}
											if ($rumuskolom5 <> '') {
												$targetkumulatif = $isikolom5;
											} else {
												if ($rumuskolom4 <> '') {
													$targetkumulatif = $isikolom4;
												} else {
													if ($rumuskolom3 <> '') {
														$targetkumulatif = $isikolom3;
													} else {
														if ($rumuskolom2 <> '') {
															$targetkumulatif = $isikolom2;
														} else {
															$targetkumulatif = $isikolom1;
														}
													}
												}
											}
											//echo $belakangkoma;
											//die;
											$target = $targetkumulatif;
											//$target = number_format($targetkumulatif, $belakangkoma, ",", ".");
										}
										$s++;
									}
									if ($rowlevelPROGRAM['hitungan_pk'] == '2' || $rowlevelPROGRAM['hitungan_pk'] == '3') {
										$sqlskor = "select nilai,rumus
										from tb_data_pk_balai_akhir
										where deleted = '0' and id_balai = '" . $kodebalai . "' and kode_tahun='" . $tahun . "'
										and tahun = '" . $tahunsekarang . "'
										and id_indikator = '" . $rowlevelPROGRAM['id'] . "'";
										//echo $sqlskor;
										//die;
										$resultskor = mysqli_query($link, $sqlskor);
										$numskor = mysqli_num_rows($resultskor);
										if ($numskor > 0) {
											while ($rowskor = mysqli_fetch_assoc($resultskor)) {
												$nilai = $rowskor['nilai'];
												$rumus = $rowskor['rumus'];
												$tempnilai = explode("|", $nilai);

												for ($s = 0; $s < 10; $s++) {
													$rumus = str_replace("input" . $s, (float) ($tempnilai[$s]), $rumus);
												}
												//echo $rumus;
												for ($s = 0; $s < 10; $s++) {
													$rumus = str_replace("nilai" . $s, (float) ($tempnilai[$s]), $rumus);
												}

												for ($s = 10; $s < 21; $s++) {
													$rumus = str_replace("entry" . $s, (float) ($tempnilai[$s]), $rumus);
												}

												for ($s = 10; $s < 21; $s++) {
													$rumus = str_replace("rumus" . $s, (float) ($tempnilai[$s]), $rumus);
												}


												//echo $rumus."<br>";
												$rumus = str_replace("||", "|0|", $rumus);
												$tempnilairumus = explode("|", $rumus);
												$finalrumus = $tempnilairumus[count($tempnilairumus) - 1];

												//$finalrumus = str_replace("/0", "*0", $finalrumus);
												$tempformula_string5 = explode("/", $finalrumus);
												for ($s = 0; $s < count($tempformula_string5); $s++) {
													if ((float) $tempformula_string5[$s] == 0) {
														$finalrumus = str_replace("0/0", "0", $finalrumus);
														$finalrumus = str_replace("0/(0+0)", "0", $finalrumus);
														$finalrumus = str_replace("/0", "*0", $finalrumus);
													}
												}

												//echo $finalrumus . "<br>" . $row['id'] . "<aa>" . $rumus . "<bb>";
												//die;
												try {
													eval('$nilaisekarang = ' . $finalrumus . ';');
												} catch (DivisionByZeroError $e) {
													eval('$nilaisekarang = 0;');
												} catch (ParseError $e) {
													eval('$nilaisekarang = 0;');
												}
												//die;
												//echo $nilaisekarang."<br>";
											}
											$targetkumulatif = $nilaisekarang;
											$target = $targetkumulatif;
											//echo $target."<br>";
										} else {
											$targetkumulatif = 0;
											$target = 0;
											$output_tahun_berjalan = 0;
											$target_tahun_berjalan = 0;
										}
									}
									if ((float) $rowlevelPROGRAM['terpilih'] == 0) {
										array_push($myArrayIKU, $rowlevelPROGRAM['kode_unique']);
										$currentY = $pdf->GetY();
										$html = '
										<table border="1"  cellpadding="2" style="width:100%;" >
										<tr>
										<td align="justify" width="800px">
										' . $rowlevelPROGRAM['textindikator'] . ' 
										</td>
										<td align="center" width="200px">
										' . number_format($target, $belakangkoma, ",", ".") . ' ' . $rowlevelPROGRAM['namaoutcome'] . '
										</td>
										</tr>
										</table>
										';
										$pdf->writeHTML($html, true, false, true, false, '');
										$currentY = $pdf->GetY();
										$pdf->SetY($currentY - 4.95);
									}

									$myArray[] = (object)
									[
										'id' => (float) $rowlevelPROGRAM['id'],
										'tahun' => (float) $rowlevelPROGRAM['tahun'],
										'id_parent' => $rowlevelPROGRAM['id_parent'],
										'kdunor' => $rowlevelPROGRAM['kdunor'],
										'nama_unor' => $rowlevelPROGRAM['nama_unor'],
										'urutlevel' => (float) $rowlevelPROGRAM['urutlevel'],
										'level' => $rowlevelPROGRAM['level'],
										'kode' => $rowlevelPROGRAM['kode'],
										'kode_unique' => $rowlevelPROGRAM['kode_unique'],
										'textindikator' => $rowlevelPROGRAM['textindikator'],
										'satuan' => $rowlevelPROGRAM['satuan'],
										'namasatuan' => $rowlevelPROGRAM['namasatuan'],
										'output' => $rowlevelPROGRAM['output'],
										'namaoutput' => $rowlevelPROGRAM['namaoutput'],
										'outcome' => $rowlevelPROGRAM['outcome'],
										'namaoutcome' => $rowlevelPROGRAM['namaoutcome'],
										'penanggungjawab' => $rowlevelPROGRAM['penanggungjawab'],
										'pelaksana' => $rowlevelPROGRAM['pelaksana'],
										'pelaksanacode' => $rowlevelPROGRAM['pelaksanacode'],
										'pelaksanalabel' => $rowlevelPROGRAM['pelaksanalabel'],
										'target' => $rowlevelPROGRAM['target'],
										'urut' => (float) $rowlevelPROGRAM['urut'],
										'hitungan_pk' => $rowlevelPROGRAM['hitungan_pk'],
										'namahitungan_pk' => $rowlevelPROGRAM['namahitungan_pk'],
										'targetkumulatif' => 0,
										'output_tahun_berjalan' => 0,
										'target_tahun_berjalan' => 0,
										'class' => $rowlevelPROGRAM['class'],
									];
								}
							}
						}
					}
				}
				// KEGIATAN			
			}

			if ($row['hitungan_pk'] == '1') {
				$target = number_format($target, 0, ",", ".");
				$output_tahun_berjalan = number_format($output_tahun_berjalan, 0, ",", ".");
				$target_tahun_berjalan = number_format($target_tahun_berjalan, 0, ",", ".");
			} else {
				$target = number_format($target, 2, ",", ".");
				$output_tahun_berjalan = number_format($output_tahun_berjalan, 2, ",", ".");
				$target_tahun_berjalan = number_format($target_tahun_berjalan, 2, ",", ".");
			}
		}
	}
}

$currentY = $pdf->GetY();
$pdf->SetY($currentY + 5);
$html = '<tr>
<td align="left" colspan="3" width="820px">
<b>PROGRAM</b>
</td>
<td align="center" width="170px">
<b>ANGGARAN</b>
</td>
</tr>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->SetFont('dejavusans', '', 10);

$sqlpagu = "select a.kdprogram,a.nmprogram 
,ifnull((select sum(b.pg) from paket_pk_akhir b where b.kdprogram=a.kdprogram
and b.kdsatker in (" . $groupkode_satker_pendek . ")),0) as pagu
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
	' . $urut . '.' . $rowpagu['nmprogram'] . '
	</td>
	<td align="center" width="50px">
	Rp.
	</td>
	<td align="right" width="200px">
	' . replace_dot_koma($rowpagu['pagu']) . '
	</td>
	</tr>';
	$urut++;
}
$html = $html . '<tr>
<td align="left" colspan="3" width="740px">
</td>
<td align="center" width="50px">
<b>Rp.</b>
</td>
<td align="right" width="200px">
<b>' . replace_dot_koma($totalpagu) . '</b>
</td>
</tr>';

$pdf->writeHTML($html, true, false, true, false, '');


$pdf->SetFont('helvetica', '', 11);
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

$myArray = array();
$pdf->AddPage('L', $resolution);
$pdf->SetFont('helvetica', '', 11);
$currentY = $pdf->GetY();
$html = '
<table border="0"  cellpadding="2" style="width:100%;" >
<thead>
';
$html = $html . '	
</thead>  
<tbody>
<tr>
<td align="center" width="1000px">	
<b>PERJANJIAN KINERJA TAHUN ' . $tahunsekarang . '</b>
</td>
</tr>
<tr>
<td align="center" width="1000px">	
<b>' . $nama_balai . '</b>
</td>
</tr>
<tr>
<td align="center" width="1000px">	
<b>DIREKTORAT JENDERAL SUMBER DAYA AIR</b>
</td>
</tr>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');

$currentY = $pdf->GetY();
$pdf->SetY($currentY - 5);
$pdf->SetFont('dejavusans', '', 9);
$html = '

<table border="0"  cellpadding="2" style="width:100%;" >
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
$pdf->SetFont('dejavusans', '', 8);
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 5);
$html = '
<table border="1"  cellpadding="2" style="width:100%;" >
';
$html = $html . '
<tr>
<td align="justify" width="520px" style="background-color: #D3D3D3;">
<b>SASARAN PROGRAM / INDIKATOR SASARAN PROGRAM</b>
</td>
<td align="center" width="160px" style="background-color: #D3D3D3;">
<b>OUTPUT TAHUN BERJALAN</b>
</td>
<td align="center" width="160px" style="background-color: #D3D3D3;">
<b>OUTCOME TAHUN BERJALAN</b>
</td>
<td align="center" width="160px" style="background-color: #D3D3D3;">
<b>TARGET KUMULATIF</b>
</td>
</tr>
';
$html = $html . '
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 5);
$sqlPROGRAMLAMPIRAN2 = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
,IF(a.satuan = '0', null, a.satuan) as satuan
,IF(a.output = '0', null, a.output) as output
,IF(a.outcome = '0', null, a.outcome) as outcome
, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
IF(a.level = 'ISP', 'levelsubsubkegiatan', 
IF(a.level = 'KEGIATAN', 'levelpaket', 
IF(a.level = 'SK', 'levelpekerjaan', 
IF(a.level = 'IKSK', 'levelakhir', 
IF(a.level = 'KRO', 'levelakhir', 
IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
from tb_indikator_akhir a 
left join master_unor b
on a.kdunor = b.id
where a.deleted='0' and a.level = 'PROGRAM'
order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
$resultPROGRAMLAMPIRAN2 = mysqli_query($link, $sqlPROGRAMLAMPIRAN2);
$numPROGRAMLAMPIRAN2 = mysqli_num_rows($resultPROGRAMLAMPIRAN2);
if ($numPROGRAMLAMPIRAN2 > 0) {
	if ($resultPROGRAMLAMPIRAN2) {
		while ($rowPROGRAMLAMPIRAN2 = mysqli_fetch_assoc($resultPROGRAMLAMPIRAN2)) {
			$currentY = $pdf->GetY();
			if ($rowPROGRAMLAMPIRAN2['textindikator'] == 'DUKUNGAN MANAJEMEN' && (float) $currentY > 90) {
				$pdf->AddPage('L', $resolution);
				$pdf->SetFont('dejavusans', '', 11);

				$html = '
				<table border="0"  cellpadding="2" style="width:100%;" >
				<thead>
				';
				$html = $html . '	
				</thead>  
				<tbody>
				<tr>
				<td align="center" width="1000px">	
				<b>PERJANJIAN KINERJA TAHUN ' . $tahunsekarang . '</b>
				</td>
				</tr>
				<tr>
				<td align="center" width="1000px">	
				<b>' . $nama_balai . '</b>
				</td>
				</tr>
				<tr>
				<td align="center" width="1000px">	
				<b>DIREKTORAT JENDERAL SUMBER DAYA AIR</b>
				</td>
				</tr>
				</table>
				';
				$currentY = $pdf->GetY();
				$pdf->SetY($currentY - 5);
				$pdf->writeHTML($html, true, false, true, false, '');
				$currentY = $pdf->GetY();
				$pdf->SetY($currentY - 5);
				$pdf->SetFont('dejavusans', '', 9);
				$html = '

				<table border="0"  cellpadding="2" style="width:100%;" >
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
				<b>OUTPUT TAHUN BERJALAN</b>
				</td>
				<td align="center" width="160px" style="background-color: #D3D3D3;">
				<b>OUTCOME TAHUN BERJALAN</b>
				</td>
				<td align="center" width="160px" style="background-color: #D3D3D3;">
				<b>TARGET KUMULATIF</b>
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
				<b>PROGRAM: ' . $rowPROGRAMLAMPIRAN2['textindikator'] . '</b>
				</td>
				</tr>
				</table>
				';
				$pdf->writeHTML($html, true, false, true, false, '');
				$currentY = $pdf->GetY();
				$pdf->SetY($currentY - 4.75);
			} else {
				$html = '
				<table border="1"  cellpadding="2" style="width:100%;" >
				<tr>
				<td align="justify" width="1000px">
				<b>PROGRAM: ' . $rowPROGRAMLAMPIRAN2['textindikator'] . '</b>
				</td>
				</tr>
				</table>
				';
				$pdf->writeHTML($html, true, false, true, false, '');
				$currentY = $pdf->GetY();
				$pdf->SetY($currentY - 4.75);
			}

			//KEGIATAN
			$sqlKEGIATANLAMPIRAN2 = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
			,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
			,IF(a.satuan = '0', null, a.satuan) as satuan
			,IF(a.output = '0', null, a.output) as output
			,IF(a.outcome = '0', null, a.outcome) as outcome
			, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
			, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
			, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
			, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
			,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
			, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
			,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
			IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
			IF(a.level = 'ISP', 'levelsubsubkegiatan', 
			IF(a.level = 'KEGIATAN', 'levelpaket', 
			IF(a.level = 'SK', 'levelpekerjaan', 
			IF(a.level = 'IKSK', 'levelakhir', 
			IF(a.level = 'KRO', 'levelakhir', 
			IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
			from tb_indikator_akhir a 
			left join master_unor b
			on a.kdunor = b.id
			where a.deleted='0' and a.level = 'KEGIATAN' and a.id_parent = '" . $rowPROGRAMLAMPIRAN2['id'] . "'
			order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
			$resultKEGIATANLAMPIRAN2 = mysqli_query($link, $sqlKEGIATANLAMPIRAN2);
			$numKEGIATANLAMPIRAN2 = mysqli_num_rows($resultKEGIATANLAMPIRAN2);
			while ($rowKEGIATANLAMPIRAN2 = mysqli_fetch_assoc($resultKEGIATANLAMPIRAN2)) {
				//SK
				$sqlSKLAMPIRAN2 = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
				,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
				,IF(a.satuan = '0', null, a.satuan) as satuan
				,IF(a.output = '0', null, a.output) as output
				,IF(a.outcome = '0', null, a.outcome) as outcome
				, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
				, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
				, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
				, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
				,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
				, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
				,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
				IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
				IF(a.level = 'ISP', 'levelsubsubkegiatan', 
				IF(a.level = 'KEGIATAN', 'levelpaket', 
				IF(a.level = 'SK', 'levelpekerjaan', 
				IF(a.level = 'IKSK', 'levelakhir', 
				IF(a.level = 'KRO', 'levelakhir', 
				IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
				from tb_indikator_akhir a 
				left join master_unor b
				on a.kdunor = b.id
				where a.deleted='0' and a.level = 'SK' and a.id_parent = '" . $rowKEGIATANLAMPIRAN2['id'] . "'
				and a.id in (select b.id_parent from tb_indikator_akhir b where b.deleted = '0' 
				and b.id in ((select c.id_indikator from tb_data_pk_akhir c where b.deleted='0' and c.kode_satker in (" . $groupkode_satker . "))))
				order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
				$resultSKLAMPIRAN2 = mysqli_query($link, $sqlSKLAMPIRAN2);
				$numSKLAMPIRAN2 = mysqli_num_rows($resultSKLAMPIRAN2);
				while ($rowSKLAMPIRAN2 = mysqli_fetch_assoc($resultSKLAMPIRAN2)) {

					$sqlcektampilmaster = "select pelaksana from tb_indikator_akhir where deleted='0' and id_parent = '" . $rowSKLAMPIRAN2['id'] . "'";
					$resultcektampilmaster = mysqli_query($link, $sqlcektampilmaster);
					$numcektampilmaster = mysqli_num_rows($resultcektampilmaster);


					$sqlcektampil = "select id from tb_data_pk_tidak_cetak_akhir where deleted='0' and id_indikator in 
					(select id from tb_indikator_akhir where deleted='0' and id_parent = '" . $rowSKLAMPIRAN2['id'] . "')
					and kode_satker in (" . $groupkode_satker . ")";

					$resultcektampil = mysqli_query($link, $sqlcektampil);
					$numcektampil = mysqli_num_rows($resultcektampil);



					////
					$sqlcektampilINDIKATOR = "select pelaksana from tb_indikator_akhir where deleted='0' and id_parent = '" . $rowSKLAMPIRAN2['id'] . "' ";
					//echo $sqlcektampilINDIKATOR."<br>";
					$resultcektampilINDIKATOR = mysqli_query($link, $sqlcektampilINDIKATOR);
					$numcektampilINDIKATOR = mysqli_num_rows($resultcektampilINDIKATOR);
					$jumlahcek = 0;
					while ($rowcektampilINDIKATOR = mysqli_fetch_assoc($resultcektampilINDIKATOR)) {
						$cekpelaksana = $rowcektampilINDIKATOR['pelaksana'];
						$tempcekpelaksana = explode(",", $cekpelaksana);
						for ($sss = 0; $sss < count($tempcekpelaksana); $sss++) {
							$sqlcekjumlah = "select level_piu from master_satker where deleted='0' and kode_satker in (" . $groupkode_satker . ")
							and level_piu = '" . $tempcekpelaksana[$sss] . "'";

							$resultcekjumlah = mysqli_query($link, $sqlcekjumlah);
							$numcekjumlah = mysqli_num_rows($resultcekjumlah);
							$jumlahcek = $jumlahcek + $numcekjumlah;
						}
					}
					////


					if (($numcektampil == 0) || ($numcektampil < $jumlahcek)) {
						$currentY = $pdf->GetY();
						if ((float)$currentY > 160) {
							$pdf->AddPage('L', $resolution);
							$pdf->SetFont('dejavusans', '', 11);

							$html = '
							<table border="0"  cellpadding="2" style="width:100%;" >
							<thead>
							';
							$html = $html . '	
							</thead>  
							<tbody>
							<tr>
							<td align="center" width="1000px">	
							<b>PERJANJIAN KINERJA TAHUN ' . $tahunsekarang . '</b>
							</td>
							</tr>
							<tr>
							<td align="center" width="1000px">	
							<b>' . $nama_balai . '</b>
							</td>
							</tr>
							<tr>
							<td align="center" width="1000px">	
							<b>DIREKTORAT JENDERAL SUMBER DAYA AIR</b>
							</td>
							</tr>
							</table>
							';
							$currentY = $pdf->GetY();
							$pdf->SetY($currentY - 5);
							$pdf->writeHTML($html, true, false, true, false, '');
							$currentY = $pdf->GetY();
							$pdf->SetY($currentY - 5);
							$pdf->SetFont('dejavusans', '', 9);
							$html = '

							<table border="0"  cellpadding="2" style="width:100%;" >
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
							<b>OUTPUT TAHUN BERJALAN</b>
							</td>
							<td align="center" width="160px" style="background-color: #D3D3D3;">
							<b>OUTCOME TAHUN BERJALAN</b>
							</td>
							<td align="center" width="160px" style="background-color: #D3D3D3;">
							<b>TARGET KUMULATIF</b>
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
							<b>SK: ' . $rowSKLAMPIRAN2['textindikator'] . '</b>
							</td>
							</tr>
							</table>
							';
							$pdf->writeHTML($html, true, false, true, false, '');
							$currentY = $pdf->GetY();
							$pdf->SetY($currentY - 4.75);
						} else {
							$html = '
							<table border="1"  cellpadding="2" style="width:100%;" >
							<tr>
							<td align="justify" width="1000px">
							<b>SK: ' . $rowSKLAMPIRAN2['textindikator'] . '</b>
							</td>
							</tr>
							</table>
							';
							$pdf->writeHTML($html, true, false, true, false, '');
							$currentY = $pdf->GetY();
							$pdf->SetY($currentY - 4.75);
						}
					}
					//IKSK
					$sqlIKSKLAMPIRAN2 = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
					,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
					,IF(a.satuan = '0', null, a.satuan) as satuan
					,IF(a.output = '0', null, a.output) as output
					,IF(a.outcome = '0', null, a.outcome) as outcome
					, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
					, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
					, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
					, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
					,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
					, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
					,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
					IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
					IF(a.level = 'ISP', 'levelsubsubkegiatan', 
					IF(a.level = 'KEGIATAN', 'levelpaket', 
					IF(a.level = 'SK', 'levelpekerjaan', 
					IF(a.level = 'IKSK', 'levelakhir', 
					IF(a.level = 'KRO', 'levelakhir', 
					IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
					from tb_indikator_akhir a 
					left join master_unor b
					on a.kdunor = b.id
					where a.deleted='0' and a.level = 'IKSK' and a.id_parent = '" . $rowSKLAMPIRAN2['id'] . "'
					and a.pelaksana <> ''
					and a.id in (select b.id_indikator from tb_data_pk_akhir b where b.deleted='0' and b.kode_satker in (" . $groupkode_satker . "))
					order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
					$resultIKSKLAMPIRAN2 = mysqli_query($link, $sqlIKSKLAMPIRAN2);
					$numIKSKLAMPIRAN2 = mysqli_num_rows($resultIKSKLAMPIRAN2);
					while ($rowIKSKLAMPIRAN2 = mysqli_fetch_assoc($resultIKSKLAMPIRAN2)) {
						$belakangkoma = (float) $rowIKSKLAMPIRAN2['belakangkoma'];
						$output_tahun_berjalan = 0;
						$outcome_tahun_berjalan = 0;
						$target = 0;
						if ($rowIKSKLAMPIRAN2['hitungan_pk'] == '1') {
							$output_tahun_berjalan = 0;
							$sqloutputtahunsekarang = "select sum(volume) as volume
							from tb_data_pk_akhir
							where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
							and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "' and tahun = '" . $tahunsekarang . "'
							and kode_satker not in (select kode_satker from tb_data_pk_tidak_cetak_akhir
							where id_indikator='" . $rowIKSKLAMPIRAN2['id'] . "' and deleted='0' and kode_satker in (" . $groupkode_satker . "))";
							//echo $sqloutputtahunsekarang;
							$resultoutputtahunsekarang = mysqli_query($link, $sqloutputtahunsekarang);
							$numoutputtahunsekarang = mysqli_num_rows($resultoutputtahunsekarang);
							if ($numoutputtahunsekarang > 0) {
								while ($rowoutputtahunsekarang = mysqli_fetch_assoc($resultoutputtahunsekarang)) {
									$output_tahun_berjalan = (float) $rowoutputtahunsekarang['volume'];
								}
							}
							$output_tahun_berjalan = number_format($output_tahun_berjalan, $belakangkoma, ",", ".");

							$outcome_tahun_berjalan = 0;
							$sqloutcometahunsekarang = "select sum(target) as target
							from tb_data_pk_akhir
							where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
							and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "' and tahun = '" . $tahunsekarang . "'
							and kode_satker not in (select kode_satker from tb_data_pk_tidak_cetak_akhir
							where id_indikator='" . $rowIKSKLAMPIRAN2['id'] . "' and deleted='0' and kode_satker in (" . $groupkode_satker . "))";
							//echo $sqloutcometahunsekarang;
							$resultoutcometahunsekarang = mysqli_query($link, $sqloutcometahunsekarang);
							$numoutcometahunsekarang = mysqli_num_rows($resultoutcometahunsekarang);
							if ($numoutcometahunsekarang > 0) {
								while ($rowoutcometahunsekarang = mysqli_fetch_assoc($resultoutcometahunsekarang)) {
									$outcome_tahun_berjalan = (float) $rowoutcometahunsekarang['target'];
								}
							}
							$outcome_tahun_berjalan = number_format($outcome_tahun_berjalan, $belakangkoma, ",", ".");

							$baseline = 0;
							$sqlbaseline = "select baseline
							from tb_data_baseline
							where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
							and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "'";
							//echo $sqlbaseline;
							$resultbaseline = mysqli_query($link, $sqlbaseline);
							$numbaseline = mysqli_num_rows($resultbaseline);
							if ($numbaseline > 0) {
								while ($rowbaseline = mysqli_fetch_assoc($resultbaseline)) {
									$baseline = (float) $rowbaseline['baseline'];
								}
							}

							$target = 0;
							$sqlskor = "select sum(target) as target
							from tb_data_pk_akhir
							where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
							and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "'
							and kode_satker not in (select kode_satker from tb_data_pk_tidak_cetak_akhir
							where id_indikator='" . $rowIKSKLAMPIRAN2['id'] . "' and deleted='0' and kode_satker in (" . $groupkode_satker . "))";
							//echo $sqlskor;
							$resultskor = mysqli_query($link, $sqlskor);
							$numskor = mysqli_num_rows($resultskor);
							if ($numskor > 0) {
								while ($rowskor = mysqli_fetch_assoc($resultskor)) {
									$target = (float) $rowskor['target'] + $baseline;
								}
							}
							$target = number_format($target, $belakangkoma, ",", ".");
						}
						if ($rowIKSKLAMPIRAN2['hitungan_pk'] == '2' || $rowIKSKLAMPIRAN2['hitungan_pk'] == '3') {
							//echo $groupkode_satker;
							//die;
							if ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7755SK113') {
								$sqlskor = "select kode_satker,nilai,rumus
								from tb_data_pk_akhir
								where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
								and tahun = '" . $tahunsekarang . "'
								and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "'
								and kode_satker in (select kode_satker from master_satker where 
								kode_satker in (" . $groupkode_satker . ") and level_piu <> '7' and level_piu <> '41')
								and kode_satker not in (select kode_satker from tb_data_pk_tidak_cetak_akhir
								where id_indikator='222' and deleted='0' and kode_satker in (" . $groupkode_satker . "))";
							} else {
								$sqlskor = "select nilai,rumus
								from tb_data_pk_akhir
								where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
								and tahun = '" . $tahunsekarang . "'
								and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "'";

								if (($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7688SK110') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7688SK111')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7688SK115') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7689SK104')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7689SK105') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7690SK101')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7690SK102') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7690SK103')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7690SK104') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7690SK105')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7691SK104') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7692SK104')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK301') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK302')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK303') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK304')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7755SK112') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7688SK102')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7755SK114') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7688SK103')
								) {
									$sqlskor = $sqlskor . " and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (7,41,8,12,13,14,26,28,29,30))";
								}

								if (($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7691SK101') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7691SK102')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7691SK103')
								) {
									$sqlskor = $sqlskor . " and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (2))";
								}

								if (($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7692SK101') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7692SK102')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7692SK103')  || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7692SK201')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7692SK202')
								) {
									$sqlskor = $sqlskor . " and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (3,31))";
								}

								if (($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7693SK101') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7693SK106')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7693SK102')  || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7693SK103')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7693SK104') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7693SK105')
								) {
									$sqlskor = $sqlskor . " and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (1))";
								}

								if (($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK101') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK102')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK103')  || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK104')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK105') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK106')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK107') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK108')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK201')
								) {
									$sqlskor = $sqlskor . " and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (4))";
								}

								if (($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK101') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK102')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK103')  || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK104')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK105') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK106')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK107') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK108')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK109') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK110')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK111') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK303')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK305')
								) {
									$sqlskor = $sqlskor . " and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (5))";
								}
							}
							$resultskor = mysqli_query($link, $sqlskor);
							$numskor = mysqli_num_rows($resultskor);
							if ($numskor > 0) {
								$totaldukman = 0;
								$urutandukman = 0;
								while ($rowskor = mysqli_fetch_assoc($resultskor)) {
									if ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7755SK113') {

										//echo $rowskor['kode_satker']."_".$rowskor['nilai']."<br>";

										$nilai = $rowskor['nilai'];
										$tempnilai = explode("|", $nilai);
										$totaldukman = $totaldukman + (float) $tempnilai[count($tempnilai) - 1];
									} else {
										$nilai = $rowskor['nilai'];
										$tempnilai = explode("|", $nilai);
										$jumlahnilai = 0;
										$output_tahun_berjalan = $tempnilai[count($tempnilai) - 1];
										$outcome_tahun_berjalan = $tempnilai[count($tempnilai) - 1];
										$target = $tempnilai[count($tempnilai) - 1];

										$output_tahun_berjalan = number_format($output_tahun_berjalan, $belakangkoma, ",", ".");
										$outcome_tahun_berjalan = number_format($outcome_tahun_berjalan, $belakangkoma, ",", ".");
										$target = number_format($target, $belakangkoma, ",", ".");
									}
									$urutandukman++;
								}
								if ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7755SK113') {
									$output_tahun_berjalan = $totaldukman / $urutandukman;
									$outcome_tahun_berjalan = $totaldukman / $urutandukman;
									$target = $totaldukman / $urutandukman;

									$output_tahun_berjalan = number_format($output_tahun_berjalan, $belakangkoma, ",", ".");
									$outcome_tahun_berjalan = number_format($outcome_tahun_berjalan, $belakangkoma, ",", ".");
									$target = number_format($target, $belakangkoma, ",", ".");
								}
							} else {
								$output_tahun_berjalan = 0;
								$outcome_tahun_berjalan = 0;
								$target = 0;
							}
						}
						$sqlcektampilIKSK = "select id from tb_data_pk_tidak_cetak_akhir where deleted='0' and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "'
						and kode_satker in (" . $groupkode_satker . ")";
						$resultcektampilIKSK = mysqli_query($link, $sqlcektampilIKSK);
						$numcektampilIKSK = mysqli_num_rows($resultcektampilIKSK);

						$sqlcektampilINDIKATOR = "select pelaksana from tb_indikator_akhir where deleted='0' and id = '" . $rowIKSKLAMPIRAN2['id'] . "' ";
						//echo $sqlcektampilINDIKATOR."<br>";
						$resultcektampilINDIKATOR = mysqli_query($link, $sqlcektampilINDIKATOR);
						$numcektampilINDIKATOR = mysqli_num_rows($resultcektampilINDIKATOR);
						$jumlahcek = 0;
						while ($rowcektampilINDIKATOR = mysqli_fetch_assoc($resultcektampilINDIKATOR)) {
							$cekpelaksana = $rowcektampilINDIKATOR['pelaksana'];
							$tempcekpelaksana = explode(",", $cekpelaksana);
							for ($sss = 0; $sss < count($tempcekpelaksana); $sss++) {
								$sqlcekjumlah = "select level_piu from master_satker where deleted='0' and kode_satker in (" . $groupkode_satker . ")
								and level_piu = '" . $tempcekpelaksana[$sss] . "'";

								$resultcekjumlah = mysqli_query($link, $sqlcekjumlah);
								$numcekjumlah = mysqli_num_rows($resultcekjumlah);
								$jumlahcek = $jumlahcek + $numcekjumlah;
							}
						}

						if (($numcektampilIKSK == 0) || ($numcektampilIKSK < $jumlahcek)) {
							array_push($myArrayIKU, $rowIKSKLAMPIRAN2['kode_unique']);
							$currentY = $pdf->GetY();
							if ((float)$currentY > 161) {
								$pdf->AddPage('L', $resolution);
								$pdf->SetFont('dejavusans', '', 11);

								$html = '
								<table border="0"  cellpadding="2" style="width:100%;" >
								<thead>
								';
								$html = $html . '	
								</thead>  
								<tbody>
								<tr>
								<td align="center" width="1000px">	
								<b>PERJANJIAN KINERJA TAHUN ' . $tahunsekarang . '</b>
								</td>
								</tr>
								<tr>
								<td align="center" width="1000px">	
								<b>' . $nama_balai . '</b>
								</td>
								</tr>
								<tr>
								<td align="center" width="1000px">	
								<b>DIREKTORAT JENDERAL SUMBER DAYA AIR</b>
								</td>
								</tr>
								</table>
								';
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 5);
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 5);
								$pdf->SetFont('dejavusans', '', 9);
								$html = '

								<table border="0"  cellpadding="2" style="width:100%;" >
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
								<b>OUTPUT TAHUN BERJALAN</b>
								</td>
								<td align="center" width="160px" style="background-color: #D3D3D3;">
								<b>OUTCOME TAHUN BERJALAN</b>
								</td>
								<td align="center" width="160px" style="background-color: #D3D3D3;">
								<b>TARGET KUMULATIF</b>
								</td>
								</tr>
								';
								$html = $html . '
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 5);
								$currentY = $pdf->GetY();
								$html = '
								<table border="1"  cellpadding="2" style="width:100%;" >
								<tr>
								<td align="justify" width="520px">
								' . $rowIKSKLAMPIRAN2['textindikator'] . '
								</td>
								<td align="center" width="160px">
								' . $output_tahun_berjalan . ' ' . $rowIKSKLAMPIRAN2['namaoutput'] . '
								</td>
								<td align="center" width="160px">
								' . $outcome_tahun_berjalan . ' ' . $rowIKSKLAMPIRAN2['namasatuan'] . '
								</td>
								<td align="center" width="160px">
								' . $target . ' ' . $rowIKSKLAMPIRAN2['namaoutcome'] . '
								</td>
								</tr>
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 4.75);
							} else {
								$currentY = $pdf->GetY();
								$html = '
								<table border="1"  cellpadding="2" style="width:100%;" >
								<tr>
								<td align="justify" width="520px">
								' . $rowIKSKLAMPIRAN2['textindikator'] . '
								</td>
								<td align="center" width="160px">
								' . $output_tahun_berjalan . ' ' . $rowIKSKLAMPIRAN2['namaoutput'] . '
								</td>
								<td align="center" width="160px">
								' . $outcome_tahun_berjalan . ' ' . $rowIKSKLAMPIRAN2['namasatuan'] . '
								</td>
								<td align="center" width="160px">
								' . $target . ' ' . $rowIKSKLAMPIRAN2['namaoutcome'] . '
								</td>
								</tr>
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 4.75);
							}
						}
					}
					//IKSK
				}
				//SK

			}
			//KEGIATAN
		}
	}
}

$currentY = $pdf->GetY();
$pdf->SetY($currentY + 5);
$html = '<tr>
<td align="left" colspan="3" width="820px">
<b>PROGRAM</b>
</td>
<td align="center" width="170px">
<b>ANGGARAN</b>
</td>
</tr>';
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->SetFont('dejavusans', '', 9);

$sqlpagu = "select a.kdprogram,a.nmprogram 
,ifnull((select sum(b.pg) from paket_pk_akhir b where b.kdprogram=a.kdprogram
and b.kdsatker in (" . $groupkode_satker_pendek . ")),0) as pagu
from tprogram a
where a.kdprogram in ('FC','WA')
order by a.kdprogram";


$resultpagu = mysqli_query($link, $sqlpagu);
$html = '';
$urut = 1;
$totalpagu = 0;
while ($rowpagu = mysqli_fetch_assoc($resultpagu)) {
	$totalpagu = $totalpagu + (float) $rowpagu['pagu'];
	$html = $html . '<tr>
	<td align="left" colspan="2" width="740px">
	' . $urut . '.' . $rowpagu['nmprogram'] . '
	</td>
	<td align="center" width="50px">
	Rp.
	</td>
	<td align="right" width="200px">
	' . replace_dot_koma($rowpagu['pagu']) . '
	</td>
	</tr>';
	$urut++;
}
$html = $html . '<tr>
<td align="left" colspan="3" width="740px">
</td>
<td align="center" width="50px">
<b>Rp.</b>
</td>
<td align="right" width="200px">
<b>' . replace_dot_koma($totalpagu) . '</b>
</td>
</tr>';

$pdf->writeHTML($html, true, false, true, false, '');


$pdf->SetFont('helvetica', '', 11);
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


$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage('L', $resolution);
$currentY = $pdf->GetY();
$html = '
<table border="0"  cellpadding="2" style="width:100%;" >
<thead>
';
$html = $html . '	
</thead>  
<tbody>
<tr>
<td align="center" width="1000px">	
<b>BERITA ACARA KESEPAKATAN CAPAIAN KINERJA ' . $tahunsekarang . '</b>
</td>
</tr>
<tr>
<td align="center" width="1000px">	
<b>' . $nama_balai . ' - DIREKTORAT JENDERAL SUMBER DAYA AIR</b>
</td>
</tr>
<tr>
<td align="center" width="1000px">	
</td>
</tr>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 5);
$pdf->SetFont('dejavusans', '', 9);
$html = '

<table border="0"  cellpadding="2" style="width:100%;" >
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
$pdf->SetFont('helvetica', '', 9);
$html = '
<table border="1"  cellpadding="2" style="width:100%;" >
';
$html = $html . '
<tr>
<td align="center" width="500px" style="background-color: #D3D3D3;" rowspan="2">
<b><br>SASARAN PROGRAM / INDIKATOR SASARAN PROGRAM</b>
</td>
<td align="center" width="200px" style="background-color: #D3D3D3;">
<b>TARGET</b>
</td>
<td align="center" width="200px" style="background-color: #D3D3D3;">
<b>CAPAIAN</b>
</td>
<td align="center" width="100px" style="background-color: #D3D3D3;">
<b>KINERJA</b>
</td>
</tr>
<tr>
<td align="center" width="100px" style="background-color: #D3D3D3;">
<b>Volume</b>
</td>
<td align="center" width="100px" style="background-color: #D3D3D3;">
<b>Satuan</b>
</td>
<td align="center" width="100px" style="background-color: #D3D3D3;">
<b>Volume</b>
</td>
<td align="center" width="100px" style="background-color: #D3D3D3;">
<b>Satuan</b>
</td>
<td align="center" width="100px" style="background-color: #D3D3D3;">
<b>%</b>
</td>
</tr>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');

$currentY = $pdf->GetY();
$pdf->SetY($currentY - 5);
$myArray = array();
$myArrayLAMPIRAN2 = array();
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
, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
and zz.kode_satker='" . $kodebalai . "') as terpilih
,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
IF(a.level = 'ISP', 'levelsubsubkegiatan', 
IF(a.level = 'KEGIATAN', 'levelpaket', 
IF(a.level = 'SK', 'levelpekerjaan', 
IF(a.level = 'IKSK', 'levelakhir', 
IF(a.level = 'KRO', 'levelakhir', 
IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
from tb_indikator_akhir a 
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
			$baseline = 0;
			$target = 0;
			$target_komponen = 0;
			$target_tahun_berjalan = 0;
			$target_komponen_tahun_berjalan = 0;
			$output_tahun_berjalan = 0;
			$output_komponen_tahun_berjalan = 0;

			//ISPSK
			$sqllevelISPSK = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
			,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
			,IF(a.satuan = '0', null, a.satuan) as satuan
			,IF(a.output = '0', null, a.output) as output
			,IF(a.outcome = '0', null, a.outcome) as outcome
			, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
			, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
			, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
			, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
			,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
			, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
			,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
			and zz.kode_satker='" . $kodebalai . "') as terpilih
			,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
			IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
			IF(a.level = 'ISP', 'levelsubsubkegiatan', 
			IF(a.level = 'KEGIATAN', 'levelpaket', 
			IF(a.level = 'SK', 'levelpekerjaan', 
			IF(a.level = 'IKSK', 'levelakhir', 
			IF(a.level = 'KRO', 'levelakhir', 
			IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
			from tb_indikator_akhir a 
			left join master_unor b
			on a.kdunor = b.id
			where a.deleted='0' and a.id = '" . $row['id_parent'] . "'
			order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
			//echo $sqllevelISPSK;
			//die;
			$resultlevelISPSK = mysqli_query($link, $sqllevelISPSK);
			while ($rowlevelISPSK = mysqli_fetch_assoc($resultlevelISPSK)) {
				// KEGIATAN
				$sqllevelKEGIATAN = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
				,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
				,IF(a.satuan = '0', null, a.satuan) as satuan
				,IF(a.output = '0', null, a.output) as output
				,IF(a.outcome = '0', null, a.outcome) as outcome
				, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
				, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
				, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
				, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
				,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
				, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
				,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
				and zz.kode_satker='" . $kodebalai . "') as terpilih
				,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
				IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
				IF(a.level = 'ISP', 'levelsubsubkegiatan', 
				IF(a.level = 'KEGIATAN', 'levelpaket', 
				IF(a.level = 'SK', 'levelpekerjaan', 
				IF(a.level = 'IKSK', 'levelakhir', 
				IF(a.level = 'KRO', 'levelakhir', 
				IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
				from tb_indikator_akhir a 
				left join master_unor b
				on a.kdunor = b.id
				where a.deleted='0' and a.id = '" . $rowlevelISPSK['id_parent'] . "'
				order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
				//echo $sqllevelKEGIATAN;
				//die;
				$resultlevelKEGIATAN = mysqli_query($link, $sqllevelKEGIATAN);
				while ($rowlevelKEGIATAN = mysqli_fetch_assoc($resultlevelKEGIATAN)) {
					// TAMBAHAN
					$sqllevelINDIKATORSASARANPROGRAM = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
					,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
					,IF(a.satuan = '0', null, a.satuan) as satuan
					,IF(a.output = '0', null, a.output) as output
					,IF(a.outcome = '0', null, a.outcome) as outcome
					, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
					, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
					, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
					, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
					,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
					, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
					,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
					and zz.kode_satker='" . $kodebalai . "') as terpilih
					,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
					IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
					IF(a.level = 'ISP', 'levelsubsubkegiatan', 
					IF(a.level = 'KEGIATAN', 'levelpaket', 
					IF(a.level = 'SK', 'levelpekerjaan', 
					IF(a.level = 'IKSK', 'levelakhir', 
					IF(a.level = 'KRO', 'levelakhir', 
					IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
					from tb_indikator_akhir a 
					left join master_unor b
					on a.kdunor = b.id
					where a.deleted='0' and a.id = '" . $rowlevelKEGIATAN['id_parent'] . "'
					and a.level = 'PROGRAM'
					order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
					// TAMBAHAN
					//echo $sqllevelINDIKATORSASARANPROGRAM;
					//die;
					$resultlevelINDIKATORSASARANPROGRAM = mysqli_query($link, $sqllevelINDIKATORSASARANPROGRAM);
					while ($rowlevelINDIKATORSASARANPROGRAM = mysqli_fetch_assoc($resultlevelINDIKATORSASARANPROGRAM)) {
						// PROGRAM
						$searchValue = (float) $rowlevelINDIKATORSASARANPROGRAM['id'];
						$found = false;
						foreach ($myArray as $obj) {
							if (isset($obj->id) && $obj->id === $searchValue) {
								$found = true;
								break;
							}
						}
						if (!$found) {
							$currentY = $pdf->GetY();
							if ($rowlevelINDIKATORSASARANPROGRAM['textindikator'] == 'DUKUNGAN MANAJEMEN' && (float) $currentY > 90) {
								$pdf->AddPage('L', $resolution);
								$pdf->SetFont('dejavusans', '', 11);

								$html = '
								<table border="0"  cellpadding="2" style="width:100%;" >
								<thead>
								';
								$html = $html . '	
								</thead>  
								<tbody>
								<tr>
								<td align="center" width="1000px">	
								<b>BERITA ACARA KESEPAKATAN CAPAIAN KINERJA ' . $tahunsekarang . '</b>
								</td>
								</tr>
								<tr>
								<td align="center" width="1000px">	
								<b>' . $nama_balai . ' - DIREKTORAT JENDERAL SUMBER DAYA AIR</b>
								</td>
								</tr>
								<tr>
								<td align="center" width="1000px">	
								<b></b>
								</td>
								</tr>
								</table>
								';
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 5);
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 5);
								$pdf->SetFont('dejavusans', '', 9);
								$html = '

								<table border="0"  cellpadding="2" style="width:100%;" >
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
								<td align="center" width="500px" style="background-color: #D3D3D3;" rowspan="2">
								<b><br>SASARAN PROGRAM / INDIKATOR SASARAN PROGRAM</b>
								</td>
								<td align="center" width="200px" style="background-color: #D3D3D3;">
								<b>TARGET</b>
								</td>
								<td align="center" width="200px" style="background-color: #D3D3D3;">
								<b>CAPAIAN</b>
								</td>
								<td align="center" width="100px" style="background-color: #D3D3D3;">
								<b>KINERJA</b>
								</td>
								</tr>
								<tr>
								<td align="center" width="100px" style="background-color: #D3D3D3;">
								<b>Volume</b>
								</td>
								<td align="center" width="100px" style="background-color: #D3D3D3;">
								<b>Satuan</b>
								</td>
								<td align="center" width="100px" style="background-color: #D3D3D3;">
								<b>Volume</b>
								</td>
								<td align="center" width="100px" style="background-color: #D3D3D3;">
								<b>Satuan</b>
								</td>
								<td align="center" width="100px" style="background-color: #D3D3D3;">
								<b>%</b>
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
								<b>PROGRAM: ' . $rowlevelINDIKATORSASARANPROGRAM['textindikator'] . '</b>
								</td>
								</tr>
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 4.7);
							} else {
								$html = '
								<table border="1"  cellpadding="2" style="width:100%;" >
								<tr>
								<td align="justify" width="1000px">
								<b>PROGRAM: ' . $rowlevelINDIKATORSASARANPROGRAM['textindikator'] . '</b>
								</td>
								</tr>
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 5);
							}
						}
						$myArray[] = (object)
						[
							'id' => (float) $rowlevelINDIKATORSASARANPROGRAM['id'],
							'tahun' => (float) $rowlevelINDIKATORSASARANPROGRAM['tahun'],
							'id_parent' => $rowlevelINDIKATORSASARANPROGRAM['id_parent'],
							'kdunor' => $rowlevelINDIKATORSASARANPROGRAM['kdunor'],
							'nama_unor' => $rowlevelINDIKATORSASARANPROGRAM['nama_unor'],
							'urutlevel' => (float) $rowlevelINDIKATORSASARANPROGRAM['urutlevel'],
							'level' => $rowlevelINDIKATORSASARANPROGRAM['level'],
							'kode' => $rowlevelINDIKATORSASARANPROGRAM['kode'],
							'kode_unique' => $rowlevelINDIKATORSASARANPROGRAM['kode_unique'],
							'textindikator' => $rowlevelINDIKATORSASARANPROGRAM['textindikator'],
							'satuan' => $rowlevelINDIKATORSASARANPROGRAM['satuan'],
							'namasatuan' => $rowlevelINDIKATORSASARANPROGRAM['namasatuan'],
							'output' => $rowlevelINDIKATORSASARANPROGRAM['output'],
							'namaoutput' => $rowlevelINDIKATORSASARANPROGRAM['namaoutput'],
							'outcome' => $rowlevelINDIKATORSASARANPROGRAM['outcome'],
							'namaoutcome' => $rowlevelINDIKATORSASARANPROGRAM['namaoutcome'],
							'penanggungjawab' => $rowlevelINDIKATORSASARANPROGRAM['penanggungjawab'],
							'pelaksana' => $rowlevelINDIKATORSASARANPROGRAM['pelaksana'],
							'pelaksanacode' => $rowlevelINDIKATORSASARANPROGRAM['pelaksanacode'],
							'pelaksanalabel' => $rowlevelINDIKATORSASARANPROGRAM['pelaksanalabel'],
							'target' => $rowlevelINDIKATORSASARANPROGRAM['target'],
							'urut' => (float) $rowlevelINDIKATORSASARANPROGRAM['urut'],
							'hitungan_pk' => $rowlevelINDIKATORSASARANPROGRAM['hitungan_pk'],
							'namahitungan_pk' => $rowlevelINDIKATORSASARANPROGRAM['namahitungan_pk'],
							'targetkumulatif' => 0,
							'output_tahun_berjalan' => 0,
							'target_tahun_berjalan' => 0,
							'class' => $rowlevelINDIKATORSASARANPROGRAM['class'],
						];
						$sqllevelSASARANPROGRAM = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
						,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
						,IF(a.satuan = '0', null, a.satuan) as satuan
						,IF(a.output = '0', null, a.output) as output
						,IF(a.outcome = '0', null, a.outcome) as outcome
						, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
						, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
						, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
						, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
						,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
						, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
						,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
						and zz.kode_satker='" . $kodebalai . "') as terpilih
						,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
						IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
						IF(a.level = 'ISP', 'levelsubsubkegiatan', 
						IF(a.level = 'KEGIATAN', 'levelpaket', 
						IF(a.level = 'SK', 'levelpekerjaan', 
						IF(a.level = 'IKSK', 'levelakhir', 
						IF(a.level = 'KRO', 'levelakhir', 
						IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
						from tb_indikator_akhir a 
						left join master_unor b
						on a.kdunor = b.id
						where a.deleted='0' and a.id_parent = '" . $rowlevelINDIKATORSASARANPROGRAM['id'] . "'
						and a.level = 'SP'
						order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
						//echo $sqllevelSASARANPROGRAM;
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
								if ((float) $rowlevelSASARANPROGRAM['terpilih'] == 0) {
									$html = '
									<table border="1"  cellpadding="2" style="width:100%;" >
									<tr>
									<td align="justify" width="1000px">
									<b>SASARAN PROGRAM: ' . $rowlevelSASARANPROGRAM['textindikator'] . '</b>
									</td>
									</tr>
									</table>
									';
									$pdf->writeHTML($html, true, false, true, false, '');
									$currentY = $pdf->GetY();
									$pdf->SetY($currentY - 4.85);
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
							$sqllevelPROGRAM = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
							,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
							,IF(a.satuan = '0', null, a.satuan) as satuan
							,IF(a.output = '0', null, a.output) as output
							,IF(a.outcome = '0', null, a.outcome) as outcome
							, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
							, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
							, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
							, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
							,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk, a.belakangkoma
							,(select count(zz.id) from tb_data_pk_tidak_cetak_akhir zz where zz.id_indikator=a.id and zz.deleted='0'
							and zz.kode_satker='" . $kodebalai . "') as terpilih
        					,a.kolom1,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.rumuskolom1,a.rumuskolom2,a.rumuskolom3,a.rumuskolom4,a.rumuskolom5,a.isian_kolom
							,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
							IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
							IF(a.level = 'ISP', 'levelsubsubkegiatan', 
							IF(a.level = 'KEGIATAN', 'levelpaket', 
							IF(a.level = 'SK', 'levelpekerjaan', 
							IF(a.level = 'IKSK', 'levelakhir', 
							IF(a.level = 'KRO', 'levelakhir', 
							IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
							from tb_indikator_akhir a 
							left join master_unor b
							on a.kdunor = b.id
							where a.deleted='0' and a.id_parent = '" . $rowlevelSASARANPROGRAM['id'] . "'
							" . $filterqueryiksk . "
							order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";

							//echo $sqllevelPROGRAM;
							//die;
							$resultlevelPROGRAM = mysqli_query($link, $sqllevelPROGRAM);
							while ($rowlevelPROGRAM = mysqli_fetch_assoc($resultlevelPROGRAM)) {
								$searchValue = (float) $rowlevelPROGRAM['id'];
								$found = false;
								foreach ($myArray as $obj) {
									if (isset($obj->id) && $obj->id === $searchValue) {
										$found = true;
										break;
									}
								}
								if (!$found) {
									$belakangkoma = (float) $rowlevelPROGRAM['belakangkoma'];
									$rumuskolom1 = $rowlevelPROGRAM['rumuskolom1'];
									$rumuskolom2 = $rowlevelPROGRAM['rumuskolom2'];
									$rumuskolom3 = $rowlevelPROGRAM['rumuskolom3'];
									$rumuskolom4 = $rowlevelPROGRAM['rumuskolom4'];
									$rumuskolom5 = $rowlevelPROGRAM['rumuskolom5'];
									$targetkumulatif = 0;
									if ($rowlevelPROGRAM['hitungan_pk'] == '1') {
										$baselinekolom1 = 0;
										$baselinekolom2 = 0;
										$baselinekolom3 = 0;
										$baselinekolom4 = 0;
										$baselinekolom5 = 0;
										$sqlbaseline = " select a.id,a.kode_tahun,a.id_indikator,a.id_balai,a.kode_balai,a.kolom1 
										,a.kolom2,a.kolom3,a.kolom4,a.kolom5,a.isian_kolom, b.nama as tahun
										from tb_data_baseline_pk_balai_akhir a 
										left join master_tahun b
										on a.kode_tahun = b.id
										where a.deleted='0' 
										and a.kode_tahun='" . $tahun . "'
										and a.id_indikator = '" . $rowlevelPROGRAM['id'] . "'
										and a.id_balai = '" . $kodebalai . "'
										order by a.id";
										//echo $sqlbaseline;
										//die;
										$resultbaseline = mysqli_query($link, $sqlbaseline);
										$numbaseline = mysqli_num_rows($resultbaseline);
										if ($numbaseline > 0) {
											while ($rowbaseline = mysqli_fetch_assoc($resultbaseline)) {
												$baselinekolom1 = (float) $rowbaseline['kolom1'];
												$baselinekolom2 = (float) $rowbaseline['kolom2'];
												$baselinekolom3 = (float) $rowbaseline['kolom3'];
												$baselinekolom4 = (float) $rowbaseline['kolom4'];
												$baselinekolom5 = (float) $rowbaseline['kolom5'];
											}
										}
										$sqltahun = "select a.tahun_awal,a.tahun_akhir from master_tahun a where a.deleted='0' and a.id = '" . $tahun . "'";
										//echo $sqltahun;
										//die;
										$resulttahun = mysqli_query($link, $sqltahun);
										while ($rowtahun = mysqli_fetch_assoc($resulttahun)) {
											$tahun_awal = (float) $rowtahun['tahun_awal'];
											$tahun_akhir = (float) $rowtahun['tahun_akhir'];
										}
										$arraytahun = array();
										$arraykolom1 = array();
										$arraykolom2 = array();
										$arraykolom3 = array();
										$arraykolom4 = array();
										$arraykolom5 = array();
										$arrayminuskolom1 = array();
										$arrayminuskolom2 = array();
										$arrayminuskolom3 = array();
										$arrayminuskolom4 = array();
										$arrayminuskolom5 = array();

										$arrayisiminuskolom1 = array();
										$arrayisiminuskolom2 = array();
										$arrayisiminuskolom3 = array();
										$arrayisiminuskolom4 = array();
										$arrayisiminuskolom5 = array();
										$s = 0;
										$totalkolom1 = 0;
										$totalkolom2 = 0;
										$totalkolom3 = 0;
										$totalkolom4 = 0;
										$totalkolom5 = 0;
										for ($x = $tahun_awal; $x <= $tahunsekarang; $x++) {
											array_push($arraytahun, $x);
											$myArraydetail = array();
											$sqldata = "select a.id,a.id_balai,a.kode_balai,a.kolom1,a.kolom2,a.kolom3
											,a.kolom4,a.kolom5,a.isian_kolom
											from tb_data_pk_balai_akhir a where a.deleted='0' and a.tahun='" . $x . "' 
											and a.id_balai = '" . $kodebalai . "' and a.id_indikator = '" . $rowlevelPROGRAM['id'] . "'";
											//echo $sqldata;
											//echo $rowlevelPROGRAM['hitungan_pk'];
											//die;
											$resultdata = mysqli_query($link, $sqldata);
											$numdata = mysqli_num_rows($resultdata);
											if ($numdata > 0) {
												if ($resultdata) {
													while ($rowdata = mysqli_fetch_assoc($resultdata)) {
														if ($rowlevelPROGRAM['hitungan_pk'] == '1') {
															//echo $sqldata;
															//die;
															$isikolom1 = 0;
															$isikolom2 = 0;
															$isikolom3 = 0;
															$isikolom4 = 0;
															$isikolom5 = 0;
															#kolom1
															if (strstr($rumuskolom1, "IKSK")) {
																$temprumuskolom1 = explode("+", $rumuskolom1);
																for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom1); $jumlahtambah++) {
																	$sqlcekhitungan = "select id,hitungan_pk from tb_indikator_akhir where deleted = '0'
                                                    				and kode_unique = '" . $temprumuskolom1[$jumlahtambah] . "'";
																	//echo $sqlcekhitungan;
																	//die;
																	$resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
																	while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
																		$hitungan_pkdetail = $rowcekhitungan['hitungan_pk'];
																	}
																	if ($hitungan_pkdetail == '1') {
																		$sqlskortahunsekarang = "select sum(target) as target
																		from tb_data_pk_akhir
																		where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
																		and kdbalai = '" . $kodebalai . "') 
																		and kode_tahun='" . $tahun . "'
																		and id_indikator = (select id from tb_indikator_akhir where deleted='0' and kode_unique = '" . $temprumuskolom1[$jumlahtambah] . "')
																		and tahun = '" . $x . "'";
																		//echo $sqlskortahunsekarang;
																		//die;
																		$resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
																		$numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
																		if ($numskortahunsekarang > 0) {
																			while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
																				$target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
																			}
																		} else {
																			$target_tahun_berjalan = 0;
																		}
																	}
																	$isikolom1 = $isikolom1 + $target_tahun_berjalan;
																	//echo $isikolom1;
																	//echo $rumuskolom1;
																}
															} else if (strstr($rumuskolom1, "ambildatasebelum")) {
																$ambildatasebelum = 0;
																if ($x == $tahun_awal) {
																	array_push($arraykolom1, (float) $baselinekolom1);
																	$ambildatasebelum = ((float) $baselinekolom1);
																} else {
																	array_push($arraykolom1, (float) $rowdata['kolom1'] + (float) $arraykolom1[$s - 1]);
																	$ambildatasebelum = ((float) $rowdata['kolom1'] + (float) $arraykolom1[$s - 1]);
																}
																$cekrumuskolom1 = str_replace("ambildatasebelumbaseline1", $ambildatasebelum, $rumuskolom1);
																//echo $cekrumuskolom1."<br>";
																$tempformula_string5 = explode("/", $cekrumuskolom1);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom1 = str_replace("0/0", "0", $cekrumuskolom1);
																		$cekrumuskolom1 = str_replace("0/(0+0)", "0", $cekrumuskolom1);
																		$cekrumuskolom1 = str_replace("/0", "*0", $cekrumuskolom1);
																	}
																}
																try {
																	eval('$isikolom1 = ' . $cekrumuskolom1 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom1 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom1 = 0;');
																}
															} else if (strstr($rumuskolom1, "given")) {
																$cekrumuskolom1 = $rumuskolom1;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom1 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom1);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom1);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom1 = str_replace("0/0", "0", $cekrumuskolom1);
																		$cekrumuskolom1 = str_replace("0/(0+0)", "0", $cekrumuskolom1);
																		$cekrumuskolom1 = str_replace("/0", "*0", $cekrumuskolom1);
																	}
																}
																try {
																	eval('$isikolom1 = ' . $cekrumuskolom1 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom1 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom1 = 0;');
																}
															} else if (strstr($rumuskolom1, "isikolom")) {
																$cekrumuskolom1 = $rumuskolom1;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom1 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom1);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom1);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom1 = str_replace("0/0", "0", $cekrumuskolom1);
																		$cekrumuskolom1 = str_replace("0/(0+0)", "0", $cekrumuskolom1);
																		$cekrumuskolom1 = str_replace("/0", "*0", $cekrumuskolom1);
																	}
																}
																try {
																	eval('$isikolom1 = ' . $cekrumuskolom1 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom1 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom1 = 0;');
																}
																//echo $isikolom1."<br>";
															} else if (strstr($rumuskolom1, "totalkolom")) {
																$cekrumuskolom1 = $rumuskolom1;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom1 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom1);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom1);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom1 = str_replace("0/0", "0", $cekrumuskolom1);
																		$cekrumuskolom1 = str_replace("0/(0+0)", "0", $cekrumuskolom1);
																		$cekrumuskolom1 = str_replace("/0", "*0", $cekrumuskolom1);
																	}
																}
																try {
																	eval('$isikolom1 = ' . $cekrumuskolom1 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom1 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom1 = 0;');
																}
																//echo $isikolom1."<br>";
															} else {
																$isikolom1 = (float) $rowdata['kolom1'];
															}
															$totalkolom1 = $totalkolom1 + (float) $isikolom1;
															array_push($arrayminuskolom1, (float) $isikolom1);
															#kolom1

															#kolom2
															if (strstr($rumuskolom2, "IKSK")) {
																$temprumuskolom2 = explode("+", $rumuskolom2);
																for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom2); $jumlahtambah++) {
																	$sqlcekhitungan = "select id,hitungan_pk from tb_indikator_akhir where deleted = '0'
                                                    				and kode_unique = '" . $temprumuskolom2[$jumlahtambah] . "'";
																	$resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
																	while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
																		$hitungan_pkdetail = $rowcekhitungan['hitungan_pk'];
																	}
																	if ($hitungan_pkdetail == '1') {
																		$sqlskortahunsekarang = "select sum(target) as target
																		from tb_data_pk_akhir
																		where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
																		and kdbalai = '" . $kodebalai . "') 
																		and kode_tahun='" . $tahun . "'
																		and id_indikator = (select id from tb_indikator_akhir where deleted='0' and kode_unique = '" . $temprumuskolom2[$jumlahtambah] . "')
																		and tahun = '" . $x . "'";
																		//echo $sqlskortahunsekarang;
																		$resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
																		$numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
																		if ($numskortahunsekarang > 0) {
																			while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
																				$target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
																			}
																		} else {
																			$target_tahun_berjalan = 0;
																		}
																	}
																	$isikolom2 = $isikolom2 + $target_tahun_berjalan;
																}
															} else if (strstr($rumuskolom2, "ambildatasebelum")) {
																$ambildatasebelum = 0;
																if ($x == $tahun_awal) {
																	array_push($arraykolom2, (float) $baselinekolom2);
																	$ambildatasebelum = ((float) $baselinekolom2);
																} else {
																	array_push($arraykolom2, (float) $rowdata['kolom2'] + (float) $arraykolom2[$s - 1]);
																	$ambildatasebelum = ((float) $rowdata['kolom2'] + (float) $arraykolom2[$s - 1]);
																}
																$cekrumuskolom2 = str_replace("ambildatasebelumbaseline2", $ambildatasebelum, $rumuskolom2);
																//echo $cekrumuskolom2."<br>";
																$tempformula_string5 = explode("/", $cekrumuskolom2);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom2 = str_replace("0/0", "0", $cekrumuskolom2);
																		$cekrumuskolom2 = str_replace("0/(0+0)", "0", $cekrumuskolom2);
																		$cekrumuskolom2 = str_replace("/0", "*0", $cekrumuskolom2);
																	}
																}
																try {
																	eval('$isikolom2 = ' . $cekrumuskolom2 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom2 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom2 = 0;');
																}
															} else if (strstr($rumuskolom2, "given")) {
																$cekrumuskolom2 = $rumuskolom2;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom2 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom2);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom2);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom2 = str_replace("0/0", "0", $cekrumuskolom2);
																		$cekrumuskolom2 = str_replace("0/(0+0)", "0", $cekrumuskolom2);
																		$cekrumuskolom2 = str_replace("/0", "*0", $cekrumuskolom2);
																	}
																}
																try {
																	eval('$isikolom2 = ' . $cekrumuskolom2 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom2 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom2 = 0;');
																}
															} else if (strstr($rumuskolom2, "isikolom")) {
																$cekrumuskolom2 = $rumuskolom2;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom2 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom2);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom2);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom2 = str_replace("0/0", "0", $cekrumuskolom2);
																		$cekrumuskolom2 = str_replace("0/(0+0)", "0", $cekrumuskolom2);
																		$cekrumuskolom2 = str_replace("/0", "*0", $cekrumuskolom2);
																	}
																}
																try {
																	eval('$isikolom2 = ' . $cekrumuskolom2 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom2 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom2 = 0;');
																}
																//echo $isikolom2."<br>";
															} else if (strstr($rumuskolom2, "totalkolom")) {
																$cekrumuskolom2 = $rumuskolom2;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom2 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom2);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom2);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom2 = str_replace("0/0", "0", $cekrumuskolom2);
																		$cekrumuskolom2 = str_replace("0/(0+0)", "0", $cekrumuskolom2);
																		$cekrumuskolom2 = str_replace("/0", "*0", $cekrumuskolom2);
																	}
																}
																try {
																	eval('$isikolom2 = ' . $cekrumuskolom2 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom2 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom2 = 0;');
																}
																//echo $isikolom2."<br>";
															} else {
																$isikolom2 = (float) $rowdata['kolom2'];
															}
															$totalkolom2 = $totalkolom2 + (float) $isikolom2;
															array_push($arrayminuskolom2, (float) $isikolom2);
															#kolom2

															#kolom3
															if (strstr($rumuskolom3, "IKSK")) {
																$temprumuskolom3 = explode("+", $rumuskolom3);
																for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom3); $jumlahtambah++) {
																	$sqlcekhitungan = "select id,hitungan_pk from tb_indikator_akhir where deleted = '0'
                                                    				and kode_unique = '" . $temprumuskolom3[$jumlahtambah] . "'";
																	$resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
																	while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
																		$hitungan_pkdetail = $rowcekhitungan['hitungan_pk'];
																	}
																	if ($hitungan_pkdetail == '1') {
																		$sqlskortahunsekarang = "select sum(target) as target
																		from tb_data_pk_akhir
																		where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
																		and kdbalai = '" . $kodebalai . "') 
																		and kode_tahun='" . $tahun . "'
																		and id_indikator = (select id from tb_indikator_akhir where deleted='0' and kode_unique = '" . $temprumuskolom3[$jumlahtambah] . "')
																		and tahun = '" . $x . "'";
																		//echo $sqlskortahunsekarang;
																		$resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
																		$numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
																		if ($numskortahunsekarang > 0) {
																			while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
																				$target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
																			}
																		} else {
																			$target_tahun_berjalan = 0;
																		}
																	}
																	$isikolom3 = $isikolom3 + $target_tahun_berjalan;
																}
															} else if (strstr($rumuskolom3, "ambildatasebelum")) {
																$ambildatasebelum = 0;
																if ($x == $tahun_awal) {
																	array_push($arraykolom3, (float) $baselinekolom3);
																	$ambildatasebelum = ((float) $baselinekolom3);
																} else {
																	array_push($arraykolom3, (float) $rowdata['kolom3'] + (float) $arraykolom3[$s - 1]);
																	$ambildatasebelum = ((float) $rowdata['kolom3'] + (float) $arraykolom3[$s - 1]);
																}
																$cekrumuskolom3 = str_replace("ambildatasebelumbaseline3", $ambildatasebelum, $rumuskolom3);
																//echo $cekrumuskolom3."<br>";
																$tempformula_string5 = explode("/", $cekrumuskolom3);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom3 = str_replace("0/0", "0", $cekrumuskolom3);
																		$cekrumuskolom3 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
																		$cekrumuskolom3 = str_replace("/0", "*0", $cekrumuskolom3);
																	}
																}
																try {
																	eval('$isikolom3 = ' . $cekrumuskolom3 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom3 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom3 = 0;');
																}
															} else if (strstr($rumuskolom3, "given")) {
																$cekrumuskolom3 = $rumuskolom3;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom3 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom3);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom3);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom3 = str_replace("0/0", "0", $cekrumuskolom3);
																		$cekrumuskolom3 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
																		$cekrumuskolom3 = str_replace("/0", "*0", $cekrumuskolom3);
																	}
																}
																try {
																	eval('$isikolom3 = ' . $cekrumuskolom3 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom3 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom3 = 0;');
																}
															} else if (strstr($rumuskolom3, "isikolom")) {
																$cekrumuskolom3 = $rumuskolom3;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom3 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom3);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom3);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom3 = str_replace("0/0", "0", $cekrumuskolom3);
																		$cekrumuskolom3 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
																		$cekrumuskolom3 = str_replace("/0", "*0", $cekrumuskolom3);
																	}
																}
																try {
																	eval('$isikolom3 = ' . $cekrumuskolom3 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom3 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom3 = 0;');
																}
																//echo $isikolom3."<br>";
															} else if (strstr($rumuskolom3, "totalkolom")) {
																$cekrumuskolom3 = $rumuskolom3;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom3 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom3);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom3);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom3 = str_replace("0/0", "0", $cekrumuskolom3);
																		$cekrumuskolom3 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
																		$cekrumuskolom3 = str_replace("/0", "*0", $cekrumuskolom3);
																	}
																}
																try {
																	eval('$isikolom3 = ' . $cekrumuskolom3 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom3 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom3 = 0;');
																}
																//echo $isikolom3."<br>";
															} else {
																$isikolom3 = (float) $rowdata['kolom3'];
															}
															$totalkolom3 = $totalkolom3 + (float) $isikolom3;
															array_push($arrayminuskolom3, (float) $isikolom3);
															#kolom3

															#kolom4
															if (strstr($rumuskolom4, "IKSK")) {
																$temprumuskolom4 = explode("+", $rumuskolom4);
																for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom4); $jumlahtambah++) {
																	$sqlcekhitungan = "select id,hitungan_pk from tb_indikator_akhir where deleted = '0'
                                                    				and kode_unique = '" . $temprumuskolom4[$jumlahtambah] . "'";
																	$resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
																	while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
																		$hitungan_pkdetail = $rowcekhitungan['hitungan_pk'];
																	}
																	if ($hitungan_pkdetail == '1') {
																		$sqlskortahunsekarang = "select sum(target) as target
																		from tb_data_pk_akhir
																		where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
																		and kdbalai = '" . $kodebalai . "') 
																		and kode_tahun='" . $tahun . "'
																		and id_indikator = (select id from tb_indikator_akhir where deleted='0' and kode_unique = '" . $temprumuskolom4[$jumlahtambah] . "')
																		and tahun = '" . $x . "'";
																		//echo $sqlskortahunsekarang;
																		$resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
																		$numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
																		if ($numskortahunsekarang > 0) {
																			while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
																				$target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
																			}
																		} else {
																			$target_tahun_berjalan = 0;
																		}
																	}
																	$isikolom4 = $isikolom4 + $target_tahun_berjalan;
																}
															} else if (strstr($rumuskolom4, "ambildatasebelum")) {
																$ambildatasebelum = 0;
																if ($x == $tahun_awal) {
																	array_push($arraykolom4, (float) $baselinekolom4);
																	$ambildatasebelum = ((float) $baselinekolom4);
																} else {
																	array_push($arraykolom4, (float) $rowdata['kolom4'] + (float) $arraykolom4[$s - 1]);
																	$ambildatasebelum = ((float) $rowdata['kolom4'] + (float) $arraykolom4[$s - 1]);
																}
																$cekrumuskolom4 = str_replace("ambildatasebelumbaseline4", $ambildatasebelum, $rumuskolom4);
																//echo $cekrumuskolom4."<br>";
																$tempformula_string5 = explode("/", $cekrumuskolom4);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom4 = str_replace("0/0", "0", $cekrumuskolom4);
																		$cekrumuskolom4 = str_replace("0/(0+0)", "0", $cekrumuskolom3);
																		$cekrumuskolom4 = str_replace("/0", "*0", $cekrumuskolom4);
																	}
																}
																try {
																	eval('$isikolom4 = ' . $cekrumuskolom4 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom4 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom4 = 0;');
																}
															} else if (strstr($rumuskolom4, "given")) {
																$cekrumuskolom4 = $rumuskolom4;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom4 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom4);
																}
																try {
																	eval('$isikolom4 = ' . $cekrumuskolom4 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom4 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom4 = 0;');
																}
															} else if (strstr($rumuskolom4, "isikolom")) {
																$cekrumuskolom4 = $rumuskolom4;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom4 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom4);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom4);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom4 = str_replace("0/0", "0", $cekrumuskolom4);
																		$cekrumuskolom4 = str_replace("0/(0+0)", "0", $cekrumuskolom4);
																		$cekrumuskolom4 = str_replace("/0", "*0", $cekrumuskolom4);
																	}
																}
																try {
																	eval('$isikolom4 = ' . $cekrumuskolom4 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom4 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom4 = 0;');
																}
																//echo $isikolom4."<br>";
															} else if (strstr($rumuskolom4, "totalkolom")) {
																$cekrumuskolom4 = $rumuskolom4;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom4 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom4);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom4);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom4 = str_replace("0/0", "0", $cekrumuskolom4);
																		$cekrumuskolom4 = str_replace("0/(0+0)", "0", $cekrumuskolom4);
																		$cekrumuskolom4 = str_replace("/0", "*0", $cekrumuskolom4);
																	}
																}
																try {
																	eval('$isikolom4 = ' . $cekrumuskolom4 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom4 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom4 = 0;');
																}
																//echo $isikolom4."<br>";
															} else {
																$isikolom4 = (float) $rowdata['kolom4'];
															}
															$totalkolom4 = $totalkolom4 + (float) $isikolom4;
															array_push($arrayminuskolom4, (float) $isikolom4);
															#kolom4

															#kolom5
															if (strstr($rumuskolom5, "IKSK")) {
																$temprumuskolom5 = explode("+", $rumuskolom5);
																for ($jumlahtambah = 0; $jumlahtambah < count($temprumuskolom5); $jumlahtambah++) {
																	$sqlcekhitungan = "select id,hitungan_pk from tb_indikator_akhir where deleted = '0'
                                                    				and kode_unique = '" . $temprumuskolom5[$jumlahtambah] . "'";
																	$resultcekhitungan = mysqli_query($link, $sqlcekhitungan);
																	while ($rowcekhitungan = mysqli_fetch_assoc($resultcekhitungan)) {
																		$hitungan_pkdetail = $rowcekhitungan['hitungan_pk'];
																	}
																	if ($hitungan_pkdetail == '1') {
																		$sqlskortahunsekarang = "select sum(target) as target
																		from tb_data_pk_akhir
																		where deleted = '0' and kode_satker in (select kode_satker from master_satker where deleted='0'
																		and kdbalai = '" . $kodebalai . "') 
																		and kode_tahun='" . $tahun . "'
																		and id_indikator = (select id from tb_indikator_akhir where deleted='0' and kode_unique = '" . $temprumuskolom5[$jumlahtambah] . "')
																		and tahun = '" . $x . "'";
																		//echo $sqlskortahunsekarang;
																		$resultskortahunsekarang = mysqli_query($link, $sqlskortahunsekarang);
																		$numskortahunsekarang = mysqli_num_rows($resultskortahunsekarang);
																		if ($numskortahunsekarang > 0) {
																			while ($rowskortahunsekarang = mysqli_fetch_assoc($resultskortahunsekarang)) {
																				$target_tahun_berjalan = (float) $rowskortahunsekarang['target'];
																			}
																		} else {
																			$target_tahun_berjalan = 0;
																		}
																	}
																	$isikolom5 = $isikolom5 + $target_tahun_berjalan;
																}
															} else if (strstr($rumuskolom5, "ambildatasebelum")) {
																$ambildatasebelum = 0;
																if ($x == $tahun_awal) {
																	array_push($arraykolom5, (float) $baselinekolom5);
																	$ambildatasebelum = ((float) $baselinekolom5);
																} else {
																	array_push($arraykolom5, (float) $rowdata['kolom5'] + (float) $arraykolom5[$s - 1]);
																	$ambildatasebelum = ((float) $rowdata['kolom5'] + (float) $arraykolom5[$s - 1]);
																}
																$cekrumuskolom5 = str_replace("ambildatasebelumbaseline5", $ambildatasebelum, $rumuskolom5);
																//echo $cekrumuskolom5."<br>";
																$tempformula_string5 = explode("/", $cekrumuskolom5);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																		$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																		$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																	}
																}
																try {
																	eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom5 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom5 = 0;');
																}
															} else if (strstr($rumuskolom5, "ambildatamin")) {
																$cekrumuskolom5 = $rumuskolom5;
																if (strstr($rumuskolom5, "ambildatamin1")) {
																	$ambildatasebelum1 = 0;
																	if ($x == $tahun_awal) {
																		array_push($arrayisiminuskolom1, (float) $baselinekolom1);
																		$ambildatasebelum1 = ((float) $baselinekolom1);
																	} else {
																		array_push($arrayisiminuskolom1, (float) $arrayminuskolom1[$s - 1]);
																		$ambildatasebelum1 = ((float) $arrayminuskolom1[$s - 1]);
																	}
																	$cekrumuskolom5 = str_replace("ambildatamin1", $ambildatasebelum1, $cekrumuskolom5);
																	//echo $cekrumuskolom5."<br>";
																	$tempformula_string5 = explode("/", $cekrumuskolom5);
																	for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																		if ((float) $tempformula_string5[$ceknol] == 0) {
																			$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																		}
																	}
																}
																if (strstr($rumuskolom5, "ambildatamin2")) {
																	$ambildatasebelum2 = 0;
																	if ($x == $tahun_awal) {
																		array_push($arrayisiminuskolom2, (float) $baselinekolom2);
																		$ambildatasebelum2 = ((float) $baselinekolom2);
																	} else {
																		array_push($arrayisiminuskolom2, (float) $arrayminuskolom2[$s - 1]);
																		$ambildatasebelum2 = ((float) $arrayminuskolom2[$s - 1]);
																	}
																	$cekrumuskolom5 = str_replace("ambildatamin2", $ambildatasebelum2, $cekrumuskolom5);
																	//echo $cekrumuskolom5."<br>";
																	$tempformula_string5 = explode("/", $cekrumuskolom5);
																	for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																		if ((float) $tempformula_string5[$ceknol] == 0) {
																			$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																		}
																	}
																}
																if (strstr($rumuskolom5, "ambildatamin3")) {
																	$ambildatasebelum3 = 0;
																	if ($x == $tahun_awal) {
																		array_push($arrayisiminuskolom3, (float) $baselinekolom3);
																		$ambildatasebelum3 = ((float) $baselinekolom3);
																	} else {
																		array_push($arrayisiminuskolom3, (float) $arrayminuskolom3[$s - 1]);
																		$ambildatasebelum3 = ((float) $arrayminuskolom3[$s - 1]);
																	}
																	$cekrumuskolom5 = str_replace("ambildatamin3", $ambildatasebelum3, $cekrumuskolom5);
																	//echo $cekrumuskolom5."<br>";
																	$tempformula_string5 = explode("/", $cekrumuskolom5);
																	for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																		if ((float) $tempformula_string5[$ceknol] == 0) {
																			$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																		}
																	}
																}
																if (strstr($rumuskolom5, "ambildatamin4")) {
																	$ambildatasebelum4 = 0;
																	if ($x == $tahun_awal) {
																		array_push($arrayisiminuskolom4, (float) $baselinekolom4);
																		$ambildatasebelum4 = ((float) $baselinekolom4);
																	} else {
																		array_push($arrayisiminuskolom4, (float) $arrayminuskolom4[$s - 1]);
																		$ambildatasebelum4 = ((float) $arrayminuskolom4[$s - 1]);
																	}
																	$cekrumuskolom5 = str_replace("ambildatamin4", $ambildatasebelum4, $cekrumuskolom5);
																	//echo $cekrumuskolom5."<br>";
																	$tempformula_string5 = explode("/", $cekrumuskolom5);
																	for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																		if ((float) $tempformula_string5[$ceknol] == 0) {
																			$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																		}
																	}
																}
																if (strstr($rumuskolom5, "ambildatamin5")) {
																	$ambildatasebelum5 = 0;
																	if ($x == $tahun_awal) {
																		array_push($arrayisiminuskolom5, (float) $baselinekolom5);
																		$ambildatasebelum5 = ((float) $baselinekolom5);
																	} else {
																		array_push($arrayisiminuskolom5, (float) $arrayminuskolom5[$s - 1]);
																		$ambildatasebelum5 = ((float) $arrayminuskolom5[$s - 1]);
																	}
																	$cekrumuskolom5 = str_replace("ambildatamin5", $ambildatasebelum5, $cekrumuskolom5);
																	//echo $cekrumuskolom5."<br>";
																	$tempformula_string5 = explode("/", $cekrumuskolom5);
																	for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																		if ((float) $tempformula_string5[$ceknol] == 0) {
																			$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																			$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																		}
																	}
																}

																try {
																	eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom5 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom5 = 0;');
																}
															} else if (strstr($rumuskolom5, "given")) {
																$cekrumuskolom5 = $rumuskolom5;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom5 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom5);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom5);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																		$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																		$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																	}
																}
																try {
																	eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom5 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom5 = 0;');
																}
															} else if (strstr($rumuskolom5, "isikolom")) {
																$cekrumuskolom5 = $rumuskolom5;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom5 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom5);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom5);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	//echo $tempformula_string5[$ceknol]."<br>";
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																		$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																		$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																	}
																}
																//echo $cekrumuskolom5."<br>";
																try {
																	eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom5 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom5 = 0;');
																}
																//echo $isikolom5."<br>";
															} else if (strstr($rumuskolom5, "totalkolom")) {
																$cekrumuskolom5 = $rumuskolom5;
																for ($paramameter = 1; $paramameter < 10; $paramameter++) {
																	$cekrumuskolom5 = str_replace("given" . $paramameter, (float) $rowdata['kolom' . $paramameter], $cekrumuskolom5);
																}
																$tempformula_string5 = explode("/", $cekrumuskolom5);
																for ($ceknol = 0; $ceknol < count($tempformula_string5); $ceknol++) {
																	//echo $tempformula_string5[$ceknol]."<br>";
																	if ((float) $tempformula_string5[$ceknol] == 0) {
																		$cekrumuskolom5 = str_replace("0/0", "0", $cekrumuskolom5);
																		$cekrumuskolom5 = str_replace("0/(0+0)", "0", $cekrumuskolom5);
																		$cekrumuskolom5 = str_replace("/0", "*0", $cekrumuskolom5);
																	}
																}
																//echo $cekrumuskolom5."<br>";
																try {
																	eval('$isikolom5 = ' . $cekrumuskolom5 . ';');
																} catch (DivisionByZeroError $e) {
																	eval('$isikolom5 = 0;');
																} catch (ParseError $e) {
																	eval('$isikolom5 = 0;');
																}
																//echo $isikolom5."<br>";
															} else {
																$isikolom5 = (float) $rowdata['kolom5'];
															}
															$totalkolom5 = $totalkolom5 + (float) $isikolom5;
															array_push($arrayminuskolom5, (float) $isikolom5);
															#kolom5
															//echo $isikolom5."<br>";
															//echo $totalkolom1."<br>";
														}
													}
												}
											}
											if ($rumuskolom5 <> '') {
												$targetkumulatif = $isikolom5;
											} else {
												if ($rumuskolom4 <> '') {
													$targetkumulatif = $isikolom4;
												} else {
													if ($rumuskolom3 <> '') {
														$targetkumulatif = $isikolom3;
													} else {
														if ($rumuskolom2 <> '') {
															$targetkumulatif = $isikolom2;
														} else {
															$targetkumulatif = $isikolom1;
														}
													}
												}
											}
											//echo $belakangkoma;
											//die;
											$target = $targetkumulatif;
											//$target = number_format($targetkumulatif, $belakangkoma, ",", ".");
										}
										$s++;
									}
									if ($rowlevelPROGRAM['hitungan_pk'] == '2' || $rowlevelPROGRAM['hitungan_pk'] == '3') {
										$sqlskor = "select nilai,rumus
										from tb_data_pk_balai_akhir
										where deleted = '0' and id_balai = '" . $kodebalai . "' and kode_tahun='" . $tahun . "'
										and tahun = '" . $tahunsekarang . "'
										and id_indikator = '" . $rowlevelPROGRAM['id'] . "'";
										//echo $sqlskor;
										//die;
										$resultskor = mysqli_query($link, $sqlskor);
										$numskor = mysqli_num_rows($resultskor);
										if ($numskor > 0) {
											while ($rowskor = mysqli_fetch_assoc($resultskor)) {
												$nilai = $rowskor['nilai'];
												$rumus = $rowskor['rumus'];
												$tempnilai = explode("|", $nilai);

												for ($s = 0; $s < 10; $s++) {
													$rumus = str_replace("input" . $s, (float) ($tempnilai[$s]), $rumus);
												}
												//echo $rumus;
												for ($s = 0; $s < 10; $s++) {
													$rumus = str_replace("nilai" . $s, (float) ($tempnilai[$s]), $rumus);
												}

												for ($s = 10; $s < 21; $s++) {
													$rumus = str_replace("entry" . $s, (float) ($tempnilai[$s]), $rumus);
												}

												for ($s = 10; $s < 21; $s++) {
													$rumus = str_replace("rumus" . $s, (float) ($tempnilai[$s]), $rumus);
												}


												//echo $rumus."<br>";
												$rumus = str_replace("||", "|0|", $rumus);
												$tempnilairumus = explode("|", $rumus);
												$finalrumus = $tempnilairumus[count($tempnilairumus) - 1];

												//$finalrumus = str_replace("/0", "*0", $finalrumus);
												$tempformula_string5 = explode("/", $finalrumus);
												for ($s = 0; $s < count($tempformula_string5); $s++) {
													if ((float) $tempformula_string5[$s] == 0) {
														$finalrumus = str_replace("0/0", "0", $finalrumus);
														$finalrumus = str_replace("0/(0+0)", "0", $finalrumus);
														$finalrumus = str_replace("/0", "*0", $finalrumus);
													}
												}

												//echo $finalrumus . "<br>" . $row['id'] . "<aa>" . $rumus . "<bb>";
												//die;
												try {
													eval('$nilaisekarang = ' . $finalrumus . ';');
												} catch (DivisionByZeroError $e) {
													eval('$nilaisekarang = 0;');
												} catch (ParseError $e) {
													eval('$nilaisekarang = 0;');
												}
												//die;
												//echo $nilaisekarang."<br>";
											}
											$targetkumulatif = $nilaisekarang;
											$target = $targetkumulatif;
											//echo $target."<br>";
										} else {
											$targetkumulatif = 0;
											$target = 0;
											$output_tahun_berjalan = 0;
											$target_tahun_berjalan = 0;
										}
									}
									$capaian = 0;
									$kinerja = 0;
									$sqlcapaian = "select capaian
									from tb_data_pk_balai_akhir
									where deleted = '0' and id_balai = '" . $kodebalai . "' and kode_tahun='" . $tahun . "'
									and tahun = '" . $tahunsekarang . "'
									and id_indikator = '" . $rowlevelPROGRAM['id'] . "'";
									$resultcapaian = mysqli_query($link, $sqlcapaian);
									$numcapaian = mysqli_num_rows($resultcapaian);
									if ($numcapaian > 0) {
										while ($rowcapaian = mysqli_fetch_assoc($resultcapaian)) {
											$capaian = $rowcapaian['capaian'];
										}
									}

									if ($target == '0') {
										$kinerja = 0;
									} else {
										$kinerja = ($capaian / $target) * 100;
									}

									if (number_format($capaian, $belakangkoma, ",", ".") == number_format($target, $belakangkoma, ",", ".")) {
										$kinerja = 100;
									}

									if ((float) $rowlevelPROGRAM['terpilih'] == 0) {
										$currentY = $pdf->GetY();
										$html = '
										<table border="1"  cellpadding="2" style="width:100%;" >
										<tr>
										<td align="justify" width="500px">
										' . $rowlevelPROGRAM['textindikator'] . ' 
										</td>
										<td align="center" width="100px">
										' . number_format($target, $belakangkoma, ",", ".") . '
										</td>
										<td align="center" width="100px">
										' . $rowlevelPROGRAM['namaoutcome'] . '
										</td>
										<td align="center" width="100px">
										' . number_format($capaian, $belakangkoma, ",", ".") . '
										</td>
										<td align="center" width="100px">
										' . $rowlevelPROGRAM['namaoutcome'] . '
										</td>
										<td align="center" width="100px">
										' . number_format($kinerja, 2, ",", ".") . '
										</td>
										</tr>
										</table>
										';
										$pdf->writeHTML($html, true, false, true, false, '');
										$currentY = $pdf->GetY();
										$pdf->SetY($currentY - 4.95);
									}

									$myArray[] = (object)
									[
										'id' => (float) $rowlevelPROGRAM['id'],
										'tahun' => (float) $rowlevelPROGRAM['tahun'],
										'id_parent' => $rowlevelPROGRAM['id_parent'],
										'kdunor' => $rowlevelPROGRAM['kdunor'],
										'nama_unor' => $rowlevelPROGRAM['nama_unor'],
										'urutlevel' => (float) $rowlevelPROGRAM['urutlevel'],
										'level' => $rowlevelPROGRAM['level'],
										'kode' => $rowlevelPROGRAM['kode'],
										'kode_unique' => $rowlevelPROGRAM['kode_unique'],
										'textindikator' => $rowlevelPROGRAM['textindikator'],
										'satuan' => $rowlevelPROGRAM['satuan'],
										'namasatuan' => $rowlevelPROGRAM['namasatuan'],
										'output' => $rowlevelPROGRAM['output'],
										'namaoutput' => $rowlevelPROGRAM['namaoutput'],
										'outcome' => $rowlevelPROGRAM['outcome'],
										'namaoutcome' => $rowlevelPROGRAM['namaoutcome'],
										'penanggungjawab' => $rowlevelPROGRAM['penanggungjawab'],
										'pelaksana' => $rowlevelPROGRAM['pelaksana'],
										'pelaksanacode' => $rowlevelPROGRAM['pelaksanacode'],
										'pelaksanalabel' => $rowlevelPROGRAM['pelaksanalabel'],
										'target' => $rowlevelPROGRAM['target'],
										'urut' => (float) $rowlevelPROGRAM['urut'],
										'hitungan_pk' => $rowlevelPROGRAM['hitungan_pk'],
										'namahitungan_pk' => $rowlevelPROGRAM['namahitungan_pk'],
										'targetkumulatif' => 0,
										'output_tahun_berjalan' => 0,
										'target_tahun_berjalan' => 0,
										'class' => $rowlevelPROGRAM['class'],
									];
								}
							}
						}
					}
				}
				// KEGIATAN			
			}

			if ($row['hitungan_pk'] == '1') {
				$target = number_format($target, 0, ",", ".");
				$output_tahun_berjalan = number_format($output_tahun_berjalan, 0, ",", ".");
				$target_tahun_berjalan = number_format($target_tahun_berjalan, 0, ",", ".");
			} else {
				$target = number_format($target, 2, ",", ".");
				$output_tahun_berjalan = number_format($output_tahun_berjalan, 2, ",", ".");
				$target_tahun_berjalan = number_format($target_tahun_berjalan, 2, ",", ".");
			}
		}
	}
}

$currentY = $pdf->GetY();
$pdf->SetFont('dejavusans', '', 8);
$html = '<tr>
<td align="left" width="1000px">
<i>Penetapan berita acara ini bersifat mengikat sebagai acuan data pencapaian kinerja Direktorat Jenderal Sumber Daya Air Tahun ' . $tahunsekarang . '</i>
</td>
</tr>';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->SetFont('helvetica', '', 11);
$html = '
<tr>
<td align="center" width="300px">
<b>Mengetahui</b>
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
<b>' . ucfirst($lokasi_pejabat) . ', ' . tgl_indoku(getLastWorkingDayOfYear(get_Isi_Field1('tahun','master_pk','id','3'))) . '</b>
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

$pdf->AddPage('L', $resolution);
$pdf->SetFont('helvetica', '', 11);
$currentY = $pdf->GetY();
$html = '
<table border="0"  cellpadding="2" style="width:100%;" >
<thead>
';
$html = $html . '	
</thead>  
<tbody>
<tr>
<td align="center" width="1000px">	
<b>BERITA ACARA KESEPAKATAN CAPAIAN KINERJA ' . $tahunsekarang . '</b>
</td>
</tr>
<tr>
<td align="center" width="1000px">	
<b>' . $nama_balai . ' - DIREKTORAT JENDERAL SUMBER DAYA AIR</b>
</td>
</tr>
<tr>
<td align="center" width="1000px">	
</td>
</tr>
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 5);
$pdf->SetFont('dejavusans', '', 9);
$html = '

<table border="0"  cellpadding="2" style="width:100%;" >
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
$pdf->SetFont('dejavusans', '', 8);
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 5);
$html = '
<table border="1"  cellpadding="2" style="width:100%;" >
';
$html = $html . '
<tr>
<td align="justify" width="240px" style="background-color: #D3D3D3;" rowspan="2">
<b>SASARAN PROGRAM / INDIKATOR SASARAN PROGRAM</b>
</td>
<td align="center" width="160px" style="background-color: #D3D3D3;">
<b>OUTPUT<br>(Tahun Berjalan)</b>
</td>
<td align="center" width="160px" style="background-color: #D3D3D3;">
<b>CAPAIAN OUTPUT</b>
</td>
<td align="center" width="60px" style="background-color: #D3D3D3;">
<b>KINERJA</b>
</td>
<td align="center" width="160px" style="background-color: #D3D3D3;">
<b>OUTCOME<br>(Tahun Berjalan)</b>
</td>
<td align="center" width="160px" style="background-color: #D3D3D3;">
<b>CAPAIAN OUTCOME</b>
</td>
<td align="center" width="60px" style="background-color: #D3D3D3;">
<b>KINERJA</b>
</td>
</tr>
<tr>
<td align="center" width="80px" style="background-color: #D3D3D3;">
<b>Volume</b>
</td>
<td align="center" width="80px" style="background-color: #D3D3D3;">
<b>Satuan</b>
</td>
<td align="center" width="80px" style="background-color: #D3D3D3;">
<b>Volume</b>
</td>
<td align="center" width="80px" style="background-color: #D3D3D3;">
<b>Satuan</b>
</td>
<td align="center" width="60px" style="background-color: #D3D3D3;">
<b>%</b>
</td>
<td align="center" width="80px" style="background-color: #D3D3D3;">
<b>Volume</b>
</td>
<td align="center" width="80px" style="background-color: #D3D3D3;">
<b>Satuan</b>
</td>
<td align="center" width="80px" style="background-color: #D3D3D3;">
<b>Volume</b>
</td>
<td align="center" width="80px" style="background-color: #D3D3D3;">
<b>Satuan</b>
</td>
<td align="center" width="60px" style="background-color: #D3D3D3;">
<b>%</b>
</td>
</tr>
';
$html = $html . '
</table>
';
$pdf->writeHTML($html, true, false, true, false, '');
$currentY = $pdf->GetY();
$pdf->SetY($currentY - 5);
$sqlPROGRAMLAMPIRAN2 = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
,IF(a.satuan = '0', null, a.satuan) as satuan
,IF(a.output = '0', null, a.output) as output
,IF(a.outcome = '0', null, a.outcome) as outcome
, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
IF(a.level = 'ISP', 'levelsubsubkegiatan', 
IF(a.level = 'KEGIATAN', 'levelpaket', 
IF(a.level = 'SK', 'levelpekerjaan', 
IF(a.level = 'IKSK', 'levelakhir', 
IF(a.level = 'KRO', 'levelakhir', 
IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
from tb_indikator_akhir a 
left join master_unor b
on a.kdunor = b.id
where a.deleted='0' and a.level = 'PROGRAM'
order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
$resultPROGRAMLAMPIRAN2 = mysqli_query($link, $sqlPROGRAMLAMPIRAN2);
$numPROGRAMLAMPIRAN2 = mysqli_num_rows($resultPROGRAMLAMPIRAN2);
if ($numPROGRAMLAMPIRAN2 > 0) {
	if ($resultPROGRAMLAMPIRAN2) {
		while ($rowPROGRAMLAMPIRAN2 = mysqli_fetch_assoc($resultPROGRAMLAMPIRAN2)) {
			$currentY = $pdf->GetY();
			if ($rowPROGRAMLAMPIRAN2['textindikator'] == 'DUKUNGAN MANAJEMEN' && (float) $currentY > 90) {
				$pdf->AddPage('L', $resolution);
				$pdf->SetFont('dejavusans', '', 11);

				$html = '
				<table border="0"  cellpadding="2" style="width:100%;" >
				<thead>
				';
				$html = $html . '	
				</thead>  
				<tbody>
				<tr>
				<td align="center" width="1000px">	
				<b>BERITA ACARA KESEPAKATAN CAPAIAN KINERJA ' . $tahunsekarang . '</b>
				</td>
				</tr>
				<tr>
				<td align="center" width="1000px">	
				<b>' . $nama_balai . ' - DIREKTORAT JENDERAL SUMBER DAYA AIR</b>
				</td>
				</tr>
				<tr>
				<td align="center" width="1000px">	
				</td>
				</tr>
				</table>
				';
				$currentY = $pdf->GetY();
				$pdf->SetY($currentY - 5);
				$pdf->writeHTML($html, true, false, true, false, '');
				$currentY = $pdf->GetY();
				$pdf->SetY($currentY - 5);
				$pdf->SetFont('dejavusans', '', 9);
				$html = '

				<table border="0"  cellpadding="2" style="width:100%;" >
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
				<td align="justify" width="240px" style="background-color: #D3D3D3;" rowspan="2">
				<b>SASARAN PROGRAM / INDIKATOR SASARAN PROGRAM</b>
				</td>
				<td align="center" width="160px" style="background-color: #D3D3D3;">
				<b>OUTPUT<br>(Tahun Berjalan)</b>
				</td>
				<td align="center" width="160px" style="background-color: #D3D3D3;">
				<b>CAPAIAN OUTPUT</b>
				</td>
				<td align="center" width="60px" style="background-color: #D3D3D3;">
				<b>KINERJA</b>
				</td>
				<td align="center" width="160px" style="background-color: #D3D3D3;">
				<b>OUTCOME<br>(Tahun Berjalan)</b>
				</td>
				<td align="center" width="160px" style="background-color: #D3D3D3;">
				<b>CAPAIAN OUTCOME</b>
				</td>
				<td align="center" width="60px" style="background-color: #D3D3D3;">
				<b>KINERJA</b>
				</td>
				</tr>
				<tr>
				<td align="center" width="80px" style="background-color: #D3D3D3;">
				<b>Volume</b>
				</td>
				<td align="center" width="80px" style="background-color: #D3D3D3;">
				<b>Satuan</b>
				</td>
				<td align="center" width="80px" style="background-color: #D3D3D3;">
				<b>Volume</b>
				</td>
				<td align="center" width="80px" style="background-color: #D3D3D3;">
				<b>Satuan</b>
				</td>
				<td align="center" width="60px" style="background-color: #D3D3D3;">
				<b>%</b>
				</td>
				<td align="center" width="80px" style="background-color: #D3D3D3;">
				<b>Volume</b>
				</td>
				<td align="center" width="80px" style="background-color: #D3D3D3;">
				<b>Satuan</b>
				</td>
				<td align="center" width="80px" style="background-color: #D3D3D3;">
				<b>Volume</b>
				</td>
				<td align="center" width="80px" style="background-color: #D3D3D3;">
				<b>Satuan</b>
				</td>
				<td align="center" width="60px" style="background-color: #D3D3D3;">
				<b>%</b>
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
				<b>PROGRAM: ' . $rowPROGRAMLAMPIRAN2['textindikator'] . '</b>
				</td>
				</tr>
				</table>
				';
				$pdf->writeHTML($html, true, false, true, false, '');
				$currentY = $pdf->GetY();
				$pdf->SetY($currentY - 4.75);
			} else {
				$html = '
				<table border="1"  cellpadding="2" style="width:100%;" >
				<tr>
				<td align="justify" width="1000px">
				<b>PROGRAM: ' . $rowPROGRAMLAMPIRAN2['textindikator'] . '</b>
				</td>
				</tr>
				</table>
				';
				$pdf->writeHTML($html, true, false, true, false, '');
				$currentY = $pdf->GetY();
				$pdf->SetY($currentY - 4.75);
			}

			//KEGIATAN
			$sqlKEGIATANLAMPIRAN2 = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
			,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
			,IF(a.satuan = '0', null, a.satuan) as satuan
			,IF(a.output = '0', null, a.output) as output
			,IF(a.outcome = '0', null, a.outcome) as outcome
			, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
			, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
			, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
			, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
			,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
			, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
			,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
			IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
			IF(a.level = 'ISP', 'levelsubsubkegiatan', 
			IF(a.level = 'KEGIATAN', 'levelpaket', 
			IF(a.level = 'SK', 'levelpekerjaan', 
			IF(a.level = 'IKSK', 'levelakhir', 
			IF(a.level = 'KRO', 'levelakhir', 
			IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
			from tb_indikator_akhir a 
			left join master_unor b
			on a.kdunor = b.id
			where a.deleted='0' and a.level = 'KEGIATAN' and a.id_parent = '" . $rowPROGRAMLAMPIRAN2['id'] . "'
			order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
			$resultKEGIATANLAMPIRAN2 = mysqli_query($link, $sqlKEGIATANLAMPIRAN2);
			$numKEGIATANLAMPIRAN2 = mysqli_num_rows($resultKEGIATANLAMPIRAN2);
			while ($rowKEGIATANLAMPIRAN2 = mysqli_fetch_assoc($resultKEGIATANLAMPIRAN2)) {
				//SK
				$sqlSKLAMPIRAN2 = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
				,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
				,IF(a.satuan = '0', null, a.satuan) as satuan
				,IF(a.output = '0', null, a.output) as output
				,IF(a.outcome = '0', null, a.outcome) as outcome
				, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
				, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
				, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
				, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
				,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
				, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
				,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
				IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
				IF(a.level = 'ISP', 'levelsubsubkegiatan', 
				IF(a.level = 'KEGIATAN', 'levelpaket', 
				IF(a.level = 'SK', 'levelpekerjaan', 
				IF(a.level = 'IKSK', 'levelakhir', 
				IF(a.level = 'KRO', 'levelakhir', 
				IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
				from tb_indikator_akhir a 
				left join master_unor b
				on a.kdunor = b.id
				where a.deleted='0' and a.level = 'SK' and a.id_parent = '" . $rowKEGIATANLAMPIRAN2['id'] . "'
				and a.id in (select b.id_parent from tb_indikator_akhir b where b.deleted = '0' 
				and b.id in ((select c.id_indikator from tb_data_pk_akhir c where b.deleted='0' and c.kode_satker in (" . $groupkode_satker . "))))
				order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
				$resultSKLAMPIRAN2 = mysqli_query($link, $sqlSKLAMPIRAN2);
				$numSKLAMPIRAN2 = mysqli_num_rows($resultSKLAMPIRAN2);
				while ($rowSKLAMPIRAN2 = mysqli_fetch_assoc($resultSKLAMPIRAN2)) {

					$sqlcektampilmaster = "select pelaksana from tb_indikator_akhir where deleted='0' and id_parent = '" . $rowSKLAMPIRAN2['id'] . "'";
					$resultcektampilmaster = mysqli_query($link, $sqlcektampilmaster);
					$numcektampilmaster = mysqli_num_rows($resultcektampilmaster);


					$sqlcektampil = "select id from tb_data_pk_tidak_cetak_akhir where deleted='0' and id_indikator in 
					(select id from tb_indikator_akhir where deleted='0' and id_parent = '" . $rowSKLAMPIRAN2['id'] . "')
					and kode_satker in (" . $groupkode_satker . ")";

					$resultcektampil = mysqli_query($link, $sqlcektampil);
					$numcektampil = mysqli_num_rows($resultcektampil);



					////
					$sqlcektampilINDIKATOR = "select pelaksana from tb_indikator_akhir where deleted='0' and id_parent = '" . $rowSKLAMPIRAN2['id'] . "' ";
					//echo $sqlcektampilINDIKATOR."<br>";
					$resultcektampilINDIKATOR = mysqli_query($link, $sqlcektampilINDIKATOR);
					$numcektampilINDIKATOR = mysqli_num_rows($resultcektampilINDIKATOR);
					$jumlahcek = 0;
					while ($rowcektampilINDIKATOR = mysqli_fetch_assoc($resultcektampilINDIKATOR)) {
						$cekpelaksana = $rowcektampilINDIKATOR['pelaksana'];
						$tempcekpelaksana = explode(",", $cekpelaksana);
						for ($sss = 0; $sss < count($tempcekpelaksana); $sss++) {
							$sqlcekjumlah = "select level_piu from master_satker where deleted='0' and kode_satker in (" . $groupkode_satker . ")
							and level_piu = '" . $tempcekpelaksana[$sss] . "'";

							$resultcekjumlah = mysqli_query($link, $sqlcekjumlah);
							$numcekjumlah = mysqli_num_rows($resultcekjumlah);
							$jumlahcek = $jumlahcek + $numcekjumlah;
						}
					}
					////


					if (($numcektampil == 0) || ($numcektampil < $jumlahcek)) {
						$currentY = $pdf->GetY();
						if ((float)$currentY > 160) {
							$pdf->AddPage('L', $resolution);
							$pdf->SetFont('dejavusans', '', 11);

							$html = '
							<table border="0"  cellpadding="2" style="width:100%;" >
							<thead>
							';
							$html = $html . '	
							</thead>  
							<tbody>
							<tr>
							<td align="center" width="1000px">	
							<b>BERITA ACARA KESEPAKATAN CAPAIAN KINERJA ' . $tahunsekarang . '</b>
							</td>
							</tr>
							<tr>
							<td align="center" width="1000px">	
							<b>' . $nama_balai . ' - DIREKTORAT JENDERAL SUMBER DAYA AIR</b>
							</td>
							</tr>
							<tr>
							<td align="center" width="1000px">	
							</td>
							</tr>
							</table>
							';
							$currentY = $pdf->GetY();
							$pdf->SetY($currentY - 5);
							$pdf->writeHTML($html, true, false, true, false, '');
							$currentY = $pdf->GetY();
							$pdf->SetY($currentY - 5);
							$pdf->SetFont('dejavusans', '', 9);
							$html = '

							<table border="0"  cellpadding="2" style="width:100%;" >
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
							<td align="justify" width="240px" style="background-color: #D3D3D3;" rowspan="2">
							<b>SASARAN PROGRAM / INDIKATOR SASARAN PROGRAM</b>
							</td>
							<td align="center" width="160px" style="background-color: #D3D3D3;">
							<b>OUTPUT<br>(Tahun Berjalan)</b>
							</td>
							<td align="center" width="160px" style="background-color: #D3D3D3;">
							<b>CAPAIAN OUTPUT</b>
							</td>
							<td align="center" width="60px" style="background-color: #D3D3D3;">
							<b>KINERJA</b>
							</td>
							<td align="center" width="160px" style="background-color: #D3D3D3;">
							<b>OUTCOME<br>(Tahun Berjalan)</b>
							</td>
							<td align="center" width="160px" style="background-color: #D3D3D3;">
							<b>CAPAIAN OUTCOME</b>
							</td>
							<td align="center" width="60px" style="background-color: #D3D3D3;">
							<b>KINERJA</b>
							</td>
							</tr>
							<tr>
							<td align="center" width="80px" style="background-color: #D3D3D3;">
							<b>Volume</b>
							</td>
							<td align="center" width="80px" style="background-color: #D3D3D3;">
							<b>Satuan</b>
							</td>
							<td align="center" width="80px" style="background-color: #D3D3D3;">
							<b>Volume</b>
							</td>
							<td align="center" width="80px" style="background-color: #D3D3D3;">
							<b>Satuan</b>
							</td>
							<td align="center" width="60px" style="background-color: #D3D3D3;">
							<b>%</b>
							</td>
							<td align="center" width="80px" style="background-color: #D3D3D3;">
							<b>Volume</b>
							</td>
							<td align="center" width="80px" style="background-color: #D3D3D3;">
							<b>Satuan</b>
							</td>
							<td align="center" width="80px" style="background-color: #D3D3D3;">
							<b>Volume</b>
							</td>
							<td align="center" width="80px" style="background-color: #D3D3D3;">
							<b>Satuan</b>
							</td>
							<td align="center" width="60px" style="background-color: #D3D3D3;">
							<b>%</b>
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
							<b>SK: ' . $rowSKLAMPIRAN2['textindikator'] . '</b>
							</td>
							</tr>
							</table>
							';
							$pdf->writeHTML($html, true, false, true, false, '');
							$currentY = $pdf->GetY();
							$pdf->SetY($currentY - 4.75);
						} else {
							$html = '
							<table border="1"  cellpadding="2" style="width:100%;" >
							<tr>
							<td align="justify" width="1000px">
							<b>SK: ' . $rowSKLAMPIRAN2['textindikator'] . '</b>
							</td>
							</tr>
							</table>
							';
							$pdf->writeHTML($html, true, false, true, false, '');
							$currentY = $pdf->GetY();
							$pdf->SetY($currentY - 4.75);
						}
					}
					//IKSK
					$sqlIKSKLAMPIRAN2 = "select a.id,a.tahun,a.id_parent,a.kdunor,b.nama_kategori as nama_unor,a.urutlevel,a.level
					,a.kode,a.kode_unique,IF(a.level = 'UNOR', b.nama_kategori, a.nama) as textindikator
					,IF(a.satuan = '0', null, a.satuan) as satuan
					,IF(a.output = '0', null, a.output) as output
					,IF(a.outcome = '0', null, a.outcome) as outcome
					, (select x.singkatan from master_satuan x where x.id=a.satuan) as namasatuan
					, (select x.singkatan from master_satuan x where x.id=a.output) as namaoutput
					, (select x.singkatan from master_satuan x where x.id=a.outcome) as namaoutcome
					, (select z.nama from master_nilai z where z.id=a.hitungan_pk) as namahitungan_pk
					,a.penanggungjawab,a.pelaksana,a.pelaksanacode,a.pelaksanalabel,a.target ,a.level,a.urut,a.hitungan_pk
					, a.jumlahkomponen, a.kolomkomponen, a.bobotkomponen, a.rumuskomponen, a.belakangkoma
					,IF(a.level = 'UNOR', 'levelkomponen', IF(a.level = 'SS', 'levelsubkomponen', IF(a.level = 'ISS', 'levelprogram', 
					IF(a.level = 'PROGRAM', 'levelkegiatan', IF(a.level = 'SP', 'levelsubkegiatan', 
					IF(a.level = 'ISP', 'levelsubsubkegiatan', 
					IF(a.level = 'KEGIATAN', 'levelpaket', 
					IF(a.level = 'SK', 'levelpekerjaan', 
					IF(a.level = 'IKSK', 'levelakhir', 
					IF(a.level = 'KRO', 'levelakhir', 
					IF(a.level = 'RO', 'levelakhir', 'levelakhir'))))))))))) as class
					from tb_indikator_akhir a 
					left join master_unor b
					on a.kdunor = b.id
					where a.deleted='0' and a.level = 'IKSK' and a.id_parent = '" . $rowSKLAMPIRAN2['id'] . "'
					and a.pelaksana <> ''
					and a.id in (select b.id_indikator from tb_data_pk_akhir b where b.deleted='0' and b.kode_satker in (" . $groupkode_satker . "))
					order by a.id_parent ASC,a.urutlevel ASC, a.urut ASC ";
					$resultIKSKLAMPIRAN2 = mysqli_query($link, $sqlIKSKLAMPIRAN2);
					$numIKSKLAMPIRAN2 = mysqli_num_rows($resultIKSKLAMPIRAN2);
					while ($rowIKSKLAMPIRAN2 = mysqli_fetch_assoc($resultIKSKLAMPIRAN2)) {
						$belakangkoma = (float) $rowIKSKLAMPIRAN2['belakangkoma'];
						$output_tahun_berjalan = 0;
						$outcome_tahun_berjalan = 0;
						$target = 0;
						$capaian = 0;
						$kinerja = 0;
						$capaian_outcome = 0;
						$kinerja_outcome  = 0;
						if ($rowIKSKLAMPIRAN2['hitungan_pk'] == '1') {
							$output_tahun_berjalan = 0;
							$sqloutputtahunsekarang = "select sum(volume) as volume
							from tb_data_pk_akhir
							where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
							and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "' and tahun = '" . $tahunsekarang . "'";
							//echo $sqloutputtahunsekarang;
							$resultoutputtahunsekarang = mysqli_query($link, $sqloutputtahunsekarang);
							$numoutputtahunsekarang = mysqli_num_rows($resultoutputtahunsekarang);
							if ($numoutputtahunsekarang > 0) {
								while ($rowoutputtahunsekarang = mysqli_fetch_assoc($resultoutputtahunsekarang)) {
									$output_tahun_berjalan = (float) $rowoutputtahunsekarang['volume'];
								}
							}
							$output_tahun_berjalan = number_format($output_tahun_berjalan, $belakangkoma, ",", ".");

							$outcome_tahun_berjalan = 0;
							$sqloutcometahunsekarang = "select sum(target) as target
							from tb_data_pk_akhir
							where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
							and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "' and tahun = '" . $tahunsekarang . "'";
							//echo $sqloutcometahunsekarang;
							$resultoutcometahunsekarang = mysqli_query($link, $sqloutcometahunsekarang);
							$numoutcometahunsekarang = mysqli_num_rows($resultoutcometahunsekarang);
							if ($numoutcometahunsekarang > 0) {
								while ($rowoutcometahunsekarang = mysqli_fetch_assoc($resultoutcometahunsekarang)) {
									$outcome_tahun_berjalan = (float) $rowoutcometahunsekarang['target'];
								}
							}
							$outcome_tahun_berjalan = number_format($outcome_tahun_berjalan, $belakangkoma, ",", ".");

							$baseline = 0;
							$sqlbaseline = "select baseline
							from tb_data_baseline
							where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
							and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "'";
							//echo $sqlbaseline;
							$resultbaseline = mysqli_query($link, $sqlbaseline);
							$numbaseline = mysqli_num_rows($resultbaseline);
							if ($numbaseline > 0) {
								while ($rowbaseline = mysqli_fetch_assoc($resultbaseline)) {
									$baseline = (float) $rowbaseline['baseline'];
								}
							}

							$target = 0;
							$sqlskor = "select sum(target) as target
							from tb_data_pk_akhir
							where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
							and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "'";
							//echo $sqlskor;
							$resultskor = mysqli_query($link, $sqlskor);
							$numskor = mysqli_num_rows($resultskor);
							if ($numskor > 0) {
								while ($rowskor = mysqli_fetch_assoc($resultskor)) {
									$target = (float) $rowskor['target'] + $baseline;
								}
							}
							$target = number_format($target, $belakangkoma, ",", ".");


							$sqlcapaian = "select sum(capaian) as capaian
							from tb_data_pk_akhir
							where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
							and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "' and tahun = '" . $tahunsekarang . "'";
							//echo $sqlcapaian;
							$resultcapaian = mysqli_query($link, $sqlcapaian);
							$numcapaian = mysqli_num_rows($resultcapaian);
							if ($numcapaian > 0) {
								while ($rowcapaian = mysqli_fetch_assoc($resultcapaian)) {
									$capaian = (float) $rowcapaian['capaian'];
								}
							}
							if ((float) replace_comma($output_tahun_berjalan) == 0) {
								$kinerja = 0;
							} else {
								$kinerja = ($capaian / replace_comma($output_tahun_berjalan)) * 100;
							}
							$capaian = number_format($capaian, $belakangkoma, ",", ".");
							$kinerja = number_format($kinerja, 2, ",", ".");

							if ($capaian == $output_tahun_berjalan) {
								$kinerja = number_format('100', 2, ",", ".");
							}


							$sqlcapaian_outcome = "select sum(capaian_outcome) as capaian_outcome
							from tb_data_pk_akhir
							where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
							and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "' and tahun = '" . $tahunsekarang . "'";
							//echo $sqlcapaian_outcome;
							$resultcapaian_outcome = mysqli_query($link, $sqlcapaian_outcome);
							$numcapaian_outcome = mysqli_num_rows($resultcapaian_outcome);
							if ($numcapaian_outcome > 0) {
								while ($rowcapaian_outcome = mysqli_fetch_assoc($resultcapaian_outcome)) {
									$capaian_outcome = (float) $rowcapaian_outcome['capaian_outcome'];
								}
							}
							if ((float) replace_comma($outcome_tahun_berjalan) == 0) {
								$kinerja_outcome  = 0;
							} else {
								$kinerja_outcome  = ($capaian_outcome / replace_comma($outcome_tahun_berjalan)) * 100;
							}
							$capaian_outcome = number_format($capaian_outcome, $belakangkoma, ",", ".");
							$kinerja_outcome  = number_format($kinerja_outcome, 2, ",", ".");

							if ($capaian_outcome == $outcome_tahun_berjalan) {
								$kinerja_outcome = number_format('100', 2, ",", ".");
							}
						}
						if ($rowIKSKLAMPIRAN2['hitungan_pk'] == '2' || $rowIKSKLAMPIRAN2['hitungan_pk'] == '3') {
							//echo $groupkode_satker;
							//die;
							if ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7755SK113') {
								$sqlskor = "select kode_satker,nilai,rumus
								from tb_data_pk_akhir
								where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
								and tahun = '" . $tahunsekarang . "'
								and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "'
								and kode_satker in (select kode_satker from master_satker where 
								kode_satker in (" . $groupkode_satker . ") and level_piu <> '7' and level_piu <> '41')
								and kode_satker not in (select kode_satker from tb_data_pk_tidak_cetak_akhir
								where id_indikator='222' and deleted='0' and kode_satker in (" . $groupkode_satker . "))";

								$sqlcapaian = "select avg(capaian) as capaian
								from tb_data_pk_akhir
								where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
								and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "' and tahun = '" . $tahunsekarang . "'
								and kode_satker in (select kode_satker from master_satker where 
								kode_satker in (" . $groupkode_satker . ") and level_piu <> '7' and level_piu <> '41')
								and kode_satker not in (select kode_satker from tb_data_pk_tidak_cetak_akhir
								where id_indikator='222' and deleted='0' and kode_satker in (" . $groupkode_satker . "))";
								//echo $sqlcapaian;
								$resultcapaian = mysqli_query($link, $sqlcapaian);
								$numcapaian = mysqli_num_rows($resultcapaian);
								if ($numcapaian > 0) {
									while ($rowcapaian = mysqli_fetch_assoc($resultcapaian)) {
										$capaian = (float) $rowcapaian['capaian'];
									}
								}
								$capaian = number_format($capaian, $belakangkoma, ",", ".");
								$capaian_outcome = $capaian;

							} else {
								$sqlskor = "select nilai,rumus
								from tb_data_pk_akhir
								where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
								and tahun = '" . $tahunsekarang . "'
								and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "'";

								if (($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7688SK110') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7688SK111')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7688SK115') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7689SK104')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7689SK105') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7690SK101')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7690SK102') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7690SK103')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7690SK104') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7690SK105')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7691SK104') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7692SK104')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK301') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK302')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK303') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK304')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7755SK112') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7688SK102')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7755SK114') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7688SK103')
								) {
									$sqlskor = $sqlskor . " and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (7,41,8,12,13,14,26,28,29,30))";

									$sqlcapaian = "select avg(capaian) as capaian
									from tb_data_pk_akhir
									where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
									and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "' and tahun = '" . $tahunsekarang . "'
									and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (7,41,8,12,13,14,26,28,29,30))";
									//echo $sqlcapaian;
									$resultcapaian = mysqli_query($link, $sqlcapaian);
									$numcapaian = mysqli_num_rows($resultcapaian);
									if ($numcapaian > 0) {
										while ($rowcapaian = mysqli_fetch_assoc($resultcapaian)) {
											$capaian = (float) $rowcapaian['capaian'];
										}
									}
									$capaian = number_format($capaian, $belakangkoma, ",", ".");
									$capaian_outcome = $capaian;
								}

								if (($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7691SK101') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7691SK102')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7691SK103')
								) {
									$sqlskor = $sqlskor . " and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (2))";

									$sqlcapaian = "select avg(capaian) as capaian
									from tb_data_pk_akhir
									where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
									and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "' and tahun = '" . $tahunsekarang . "'
									and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (2))";
									//echo $sqlcapaian;
									$resultcapaian = mysqli_query($link, $sqlcapaian);
									$numcapaian = mysqli_num_rows($resultcapaian);
									if ($numcapaian > 0) {
										while ($rowcapaian = mysqli_fetch_assoc($resultcapaian)) {
											$capaian = (float) $rowcapaian['capaian'];
										}
									}
									$capaian = number_format($capaian, $belakangkoma, ",", ".");
									$capaian_outcome = $capaian;
								}

								if (($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7692SK101') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7692SK102')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7692SK103')  || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7692SK201')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7692SK202')
								) {
									$sqlskor = $sqlskor . " and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (3,31))";

									$sqlcapaian = "select avg(capaian) as capaian
									from tb_data_pk_akhir
									where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
									and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "' and tahun = '" . $tahunsekarang . "'
									and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (3,31))";
									//echo $sqlcapaian;
									$resultcapaian = mysqli_query($link, $sqlcapaian);
									$numcapaian = mysqli_num_rows($resultcapaian);
									if ($numcapaian > 0) {
										while ($rowcapaian = mysqli_fetch_assoc($resultcapaian)) {
											$capaian = (float) $rowcapaian['capaian'];
										}
									}
									$capaian = number_format($capaian, $belakangkoma, ",", ".");
									$capaian_outcome = $capaian;
								}

								if (($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7693SK101') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7693SK106')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7693SK102')  || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7693SK103')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7693SK104') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7693SK105')
								) {
									$sqlskor = $sqlskor . " and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (1))";

									$sqlcapaian = "select avg(capaian) as capaian
									from tb_data_pk_akhir
									where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
									and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "' and tahun = '" . $tahunsekarang . "'
									and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (1))";
									//echo $sqlcapaian;
									$resultcapaian = mysqli_query($link, $sqlcapaian);
									$numcapaian = mysqli_num_rows($resultcapaian);
									if ($numcapaian > 0) {
										while ($rowcapaian = mysqli_fetch_assoc($resultcapaian)) {
											$capaian = (float) $rowcapaian['capaian'];
										}
									}
									$capaian = number_format($capaian, $belakangkoma, ",", ".");
									$capaian_outcome = $capaian;
								}

								if (($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK101') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK102')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK103')  || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK104')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK105') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK106')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK107') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK108')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7694SK201')
								) {
									$sqlskor = $sqlskor . " and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (4))";

									$sqlcapaian = "select avg(capaian) as capaian
									from tb_data_pk_akhir
									where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
									and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "' and tahun = '" . $tahunsekarang . "'
									and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (4))";
									//echo $sqlcapaian;
									$resultcapaian = mysqli_query($link, $sqlcapaian);
									$numcapaian = mysqli_num_rows($resultcapaian);
									if ($numcapaian > 0) {
										while ($rowcapaian = mysqli_fetch_assoc($resultcapaian)) {
											$capaian = (float) $rowcapaian['capaian'];
										}
									}
									$capaian = number_format($capaian, $belakangkoma, ",", ".");
									$capaian_outcome = $capaian;
								}

								if (($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK101') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK102')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK103')  || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK104')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK105') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK106')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK107') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK108')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK109') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK110')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK111') || ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK303')
									|| ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7695SK305')
								) {
									$sqlskor = $sqlskor . " and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (5))";

									$sqlcapaian = "select avg(capaian) as capaian
									from tb_data_pk_akhir
									where deleted = '0' and kode_satker in (" . $groupkode_satker . ") and kode_tahun='" . $tahun . "'
									and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "' and tahun = '" . $tahunsekarang . "'
									and kode_satker in (select kode_satker from master_satker
									where deleted='0' and kode_satker in (" . $groupkode_satker . ") and level_piu
									in (5))";
									//echo $sqlcapaian;
									$resultcapaian = mysqli_query($link, $sqlcapaian);
									$numcapaian = mysqli_num_rows($resultcapaian);
									if ($numcapaian > 0) {
										while ($rowcapaian = mysqli_fetch_assoc($resultcapaian)) {
											$capaian = (float) $rowcapaian['capaian'];
										}
									}
									$capaian = number_format($capaian, $belakangkoma, ",", ".");
									$capaian_outcome = $capaian;
								}
							}
							$resultskor = mysqli_query($link, $sqlskor);
							$numskor = mysqli_num_rows($resultskor);
							if ($numskor > 0) {
								$totaldukman = 0;
								$urutandukman = 0;
								while ($rowskor = mysqli_fetch_assoc($resultskor)) {
									if ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7755SK113') {

										//echo $rowskor['kode_satker']."_".$rowskor['nilai']."<br>";

										$nilai = $rowskor['nilai'];
										$tempnilai = explode("|", $nilai);
										$totaldukman = $totaldukman + (float) $tempnilai[count($tempnilai) - 1];
									} else {
										$nilai = $rowskor['nilai'];
										$tempnilai = explode("|", $nilai);
										$jumlahnilai = 0;
										$output_tahun_berjalan = $tempnilai[count($tempnilai) - 1];
										$outcome_tahun_berjalan = $tempnilai[count($tempnilai) - 1];
										$target = $tempnilai[count($tempnilai) - 1];

										$output_tahun_berjalan = number_format($output_tahun_berjalan, $belakangkoma, ",", ".");
										$outcome_tahun_berjalan = number_format($outcome_tahun_berjalan, $belakangkoma, ",", ".");
										$target = number_format($target, $belakangkoma, ",", ".");
									}
									$urutandukman++;
								}
								if ($rowIKSKLAMPIRAN2['kode_unique'] == 'IKSK7755SK113') {
									$output_tahun_berjalan = $totaldukman / $urutandukman;
									$outcome_tahun_berjalan = $totaldukman / $urutandukman;
									$target = $totaldukman / $urutandukman;

									$output_tahun_berjalan = number_format($output_tahun_berjalan, $belakangkoma, ",", ".");
									$outcome_tahun_berjalan = number_format($outcome_tahun_berjalan, $belakangkoma, ",", ".");
									$target = number_format($target, $belakangkoma, ",", ".");
								}

								if ((float) $output_tahun_berjalan == (float) $capaian) {
									$kinerja = number_format('100', 2, ",", ".");
									$kinerja_outcome = number_format('100', 2, ",", ".");
								}
								else {
									if ((float) $output_tahun_berjalan == 0) {
										$kinerja = number_format('0', 2, ",", ".");
										$kinerja_outcome = number_format('0', 2, ",", ".");
									}
									else {
										$kinerja = ((float) replace_comma($capaian) / (float) replace_comma($output_tahun_berjalan)) *100;
										$kinerja = number_format($kinerja, 2, ",", ".");
										$kinerja_outcome = $kinerja;
									}
								}

							} else {
								$output_tahun_berjalan = 0;
								$outcome_tahun_berjalan = 0;
								$target = 0;
								$kinerja = 0;
								$kinerja_outcome = 0;
							}
						}


						$sqlcektampilIKSK = "select id from tb_data_pk_tidak_cetak_akhir where deleted='0' and id_indikator = '" . $rowIKSKLAMPIRAN2['id'] . "'
						and kode_satker in (" . $groupkode_satker . ")";
						$resultcektampilIKSK = mysqli_query($link, $sqlcektampilIKSK);
						$numcektampilIKSK = mysqli_num_rows($resultcektampilIKSK);

						$sqlcektampilINDIKATOR = "select pelaksana from tb_indikator_akhir where deleted='0' and id = '" . $rowIKSKLAMPIRAN2['id'] . "' ";
						//echo $sqlcektampilINDIKATOR."<br>";
						$resultcektampilINDIKATOR = mysqli_query($link, $sqlcektampilINDIKATOR);
						$numcektampilINDIKATOR = mysqli_num_rows($resultcektampilINDIKATOR);
						$jumlahcek = 0;
						while ($rowcektampilINDIKATOR = mysqli_fetch_assoc($resultcektampilINDIKATOR)) {
							$cekpelaksana = $rowcektampilINDIKATOR['pelaksana'];
							$tempcekpelaksana = explode(",", $cekpelaksana);
							for ($sss = 0; $sss < count($tempcekpelaksana); $sss++) {
								$sqlcekjumlah = "select level_piu from master_satker where deleted='0' and kode_satker in (" . $groupkode_satker . ")
								and level_piu = '" . $tempcekpelaksana[$sss] . "'";

								$resultcekjumlah = mysqli_query($link, $sqlcekjumlah);
								$numcekjumlah = mysqli_num_rows($resultcekjumlah);
								$jumlahcek = $jumlahcek + $numcekjumlah;
							}
						}

						if (($numcektampilIKSK == 0) || ($numcektampilIKSK < $jumlahcek)) {
							$currentY = $pdf->GetY();
							if ((float)$currentY > 161) {
								$pdf->AddPage('L', $resolution);
								$pdf->SetFont('dejavusans', '', 11);

								$html = '
								<table border="0"  cellpadding="2" style="width:100%;" >
								<thead>
								';
								$html = $html . '	
								</thead>  
								<tbody>
								<tr>
								<td align="center" width="1000px">	
								<b>BERITA ACARA KESEPAKATAN CAPAIAN KINERJA ' . $tahunsekarang . '</b>
								</td>
								</tr>
								<tr>
								<td align="center" width="1000px">	
								<b>' . $nama_balai . ' - DIREKTORAT JENDERAL SUMBER DAYA AIR</b>
								</td>
								</tr>
								<tr>
								<td align="center" width="1000px">	
								</td>
								</tr>
								</table>
								';
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 5);
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 5);
								$pdf->SetFont('dejavusans', '', 9);
								$html = '

								<table border="0"  cellpadding="2" style="width:100%;" >
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
								<td align="justify" width="240px" style="background-color: #D3D3D3;" rowspan="2">
								<b>SASARAN PROGRAM / INDIKATOR SASARAN PROGRAM</b>
								</td>
								<td align="center" width="160px" style="background-color: #D3D3D3;">
								<b>OUTPUT<br>(Tahun Berjalan)</b>
								</td>
								<td align="center" width="160px" style="background-color: #D3D3D3;">
								<b>CAPAIAN OUTPUT</b>
								</td>
								<td align="center" width="60px" style="background-color: #D3D3D3;">
								<b>KINERJA</b>
								</td>
								<td align="center" width="160px" style="background-color: #D3D3D3;">
								<b>OUTCOME<br>(Tahun Berjalan)</b>
								</td>
								<td align="center" width="160px" style="background-color: #D3D3D3;">
								<b>CAPAIAN OUTCOME</b>
								</td>
								<td align="center" width="60px" style="background-color: #D3D3D3;">
								<b>KINERJA</b>
								</td>
								</tr>
								<tr>
								<td align="center" width="80px" style="background-color: #D3D3D3;">
								<b>Volume</b>
								</td>
								<td align="center" width="80px" style="background-color: #D3D3D3;">
								<b>Satuan</b>
								</td>
								<td align="center" width="80px" style="background-color: #D3D3D3;">
								<b>Volume</b>
								</td>
								<td align="center" width="80px" style="background-color: #D3D3D3;">
								<b>Satuan</b>
								</td>
								<td align="center" width="60px" style="background-color: #D3D3D3;">
								<b>%</b>
								</td>
								<td align="center" width="80px" style="background-color: #D3D3D3;">
								<b>Volume</b>
								</td>
								<td align="center" width="80px" style="background-color: #D3D3D3;">
								<b>Satuan</b>
								</td>
								<td align="center" width="80px" style="background-color: #D3D3D3;">
								<b>Volume</b>
								</td>
								<td align="center" width="80px" style="background-color: #D3D3D3;">
								<b>Satuan</b>
								</td>
								<td align="center" width="60px" style="background-color: #D3D3D3;">
								<b>%</b>
								</td>
								</tr>
								';
								$html = $html . '
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 5);
								$currentY = $pdf->GetY();
								$html = '
								<table border="1"  cellpadding="2" style="width:100%;" >
								<tr>
								<td align="justify" width="240px">
								' . $rowIKSKLAMPIRAN2['textindikator'] . '
								</td>
								<td align="center" width="80px">
								' . $output_tahun_berjalan . '
								</td>
								<td align="center" width="80px">
								' . $rowIKSKLAMPIRAN2['namaoutput'] . '
								</td>
								<td align="center" width="80px">
								' . $capaian . '
								</td>
								<td align="center" width="80px">
								' . $rowIKSKLAMPIRAN2['namaoutput'] . '
								</td>
								<td align="center" width="60px">
								' . $kinerja . '
								</td>
								<td align="center" width="80px">
								' . $outcome_tahun_berjalan . '
								</td>
								<td align="center" width="80px">
								' . $rowIKSKLAMPIRAN2['namasatuan'] . '
								</td>
								<td align="center" width="80px">
								' . $capaian_outcome . '
								</td>
								<td align="center" width="80px">
								' . $rowIKSKLAMPIRAN2['namasatuan'] . '
								</td>
								<td align="center" width="60px">
								' . $kinerja_outcome . '
								</td>
								</tr>
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 4.75);
							} else {
								$currentY = $pdf->GetY();
								$html = '
								<table border="1"  cellpadding="2" style="width:100%;" >
								<tr>
								<td align="justify" width="240px">
								' . $rowIKSKLAMPIRAN2['textindikator'] . '
								</td>
								<td align="center" width="80px">
								' . $output_tahun_berjalan . '
								</td>
								<td align="center" width="80px">
								' . $rowIKSKLAMPIRAN2['namaoutput'] . '
								</td>
								<td align="center" width="80px">
								' . $capaian . '
								</td>
								<td align="center" width="80px">
								' . $rowIKSKLAMPIRAN2['namaoutput'] . '
								</td>
								<td align="center" width="60px">
								' . $kinerja . '
								</td>
								<td align="center" width="80px">
								' . $outcome_tahun_berjalan . '
								</td>
								<td align="center" width="80px">
								' . $rowIKSKLAMPIRAN2['namasatuan'] . '
								</td>
								<td align="center" width="80px">
								' . $capaian_outcome . '
								</td>
								<td align="center" width="80px">
								' . $rowIKSKLAMPIRAN2['namasatuan'] . '
								</td>
								<td align="center" width="60px">
								' . $kinerja_outcome . '
								</td>
								</tr>
								</table>
								';
								$pdf->writeHTML($html, true, false, true, false, '');
								$currentY = $pdf->GetY();
								$pdf->SetY($currentY - 4.75);
							}
						}
					}
					//IKSK
				}
				//SK

			}
			//KEGIATAN
		}
	}
}

$currentY = $pdf->GetY();
$pdf->SetFont('dejavusans', '', 8);
$html = '<tr>
<td align="left" width="1000px">
<i>Penetapan berita acara ini bersifat mengikat sebagai acuan data pencapaian kinerja Direktorat Jenderal Sumber Daya Air Tahun ' . $tahunsekarang . '</i>
</td>
</tr>';

$pdf->writeHTML($html, true, false, true, false, '');


$pdf->SetFont('helvetica', '', 11);
$html = '
<tr>
<td align="center" width="300px">
<b>Mengetahui</b>
</td>
<td align="center" width="175px">
</td>
<td align="center" width="175px">
</td>
<td align="center" width="300px">
<b>' . ucfirst($lokasi_pejabat) . ', ' . tgl_indoku(getLastWorkingDayOfYear(get_Isi_Field1('tahun','master_pk','id','3'))) . '</b>
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
<b>Lampiran 4</b>
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

$arrayiku = array();
$iku = 0;
$sqliku = "select * from tb_iku where deleted='0' and REPLACE(KODE, ' ', '') in (" . $variku . ")";
//echo $sqliku;
$resultiku = mysqli_query($link, $sqliku);
while ($rowiku = mysqli_fetch_assoc($resultiku)) {
	if (!in_array($rowiku['SASARANPROGRAM'], $arrayiku)) {
		$tulissasaran = $rowiku['SASARANPROGRAM'];
	} else {
		$tulissasaran = "";
	}
	array_push($arrayiku, $rowiku['SASARANPROGRAM']);
	$currentY = $pdf->GetY();
	$pdf->SetY($currentY - 4.7);
	$currentY = $pdf->GetY();
	if ($currentY > 110) {
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
		<b>Lampiran 4</b>
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
$pdf->Output('Cetak_Perjanjian_Kinerja.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+

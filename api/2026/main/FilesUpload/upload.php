<?php
header("Access-Control-Allow-Origin: *");
session_start();
include '../../library/config.php';
check_injection();
//check_login();
//upload.php

$ekstensiYangDibolehkan = [
	'png',
	'jpg',
	'jpeg',
	'gif',
	'pdf'
];
$ekstensipanjangYangDibolehkan = [
	'image/png',
	'image/jpg',
	'image/jpeg',
	'application/pdf'
];

if (isset($_FILES['images'])) {
	for ($count = 0; $count < count($_FILES['images']['name']); $count++) {
		$extension = pathinfo($_FILES['images']['name'][$count], PATHINFO_EXTENSION);
		if (!in_array($extension, $ekstensiYangDibolehkan)) {
			mysqli_close($link);
			echo json_encode(array('response' => 'error', 'message' => 'File Tidak Valid'));
			die();
		}
		if (!in_array(mime_content_type($_FILES['images']['tmp_name'][$count]), $ekstensipanjangYangDibolehkan)) {
			mysqli_close($link);
			echo json_encode(array('response' => 'error', 'message' => 'File Tidak Valid'));
			die();
		}

		$new_name = uniqid() . '.' . $extension;
		$new_name = date('YmdHis') . "_" . preg_replace('/\s+/', '', md5(basename($_FILES['images']['name'][$count])) . "." . $extension);
		move_uploaded_file($_FILES['images']['tmp_name'][$count], $docRoot.$_POST['folder'] . $new_name);
		echo $new_name." ";
		//die();

	}

	echo 'success';
}

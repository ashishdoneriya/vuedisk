<?php
include_once './base-dir.php';

header("Access-Control-Allow-Methods: POST");
$data = json_decode(file_get_contents('php://input'), true);
$parentDir = htmlspecialchars(strip_tags($data['parentDir']));
$url = htmlspecialchars(strip_tags($data['url']));
$filename = htmlspecialchars(strip_tags($data['filename']));
if ($filename == null || $filename == '') {
	$filename = basename($url);
}
copy($url, $parentDir . '/' . $filename);
if (strpos($parentDir, '/') != 0) {
	$parentDir =  '/' . $parentDir;
}
$parentDir = $baseDir . $parentDir;

if (strpos($parentDir, '/./') != false || strpos($parentDir, '..') != false) {
	echo "error";
	return;
}

shell_exec('wget $url -O $filename -P $parentDir');

echo "success";
?>

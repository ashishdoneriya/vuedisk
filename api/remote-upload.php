<?php
header("Access-Control-Allow-Methods: POST");
$data = json_decode(file_get_contents('php://input'), true);
$currentDir = htmlspecialchars(strip_tags($data['currentDir']));
$url = htmlspecialchars(strip_tags($data['url']));
$filename = htmlspecialchars(strip_tags($data['filename']));
if ($filename == null || $filename == '') {
	$filename = basename($url);
}
copy($url, $currentDir . '/' . $filename);
echo "success";
?>
<?php
include_once './base-dir.php';

if (!isSessionActive()) {
	echo '{"status" : "failed", "message" : "Login Required"}';
	return;
}

header("Access-Control-Allow-Methods: POST");
$data = json_decode(file_get_contents('php://input'), true);
$dirname = htmlspecialchars(strip_tags($data['dirname']));
$parentPath = htmlspecialchars(strip_tags($data['parentPath']));
if (strpos($parentPath, '/') != 0) {
	$parentPath =  '/' . $parentPath;
}

$path = $baseDir . $parentPath . '/' . $dirname;
if (strpos($path, '/./') != false || strpos($path, '..') != false) {
	return;
}

mkdir($path);
echo 'success';
?>

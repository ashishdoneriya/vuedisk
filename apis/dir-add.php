<?php
include_once './base-dir.php';

$baseDir = getBaseDirectory();
if ($baseDir == null) {
	http_response_code(401);
	echo '{"status" : "failed", "message" : "Login Required"}';
	return;
}

header("Access-Control-Allow-Methods: POST");
$data = json_decode(file_get_contents('php://input'), true);
$dirname = $data['dirname'];
$parentPath = $data['parentPath'];

if (strpos($parentPath, '/') != 0) {
	$parentPath =  '/' . $parentPath;
}

$path = $baseDir . $parentPath . '/' . $dirname;
if (strpos($path, '/./') != false || strpos($path, '..') != false) {
	return;
}

if (file_exists($path)){
	http_response_code(409);
	echo '{"status" : "failed", "message" : "Directory already exists"}';
	return;
}

mkdir($path);
echo 'success';
?>

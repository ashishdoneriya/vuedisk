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
$parentDir = $data['parentDir'];
$oldName = $data['oldName'];
$newName = $data['newName'];

if (strpos($parentDir, '/') != 0) {
	$parentDir =  '/' . $parentDir;
}
$parentDir = $baseDir . $parentDir;

if (strpos($parentDir, '/./') != false || strpos($parentDir, '..') != false
	|| strpos($oldName, '/./') != false || strpos($oldName, '..') != false
		|| strpos($newName, '/./') != false || strpos($newName, '..') != false) {
	return;
}

if (file_exists($parentDir . '/' . $newName)) {
	http_response_code(409);
	echo '{"status" : "failed", "message" : "Unable to rename file. Already exists"}';
	return;
}

rename($parentDir . '/' . $oldName, $parentDir . '/' . $newName);

echo 'success';
?>

<?php
include_once './base-dir.php';

if (!isSessionActive()) {
	echo '{"status" : "failed", "message" : "Login Required"}';
	return;
}

header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents('php://input'), true);
$sourceDir = $data['sourceDir'];
$destinationDir = $data['destinationDir'];
$files = $data['files'];
$files = json_decode($files, true);

if (strpos($sourceDir, '/') != 0) {
	$sourceDir =  '/' . $sourceDir;
}
if (strpos($destinationDir, '/') != 0) {
	$destinationDir =  '/' . $destinationDir;
}
$sourceDir = $baseDir . $sourceDir;
$destinationDir = $baseDir . $destinationDir;

if (strpos($sourceDir, '/./') != false || strpos($sourceDir, '..') != false
	|| strpos($destinationDir, '/./') != false || strpos($destinationDir, '..') != false) {
	return;
}

foreach ($files as $file) {
	if (strpos($sourceDir, '/./') != false || strpos($sourceDir, '..') != false) {
		return;
	}
	rename($sourceDir . '/' . $file, $destinationDir . '/' . $file);
}

echo 'success';
?>

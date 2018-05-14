<?php
include_once './base-dir.php';

header("Access-Control-Allow-Methods: POST");

if (!isSessionActive()) {
	echo '{"status" : "failed", "message" : "Login Required"}';
	return;
}

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
	$src = $sourceDir . '/' . $file;
	$dst = $destinationDir . '/' . $file;
	if(is_dir($src)) {
		recursive_copy($src, $dst);
	} else {
		copy($src, $dst);
	}
}

function recursive_copy($src, $dst) {
	$dir = opendir($src);
	mkdir($dst);
	while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
					if ( is_dir($src . '/' . $file) ) {
						recursive_copy($src . '/' . $file,$dst . '/' . $file);
					}
					else {
							copy($src . '/' . $file,$dst . '/' . $file);
					}
			}
	}
	closedir($dir);
}

echo 'success';
?>

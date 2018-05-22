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

$files = $data['files'];
$files = json_decode($files, true);

if (strpos($parentDir, '/') != 0) {
	$parentDir =  '/' . $parentDir;
}
$parentDir = $baseDir . $parentDir;

if (strpos($parentDir, '/./') != false || strpos($parentDir, '..') != false) {
	return;
}
$totalSize = 0;

foreach ((array)$files as $file) {
	$tempPath = $parentDir . '/' . $file;
	if (strpos($tempPath, '/./') != false || strpos($tempPath, '..') != false) {
		return;
	}
	if (is_dir($tempPath)) {
		$totalSize = $totalSize + getDirectorySize($tempPath);
	} else {
		$totalSize = $totalSize + filesize($tempPath);
	}
}

echo getSizeFormatted($totalSize);

function getSizeFormatted($size) {
	if ($size > 1073741824) {
		return round($size / 1073741824.0, 2) . ' GB';
	}
	if ($size > 1048576) {
		return round($size / 1048576) . ' MB';
	}
	if ($size > 1024) {
		return round($size / 1024) . ' KB';
	}
	return $size . ' B';
}

function getDirectorySize($directory) {
	$size = 0;
	foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
			try {
				$size += $file->getSize();
			} catch (Exception $e) {

			}
		}
	return $size;
}

?>

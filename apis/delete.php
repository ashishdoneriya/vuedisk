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

foreach ($files as $file) {
	$fullPath = $parentDir . '/' . $file;

	if ($fullPath == $baseDir . '/' || $fullPath == $baseDir) {
		return;
	}

	if (is_dir($fullPath)) {
		if (endswith($fullPath, '/')) {
			$fullPath = rtrim($fullPath, '/');
		}
		rrmdir($fullPath);
	} else {
		unlink($fullPath);
		$thumbnailPath1 = $parentDir . '/.thumbnail/320px/' . $file;
		if (file_exists($thumbnailPath1)) {
			unlink($thumbnailPath1);
		}
		$thumbnailPath2 = $parentDir . '/.thumbnail/720px/' . $file;
		if (file_exists($thumbnailPath2)) {
			unlink($thumbnailPath2);
		}
	}
}

echo 'success';

function endswith($string, $test) {
	$strlen = strlen($string);
	$testlen = strlen($test);
	if ($testlen > $strlen) return false;
	return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
}

function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (is_dir($dir."/".$object))
					rrmdir($dir."/".$object);
				else
					unlink($dir."/".$object);
			}
		}
		rmdir($dir);
	}
}
?>

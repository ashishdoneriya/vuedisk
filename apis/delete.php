<?php
include_once './base-dir.php';

if (!isSessionActive()) {
	echo '{"status" : "failed", "message" : "Login Required"}';
	return;
}

header("Access-Control-Allow-Methods: POST");
$data = json_decode(file_get_contents('php://input'), true);
$parentDir = htmlspecialchars(strip_tags($data['parentDir']));
$file = htmlspecialchars(strip_tags($data['file']));

if (strpos($parentDir, '/') != 0) {
	$parentDir =  '/' . $parentDir;
}
$parentDir = $baseDir . $parentDir;

if (strpos($parentDir, '/./') != false || strpos($parentDir, '..') != false) {
	return;
}

$fullPath = $parentDir . '/' . $file;

if ($fullPath == $baseDir . '/' || $fullPath == $baseDir) {
	return;
}

if (is_dir($fullPath)) {
	shell_exec('rmdir "' . $fullPath . '"');
} else {
	shell_exec('rm "' . $fullPath . '"');
}

echo 'success';
?>

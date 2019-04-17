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
$parentDir = htmlspecialchars(strip_tags($data['parentDir']));
$url = $data['url'];

$filename = strip_tags($data['filename']);
if ($filename == null || $filename == '') {
	$filename = basename($url);
}
if (strpos($parentDir, '/') != 0) {
	$parentDir =  '/' . $parentDir;
}
$parentDir = $baseDir . $parentDir;

if (strpos($parentDir, '/./') != false || strpos($parentDir, '..') != false) {
	echo "error";
	return;
}

$command = 'wget --background --quiet "' . $url . '" -O "' . $parentDir . '/' . $filename . '"';
shell_exec($command);

echo "success";
?>

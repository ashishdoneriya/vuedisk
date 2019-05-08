<?php
include_once './base-dir.php';

$baseDir = getBaseDirectory();
if ($baseDir == null) {
	http_response_code(401);
	echo '{"status" : "failed", "message" : "Login Required"}';
	return;
}

$path = $_GET['path'];

if (strpos($path, '/') != 0) {
	$path =  '/' . $path;
}
$path = $baseDir . $path;

if (file_exists($path)) {
    set_time_limit(300);
		$size = intval(sprintf("%u", filesize($path)));
		header('Cache-Control: no-cache');
    header('Content-Type: audio/wav');
    header('Content-Transfer-Encoding: binary');
		header('Content-Length: '.$size);
		header('Accept-Ranges: bytes');
    header('Content-Disposition: inline;filename="'.basename($path).'"');
    readfile($path);
		exit;
} else {
	echo 'File "'.$filename.'" does not exist!';
}
?>

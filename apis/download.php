<?php
include_once './base-dir.php';

if (!isSessionActive()) {
	echo '{"status" : "failed", "message" : "Login Required"}';
	return;
}



		$path = $_GET['path'];
		if (strpos($path, '/') != 0) {
			$path =  '/' . $path;
		}
		$path = $baseDir . $path;
		$basename = basename($path);
		header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Disposition: attachment; filename="' . $basename . '"');
    header("Content-Length: ". filesize($path));
    readfile($path);
?>

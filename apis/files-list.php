<?php
include_once './base-dir.php';

$baseDir = getBaseDirectory();
if ($baseDir == null) {
	http_response_code(401);
	echo '{"status" : "failed", "message" : "Login Required"}';
	return;
}
$path =  $_GET['path'];

if (strpos($path, '/') != 0) {
	$path =  '/' . $path;
}
$path = $baseDir . $path;

if (strpos($path, '/./') != false || strpos($path, '..') != false) {
	echo "error";
	return;
}

if (!file_exists($path)) {
	http_response_code(406);
	echo '{"status" : "failed", "message" : "No such file or directory exists"}';
	return;
}

$list = array();
if ($handle = opendir($path)) {
	while (false !== ($entry = readdir($handle))) {
		$filepath = $path . '/' . $entry;
		if (is_dir($filepath) && $entry != '.' && $entry != '..') {
			array_push($list, array('name' => $entry , 'isDir' => true));
		}
	}
	closedir($handle);
}
if ($handle = opendir($path)) {
	while (false !== ($entry = readdir($handle))) {
		$filepath = $path . '/'. $entry;
		if (!is_dir($filepath)) {
			$mime = mime_content_type($filepath);
			if ($mime == false) {
				$mime = 'none/none';
			}
			$size =  getSize($filepath);
			if ($size == ' B') {
				$size = '0 B';
			}
			if (strpos($mime, 'image') !== false) {
				$dimension = getimagesize($filepath);
				array_push($list, array('name' => $entry , 'isDir' => false, 'size' => $size, 'mime' => $mime, 'dimension' => $dimension));
			} else {
				array_push($list, array('name' => $entry , 'isDir' => false, 'size' => $size, 'mime' => $mime));
			}

		}
	}
	closedir($handle);
}

function getSize($file) {
	$size = filesize($file);
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

echo json_encode($list);

?>

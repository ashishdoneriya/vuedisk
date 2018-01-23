<?php
include_once './base-dir.php';

$path =  htmlspecialchars(strip_tags($_GET['path']));
if (strpos($path, '/') != 0) {
	$path =  '/' . $path;
}
$path = $baseDir . $path;

if (strpos($path, '/./') != false || strpos($path, '/../') != false) {
	echo "error";
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
			array_push($list, array('name' => $entry , 'isDir' => false, 'size' => getSize($filepath)));
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

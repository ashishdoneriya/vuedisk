<?php

header("Access-Control-Allow-Methods: GET");
$path = htmlspecialchars(strip_tags($_GET['path']));

if (! ends_with ($path, '/')) {
	$path = $path . '/';
}
$list = array();
if ($handle = opendir($path)) {
	while (false !== ($entry = readdir($handle))) {
		$filepath = $path.$entry;
		$file = array('name' => $entry , 'path' => $filepath);	
		if (is_dir($filepath)) {
			if ($entry != '.' && $entry != '..') {
				$file['isDir'] = true;
				array_push($list, $file);
			}
		}
	}
	closedir($handle);
}
if ($handle = opendir($path)) {
	while (false !== ($entry = readdir($handle))) {
		$filepath = $path.$entry;
		$file = array('name' => $entry , 'path' => $filepath);	
		if (!is_dir($filepath)) {
			$file['isDir'] = false;
			$file['size'] = getSize($filepath);
			array_push($list, $file);
		}
	}
	closedir($handle);
}
function ends_with($haystack, $needles) {
	foreach ((array) $needles as $needle) {
		if ((string) $needle === substr($haystack, -strlen($needle))) {
			return true;
		}
	}
	return false;
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
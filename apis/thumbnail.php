<?php
include_once './base-dir.php';
ini_set('memory_limit', '-1');
if (!isSessionActive()) {
	echo '{"status" : "failed", "message" : "Login Required"}';
	return;
}

$type = $_GET['type'];
$name = $_GET['name'];
$parent = $_GET['parent'];

if (strpos($parent, '/') != 0) {
	$parent =  '/' . $parent;
}
$parent = $baseDir . $parent;

if (strpos($parent, '/./') != false || strpos($parent, '..') != false) {
	return;
}

$finalPath = '';

$extension = get_extension($name);
if ($extension != 'jpg' && $extension != 'jpeg' && $extension != 'JPG' && $extension != 'JPEG') {
	$finalPath = $parent . '/' . $name;
} else {
	$thumbnailDirPath = $parent . '/' . '.thumbnail/';

	$finalPath = $thumbnailDirPath . ($type == 'small' ? '200px' : '500px') . '/' . $name;

	if (!file_exists($finalPath)) {
		if (!file_exists($thumbnailDirPath)) {
			mkdir($thumbnailDirPath);
		}
		if (!file_exists($thumbnailDirPath . '/200px')) {
			mkdir($thumbnailDirPath . '/200px');
		}
		if (!file_exists($thumbnailDirPath . '/500px')) {
			mkdir($thumbnailDirPath . '/500px');
		}
		make_thumb($parent . '/' . $name, $finalPath, '200');
	}

}

$basename = basename($finalPath);
header("Pragma: public"); // required
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header('Content-Disposition: attachment; filename="' . $basename . '"');
header("Content-Length: ". filesize($finalPath));
readfile($finalPath);

function get_extension($file) {
	return substr(strrchr($file,'.'),1);
}

function make_thumb($src, $dest, $desired_height) {

	/* read the source image */
	$source_image = imagecreatefromjpeg($src);
	$width = imagesx($source_image);
	$height = imagesy($source_image);
	
	/* find the "desired height" of this thumbnail, relative to the desired width  */
	$desired_width = floor($desired_height / $height * $width);
	
	/* create a new, "virtual" image */
	$virtual_image = imagecreatetruecolor($desired_width, $desired_height);
	
	/* copy source image at a resized size */
	imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
	
	/* create the physical thumbnail image to its destination */
	imagejpeg($virtual_image, $dest);
}
?>

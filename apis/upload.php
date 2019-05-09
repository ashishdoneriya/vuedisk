<?php

include_once './base-dir.php';

$baseDir = getBaseDirectory();
if ($baseDir == null) {
	http_response_code(401);
	echo '{"status" : "failed", "message" : "Login Required"}';
	return;
}

$num = $_POST['num'];

$tmp_name = $_FILES['upload']['tmp_name'];
$target_path = $baseDir .  '/.cache/phpFileManager/';
$filename =  $_POST['fileUniqueId'];
$target_file = $baseDir .  '/.cache/phpFileManager/' . $filename;
$num = $_POST['num'];
$num_chunks = $_POST['num_chunks'];
if (!file_exists($target_path)) {
    mkdir($target_file, 0777, true);
}
move_uploaded_file($tmp_name, $target_file.$num);

$lockPath =  $target_path . $filename . '/lock/';

if (!file_exists($lockPath)) {
    mkdir($lockPath, 0777, true);
}
$chunksUploadedPath =  $target_path . $filename . '/chunksUploaded.txt';
$serialNumPath =  $target_path . $filename . '/serialNo.txt';
while (true) {
	$lock = getLock($lockPath, $filename);
	if ($lock == 1) {
		break;
	}
	sleep(mt_rand(10, 50));
}

$uploadedChunks = updateAndGetChunksUploaded($chunksUploadedPath);
$serialNumber = getSerialNumber();

if ($serialNumber == 0) {
	if ($num == 1) {
		$serialNumber = 1;
		$final = fopen($target_file.'1', 'ab');
		$i = $serialNumber + 1;
		for (; $i <= $uploadedChunks; $i++) {
			if (!file_exists($target_file.$i)) {
				break;
			}
			$file = fopen($target_file.$i, 'rb');
			$buff = fread($file, filesize($target_file.$i));
			fclose($file);
			$write = fwrite($final, $buff);
			unlink($target_file.$i);
		}
		fclose($final);
		setSerialNumber($i - 1);
	}
} else {
	$i = $serialNumber + 1;
	$final = fopen($target_file.'1', 'ab');
	for (; $i <= $uploadedChunks; $i++) {
		if (!file_exists($target_file.$i)) {
			break;
		}
		$file = fopen($target_file.$i, 'rb');
		$buff = fread($file, filesize($target_file.$i));
		fclose($file);
		$write = fwrite($final, $buff);
		unlink($target_file.$i);
	}
	fclose($final);
	setSerialNumber($i - 1);
}


// and THAT's what you were asking for
// when this triggers - that means your chunks are uploaded
if ($uploadedChunks == $num_chunks) {
	$parentDir = $_POST['parentDir'];

	if (!ends_with($parentDir, '/')) {
		$parentDir = $parentDir . '/';
	}

	if (strpos($parentDir, '/') != 0) {
		$parentDir =  '/' . $parentDir;
	}
	if (!file_exists($parentDir)) {
		mkdir($parentDir, 0777, true);
	}

	if (strpos($parentDir, '/./') != false || strpos($parentDir, '..') != false) {
		return;
	}
	$filenameOriginal = $_FILES['upload']['name'];
	rename($target_file.'1', $parentDir . $filenameOriginal );
	rrmdir($target_file);
}

if (file_exists($lockPath)) {

	$handle = opendir($lockPath);
	while (false !== ($entry = readdir($handle))) {
	  if ($entry !== '.' && $entry !== '..') { // <-- better use strict comparison here
		rrmdir($lockPath . $entry);
	  }
	}
	closedir($handle);
}

echo 'success';

function updateAndGetChunksUploaded($path) {
	if (file_exists($path)) {
		$uploaded = (int) file_get_contents($path);
		$uploaded += 1;
		unlink($path);
		$myfile = fopen($path, "w");
		fwrite($myfile, (string) $uploaded);
		fclose($myfile);
		return $uploaded;
	} else {
		$myfile = fopen($path, "w");
		fwrite($myfile, '1');
		fclose($myfile);
		return 1;
	}
}

function setSerialNumber($serialNumber) {
	global $serialNumPath;
	if (file_exists($serialNumPath)) {
		unlink($serialNumPath);
	}
	$myfile = fopen($serialNumPath, "w");
	fwrite($myfile, (string) $serialNumber);
	fclose($myfile);
}

function getSerialNumber() {
	global $serialNumPath;
	if (file_exists($serialNumPath)) {
		return (int) file_get_contents($serialNumPath);
	} else {
		return 0;
	}
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

function getLock($lockPath, $filename) {
	if (is_dir_empty($lockPath) == 1) {
		$milliseconds = round(microtime(true) * 1000);
		mkdir($lockPath. '/'.$milliseconds, 0777, true);
		return isLowestTimestamp($lockPath, $milliseconds);
	} else {
		return 0;
	}

}

function isLowestTimestamp($path, $milliseconds) {

	$dir = opendir($path);
	while(false != ($file = readdir($dir))) {
        if(($file != ".") && ($file != "..")) {
               if ((int)$file < $milliseconds) {
				   return 0;
			   }
        }
	}
	return 1;
}

function is_dir_empty($dir) {
	$handle = opendir($dir);
	while (false !== ($entry = readdir($handle))) {
	  if ($entry !== '.' && $entry !== '..') { // <-- better use strict comparison here
		closedir($handle); // <-- always clean up! Close the directory stream
		return 0;
	  }
	}
	closedir($handle); // <-- always clean up! Close the directory stream
	return 1;
}

function ends_with($haystack, $needles) {
	foreach ((array) $needles as $needle) {
		if ((string) $needle === substr($haystack, -strlen($needle))) {
			return true;
		}
	}
	return false;
}

?>

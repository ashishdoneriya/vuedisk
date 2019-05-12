<?php

include_once './base-dir.php';

$baseDir = getBaseDirectory();
if ($baseDir == null) {
	http_response_code(401);
	echo '{"status" : "failed", "message" : "Login Required"}';
	return;
}

$chunkNumber = $_POST['num'];

$tmp_name = $_FILES['upload']['tmp_name'];
$fileUniqueId =  $_POST['fileUniqueId'];

$workspaceDir = $baseDir .  '/.cache/vuedisk/' . $fileUniqueId;
$tempTargetFilePath = $workspaceDir . '/' . $fileUniqueId;
$lockPath =  $workspaceDir . '/lock/';
$serialNumFilePath =  $workspaceDir . '/serialNo.txt';

$chunkNumber = $_POST['num'];
$totalNumChunks = $_POST['num_chunks'];
if (!file_exists($workspaceDir)) {
    mkdir($workspaceDir, 0777, true);
}

// Moving the uploaded chunk file from php temp path to our cache
move_uploaded_file($tmp_name, $tempTargetFilePath.$chunkNumber);


// Creating lock path
if (!file_exists($lockPath)) {
    mkdir($lockPath, 0777, true);
}

// Acquiring lock
while (true) {
	$lock = acquireLock($lockPath);
	if ($lock == 1) {
		break;
	}
	sleep(mt_rand(10, 50));
}

// Checking how many number of chunks have been merged serial wise
$serialNumber = getSerialNumber();
$updatedSerialNum = 0;
if ($serialNumber == 0) {
	if ($chunkNumber == 1) {
		$serialNumber = 1;
		$destFile = fopen($tempTargetFilePath.'1', 'ab');
		$i = $serialNumber + 1;
		for (; $i <= $totalNumChunks; $i++) {
			if (!file_exists($tempTargetFilePath.$i)) {
				break;
			}
			$srcFile = fopen($tempTargetFilePath.$i, 'rb');
			$buff = fread($srcFile, filesize($tempTargetFilePath.$i));
			fclose($srcFile);
			$write = fwrite($destFile, $buff);
			unlink($tempTargetFilePath.$i);
		}
		fclose($destFile);
		setSerialNumber($i - 1);
		$updatedSerialNum = $i - 1;
	}
} else {
	$i = $serialNumber + 1;
	$destFile = fopen($tempTargetFilePath.'1', 'ab');
	for (; $i <= $totalNumChunks; $i++) {
		if (!file_exists($tempTargetFilePath.$i)) {
			break;
		}
		$srcFile = fopen($tempTargetFilePath.$i, 'rb');
		$buff = fread($srcFile, filesize($tempTargetFilePath.$i));
		fclose($srcFile);
		$write = fwrite($destFile, $buff);
		unlink($tempTargetFilePath.$i);
	}
	fclose($destFile);
	setSerialNumber($i - 1);
	$updatedSerialNum = $i - 1;
}

// when this triggers - that means chunks are uploaded
if ($updatedSerialNum == $totalNumChunks) {
	$parentDir = $_POST['parentDir'];

	if (!ends_with($parentDir, '/')) {
		$parentDir = $parentDir . '/';
	}

	if (strpos($parentDir, '/') != 0) {
		$parentDir =  '/' . $parentDir;
	}
	if (strpos($parentDir, '/./') != false || strpos($parentDir, '..') != false) {
		return;
	}

	$parentDir = $baseDir . $parentDir;

	if (!file_exists($parentDir)) {
		mkdir($parentDir, 0777, true);
	}

	$originalFileName = $_FILES['upload']['name'];
	rename($tempTargetFilePath.'1', $parentDir . $originalFileName );
	rrmdir($workspaceDir);
}

// Releasing lock
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

function setSerialNumber($serialNumber) {
	global $serialNumFilePath;
	if (file_exists($serialNumFilePath)) {
		unlink($serialNumFilePath);
	}
	$myfile = fopen($serialNumFilePath, "w");
	fwrite($myfile, (string) $serialNumber);
	fclose($myfile);
}

function getSerialNumber() {
	global $serialNumFilePath;
	if (file_exists($serialNumFilePath)) {
		return (int) file_get_contents($serialNumFilePath);
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

function acquireLock($lockPath) {
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

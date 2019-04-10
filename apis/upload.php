<?php

include_once './base-dir.php';

$baseDir = getBaseDirectory();
if ($baseDir == null) {
	http_response_code(401);
	echo '{"status" : "failed", "message" : "Login Required"}';
	return;
}

$parentDir = $_POST['parentDir'];

function ends_with($haystack, $needles) {
	foreach ((array) $needles as $needle) {
		if ((string) $needle === substr($haystack, -strlen($needle))) {
			return true;
		}
	}
	return false;
}

if (!ends_with($parentDir, '/')) {
	$parentDir = $parentDir . '/';
}

if (strpos($parentDir, '/') != 0) {
	$parentDir =  '/' . $parentDir;
}

$parentDir = $baseDir . $parentDir . '/';
$num = $_POST['num'];

$target_path = $parentDir;

$tmp_name = $_FILES['upload']['tmp_name'];
$filename = $_FILES['upload']['name'];
$target_file = $target_path.$filename;
$num = $_POST['num'];
$num_chunks = $_POST['num_chunks'];
if (!file_exists($target_path)) {
    mkdir($lockPath, 0777, true);
}
move_uploaded_file($tmp_name, $target_file.$num);

$lockPath =  $target_path . 'locks/'. $filename . '/';
if (!file_exists($lockPath)) {
    mkdir($lockPath, 0777, true);
}

while (true) {
	$lock = getLock($lockPath, $filename);
	if ($lock == 1) {
		break;
	}

	sleep(10);
}

// count number of uploaded chunks
$chunksUploaded = 0;
for ( $i = 1; $i <= $num_chunks; $i++ ) {
    if ( file_exists( $target_file.$i ) ) {
         ++$chunksUploaded;
    }
}

// and THAT's what you were asking for
// when this triggers - that means your chunks are uploaded
if ($chunksUploaded == $num_chunks) {

    /* here you can reassemble chunks together */
    for ($i = 1; $i <= $num_chunks; $i++) {

      $file = fopen($target_file.$i, 'rb');
      $buff = fread($file, filesize($target_file.$i));
      fclose($file);

      $final = fopen($target_file, 'ab');
      $write = fwrite($final, $buff);
      fclose($final);

      unlink($target_file.$i);
	}
	rrmdir($lockPath);
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

?>

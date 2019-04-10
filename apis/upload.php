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

$lockPath =  $target_path . 'temp/'. $filename . '/lock/';

if (!file_exists($lockPath)) {
    mkdir($lockPath, 0777, true);
}
$chunksUploadedPath =  $target_path . 'temp/'. $filename . '/chunksUploaded.txt';

while (true) {
	$lock = getLock($lockPath, $filename);
	if ($lock == 1) {
		break;
	}

	sleep(10);
}

$uploadedChunks = updateAndGetChunksUploaded($chunksUploadedPath);

// and THAT's what you were asking for
// when this triggers - that means your chunks are uploaded
if ($uploadedChunks == $num_chunks) {
	$isFirst = 1;
    /* here you can reassemble chunks together */
    for ($i = 1; $i <= $num_chunks; $i++) {
	  if (file_exists($target_file.$i)) {
		if ($isFirst == 1) {
			rename($target_file.$i, $target_file);
			$isFirst = 0;
		} else {
			$source_file = fopen($target_file.$i, 'rb');
			$final = fopen($target_file, 'ab');
			if ($source_file !== false && $final !== false) {
				while (($buffer = fgets($source_file, 5242880)) !== false) {
					fwrite($final, $buffer);
				}
				fclose($source_file);
				fclose($final);
				unlink($target_file.$i);
			}

		}
	  }
	}
	rrmdir($target_path . 'temp/'. $filename);

} else if ($num > 1) {
	$prevNum = $num - 1;
	if (file_exists($target_file.$prevNum)) {

		$file = fopen($target_file.$num, 'rb');
		$buff = fread($file, filesize($target_file.$num));
		fclose($file);

		$final = fopen($target_file.$prevNum, 'ab');
		$write = fwrite($final, $buff);
		fclose($final);

		unlink($target_file.$num);
		rename($target_file.$prevNum, $target_file.$num );
	}
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

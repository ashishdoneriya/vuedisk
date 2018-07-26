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
$chunksize = 5 * (1024 * 1024); //5 MB (= 5 242 880 bytes) per one chunk of file.

if (file_exists($path)) {
    set_time_limit(300);
    $size = intval(sprintf("%u", filesize($path)));
    header('Content-Type: application/octet-stream');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: '.$size);
    header('Content-Disposition: attachment;filename="'.basename($path).'"');

    if ($size > $chunksize) {
        $handle = fopen($path, 'rb');
        while (!feof($handle)) {
          print(@fread($handle, $chunksize));
          ob_flush();
          flush();
        }
        fclose($handle);
		} else {
			readfile($path);
		}
		exit;
} else {
	echo 'File "'.$filename.'" does not exist!';
}
?>

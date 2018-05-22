<?php
include_once './base-dir.php';

$baseDir = getBaseDirectory();
if ($baseDir == null) {
	http_response_code(401);
	echo '{"status" : "failed", "message" : "Login Required"}';
	return;
}

header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents('php://input'), true);
$sourceDir = $data['sourceDir'];
$destinationDir = $data['destinationDir'];
$files = $data['files'];
$files = json_decode($files, true);

if (strpos($sourceDir, '/') != 0) {
	$sourceDir =  '/' . $sourceDir;
}
if (strpos($destinationDir, '/') != 0) {
	$destinationDir =  '/' . $destinationDir;
}
$sourceDir = $baseDir . $sourceDir;
$destinationDir = $baseDir . $destinationDir;

if (strpos($sourceDir, '/./') != false || strpos($sourceDir, '..') != false
	|| strpos($destinationDir, '/./') != false || strpos($destinationDir, '..') != false) {
	return;
}

foreach ($files as $file) {
	if (strpos($sourceDir, '/./') != false || strpos($sourceDir, '..') != false) {
		return;
	}
	if (!is_dir($sourceDir . '/' . $file)) {
		if (file_exists($destinationDir . '/' . $file)) {
			continue;
		}
		if (file_exists($sourceDir . '/.thumbnail/320px/' . $file)) {
			if (!file_exists( $destinationDir . '/.thumbnail')) {
				mkdir( $destinationDir . '/.thumbnail');
			}
			if (!file_exists($destinationDir . '/.thumbnail/320px')) {
				mkdir( $destinationDir . '/.thumbnail/320px');
			}
			rename($sourceDir . '/.thumbnail/320px/' . $file, $destinationDir . '/.thumbnail/320px/' . $file);
		}

		if (file_exists($sourceDir . '/.thumbnail/720px/' . $file)) {
			if (!file_exists( $destinationDir . '/.thumbnail')) {
				mkdir( $destinationDir . '/.thumbnail');
			}
			if (!file_exists($destinationDir . '/.thumbnail/720px')) {
				mkdir( $destinationDir . '/.thumbnail/720px');
			}
			rename($sourceDir . '/.thumbnail/720px/' . $file, $destinationDir . '/.thumbnail/720px/' . $file);
		}

	}
	rename($sourceDir . '/' . $file, $destinationDir . '/' . $file);

}

echo 'success';
?>

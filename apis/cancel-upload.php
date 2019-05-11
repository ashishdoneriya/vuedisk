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
$dirname = $data['fileUniqueId'];

$target_file = $baseDir .  '/.cache/vuedisk/' . $dirname;

rrmdir($target_file);

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
?>

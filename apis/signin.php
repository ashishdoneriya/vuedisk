
<?php

header("Access-Control-Allow-Methods: POST");

include_once './base-dir.php';

$data = json_decode(file_get_contents('php://input'), TRUE);
$username =$data['username'];
$password =$data['password'];
session_start();
$baseDir = $_SESSION['baseDirectory'];

if (!$password) {
	echo '{"status" : "failed", "message" : "Please enter valid information"}';
	return;
}
$baseDirectory = getBaseDirectory($username, $password);
if ($baseDirectory != null) {
	session_start();
	$_SESSION['LAST_ACTIVITY'] = time();
	$_SESSION['baseDirectory'] = $baseDirectory;
	echo '{"status" : "success"}';
} else {
	echo '{"status" : "failed", "message" : "Invalid username or password"}';
}
?>

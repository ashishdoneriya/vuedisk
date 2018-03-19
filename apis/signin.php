
<?php

header("Access-Control-Allow-Methods: POST");

include_once './base-dir.php';

$data = json_decode(file_get_contents('php://input'), TRUE);
$password =$data['password'];
if (!$password) {
	echo '{"status" : "failed", "message" : "Please enter valid information"}';
	return;
}
if ($password == $globalPassword) {
	session_start();
	$_SESSION['LAST_ACTIVITY'] = time();
	echo '{"status" : "success"}';
} else {
	echo '{"status" : "failed", "message" : "Invalid Password"}';
}
?>

<?php
	$baseDir = "/home/impadmin";
	$globalPassword = "pass@123";
	// Note : don't add '/' at the end
	
$credentials_list = array();
array_push($credentials_list, array('username' => 'ashish', 'password' => 'pass@123', 'baseDirectory' => '/home/ashish'));
array_push($credentials_list, array('username' => 'root', 'password' => 'root', 'baseDirectory' => '/'));
array_push($credentials_list, array('username' => 'user2', 'password' => 'pass2', 'baseDirectory' => '/path-to-a-directory-1'));

function getBaseDirectory($username, $password) {
	foreach ($credentials_list as $credentials) {
		if ($credentials['username'] == $username) {
			if ($credentials['password'] == $password) {
				return $credentials['baseDirectory'];
			} else {
				return false;
			}
		}
	}
	return false;
}


function isSessionActive() {
	session_start();
	if (isset($_SESSION['LAST_ACTIVITY']) && ($_SESSION['LAST_ACTIVITY'] + 86400) > time()) {
		$_SESSION['LAST_ACTIVITY'] = time();
		return true;
	} else {
		// remove all session variables
	session_unset();
		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (ini_get("session.use_cookies")) {
		    $params = session_get_cookie_params();
		    setcookie(session_name(), '', time() - 42000,
		        $params["path"], $params["domain"],
		        $params["secure"], $params["httponly"]
		    );
		}
		// destroy the session
		session_destroy();
		return false;
	}
}
?>

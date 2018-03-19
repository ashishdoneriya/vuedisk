<?php
	$baseDir = "/home/impadmin";
	$globalPassword = "pass@123";
	// Note : don't add '/' at the end
	
	
function isSessionActive() {
	session_start();
	if (isset($_SESSION['LAST_ACTIVITY']) && ($_SESSION['LAST_ACTIVITY'] + 86400) > time()) {
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

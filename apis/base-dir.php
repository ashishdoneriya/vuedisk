<?php

// Note : don't add '/' at the end
$credentials_list = array();
array_push($credentials_list, array('username' => 'admin', 'password' => 'admin', 'baseDirectory' => '/home/ashishdoneriya'));
array_push($credentials_list, array('username' => 'user2', 'password' => 'pass2', 'baseDirectory' => '/path-to-a-directory-1'));


/**
 *	Return the path of base directory if credentials are valid otherwise null
 */
function areCredentialsValid($username, $password) {
	global $credentials_list;
	foreach ($credentials_list as $credentials) {
		if ($credentials['username'] == $username) {
			if ($credentials['password'] == $password) {
				return $credentials['baseDirectory'];
			}
			break;
		}
	}
	return null;
}

/**
 * Returns the path of base directory saved in the session
 */
function getBaseDirectory() {
	session_start();
	if (isset($_SESSION['LAST_ACTIVITY']) && ($_SESSION['LAST_ACTIVITY'] + 86400) > time()) {
		$_SESSION['LAST_ACTIVITY'] = time();
		return $_SESSION['baseDirectory'];
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
		return null;
	}
}
?>

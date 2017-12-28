
<?php
// In PHP versions earlier than 4.1.0, $HTTP_POST_FILES should be used instead
// of $_FILES.

/**
 * Get the given variable from $_REQUEST or from the url
 * @param string $variableName
 * @param mixed $default
 * @return mixed|null
 */
function getParam($variableName, $default = null) {

	// Was the variable actually part of the request
	if (array_key_exists($variableName, $_REQUEST)) {
		return $_REQUEST[$variableName];
	}

	// Was the variable part of the url
	$urlParts = explode('/', preg_replace('/\?.+/', '', $_SERVER['REQUEST_URI']));
	$position = array_search($variableName, $urlParts);
	if ($position !== false && array_key_exists($position + 1, $urlParts)) {
		return $urlParts[$position + 1];
	}

	return $default;
}

function ends_with($haystack, $needles) {
	foreach ((array) $needles as $needle) {
		if ((string) $needle === substr($haystack, -strlen($needle))) {
			return true;
		}
	}
	return false;
}

$uploaddir = getParam('dir');

if (!ends_with($uploaddir, '/')) {
	$uploaddir = $uploaddir . '/';
}

$uploadfile = $uploaddir . str_replace(' ', ' ', basename($_FILES['file']['name']));

if (copy($_FILES['file']['tmp_name'], $uploadfile)) {
	echo "success";
} else {
	echo "error";
}
unlink($_FILES['file']['tmp_name']);

?>

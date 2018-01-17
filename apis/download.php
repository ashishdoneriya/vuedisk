<?php

include_once './base-dir.php';

    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    $path = $_GET['path'];
    $basename = basename($path);
    header('Content-Disposition: attachment; filename="' . $basename . '"');
    header("Content-Length: ". filesize($path));
    readfile($path);
?>

<?php
header("Access-Control-Allow-Methods: POST");
$data = json_decode(file_get_contents('php://input'), true);
$dirname = htmlspecialchars(strip_tags($data['dirname']));
$parentPath = htmlspecialchars(strip_tags($data['parentPath']));
mkdir($parentPath . '/' . $dirname, 644);
echo 'success';
?>
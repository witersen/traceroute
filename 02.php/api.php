<?php

define('BASE_PATH', __DIR__);

date_default_timezone_set('PRC');

require_once BASE_PATH . '/config/manual.config.php';

require_once BASE_PATH . '/app/function/socket.function.php';

$requestCode = time() . mt_rand();
$requestFile = BASE_PATH . '/data/' . $requestCode . ".json";
$replyCode = $requestCode . '_reply';
$replyFile = BASE_PATH . '/data/' . $replyCode . ".json";

$requestPayload = file_get_contents("php://input");
$requestPayload = !empty($requestPayload) ? json_decode($requestPayload, true) : array();

$info = array(
    "controller" => $_GET['c'],
    "action" => $_GET['a'],
    "requestPayload" => $requestPayload
);

file_put_contents($requestFile, json_encode($info));

requestReplyExec("php " . BASE_PATH . '/cli.php ' . $requestCode);

echo file_get_contents($replyFile);

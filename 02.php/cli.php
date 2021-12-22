<?php

define('BASE_PATH', __DIR__);

date_default_timezone_set('PRC');

require_once BASE_PATH . '/app/core/controller.class.php';

if (!isset($argv[1])) {
    return;
}

$requestCode = $argv[1];
$requestFile = BASE_PATH . '/data/' . $requestCode . ".json";
$replyCode = $requestCode . '_reply';
$replyFile = BASE_PATH . '/data/' . $replyCode . ".json";

$info = json_decode(file_get_contents($requestFile), true);

/**
 * 控制器
 */
$controller_perifx = $info["controller"]; //控制器前缀
$controller_name = $controller_perifx . '.class'; //控制器名称
$controller_path = BASE_PATH . '/app/controller/' . $controller_name . '.php'; //控制器路径

/**
 * 方法
 */
$action = $info["action"];

/**
 * 参数
 */
$requestPayload = $info["requestPayload"];

/**
 * 检查控制器和方法是否存在并实例化
 */
if (file_exists($controller_path)) {
    $controller = new $controller_perifx(); 
    if (is_callable(array($controller, $action))) {
        file_put_contents($replyFile, json_encode($controller->$action($requestPayload)));
    }
}

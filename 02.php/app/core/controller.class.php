<?php

/*
 * 控制器基类，所有的控制器都要继承此类
 */

//require model
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/manual.config.php';

//require controller
require_once BASE_PATH . '/app/controller/icmp.class.php';
require_once BASE_PATH . '/app/controller/location.class.php';

//require function
require_once BASE_PATH . '/app/function/format.function.php';
require_once BASE_PATH . '/app/function/socket.function.php';

class Controller {

    function __construct() {
        
    }

}

<?php
/**
 * Created by PhpStorm.
 * User: mchenyuxiang
 * Date: 2016/12/23
 * Time: 15:53
 */

if(version_compare(PHP_VERSION,'5.3.0','<')){
    die('require PHP > 5.3.0!');
}

define('BIND_MODULE','Admin');
define('APP_DEBUG',false);
define('APP_PATH',"./App/");
define('THINK_PATH',"./Include/");
require THINK_PATH . 'ThinkPHP.php';
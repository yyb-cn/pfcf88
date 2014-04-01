<?php 
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | Copyright (c) 2012 http://www.yigaoboda.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 
// +----------------------------------------------------------------------

if(!defined("ADMIN_ROOT"))
{
	die("Invalid access");
}
require './system/common.php';

define('BASE_PATH','./');
define('THINK_PATH', './admin/ThinkPHP');
//定义项目名称和路�?
define('APP_NAME', 'admin');
define('APP_PATH', './admin');

// 加载框架入口文件 
require(THINK_PATH."/ThinkPHP.php");

//实例化一个网站应用实�?
$AppWeb = new App(); 
//应用程序初始�?
$AppWeb->run();

?>
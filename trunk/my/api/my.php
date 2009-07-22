<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: my.php 10611 2008-12-11 01:16:22Z zhengqingpeng $
*/

error_reporting(0);
define('IN_UCHOME', TRUE);
define('X_VER', '1.5');
define('S_ROOT', substr(dirname(__FILE__), 0, -3));
//获取时间
$_SGLOBAL['timestamp'] = time();
$space = array();
include_once S_ROOT.'./config.php';
include_once S_ROOT.'./data/data_config.php';
include_once S_ROOT.'./source/function_common.php';
include_once S_ROOT.'./api/class/MyBase.php';
include_once S_ROOT.'./api/class/APIErrorResponse.php';
include_once S_ROOT.'./api/class/APIResponse.php';

//链接数据库
dbconnect();

$server = new my();
$response = $server->parseRequest();
echo $server->formatResponse($response);
?>
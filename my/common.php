<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: common.php 7589 2008-06-13 10:11:32Z liguode $
*/

@define('IN_UCHOME', TRUE);
define('X_VER', '1.2');
define('X_RELEASE', '20080612');
define('D_BUG', '0');

//D_BUG?error_reporting(7):error_reporting(0);
$_SGLOBAL = $_SCONFIG = $_SBLOCK = $_TPL = $_SCOOKIE = $space = array();

//程序目录
define('S_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);

//获取时间
$mtime = explode(' ', microtime());
$_SGLOBAL['timestamp'] = $mtime[1];
$_SGLOBAL['supe_starttime'] = $_SGLOBAL['timestamp'] + $mtime[0];

//基本文件
if(!@include_once(S_ROOT.'./config.php')) {
	header("Location: install/index.php");
	exit();
}
include_once(S_ROOT.'./source/function_common.php');

//链接数据库
dbconnect();

//配置文件
if(!@include_once(S_ROOT.'./data/data_config.php')) {
	include_once(S_ROOT.'./source/function_cache.php');
	config_cache();
}

//强制使用字符集
if($_SCONFIG['headercharset']) {
	@header('Content-Type: text/html; charset='.$_SC['charset']);
}

//GPC过滤
$magic_quote = get_magic_quotes_gpc();
if(empty($magic_quote)) {
	$_GET = saddslashes($_GET);
	$_POST = saddslashes($_POST);
}

//COOKIE
$prelength = strlen($_SC['cookiepre']);
foreach($_COOKIE as $key => $val) {
	if(substr($key, 0, $prelength) == $_SC['cookiepre']) {
		$_SCOOKIE[(substr($key, $prelength))] = empty($magic_quote) ? saddslashes($val) : $val;
	}
}

//启用GIP
if ($_SC['gzipcompress'] && function_exists('ob_gzhandler')) {
	ob_start('ob_gzhandler');
} else {
	ob_start();
}

//初始化
$_SGLOBAL['supe_uid'] = 0;
$_SGLOBAL['supe_username'] = '';
$_SGLOBAL['inajax'] = empty($_GET['inajax'])?0:intval($_GET['inajax']);
$_SGLOBAL['ajaxmenuid'] = empty($_GET['ajaxmenuid'])?'':$_GET['ajaxmenuid'];
$_SGLOBAL['refer'] = empty($_SERVER['HTTP_REFERER'])?'':$_SERVER['HTTP_REFERER'];

//获取用户信息
getuser();

//应用列表
@include_once(S_ROOT.'./data/data_app.php');
if(empty($_SGLOBAL['app'])) {
	include_once(S_ROOT.'./source/function_cache.php');
	app_cache();
}

?>
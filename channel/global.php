<?php 

//exit(print_r($_SERVER,1));

if(strtolower(substr($_SERVER['SCRIPT_FILENAME'],-10))=='global.php') exit('No direct script access allowed');
/* ======= >>> KingCMS <<< ========================== *

 +   @License      http://www.KingCMS.com/license/    +

 +   @Link         http://www.KingCMS.com/            +

 +   @E-Mail       KingCMS(a)Gmail.com                +

 +   Copyright (c) KingCMS.com All Rights Reserved.   +

 * ================================================== */


define('ROOT',dirname(__FILE__).'/');

define('INC',True);

require ROOT.'config.php';

require ROOT.'system/core.class.php';

require ROOT.'system/lib/func.php';

//Beta版->正式版的重大变更，自动升级config.php
if(!defined('DB_TYPE') && defined('KC_DB_TYPE')){
	$isupdate=1;
	$s=kc_f_get_contents('config.php');
	$s=str_replace('KC_DB_','DB_',$s);
	$s=str_replace('DB_ADMIN','KC_DB_ADMIN',$s);
	$s=str_replace('KC_CONFIG_','',$s);
	$s=str_replace('KC_CACHE_PATH','PATH_CACHE',$s);
	/*
	$s=str_replace('KC_DB_TYPE','DB_TYPE',$s);
	$s=str_replace('KC_DB_CHARSET','DB_CHARSET',$s);
	$s=str_replace('KC_DB_PRE','DB_PRE',$s);
	//$s=str_replace('KC_DB_ADMIN','DB_ADMIN',$s);
	$s=str_replace('KC_DB_HOST','DB_HOST',$s);
	$s=str_replace('KC_DB_DATA','DB_DATA',$s);
	$s=str_replace('KC_DB_USER','DB_USER',$s);
	$s=str_replace('KC_DB_PASS','DB_PASS',$s);
	$s=str_replace('KC_DB_SQLITE','DB_SQLITE',$s);
	
	$s=str_replace('KC_CONFIG_LANGUAGE','LANGUAGE',$s);
	$s=str_replace('KC_CACHE_PATH','PATH_CACHE',$s);
	$s=str_replace('KC_CONFIG_DEBUG','DEBUG',$s);
	*/
	kc_f_put_contents('config.php',$s);
	
	require ROOT.'config.php';
	
}

require ROOT.'system/lib/kc_'.DB_TYPE.'_class.php';

require ROOT.'system/lib/kc_language_class.php';

require ROOT.'system/lib/kc_cache_class.php';

require ROOT.'system/lib/kc_skin_class.php';

/* ------>>> 全局变量 <<<---------------------------- */

$action=isset($_GET['action'])?$_GET['action']:'';

$ismethod=False;  //是否POST提交

$ischeck=True;  //是否通过表单验证

$check_num=0;  //出现验证错误次数


/* ------>>> 定义常量 <<<---------------------------- */

define('KC_MAGIC_QUOTES_GPC',get_magic_quotes_gpc());

define('NL',chr(13).chr(10));

define('PAGE_CHARSET','UTF-8');

define('DB_PREFIX',DB_DATA.'.'.DB_PRE);

define('CMD',kc_get('CMD',4));


/* ------>>> 开始执行页面 <<<------------------------ */

kc_pageLoad();

$king=new KingCMS_class;

if(!empty($isupdate)){//检测是否Beta版更新为正式版
	$_array=array(
		'kmsg'=>$king->lang->get('system/install/update'),
		'adminname'=>'CiBill',
		'ndate'=>time(),
		'issys'=>1,
		'klink'=>'',
	);
	$king->db->insert('%s_message',$_array);
	$cachepath='system/message';
	$king->cache->rd($cachepath);
	
	$_array=array(
		'admineditor'=>'xheditor',
	);
	$_where='admineditor=\'nicedit\'';
	$king->db->update('%s_admin',$_array,$_where);
}

DEBUG && set_error_handler('kc_error_handler');

$king->pageEngine();

?>
<?php 

//exit(print_r($_SERVER,1));

if(strtolower(substr($_SERVER['SCRIPT_FILENAME'],-10))=='global.php') exit('No direct script access allowed');
/* ======= >>> KingCMS <<< ========================== *

 +   @License      http://www.KingCMS.com/license/    +

 +   @Link         http://www.KingCMS.com/            +

 +   @E-Mail       KingCMS(a)Gmail.com                +

 +   Copyright (c) KingCMS.com All Rights Reserved.   +

 * ================================================== */


define('KC_ROOT',dirname(__FILE__).'/');

define('KC_IN',True);

require_once KC_ROOT.'config.php';

require_once KC_ROOT.'system/lib/kc.class.php';

require_once KC_ROOT.'system/lib/kc.func.php';

require_once KC_ROOT.'system/lib/'.KC_DB_TYPE.'.class.php';

require_once KC_ROOT.'system/lib/language.class.php';

require_once KC_ROOT.'system/lib/cache.class.php';

require_once KC_ROOT.'system/lib/skin.class.php';


/* ------>>> 全局变量 <<<---------------------------- */

$action=isset($_GET['action'])?$_GET['action']:'';

$ismethod=False;  //是否POST提交

$ischeck=True;  //是否通过表单验证

$check_num=0;  //出现验证错误次数


/* ------>>> 定义常量 <<<---------------------------- */

define('KC_MAGIC_QUOTES_GPC',get_magic_quotes_gpc());

define('NL',chr(13).chr(10));

define('KC_PAGE_CHARSET','UTF-8');

define('KC_DB_PREFIX',KC_DB_DATA.'.'.KC_DB_PRE);

define('CMD',kc_get('CMD',4));


/* ------>>> 开始执行页面 <<<------------------------ */

kc_pageLoad();

$king=new KingCMS_class;

KC_CONFIG_DEBUG && set_error_handler('kc_error_handler');

$king->pageEngine();

?>
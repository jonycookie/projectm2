<?php
/**
 * @package iCMS V3.1
 * @copyright 2007-2009, iDreamSoft
 * @license http://www.idreamsoft.cn iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
define('iCMS', TRUE);
define('iCMS_BUG', FALSE);
define('iCMS_TPL_BUG', FALSE);
define('iPATH',dirname(strtr(__FILE__,'\\','/'))."/");
define('iCMS_PLUGINS_PATH',iPATH."plugins");
error_reporting(iCMS_BUG?E_ALL ^ E_NOTICE:0);
$_iGLOBAL=array();
$_iGLOBAL['timestamp']=time();
header('Content-Type: text/html; charset=utf-8');
// 防止 PHP 5.1.x 使用时间函数报错
function_exists('date_default_timezone_set') && date_default_timezone_set('Etc/GMT+0');
unset($_ENV,$HTTP_ENV_VARS,$_REQUEST,$HTTP_POST_VARS,$HTTP_GET_VARS,$HTTP_POST_FILES,$HTTP_COOKIE_VARS,$HTTP_SESSION_VARS,$HTTP_SERVER_VARS);
unset($GLOBALS['_ENV'],$GLOBALS['HTTP_ENV_VARS'],$GLOBALS['_REQUEST'],$GLOBALS['HTTP_POST_VARS'],$GLOBALS['HTTP_GET_VARS'],$GLOBALS['HTTP_POST_FILES'],$GLOBALS['HTTP_COOKIE_VARS'],$GLOBALS['HTTP_SESSION_VARS'],$GLOBALS['HTTP_SERVER_VARS']);

if (ini_get('register_globals')){
	isset($_REQUEST['GLOBALS']) && die('发现试图覆盖 GLOBALS 的操作');
	// Variables that shouldn't be unset
	$noUnset = array('GLOBALS', '_GET', '_POST', '_COOKIE','_SERVER', '_ENV', '_FILES');
	$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
	foreach ( $input as $k => $v ){
		if ( !in_array($k, $noUnset) && isset($GLOBALS[$k]) ) {
			$GLOBALS[$k] = NULL;
			unset($GLOBALS[$k]);
		}
	}
}
// Fix for IIS, which doesn't set REQUEST_URI
if ( empty( $_SERVER['REQUEST_URI'] ) ) {

	// IIS Mod-Rewrite
	if (isset($_SERVER['HTTP_X_ORIGINAL_URL'])) {
		$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
	}
	// IIS Isapi_Rewrite
	else if (isset($_SERVER['HTTP_X_REWRITE_URL'])) {
		$_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
	}else{
		// Some IIS + PHP configurations puts the script-name in the path-info (No need to append it twice)
		if ( $_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME'] )
			$_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
		else
			$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];

		// Append the query string if it exists and isn't null
		if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
			$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
		}
	}
}

// Fix for PHP as CGI hosts that set SCRIPT_FILENAME to something ending in php.cgi for all requests
if ( isset($_SERVER['SCRIPT_FILENAME']) && ( strpos($_SERVER['SCRIPT_FILENAME'], 'php.cgi') == strlen($_SERVER['SCRIPT_FILENAME']) - 7 ) )
	$_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];

// Fix for Dreamhost and other PHP as CGI hosts
if (strpos($_SERVER['SCRIPT_NAME'], 'php.cgi') !== false)
	unset($_SERVER['PATH_INFO']);

// Fix empty PHP_SELF
$PHP_SELF = $_SERVER['PHP_SELF'];
empty($PHP_SELF) && $_SERVER['PHP_SELF'] = $PHP_SELF = preg_replace("/(\?.*)?$/",'',$_SERVER["REQUEST_URI"]);

version_compare( '4.3', phpversion(), '>' ) &&	die( '您的服务器运行的 PHP 版本是' . phpversion() . ' 但 iCMS 要求至少 4.3。' );
!extension_loaded('mysql') && die( '您的 PHP 安装看起来缺少 MySQL 数据库部分，这对 iCMS 来说是必须的。' );

define('__SELF__',$PHP_SELF);
define('__REF__',empty($_SERVER['HTTP_REFERER'])?'':$_SERVER['HTTP_REFERER']);

require_once iPATH.'include/compat.php';
require_once iPATH.'include/version.php';
require_once iPATH.'include/cookie.php';
require_once iPATH.'include/config.php';
require_once iPATH.'include/mysql.class.php';
require_once iPATH.'include/site.config.php';
require_once iPATH.'include/common.php';


if ( get_magic_quotes_gpc() ) {
	$_GET    = stripslashes_deep($_GET);
	$_POST   = stripslashes_deep($_POST);
	$_COOKIE = stripslashes_deep($_COOKIE);
}
$_GET	 = add_magic_quotes($_GET);
$_POST   = add_magic_quotes($_POST);
$_COOKIE = add_magic_quotes($_COOKIE);
$_SERVER = add_magic_quotes($_SERVER);

require_once iPATH.'include/template/template.class.php';
require_once iPATH.'include/cache.class.php';
require_once iPATH."include/iCMS.class.php";
// 系统URL
if (empty($config['url'])) {
	$uri= parse_url('http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI']).'/');
}else{
	$uri =parse_url(substr($config['url'], -1) != '/'?$config['url'].'/':$config['url']);
}
$config['dir'] 		= $uri['path'];
$config['domain'] 	= substr($uri['host'],strpos($uri['host'],'.')+1);
$config['rewrite']	= unserialize($config['rewrite']);
$config['bbs']		= unserialize($config['bbs']);
//$config['rewrite']['split']='-';
$iCMS = new iCMS;
unset($config,$db,$uri);

if($iCMS->config['customlink']&&substr($PHP_SELF,strrpos($PHP_SELF,'/'))!='/admincp.php'){
	$iCMS->rewrite=true;
	$QSArray 	= explode($iCMS->config['rewrite']['split'],$iCMS->config['rewrite']['ext'] ? substr($_SERVER['QUERY_STRING'],0,strrpos($_SERVER['QUERY_STRING'],$iCMS->config['rewrite']['ext'])) : $_SERVER['QUERY_STRING']);
	$QSC		= count($QSArray);
	for($i=0;$i<$QSC;$i++){
		($QSArray[$i]!=''||$QSArray[++$i]!="") && $_GET[$QSArray[$i]] =rawurldecode($QSArray[++$i]);
	}
}
isset($_GET['page']) && $page=(int)$_GET['page'];
if(isset($_GET['date'])){
	list($y,$m,$d)=explode('_',$_GET['date']);
	$iCMS->date=array('y'=>$y,'m'=>$m,'d'=>$d,'total'=>date('t',mktime(0,0,0,$m+1,0,$y)));
}
//$iCMS->clear_compiled_tpl();
$iCMS->db->show_errors();
?>
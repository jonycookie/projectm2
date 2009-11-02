<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: uc.php 7194 2008-04-28 03:23:03Z zhengqingpeng $
*/

error_reporting(7);

define('UC_VERSION', '1.0.0');	//用户中心版本标识
define('API_DELETEUSER', 1);		//用户删除 API 接口开关
define('API_RENAMEUSER', 1);		//用户名修改 API 接口开关
define('API_GETTAG', 1);		//获取标签 API 接口开关
define('API_SYNLOGIN', 1);		//同步登录 API 接口开关
define('API_SYNLOGOUT', 1);		//同步登出 API 接口开关
define('API_UPDATEPW', 1);		//更改用户密码 开关
define('API_UPDATEBADWORDS', 1);	//更新关键字列表 开关
define('API_UPDATEHOSTS', 1);		//更新HOST文件 开关
define('API_UPDATEAPPS', 1);		//更新应用列表 开关
define('API_UPDATECLIENT', 1);		//更新客户端缓存 开关
define('API_UPDATECREDIT', 1);		//更新用户积分 开关
define('API_GETCREDITSETTINGS', 1);	//向 UC 提供积分设置 开关
define('API_UPDATECREDITSETTINGS', 1);	//更新应用积分设置 开关

define('API_RETURN_SUCCEED', '1');
define('API_RETURN_FAILED', '-1');
define('API_RETURN_FORBIDDEN', '-2');

define('IN_UCHOME', TRUE);
define('S_ROOT', substr(dirname(__FILE__), 0, -3));

$_SGLOBAL = $_SCONFIG = $_SBLOCK = $_TPL = $_SCOOKIE = $space = array();

include_once S_ROOT.'./config.php';
include_once S_ROOT.'./data/data_config.php';
include_once S_ROOT.'./uc_client/lib/xml.class.php';
include_once S_ROOT.'./source/function_common.php';

//获取时间
$_SGLOBAL['timestamp'] = time();

//链接数据库
dbconnect();

//GPC过滤
$magic_quote = get_magic_quotes_gpc();
if(empty($magic_quote)) {
	$_GET = saddslashes($_GET);
	$_POST = saddslashes($_POST);
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

//处理
$code = $_GET['code'];
parse_str(authcode($code, 'DECODE'), $get);

define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
if(MAGIC_QUOTES_GPC) {
	$get = sstripslashes($get);
}
    
if(time() - $get['time'] > 3600) {
	exit('Authracation has expiried');
}
if(empty($get)) {
	exit('Invalid Request');
}
$action = $get['action'];
if($action == 'test') {

	exit(API_RETURN_SUCCEED);

} elseif($action == 'deleteuser') {

	!API_DELETEUSER && exit(API_RETURN_FORBIDDEN);

	//note 用户删除 API 接口
	include_once S_ROOT.'./source/function_delete.php';

	//获得用户
	$uids = $get['ids'];
	$query = $_SGLOBAL['db']->query("SELECT uid FROM ".tname('member')." WHERE uid IN ($uids)");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		deletespace($value['uid']);
	}

	exit(API_RETURN_SUCCEED);

} elseif($action == 'renameuser') {

	!API_RENAMEUSER && exit(API_RETURN_FORBIDDEN);

	//编辑用户
	$old_username = $get['oldusername'];
	$new_username = $get['newusername'];

	$_SGLOBAL['db']->query("UPDATE ".tname('member')." SET username='$new_username' WHERE username='$old_username'");
	$_SGLOBAL['db']->query("UPDATE ".tname('thread')." SET username='$new_username' WHERE username='$old_username'");
	$_SGLOBAL['db']->query("UPDATE ".tname('tagspace')." SET username='$new_username' WHERE username='$old_username'");
	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET username='$new_username' WHERE username='$old_username'");
	$_SGLOBAL['db']->query("UPDATE ".tname('session')." SET username='$new_username' WHERE username='$old_username'");
	$_SGLOBAL['db']->query("UPDATE ".tname('post')." SET username='$new_username' WHERE username='$old_username'");
	$_SGLOBAL['db']->query("UPDATE ".tname('poke')." SET fromusername='$new_username' WHERE fromusername='$old_username'");
	$_SGLOBAL['db']->query("UPDATE ".tname('notification')." SET author='$new_username' WHERE author='$old_username'");
	$_SGLOBAL['db']->query("UPDATE ".tname('friend')." SET fusername='$new_username' WHERE fusername='$old_username'");
	$_SGLOBAL['db']->query("UPDATE ".tname('feed')." SET username='$new_username' WHERE username='$old_username'");
	$_SGLOBAL['db']->query("UPDATE ".tname('doing')." SET username='$new_username' WHERE username='$old_username'");
	$_SGLOBAL['db']->query("UPDATE ".tname('comment')." SET author='$new_username' WHERE author='$old_username'");
	$_SGLOBAL['db']->query("UPDATE ".tname('blog')." SET username='$new_username' WHERE username='$old_username'");
	$_SGLOBAL['db']->query("UPDATE ".tname('album')." SET username='$new_username' WHERE username='$old_username'");

	exit(API_RETURN_SUCCEED);

} elseif($action == 'gettag') {

	!API_GETTAG && exit(API_RETURN_FORBIDDEN);

	$name = trim($get['id']);
	if(empty($name) || !preg_match('/^([\x7f-\xff_-]|\w)+$/', $name) || strlen($name) > 20) {
		exit(API_RETURN_FAILED);
	}

	$tag = $_SGLOBAL['db']->fetch_array($_SGLOBAL['db']->query("SELECT * FROM ".tname('tag')." WHERE tagname='$name'"));
	if($tag['closed']) {
		exit(API_RETURN_FAILED);
	}

	$PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
	$siteurl = 'http://'.$_SERVER['HTTP_HOST'].preg_replace("/\/+(api)?\/*$/i", '', substr($PHP_SELF, 0, strrpos($PHP_SELF, '/'))).'/';

	$query = $_SGLOBAL['db']->query("SELECT b.*
		FROM ".tname('tagblog')." tb, ".tname('blog')." b
		WHERE b.blogid=tb.blogid AND tb.tagid='$tag[tagid]' AND b.friend=0
		ORDER BY b.dateline DESC
		LIMIT 0,10");
	$bloglist = array();
	while($value = $_SGLOBAL['db']->fetch_array($query)) {
		$bloglist[] = array(
			'subject' => $value['subject'],
			'uid' => $value['uid'],
			'username' => $value['username'],
			'dateline' => $value['dateline'],
			'url' => $siteurl."space.php?uid=$value[uid]&amp;do=blog&amp;id=$value[blogid]",
			'spaceurl' => $siteurl."space.php?uid=$value[uid]"
		);
	}

	$return = array($name, $bloglist);
	echo xml_serialize($return);

} elseif($action == 'synlogin') {

	!API_SYNLOGIN && exit(API_RETURN_FORBIDDEN);

	//note 同步登录 API 接口
	obclean();
	header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

	$cookietime = 31536000;
	$uid = intval($get['uid']);
	$query = $_SGLOBAL['db']->query("SELECT uid, username, password FROM ".tname('member')." WHERE uid='$uid'");
	if($member = $_SGLOBAL['db']->fetch_array($query)) {
		$space = insertsession($member);
		//设置cookie
		ssetcookie('auth', authcode("$member[password]\t$member[uid]", 'ENCODE'), $cookietime);
	}
	ssetcookie('loginuser', $get['username'], $cookietime);

} elseif($action == 'synlogout') {

	!API_SYNLOGOUT && exit(API_RETURN_FORBIDDEN);

	//note 同步登出 API 接口
	obclean();
	header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');

	clearcookie();

} elseif($action == 'updateclient') {

	!API_UPDATECLIENT && exit(API_RETURN_FORBIDDEN);

	$post = xml_unserialize(file_get_contents('php://input'));
	$cachefile = S_ROOT.'./uc_client/data/cache/settings.php';
	$fp = fopen($cachefile, 'w');
	$s = "<?php\r\n";
	$s .= '$_CACHE[\'settings\'] = '.var_export($post, TRUE).";\r\n";
	fwrite($fp, $s);
	fclose($fp);

	exit(API_RETURN_SUCCEED);

} elseif($action == 'updatepw') {

	!API_UPDATEPW && exit(API_RETURN_FORBIDDEN);

	$username = $get['username'];
	$newpw = md5(time().rand(100000, 999999));
	$_SGLOBAL['db']->query("UPDATE ".tname('member')." SET password='$newpw' WHERE username='$username'");

	exit(API_RETURN_SUCCEED);

} elseif($action == 'updatebadwords') {

	!API_UPDATEBADWORDS && exit(API_RETURN_FORBIDDEN);

	$post = xml_unserialize(file_get_contents('php://input'));
	$cachefile = S_ROOT.'./uc_client/data/cache/badwords.php';
	$fp = fopen($cachefile, 'w');
	$s = "<?php\r\n";
	$s .= '$_CACHE[\'badwords\'] = '.var_export($post, TRUE).";\r\n";
	fwrite($fp, $s);
	fclose($fp);

	exit(API_RETURN_SUCCEED);

} elseif($action == 'updatehosts') {

	!API_UPDATEHOSTS && exit(API_RETURN_FORBIDDEN);

	$post = xml_unserialize(file_get_contents('php://input'));
	$cachefile = S_ROOT.'./uc_client/data/cache/hosts.php';
	$fp = fopen($cachefile, 'w');
	$s = "<?php\r\n";
	$s .= '$_CACHE[\'hosts\'] = '.var_export($post, TRUE).";\r\n";
	fwrite($fp, $s);
	fclose($fp);

	exit(API_RETURN_SUCCEED);

} elseif($action == 'updateapps') {

	!API_UPDATEAPPS && exit(API_RETURN_FORBIDDEN);

	$post = xml_unserialize(file_get_contents('php://input'));
	if(isset($post['UC_API'])) {
		unset($post['UC_API']);
	}
	$cachefile = S_ROOT.'./uc_client/data/cache/apps.php';
	$fp = fopen($cachefile, 'w');
	$s = "<?php\r\n";
	$s .= '$_CACHE[\'apps\'] = '.var_export($post, TRUE).";\r\n";
	fwrite($fp, $s);
	fclose($fp);

	exit(API_RETURN_SUCCEED);

} elseif($action == 'updatecredit') {

	!UPDATECREDIT && exit(API_RETURN_FORBIDDEN);

	$amount = $get['amount'];
	$uid = $get['uid'];

	$_SGLOBAL['db']->query("UPDATE ".tname('space')." SET credit=credit+'$amount' WHERE uid='$uid'");

	exit(API_RETURN_SUCCEED);

} elseif($action == 'getcreditsettings') {

	!GETCREDITSETTINGS && exit(API_RETURN_FORBIDDEN);

	$credits = array();
	$credits[1] = array(mlang('credit'), mlang('credit_unit'));

	echo xml_serialize($credits);

} elseif($action == 'updatecreditsettings') {

	!API_UPDATECREDITSETTINGS && exit(API_RETURN_FORBIDDEN);

	$outextcredits = array();

	foreach($get['credit'] as $appid => $credititems) {
		if($appid == UC_APPID) {
			foreach($credititems as $value) {
				$outextcredits[$value['appiddesc'].'|'.$value['creditdesc']] = array(
					'creditsrc' => $value['creditsrc'],
					'title' => $value['title'],
					'unit' => $value['unit'],
					'ratio' => $value['ratio']
				);
			}
		}
	}

	$cachefile = S_ROOT.'./uc_client/data/cache/creditsettings.php';
	$fp = fopen($cachefile, 'w');
	$s = "<?php\r\n";
	$s .= '$_CACHE[\'creditsettings\'] = '.var_export($outextcredits, TRUE).";\r\n";
	fwrite($fp, $s);
	fclose($fp);

	exit(API_RETURN_SUCCEED);

} else {

	exit(API_RETURN_FORBIDDEN);

}
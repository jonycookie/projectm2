<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: admincp.php 7257 2008-05-04 02:20:28Z liguode $
*/

include_once('./common.php');
include_once(S_ROOT.'./source/function_admincp.php');

//是否关闭站点
checkclose();

//需要登录
if(empty($_SGLOBAL['supe_uid'])) {
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		ssetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
	} else {
		ssetcookie('_refer', rawurlencode('admincp.php?ac='.$_GET['ac']));
	}
	showmessage('to_login', 'do.php?ac=login', 0);
}

$acs = array(
	array('config', 'privacy', 'network', 'usergroup', 'credit', 'profilefield', 'profield', 'tagtpl'),
	array('space', 'tag', 'mtag'),
	array('ad', 'censor', 'template', 'cache', 'backup', 'stat', 'block', 'cron'),
	array('blog', 'album', 'pic', 'comment', 'thread', 'post', 'doing', 'feed', 'share')
);
if(empty($_GET['ac']) || (!in_array($_GET['ac'], $acs[0]) && !in_array($_GET['ac'], $acs[1]) && !in_array($_GET['ac'], $acs[2]) && !in_array($_GET['ac'], $acs[3]))) {
	$ac = 'index';
} else {
	$ac = $_GET['ac'];
}

//来源
if(!preg_match("/admincp\.php/", $_SGLOBAL['refer'])) $_SGLOBAL['refer'] = "admincp.php?ac=$ac";

//设置菜单Cookie
$cmenus = empty($_SCOOKIE['admincp_menu'])?array():explode('|', $_SCOOKIE['admincp_menu']);
if($ac == 'index') {
	$cmenus = array_slice($cmenus, 0, 5);
} else {
	$cmenus = array_slice(array_unique(array_merge(array($ac), $cmenus)), 0, 5);
	ssetcookie('admincp_menu', implode('|', $cmenus), 365*24*3600);
}

//菜单激活
$menuactive = array($ac => ' class="active"');

//二次登录确认(半个小时)
$cpaccess = 0;
$query = $_SGLOBAL['db']->query("SELECT errorcount FROM ".tname('adminsession')." WHERE uid='$_SGLOBAL[supe_uid]' AND dateline+1800>='$_SGLOBAL[timestamp]'");
if($session = $_SGLOBAL['db']->fetch_array($query)) {
	if($session['errorcount'] == -1) {
		$_SGLOBAL['db']->query("UPDATE ".tname('adminsession')." SET dateline='$_SGLOBAL[timestamp]' WHERE uid='$_SGLOBAL[supe_uid]'");
		$cpaccess = 2;
	} elseif($session['errorcount'] <= 3) {
		$cpaccess = 1;
	}
} else {
	$_SGLOBAL['db']->query("DELETE FROM ".tname('adminsession')." WHERE uid='$_SGLOBAL[supe_uid]' OR dateline+1800<'$timestamp'");
	$_SGLOBAL['db']->query("INSERT INTO ".tname('adminsession')." (uid, ip, dateline, errorcount)
		VALUES ('$_SGLOBAL[supe_uid]', '".getonlineip()."', '$_SGLOBAL[timestamp]', '0')");
	$cpaccess = 1;
}
switch ($cpaccess) {
	case '1'://可以登录
		if(submitcheck('loginsubmit')) {
			if(!$passport = getpassport($_SGLOBAL['supe_username'], $_POST['password'])) {
				$_SGLOBAL['db']->query("UPDATE ".tname('adminsession')." SET errorcount=errorcount+1 WHERE uid='$_SGLOBAL[supe_uid]'");
				cpmessage('enter_the_password_is_incorrect', 'admincp.php');
			} else {
				$_SGLOBAL['db']->query("UPDATE ".tname('adminsession')." SET errorcount='-1' WHERE uid='$_SGLOBAL[supe_uid]'");
				$refer = empty($_SCOOKIE['_refer'])?$_SGLOBAL['refer']:rawurldecode($_SCOOKIE['_refer']);
				if(empty($refer) || preg_match("/(login)/i", $refer)) {
					$refer = 'admincp.php';
				}
				ssetcookie('_refer', '');
				showmessage('login_success', $refer, 0);
			}
		} else {
			if($_SERVER['REQUEST_METHOD'] == 'GET') {
				ssetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
			} else {
				ssetcookie('_refer', rawurlencode('admincp.php?ac='.$_GET['ac']));
			}
			include template('admin/tpl/login');
			exit();
		}
		break;
	case '2'://登录成功
		break;
	default://尝试次数太多禁止登录
		cpmessage('excessive_number_of_attempts_to_sign');
		break;
}

//权限
$isfounder = ckfounder($_SGLOBAL['supe_uid']);
@include_once(S_ROOT.'./data/data_usergroup.php');
$menus = array();
if(!empty($_SGLOBAL['usergroup'][$_SGLOBAL['member']['groupid']])) {
	$megroup = $_SGLOBAL['usergroup'][$_SGLOBAL['member']['groupid']];
	$megroup['manageprivacy'] = $megroup['manageconfig'];//隐私设置
	for($i=0; $i<3; $i++) {
		foreach ($acs[$i] as $value) {
			if($isfounder || $megroup['manage'.$value]) {
				$menus[$i][$value] = 1;
				$_SGLOBAL['usergroup'][$_SGLOBAL['member']['groupid']]['manage'.$value] = 1;
			}
		}
	}
}

include_once(S_ROOT.'./admin/admincp_'.$ac.'.php');

//模板
include_once template("admin/tpl/$ac");

?>
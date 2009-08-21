<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cp_common.php 6565 2008-03-14 09:26:09Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$op = empty($_GET['op'])?'':trim($_GET['op']);

if($op == 'logout') {
	//删除session
	if($_SGLOBAL['supe_uid']) {
		$_SGLOBAL['db']->query("DELETE FROM ".tname('session')." WHERE uid='$_SGLOBAL[supe_uid]'");
		$_SGLOBAL['db']->query("DELETE FROM ".tname('adminsession')." WHERE uid='$_SGLOBAL[supe_uid]'");//管理平台
	}

	include_once S_ROOT.'./uc_client/client.php';
	$ucsynlogout = uc_user_synlogout();
	
	clearcookie();
	ssetcookie('_refer', '');
	showmessage('security_exit', 'index.php', 1, array($ucsynlogout));

}

?>
<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space.php 10953 2009-01-12 02:55:37Z liguode $
*/

include_once('./common.php');

//是否关闭站点
checkclose();

//处理rewrite
if($_SCONFIG['allowrewrite'] && isset($_GET['rewrite'])) {
	$rws = explode('-', $_GET['rewrite']);
	if($rw_uid = intval($rws[0])) {
		$_GET['uid'] = $rw_uid;
	} else {
		$_GET['do'] = $rws[0];
	}
	if(isset($rws[1])) {
		$rw_count = count($rws);
		for ($rw_i=1; $rw_i<$rw_count; $rw_i=$rw_i+2) {
			$_GET[$rws[$rw_i]] = empty($rws[$rw_i+1])?'':$rws[$rw_i+1];
		}
	}
	unset($_GET['rewrite']);
}

//允许动作
$dos = array('feed', 'doing', 'mood', 'blog', 'album', 'thread', 'mtag', 'friend', 'wall', 'tag', 'notice', 'share', 'home', 'pm');

//获取变量
$isinvite = 0;
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
$username = empty($_GET['username'])?'':$_GET['username'];
$domain = empty($_GET['domain'])?'':$_GET['domain'];
$do = (!empty($_GET['do']) && in_array($_GET['do'], $dos))?$_GET['do']:'index';
if($do == 'home') {
	$do = 'feed';
} elseif ($do == 'index') {
	//邀请好友
	$invite = empty($_GET['invite'])?'':$_GET['invite'];
	$code = empty($_GET['code'])?'':$_GET['code'];
	if($code && !creditrule('pay', 'invite')) {
		$isinvite = -1;
	} elseif($invite) {
		$isinvite = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT id FROM ".tname('invite')." WHERE uid='$uid' AND code='$invite' AND fuid='0'"), 0);
	}
}

//是否公开
if(empty($isinvite) && empty($_SCONFIG['networkpublic'])) {
	checklogin();//需要登录
}

//获取空间
if($uid) {
	$space = getspace($uid, 'uid', 0);
} elseif ($username) {
	$space = getspace($username, 'username', 0);
} elseif ($domain) {
	$space = getspace($domain, 'domain', 0);
} else {
	if(empty($_SGLOBAL['supe_uid'])) {
		if ($do != 'mtag') {
			ssetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
			showmessage('to_login', 'do.php?ac='.$_SCONFIG['login_action']);
		}
	} else {
		$space = getspace($_SGLOBAL['supe_uid'], 'uid', 0);
	}
}

if($space) {
	//隐私检查
	if(empty($isinvite) || ($isinvite<0 && $code != space_key($space, $_GET['app']))) {
		//游客
		if(empty($_SCONFIG['networkpublic'])) {
			checklogin();//需要登录
		}
		if(!ckprivacy($do)) {
			include template('space_privacy');
			exit();
		}
	}
	//别人只查看自己
	if(!$space['self']) {
		$_GET['view'] = 'me';
	}
	if ($_GET['view'] == 'me') {
		$space['feedfriend'] = '';
	}
} elseif($do != 'mtag') {
	if($uid) {
		//判断当前用户是否删除
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('spacelog')." WHERE uid='$uid' AND flag='-1'");
		if($value = $_SGLOBAL['db']->fetch_array($query)) {
			showmessage('the_space_has_been_closed');
		}
		//虚假空间
		include_once(S_ROOT.'./uc_client/client.php');
		if($user = uc_get_user($uid, 1)) {
			$space = array('uid' => $user[0], 'username' => $user[1], 'dateline'=>$_SGLOBAL['timestamp'], 'friends'=>array());
			$_SN[$space['uid']] = $space['username'];
		}
	}
	if(empty($space)) {
		showmessage('space_does_not_exist', 'index.php?do=home', 0);
	}
}

//更新活动session
if($_SGLOBAL['supe_uid']) {
	getmember(); //获取当前用户信息
	updatetable('session', array('lastactivity' => $_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid']));
}

//计划任务
if(!empty($_SCONFIG['cronnextrun']) && $_SCONFIG['cronnextrun'] <= $_SGLOBAL['timestamp']) {
	include_once S_ROOT.'./source/function_cron.php';
	runcron();
}

//处理
include_once(S_ROOT."./source/space_{$do}.php");

?>
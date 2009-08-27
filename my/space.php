<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space.php 7589 2008-06-13 10:11:32Z liguode $
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
$dos = array('feed', 'doing', 'blog', 'album', 'thread', 'mtag', 'friend', 'wall', 'tag', 'notice', 'app');

//获取变量
$isinvite = 0;
$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
$username = empty($_GET['username'])?'':$_GET['username'];
$domain = empty($_GET['domain'])?'':$_GET['domain'];
$do = (!empty($_GET['do']) && in_array($_GET['do'], $dos))?$_GET['do']:'index';
if ($do == 'index') {
	$_SGLOBAL['do_index'] = 1;
	//邀请好友
	$invite = empty($_GET['invite'])?'':$_GET['invite'];
	if($invite) {
		$isinvite = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT id FROM ".tname('invite')." WHERE uid='$uid' AND code='$invite' AND fuid='0'"), 0);
	}
}

//是否公开
if(empty($isinvite) && empty($_SCONFIG['networkpublic'])) {
	checklogin();//需要登录
}

//获取空间
$space = array();
if($uid) {
	$space = getspace($uid);
} elseif ($username) {
	$space = getspace($username, 'username');
} elseif ($domain) {
	$space = getspace($domain, 'domain');
} else {
	if(empty($_SGLOBAL['supe_uid'])) {
		if($do != 'mtag') {
			ssetcookie('_refer', rawurlencode($_SERVER['REQUEST_URI']));
			showmessage('to_login', 'do.php?ac=login', 0);
		}
	} else {
		$space = getspace($_SGLOBAL['supe_uid']);
	}
}

//域名
if($space['domain'] && $_SCONFIG['allowdomain'] && $_SCONFIG['domainroot']) {
	$space['domainurl'] = 'http://'.$space['domain'].'.'.$_SCONFIG['domainroot'];
} else {
	if($_SCONFIG['allowrewrite']) {
		$space['domainurl'] = getsiteurl().$space[uid];
	} else {
		$space['domainurl'] = getsiteurl()."?$space[uid]";
	}
}

if(empty($space)) {
	if($do != 'mtag') {
		showmessage('space_does_not_exist', 'index.php?do', 0);
	}
} else {
	//别人只查看自己
	if(!$space['self']) {
		$_GET['view'] = 'me';
		$space['friend'] = $space['feedfriend'] = '';
	} elseif (!empty($_GET['view'])) {
		$space['friend'] = $space['feedfriend'] = '';
	}
}

//隐私检查
if(empty($isinvite) && !ckprivacy($do)) {
	$space['css'] = $space['theme'] = '';
	include template('space_privacy');
	exit();
}

//更新活动session
if($_SGLOBAL['supe_uid']) {
	updatetable('session', array('lastactivity' => $_SGLOBAL['timestamp']), array('uid'=>$_SGLOBAL['supe_uid']));
}

//空间访问统计
if(!$space['self'] && $space['uid']) {
	inserttable('log', array('id'=>$space['uid'], 'idtype'=>'uid'));//非本人
}

//计划任务
if(!empty($_SCONFIG['cronnextrun']) && $_SCONFIG['cronnextrun'] <= $_SGLOBAL['timestamp']) {
	include_once S_ROOT.'./source/function_cron.php';
	runcron();
}

//处理
include_once(S_ROOT."./source/space_{$do}.php");

?>
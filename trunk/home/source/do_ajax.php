<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_ajax.php 10342 2008-12-01 08:04:24Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$op = empty($_GET['op'])?'':$_GET['op'];

if($op == 'comment') {

	$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
	
	if($cid) {
		$cidsql = "cid='$cid' AND";
		$ajax_edit = 1;
	} else {
		$cidsql = '';
		$ajax_edit = 0;
	}

	//评论
	$list = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE $cidsql authorid='$_SGLOBAL[supe_uid]' ORDER BY dateline DESC LIMIT 0,1");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['authorid'], $value['author']);
		$list[] = $value;
	}
	
	realname_get();
	
} elseif($op == 'trace') {

	$blogid = empty($_GET['blogid'])?0:intval($_GET['blogid']);
	$tracelist = array();
	if($_SGLOBAL['supe_uid'] && $blogid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('trace')." WHERE blogid='$blogid' ORDER BY dateline DESC LIMIT 0, 18");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
			$tracelist[] = $value;
		}
	}
	//实名
	realname_get();
	
} elseif($op == 'traceall') {

	$blogid = empty($_GET['blogid'])?0:intval($_GET['blogid']);
	$start = empty($_GET['start'])?0:intval($_GET['start']);

	if(empty($_SGLOBAL['supe_uid'])) {
		showmessage('to_login', 'do.php?ac='.$_SCONFIG['login_action']);
	}
	
	$perpage = 18;
	//检查开始数
	ckstart($start, $perpage);

	$count = 0;
	
	$tracelist = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('trace')." WHERE blogid='$blogid' ORDER BY dateline DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$tracelist[] = $value;
		$count++;
	}
	$multi = smulti($start, $perpage, $count, "do.php?ac=ajax&op=traceall&blogid=$blogid", $_GET['ajaxdiv']);
	
	//实名
	realname_get();
	
} elseif($op == 'getfriendgroup') {
	
	$uid = intval($_GET['uid']);
	if($_SGLOBAL['supe_uid'] && $uid) {
		$space = getspace($_SGLOBAL['supe_uid']);
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('friend')." WHERE uid='$_SGLOBAL[supe_uid]' AND fuid='$uid'");
		$value = $_SGLOBAL['db']->fetch_array($query);
	}
	
	//获取用户
	$groups = getfriendgroup();
	
	if(empty($value['gid'])) $value['gid'] = 0;
	$group =$groups[$value['gid']];
	
} elseif($op == 'getfriendname') {
	
	//获取用户的好友分组名
	$groupname = '';
	$group = intval($_GET['group']);
	
	if($_SGLOBAL['supe_uid'] && $group) {
		$space = getspace($_SGLOBAL['supe_uid']);
		$groups = getfriendgroup();
		$groupname = $groups[$group];
	}
	
} elseif($op == 'getmtagmember') {
	
	//获取用户的好友分组名
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid='$tagid' AND uid='$uid'");
	$tagspace = $_SGLOBAL['db']->fetch_array($query);
	
} elseif($op == 'share') {

	//评论
	$list = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." WHERE uid='$_SGLOBAL[supe_uid]' ORDER BY dateline DESC LIMIT 0,1");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$value = mkshare($value);
		$list[] = $value;
	}
	
	realname_get();
	
} elseif($op == 'post') {

	$pid = empty($_GET['pid'])?0:intval($_GET['pid']);

	if($pid) {
		$pidsql = "pid='$pid' AND";
		$ajax_edit = 1;
	} else {
		$pidsql = '';
		$ajax_edit = 0;
	}
	
	//评论
	$list = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('post')." WHERE $pidsql uid='$_SGLOBAL[supe_uid]' ORDER BY dateline DESC LIMIT 0,1");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$list[] = $value;
	}
	
	realname_get();
	
} elseif($op == 'credit') {
	
	$uid = empty($_GET['uid'])?0:intval($_GET['uid']);
	include_once(S_ROOT.'./data/data_usergroup.php');
	$space = getspace($uid);
	if(empty($space)) {
		showmessage('space_does_not_exist');
	}

	$gid = getgroupid($space['credit'], $space['groupid']);
	if($gid != $space['groupid']) {
		//需要升级
		updatetable('space', array('groupid'=>$gid), array('uid'=>$space['uid']));
		$space['groupid'] = $gid;
	}
	
	$space['haveattachsize'] = '';
	if($space['self']) {
		$maxattachsize = intval(checkperm('maxattachsize'));//单位MB
		if($maxattachsize) {//0为不限制
			$space['haveattachsize'] = $maxattachsize + $space['addsize'] - $space['attachsize'];
			$space['haveattachsize'] = formatsize($space['haveattachsize']);
		}
	}
	
} elseif($op == 'album') {
	
	$id = empty($_GET['id'])?0:intval($_GET['id']);
	$start = empty($_GET['start'])?0:intval($_GET['start']);

	if(empty($_SGLOBAL['supe_uid'])) {
		showmessage('to_login', 'do.php?ac='.$_SCONFIG['login_action']);
	}
	
	$perpage = 10;
	//检查开始数
	ckstart($start, $perpage);

	$count = 0;
	
	$piclist = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('pic')." WHERE albumid='$id' AND uid='$_SGLOBAL[supe_uid]' ORDER BY dateline DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['bigpic'] = mkpicurl($value, 0);
		$value['pic'] = mkpicurl($value);
		$piclist[] = $value;
		$count++;
	}
	$multi = smulti($start, $perpage, $count, "do.php?ac=ajax&op=album&id=$id", $_GET['ajaxdiv']);

} elseif($op == 'docomment') {
	
	$doid = intval($_GET['doid']);
	$clist = $do = array();
	$icon = $_GET['icon'] == 'plus' ? 'minus' : 'plus';
	if($doid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('doing')." WHERE doid='$doid'");
		if ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
			$value['icon'] = 'plus';
			//自动展开最多20个评论
			if($value['replynum'] > 0 && ($value['replynum'] < 20 || $doid == $value['doid'])) {
				$doids[] = $value['doid'];
				$value['icon'] = 'minus';
			} elseif($value['replynum']<1) {
				$value['icon'] = 'minus';
			}
			$value['id'] = 0;
			$value['layer'] = 0;
			$clist[] = $value;
		}
	}
		
	if($_GET['icon'] == 'plus' && $value['replynum']) {

		include_once(S_ROOT.'./source/class_tree.php');
		$tree = new tree();
		
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('docomment')." WHERE doid='$doid' ORDER BY dateline");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			realname_set($value['uid'], $value['username']);
			if(empty($value['upid'])) {
				$value['upid'] = "do";
			}
			$tree->setNode($value['id'], $value['upid'], $value);
		}

		$values = $tree->getChilds("do");
		foreach ($values as $key => $id) {
			$one = $tree->getValue($id);
			$one['layer'] = $tree->getLayer($id) * 2;
			$clist[] = $one;
		}
	}
	
	realname_get();
} elseif($op == 'getmood') {
	
	$space = empty($_SGLOBAL['supe_uid'])?array():getspace($_SGLOBAL['supe_uid']);
	if($space['spacenote']) {
		$space['spacenote'] = getstr($space['spacenote'], 50);
	}
} elseif($op == 'deluserapp') {
	
	if(empty($_SGLOBAL['supe_uid'])) {
		showmessage('no_privilege');
	}
	$hash = trim($_GET['hash']);
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('myinvite')." WHERE hash='$hash' AND touid='$_SGLOBAL[supe_uid]'");
	if($value = $_SGLOBAL['db']->fetch_array($query)) {
		$_SGLOBAL['db']->query("DELETE FROM ".tname('myinvite')." WHERE hash='$hash' AND touid='$_SGLOBAL[supe_uid]'");
		showmessage('do_success');
	} else {
		showmessage('no_privilege');
	}
}

include template('do_ajax');

?>
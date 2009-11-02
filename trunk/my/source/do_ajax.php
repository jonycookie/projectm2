<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: do_ajax.php 7211 2008-04-29 01:54:02Z liguode $
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
		$list[] = $value;
	}
	
} elseif($op == 'pm') {

	//获取最新pm
	include_once(S_ROOT.'./uc_client/client.php');
	
	$list = array();
	$list[] = uc_pm_viewnode($_SGLOBAL['supe_uid'], 1);
	
} elseif($op == 'trace') {

	$blogid = empty($_GET['blogid'])?0:intval($_GET['blogid']);
	$tracelist = array();
	if($_SGLOBAL['supe_uid'] && $blogid) {
		$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('trace')." WHERE blogid='$blogid' ORDER BY dateline DESC LIMIT 0, 6");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$tracelist[] = $value;
		}
	}
	
} elseif($op == 'traceall') {

	$blogid = empty($_GET['blogid'])?0:intval($_GET['blogid']);
	$start = empty($_GET['start'])?0:intval($_GET['start']);

	if(empty($_SGLOBAL['supe_uid'])) {
		showmessage('to_login');
	}
	
	$perpage = 12;
	//检查开始数
	ckstart($start, $perpage);

	$count = 0;
	
	$tracelist = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('trace')." WHERE blogid='$blogid' ORDER BY dateline DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$tracelist[] = $value;
		$count++;
	}
	$multi = smulti($start, $perpage, $count, "do.php?ac=ajax&op=traceall&blogid=$blogid", $_GET['ajaxdiv']);
	
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
	
} elseif($op == 'share') {

	//评论
	$list = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." WHERE uid='$_SGLOBAL[supe_uid]' ORDER BY dateline DESC LIMIT 0,1");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value = mkshare($value);
		$list[] = $value;
	}
	
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
		$list[] = $value;
	}
	
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
		updatetable('session', array('groupid'=>$gid), array('uid'=>$space['uid']));
		$space['groupid'] = $gid;
	}
	$space['attachsize'] = formatsize($space['attachsize']);
	
} elseif($op == 'album') {
	
	$id = empty($_GET['id'])?0:intval($_GET['id']);
	$start = empty($_GET['start'])?0:intval($_GET['start']);

	if(empty($_SGLOBAL['supe_uid'])) {
		showmessage('to_login');
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
}

include template('do_ajax');

?>
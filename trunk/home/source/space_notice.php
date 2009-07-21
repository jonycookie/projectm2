<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_notice.php 9865 2008-11-19 05:19:44Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//分页
$perpage = 100;
$start = empty($_GET['start'])?0:intval($_GET['start']);
//检查开始数
ckstart($start, $perpage);

$list = array();
$count = 0;
	
$view = (!empty($_GET['view']) && in_array($_GET['view'], array('userapp')))?$_GET['view']:'notice';
$actives = array($view=>' class="active"');

if($view == 'userapp') {
	
	if($_GET['op'] == 'del') {
		$appid = intval($_GET['appid']);
		$_SGLOBAL['db']->query("DELETE FROM ".tname('myinvite')." WHERE appid='$appid' AND touid='$_SGLOBAL[supe_uid]'");
		showmessage('do_success', "space.php?do=notice&view=userapp", 0);
	}
	$type = intval($_GET['type']);
	$typesql = $type?"AND appid='$type'":'';
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('myinvite')." WHERE touid='$_SGLOBAL[supe_uid]' $typesql ORDER BY dateline DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$key = md5($value['typename'].$value['type']);
		
		$list[$key][] = $value;
		$count++;
		$appidarr[] = $value['appid'];
	}
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('myinvite')." WHERE touid='$_SGLOBAL[supe_uid]' ORDER BY dateline DESC");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$key = md5($value['typename'].$value['type']);
		$apparr[$key][] = $value;
	}
	
	//分页
	$multi = smulti($start, $perpage, $count, "space.php?do=$do&view=userapp");
	
} else {
	
	//通知类型
	$noticetypes = array(
		'wall' => lang('wall'),
		'piccomment' => lang('pic_comment'),
		'blogcomment' => lang('blog_comment'),
		'blogtrace' => lang('blog_trace'),
		'sharecomment' => lang('share_comment'),
		'sharenotice' => lang('share_notice'),
		'doing' => lang('doing_comment'),
		'friend' => lang('friend_notice'),
		'post' => lang('thread_comment'),
		'credit' => lang('credit'),
		'mtag' => lang('mtag')
	);
	
	$type = trim($_GET['type']);
	$typesql = $type?"AND type='$type'":'';
	
	$users = array();
	//处理查询
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('notification')." WHERE uid='$_SGLOBAL[supe_uid]' $typesql ORDER BY dateline DESC LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['authorid'], $value['author']);
		if($value['authorid']!=$space['uid'] && $space['friends'] && !in_array($value['authorid'], $space['friends'])) {
			$users[$value['authorid']] = $value;
		}
		$list[] = $value;
		$count++;
	}
	
	//分页
	$multi = smulti($start, $perpage, $count, "space.php?do=$do");
	
	//设置本次查看时间
	$wherearr = array('uid'=>$_SGLOBAL['supe_uid'], 'new'=>1);
	if($type) {
		$wherearr['type'] = $type;
	}
	updatetable('notification', array('new'=>0), $wherearr);
	
	realname_get();
	
	//更新未读的
	$newcount = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('notification')." WHERE uid='$_SGLOBAL[supe_uid]' AND new='1'"), 0);
	$newcount = intval($newcount);
	if($_SGLOBAL['member']['notenum'] != $newcount) {
		updatetable('space', array('notenum'=>$newcount), array('uid'=>$_SGLOBAL['supe_uid']));
	}
}
include_once template("space_notice");

?>
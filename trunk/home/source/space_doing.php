<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_doing.php 10785 2008-12-22 08:22:13Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//分页
$perpage = 20;
$page = empty($_GET['page'])?0:intval($_GET['page']);
if($page<1) $page=1;
$start = ($page-1)*$perpage;

//检查开始数
ckstart($start, $perpage);

$list = array();
$count = 0;

//处理查询
$f_index = '';
if($_GET['view'] == 'all') {
	
	$wheresql = "1";
	$theurl = "space.php?uid=$space[uid]&do=$do&view=all";
	$f_index = 'USE INDEX(dateline)';
	$actives = array('all'=>' class="active"');
	
} elseif(empty($space['feedfriend'])) {
	
	$wheresql = "uid='$space[uid]'";
	$theurl = "space.php?uid=$space[uid]&do=$do&view=me";
	$actives = array('me'=>' class="active"');
	
} else {
	
	$wheresql = "uid IN ($space[feedfriend])";
	$theurl = "space.php?uid=$space[uid]&do=$do";
	$f_index = 'USE INDEX(dateline)';
	$actives = array('we'=>' class="active"');
}

$doid = empty($_GET['doid'])?0:intval($_GET['doid']);
if($doid) {
	$count = 1;
	$f_index = '';
	$wheresql = "doid='$doid'";
	$theurl .= "&doid=$doid";
}


$doids = $clist = $newdoids = array();
if(empty($count)) {
	$count = $_SGLOBAL['db']->result($_SGLOBAL['db']->query("SELECT COUNT(*) FROM ".tname('doing')." WHERE $wheresql"), 0);
}
if($count) {
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('doing')." $f_index
		WHERE $wheresql
		ORDER BY dateline DESC
		LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		realname_set($value['uid'], $value['username']);
		$doids[] = $value['doid'];
		$list[] = $value;
	}
}

//单条处理
if($doid) {
	$dovalue = empty($list)?array():$list[0];
	if($dovalue) {
		if($dovalue['uid'] == $_SGLOBAL['supe_uid']) {
			$actives = array('me'=>' class="active"');
		} else {
			$space = getspace($dovalue['uid']);//对方的空间
			$actives = array('all'=>' class="active"');
		}
	}
}

//回复
if($doids) {
	$values = array();
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('docomment')." WHERE doid IN (".simplode($doids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$values[$value['dateline']] = $value;
	}
	
	//排序
	ksort($values);
	
	include_once(S_ROOT.'./source/class_tree.php');
	$tree = new tree();
	foreach ($values as $value) {
		realname_set($value['uid'], $value['username']);
		$newdoids[$value['doid']] = $value['doid'];
		if(empty($value['upid'])) {
			$value['upid'] = "do$value[doid]";
		}
		$tree->setNode($value['id'], $value['upid'], $value);
	}
}

foreach ($newdoids as $cdoid) {
	$values = $tree->getChilds("do$cdoid");
	foreach ($values as $key => $id) {
		$one = $tree->getValue($id);
		$one['layer'] = $tree->getLayer($id) * 2;
		$clist[$cdoid][] = $one;
	}
}

//分页
$multi = multi($count, $perpage, $page, $theurl);

//同心情的
$moodlist = array();
if($space['mood'] && empty($start)) {
	$query = $_SGLOBAL['db']->query("SELECT s.uid,s.username,s.name,s.namestatus,s.mood,s.updatetime,s.groupid,sf.note,sf.sex
		FROM ".tname('space')." s
		LEFT JOIN ".tname('spacefield')." sf ON sf.uid=s.uid
		WHERE s.mood='$space[mood]' ORDER BY s.updatetime DESC LIMIT 0,13");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		if($value['uid'] != $space['uid']) {
			realname_set($value['uid'], $value['username'], $value['name'], $value['namestatus']);
			$moodlist[] = $value;
			if(count($moodlist)==12) break;
		}
	}
}

//实名
realname_get();

include_once template("space_doing");

?>
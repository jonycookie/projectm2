<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_friend.php 7297 2008-05-06 07:14:42Z zhengqingpeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//分页
$perpage = 10;
$start = empty($_GET['start'])?0:intval($_GET['start']);
$list = $ols = $fuids = array();
$count = 0;

if($_GET['view'] == 'online') {
	//检查开始数
	ckstart($start, $perpage);
	
	$theurl = "space.php?uid=$space[uid]&do=friend&view=online";
	$actives = array('ol'=>' class="active"');
	
	$query = $_SGLOBAL['db']->query("SELECT f.resideprovince, f.residecity, f.note, main.uid, main.username, main.lastactivity 
		FROM ".tname('session')." main 
		LEFT JOIN ".tname('spacefield')." f ON f.uid=main.uid 
		LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['p'] = rawurlencode($value['resideprovince']);
		$value['c'] = rawurlencode($value['residecity']);
		$ols[$value['uid']] = $value['lastactivity'];
		$list[] = $value;
		$count++;
	}
	$multi = smulti($start, $perpage, $count, $theurl);

} elseif($_GET['view'] == 'visitor' || $_GET['view'] == 'trace') {
	//检查开始数
	ckstart($start, $perpage);
		
	$theurl = "space.php?uid=$space[uid]&do=friend&view=$_GET[view]";
	$actives = array($_GET['view']=>' class="active"');
	
	if($_GET['view'] == 'visitor') {//访客
		$query = $_SGLOBAL['db']->query("SELECT f.resideprovince, f.residecity, f.note, main.vuid AS uid, main.vusername AS username, main.dateline
			FROM ".tname('visitor')." main
			LEFT JOIN ".tname('spacefield')." f ON f.uid=main.vuid
			WHERE main.uid='$space[uid]'
			ORDER BY main.dateline DESC
			LIMIT $start,$perpage");
	} else {//足迹
		$query = $_SGLOBAL['db']->query("SELECT s.username, f.resideprovince, f.residecity, f.note, main.uid AS uid, main.dateline
			FROM ".tname('visitor')." main
			LEFT JOIN ".tname('space')." s ON s.uid=main.uid
			LEFT JOIN ".tname('spacefield')." f ON f.uid=main.uid
			WHERE main.vuid='$space[uid]'
			ORDER BY main.dateline DESC
			LIMIT $start,$perpage");
	}
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['p'] = rawurlencode($value['resideprovince']);
		$value['c'] = rawurlencode($value['residecity']);
		$fuids[] = $value['uid'];
		$list[] = $value;
		$count++;
	}
	$multi = smulti($start, $perpage, $count, $theurl);
	
} else {
	if(empty($start)) {
		$page = empty($_GET['page'])?1:intval($_GET['page']);
		if($page<1) $page = 1;
		$start = ($page-1)*$perpage;
	}
	//检查开始数
	ckstart($start, $perpage);
	
	//处理查询
	$theurl = "space.php?uid=$space[uid]&do=$do";
	$actives = array('me'=>' class="active"');
	
	//好友分组
	$wheresql = '';
	if($space['self']) {
		$groups = getfriendgroup();
		$group = !isset($_GET['group'])?'-1':intval($_GET['group']);
		$username = empty($_GET['key'])?'':$_GET['key'];
		if($group > -1) {
			$wheresql = "AND main.gid='$group'";
			$theurl .= "&group=$group";
		} elseif($username) {
			$wheresql = "AND main.fusername='$username'";
			$theurl .= "&key=$username";
		}
	}
	
	if($space['friendnum']) {
		$query = $_SGLOBAL['db']->query("SELECT f.resideprovince, f.residecity, f.note, main.fuid AS uid,main.fusername AS username, main.gid FROM ".tname('friend')." main
			LEFT JOIN ".tname('spacefield')." f ON f.uid=main.fuid
			WHERE main.uid='$space[uid]' AND main.status='1' $wheresql
			LIMIT $start,$perpage");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['p'] = rawurlencode($value['resideprovince']);
			$value['c'] = rawurlencode($value['residecity']);
			$value['group'] = $groups[$value['gid']];
			$fuids[] = $value['uid'];
			$list[] = $value;
			$count++;
		}
		
		//分页
		$multi = array();
		if($wheresql) {
			$multi = smulti($start, $perpage, $count, $theurl);
		} else {
			$multi['html'] = multi($space['friendnum'], $perpage, $page, $theurl);
		}
		
		//取所有好友用户名
		$query = $_SGLOBAL['db']->query("SELECT fusername FROM ".tname('friend')." WHERE uid=$_SGLOBAL[supe_uid] AND status='1'");
		while ($value = $_SGLOBAL['db']->fetch_array($query)) {
			$value['fusername'] = saddslashes($value['fusername']);
			$friends[] = $value['fusername'];
		}
		$friendstr = implode(',', $friends);
	}
	
	if($space['self']) {
		$groupselect = array($group => 'selected');
	}
}

//在线状态
if($fuids) {
	$query = $_SGLOBAL['db']->query("SELECT uid, lastactivity FROM ".tname('session')." WHERE uid IN (".simplode($fuids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$ols[$value['uid']] = $value['lastactivity'];
	}
}

include_once template("space_friend");

?>
<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_share.php 6565 2008-03-14 09:26:09Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//分页

$start = empty($_GET['start'])?0:intval($_GET['start']);
$id = empty($_GET['id'])?0:intval($_GET['id']);

if($id) {

	//读取
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." WHERE sid='$id' AND uid='$space[uid]'");
	$share = $_SGLOBAL['db']->fetch_array($query);
	//不存在
	if(empty($share)) {
		showmessage('share_does_not_exist');
	}
	$share = mkshare($share);
	
	//评论
	$perpage = 100;

	//检查开始数
	ckstart($start, $perpage);
	
	$list = array();
	$count = 0;
	$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
	$csql = $cid?"cid='$cid' AND":'';
	
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE $csql id='$id' AND idtype='sid' ORDER BY dateline LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
		$count++;
	}
	
	//分页
	$multi = smulti($start, $perpage, $count, "space.php?uid=$share[uid]&do=share&id=$id");
	
	include_once template("space_share_view");
	
} else {
	$perpage = 30;
	
	//检查开始数
	ckstart($start, $perpage);
		
	//处理查询
	if(empty($space['friend'])) {
		$wheresql = "uid='$space[uid]'";
		$theurl = "space.php?uid=$space[uid]&do=$do&view=me";
		$f_index = '';
		$actives = array('me'=>' class="active"');
	} else {
		$wheresql = "uid IN ($space[friend])";
		$theurl = "space.php?uid=$space[uid]&do=$do";
		$f_index = 'FORCE INDEX(dateline)';
		$actives = array('we'=>' class="active"');
	}
	
	$list = array();
	$count = 0;
	
	$sid = empty($_GET['sid'])?0:intval($_GET['sid']);
	$sharesql = $sid?"sid='$sid' AND":'';
			
	$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." $f_index
		WHERE $sharesql $wheresql
		ORDER BY dateline DESC
		LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value = mkshare($value);
		$list[] = $value;
		$count++;
	}
	
	//分页
	$multi = smulti($start, $perpage, $count, $theurl);
	
	include_once template("space_share_list");
}

?>
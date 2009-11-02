<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_doing.php 6565 2008-03-14 09:26:09Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//分页
$perpage = 50;
$start = empty($_GET['start'])?0:intval($_GET['start']);
//检查开始数
ckstart($start, $perpage);
	
//处理查询
if(empty($space['friend'])) {
	$wheresql = "uid='$space[uid]'";
	$theurl = "space.php?uid=$space[uid]&do=$do&view=me";
	$f_index = '';
	$actives = array('me'=>' class="active"');
} else {
	$wheresql = "uid IN ($space[friend],$space[uid])";
	$theurl = "space.php?uid=$space[uid]&do=$do";
	$f_index = 'FORCE INDEX(dateline)';
	$actives = array('we'=>' class="active"');
}

$list = array();
$count = 0;

$doid = empty($_GET['doid'])?0:intval($_GET['doid']);
$dosql = $doid?"doid='$doid' AND":'';
		
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('doing')." $f_index
	WHERE $dosql $wheresql
	ORDER BY dateline DESC
	LIMIT $start,$perpage");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$list[] = $value;
	$count++;
}

//分页
$multi = smulti($start, $perpage, $count, $theurl);

include_once template("space_doing");

?>
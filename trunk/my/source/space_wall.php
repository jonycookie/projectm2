<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_wall.php 6565 2008-03-14 09:26:09Z liguode $
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
$theurl = "space.php?uid=$space[uid]&do=$do";

$cid = empty($_GET['cid'])?0:intval($_GET['cid']);
$csql = $cid?"cid='$cid' AND":'';
	
$list = array();
$count = 0;
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('comment')." WHERE $csql id='$space[uid]' AND idtype='uid' ORDER BY dateline DESC LIMIT $start,$perpage");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$list[] = $value;
	$count++;
}
	
//分页
$multi = smulti($start, $perpage, $count, $theurl);
	
include_once template("space_wall");

?>
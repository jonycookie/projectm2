<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: network_share.php 6565 2008-03-14 09:26:09Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//分页
$perpage = 50;
$start = empty($_GET['start'])?0:intval($_GET['start']);
if(empty($_SCONFIG['networkpage'])) $start = 0;

//检查开始数
ckstart($start, $perpage);

//处理查询
$list = array();
$count = 0;
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('share')." FORCE INDEX (dateline) ORDER BY dateline DESC LIMIT $start,$perpage");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$value = mkshare($value);
	$list[] = $value;
	$count++;
}

//分页
$multi = empty($_SCONFIG['networkpage'])?array('html'=>'networkpage'):smulti($start, $perpage, $count, $theurl);

?>
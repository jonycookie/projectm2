<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_mtag.php 7296 2008-05-06 06:39:40Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./data/data_profield.php');

$start = empty($_GET['start'])?0:intval($_GET['start']);
$id = empty($_GET['id'])?0:intval($_GET['id']);
$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);

$actives = array($_GET['view'] => ' class="active"');

//指定的选吧
include_once(S_ROOT.'./source/function_space.php');
$mtag = getmtag($tagid, 1);

	
$perpage = 50;
//检查开始数
ckstart($start, $perpage);

$list = $fuids = array();
$count = 0;
$query = $_SGLOBAL['db']->query("SELECT field.*, main.username FROM ".tname('tagspace')." main 
	LEFT JOIN ".tname('spacefield')." field ON field.uid=main.uid 
	WHERE main.tagid='$tagid' LIMIT $start,$perpage");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$value['p'] = rawurlencode($value['resideprovince']);
	$value['c'] = rawurlencode($value['residecity']);
	$fuids[] = $value['uid'];
	$list[] = $value;
	$count++;
}

//在线状态
$ols = array();
if($fuids) {
	$query = $_SGLOBAL['db']->query("SELECT uid, lastactivity FROM ".tname('session')." WHERE uid IN (".simplode($fuids).")");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$ols[$value['uid']] = $value['lastactivity'];
	}
}

//分页
$multi = smulti($start, $perpage, $count, "space.php?uid=$space[uid]&do=mtag&tagid=$tagid&view=member");

include_once template("mtag_member");
	

?>
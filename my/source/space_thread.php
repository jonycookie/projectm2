<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: space_thread.php 6968 2008-04-03 10:16:37Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

@include_once(S_ROOT.'./data/data_profield.php');

//分页
$start = empty($_GET['start'])?0:intval($_GET['start']);
	
$perpage = 30;
//检查开始数
ckstart($start, $perpage);

//话题列表
$wheresql = '';
if(empty($_GET['view'])) {
	//我加入的选吧
	$tagids = array();
	$query = $_SGLOBAL['db']->query("SELECT tagid FROM ".tname('tagspace')." WHERE uid='$space[uid]'");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$tagids[$value['tagid']] = $value['tagid'];
	}
	if($tagids) {
		//加入的选吧
		$wheresql = "main.tagid IN (".simplode($tagids).")";
		$theurl = "space.php?uid=$space[uid]&do=$do";
		$f_index = 'FORCE INDEX(lastpost)';
		
	}
	$actives = array('we'=>' class="active"');
} else {
	//自己的
	$wheresql = "main.uid='$space[uid]'";
	$theurl = "space.php?uid=$space[uid]&do=$do&view=me";
	$f_index = '';
	$actives = array('me'=>' class="active"');
}

$list = array();
$count = 0;

if($wheresql) {
	$query = $_SGLOBAL['db']->query("SELECT main.*,field.tagname,field.membernum,field.fieldid FROM ".tname('thread')." main $f_index
		LEFT JOIN ".tname('mtag')." field ON field.tagid=main.tagid WHERE $wheresql 
		ORDER BY main.lastpost DESC 
		LIMIT $start,$perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$value['tagname'] = getstr($value['tagname'], 20);
		$list[] = $value;
		$count++;
	}
}

//分页
$multi = smulti($start, $perpage, $count, $theurl);

include_once template("space_thread");


?>
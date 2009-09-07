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
$tagid = empty($_GET['tagid'])?0:intval($_GET['tagid']);

$actives = array($_GET['view'] => ' class="active"');

//指定的群组
include_once(S_ROOT.'./source/function_space.php');
$mtag = getmtag($tagid, 1);

//列表
$list = array();
$query = $_SGLOBAL['db']->query("SELECT main.* FROM ".tname('thread')." main 
	WHERE main.tagid='$tagid' 
	ORDER BY main.displayorder DESC, main.lastpost DESC 
	LIMIT 0,30");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$list[] = $value;
}

//会员
$memberlist = array();
$query = $_SGLOBAL['db']->query("SELECT * FROM ".tname('tagspace')." WHERE tagid='$tagid' LIMIT 0,12");
while ($value = $_SGLOBAL['db']->fetch_array($query)) {
	$memberlist[] = $value;
}

$musers = empty($mtag['moderator'])?'':explode("\t", $mtag['moderator']);

include_once template("mtag_index");

?>
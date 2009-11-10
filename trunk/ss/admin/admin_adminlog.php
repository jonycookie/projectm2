<?php

/*
	[SupeSite] (C) 2007-2009 Comsenz Inc.
	$Id: admin_robots.php 11192 2009-02-25 01:45:53Z zhaofei $
*/

if(!defined('IN_SUPESITE_ADMINCP')) {
	exit('Access Denied');
}

//权限
if(!checkperm('manageviewlog')) {
	showmessage('no_authority_management_operation');
}

$page = empty($_GET['page']) && intval($_GET['page']) < 1 ? 1 : intval($_GET['page']);
$start = ($page - 1) * $perpage;
$perpage = empty($_GET['perpage']) ? 0 : intval($_GET['perpage']);			//默认每页显示列表数目
if(!$perpage) $perpage = 40;
$_GET['type'] = empty($_GET['type']) ? 'sys' : trim($_GET['type']);

$starttime = trim(postget('starttime'));
$endtime = trim(postget('endtime'));

$wherearr = array();
if($starttime) {
	$starttime = strtotime($starttime);
	$wherearr[] = "(dateline >= '$starttime')";
}

if($endtime) {
	$endtime = strtotime($endtime);
	$wherearr[] = "(dateline <= '$endtime')";
}

if($wherearr) {
	$wheresqlstr = ' WHERE';
	$wheresqlstr .= implode(' AND ', $wherearr);
}

$multipage = '';
$list = array();
if($_GET['type'] == 'sys') {
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('adminlog').$wheresqlstr);
	$listcount = $_SGLOBAL['db']->result($query, 0);
	
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('adminlog')." $wheresqlstr LIMIT $start, $perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}
	
} else {
	@include_once(S_ROOT.'./data/system/postnews.cache.php');
	$query = $_SGLOBAL['db']->query('SELECT COUNT(*) FROM '.tname('postlog').$wheresqlstr);
	$listcount = $_SGLOBAL['db']->result($query, 0);
	
	$query = $_SGLOBAL['db']->query('SELECT * FROM '.tname('postlog')." $wheresqlstr LIMIT $start, $perpage");
	while ($value = $_SGLOBAL['db']->fetch_array($query)) {
		$list[] = $value;
	}
	
}

$multipage = multi($listcount, $perpage, $page, $theurl.'&type='.$_GET['type']);

include template('admin/tpl/adminlog.htm', 1);
?>
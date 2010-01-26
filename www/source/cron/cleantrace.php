<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cleantrace.php 7232 2008-04-29 09:06:03Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//清理脚印和最新访客
$maxday = 90;//保留90天的
$deltime = $_SGLOBAL['timestamp'] - $maxday*3600*24;

//清理脚印
$_SGLOBAL['db']->query("DELETE FROM ".tname('trace')." WHERE dateline < '$deltime'");

//最新访客
$_SGLOBAL['db']->query("DELETE FROM ".tname('visitor')." WHERE dateline < '$deltime'");

?>
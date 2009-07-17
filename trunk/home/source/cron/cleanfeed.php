<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cleanfeed.php 10944 2009-01-09 01:56:13Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//清理feed
if($_SCONFIG['feedday'] < 3) $_SCONFIG['feedday'] = 3;
$deltime = $_SGLOBAL['timestamp'] - $_SCONFIG['feedday']*3600*24;

//执行
$_SGLOBAL['db']->query("DELETE FROM ".tname('feed')." WHERE uid>'0' AND dateline < '$deltime'");
$_SGLOBAL['db']->query("OPTIMIZE TABLE ".tname('feed'), 'SILENT');//优化表

?>
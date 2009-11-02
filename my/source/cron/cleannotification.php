<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: cleannotification.php 6565 2008-03-14 09:26:09Z liguode $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

//清理通知
$deltime = $_SGLOBAL['timestamp'] - 2*3600*24;//只保留2天

//执行
$_SGLOBAL['db']->query("DELETE FROM ".tname('notification')." WHERE dateline < '$deltime'");

?>
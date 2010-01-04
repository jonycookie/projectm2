<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: todayposts_daily.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$yesterdayposts = intval($db->result_first("SELECT sum(todayposts) FROM {$tablepre}forums"));

$historypost = $db->result_first("SELECT value FROM {$tablepre}settings WHERE variable='historyposts'");

$hpostarray = explode("\t", $historypost);
$historyposts = $hpostarray[1] < $yesterdayposts ? "$yesterdayposts\t$yesterdayposts" : "$yesterdayposts\t$hpostarray[1]";

$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('historyposts', '$historyposts')");
$db->query("UPDATE {$tablepre}forums SET todayposts='0'");

require_once DISCUZ_ROOT.'./include/cache.func.php';
$_DCACHE['settings']['historyposts'] = $historyposts;
updatesettings();

?>
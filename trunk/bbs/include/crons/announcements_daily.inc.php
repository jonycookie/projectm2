<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: announcements_daily.inc.php 9806 2007-08-15 06:04:37Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$db->query("DELETE FROM {$tablepre}announcements WHERE endtime<'$timestamp' AND endtime<>'0'");

if($db->affected_rows()) {
	require_once DISCUZ_ROOT.'./include/cache.func.php';
	updatecache('announcements');
	updatecache('announcements_forum');
	updatecache('pmlist');
}

?>
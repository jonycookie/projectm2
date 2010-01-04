<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: secqaa_daily.inc.php 9806 2007-08-15 06:04:37Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($secqaa['status'] > 0) {
	require_once DISCUZ_ROOT.'./include/cache.func.php';
	updatecache('secqaa');
}

?>
<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: supe_daily.inc.php 10857 2007-10-17 02:09:59Z liuqiang $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($supe['status'] && ($supe['maxupdateusers'] || $supe['items']['status'])) {
	require_once DISCUZ_ROOT.'./include/cache.func.php';
	updatecache(array('supe_updateusers', 'supe_updateitems'));
}

?>
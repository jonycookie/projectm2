<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: cleanup_monthly.inc.php 9806 2007-08-15 06:04:37Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$myrecordtimes = $timestamp - $_DCACHE['settings']['myrecorddays'] * 86400;
$db->query("DELETE FROM {$tablepre}mythreads WHERE dateline<'$myrecordtimes'", 'UNBUFFERED');
$db->query("DELETE FROM {$tablepre}myposts WHERE dateline<'$myrecordtimes'", 'UNBUFFERED');

$db->query("DELETE FROM {$tablepre}invites WHERE dateline<'$timestamp' - 2592000 AND status='4'", 'UNBUFFERED');
$db->query("TRUNCATE {$tablepre}relatedthreads");

?>
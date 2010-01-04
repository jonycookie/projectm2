<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: cleanup_daily.inc.php 9806 2007-08-15 06:04:37Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$db->query("UPDATE {$tablepre}advertisements SET available='0' WHERE endtime>'0' AND endtime<='$timestamp'", 'UNBUFFERED');
if($db->affected_rows()) {
	require_once DISCUZ_ROOT.'./include/cache.func.php';
	updatecache(array('settings', 'advs_archiver', 'advs_register', 'advs_index', 'advs_forumdisplay', 'advs_viewthread'));
}
$db->query("TRUNCATE {$tablepre}searchindex");
$db->query("DELETE FROM {$tablepre}threadsmod WHERE dateline<'$timestamp'-31536000", 'UNBUFFERED');
$db->query("DELETE FROM {$tablepre}subscriptions WHERE lastpost<'$timestamp'-7776000", 'UNBUFFERED');
$db->query("DELETE FROM {$tablepre}forumrecommend WHERE expiration<'$timestamp'", 'UNBUFFERED');

if($qihoo['status'] && $qihoo['relatedthreads']) {
	$db->query("DELETE FROM {$tablepre}relatedthreads WHERE expiration<'$timestamp'", 'UNBUFFERED');
}

$db->query("UPDATE {$tablepre}trades SET closed='1' WHERE expiration<>0 AND expiration<'$timestamp'", 'UNBUFFERED');
$db->query("DELETE FROM {$tablepre}tradelog WHERE status=0 AND lastupdate<'".($timestamp - 5 * 86400)."'", 'UNBUFFERED');

if($cachethreadon) {
	removedir($cachethreaddir, TRUE);
}

if($regstatus > 1) {
	$db->query("UPDATE {$tablepre}invites SET status='4' WHERE expiration<'$timestamp' AND status IN ('1', '3')");
}
?>
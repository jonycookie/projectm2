<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: viewthread_special.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$repostnum = $db->result_first("SELECT count(*) FROM {$tablepre}posts WHERE tid='$tid' AND first=0 AND invisible='0'");

$ppp = $forum['threadcaches'] && !$discuz_uid ? $_DCACHE['settings']['postperpage'] : $ppp;
$start_limit = $numpost = ($page - 1) * $ppp;
if($start_limit > $repostnum) {
	$start_limit = $numpost = 0;
	$page = 1;
}
if($thread['special'] == 3) {
	$thread['price'] < 0 && $start_limit++;
	$repostnum--;
}
$multipage = multi($repostnum, $ppp, $page, "viewthread.php?tid=$tid&amp;do=$do&amp;extra=$extra".(isset($highlight) ? "&amp;highlight=".rawurlencode($highlight) : ''));

$query = $db->query("SELECT p.*,m.username,m.adminid,m.groupid,m.credits FROM {$tablepre}posts p LEFT JOIN {$tablepre}members m ON m.uid=p.authorid WHERE p.tid='$tid' AND p.invisible='0' AND p.first='0' ORDER BY p.dateline LIMIT $start_limit, $ppp");

while($post = $db->fetch_array($query)) {
	$post['first'] = 0;
	$post = viewthread_procpost($post, 1);
	$postlist[$post['pid']] = $post;
}

if($attachpids) {
	require_once DISCUZ_ROOT.'./include/attachment.func.php';
	parseattach($attachpids, $attachtags, $postlist, $showimages);
}

include template('viewthread_special_post');
exit;

?>
<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: viewthread_video.inc.php 9806 2007-08-15 06:04:37Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($iscircle) {
        $allowvote = ($circle['allowshare'] && ($circle['ispublic'] == 1 || ($circle['ispublic'] == 2 && $circle['password'] == $_DCOOKIE['gidpw'.$gid]))) || $incircle;
}

$query = $db->query("SELECT * FROM {$tablepre}videos WHERE tid='$tid' LIMIT 1");
$videodata = $db->fetch_array($query);
$vid = $videodata['vid'];
$vautoplay = $videodata['vautoplay'];

$query = $db->query("SELECT p.*, m.uid, m.username, m.groupid, m.adminid, m.regdate, m.lastactivity, m.posts, m.digestposts, m.oltime,
	m.pageviews, m.credits, m.extcredits1, m.extcredits2, m.extcredits3, m.extcredits4, m.extcredits5, m.extcredits6,
	m.extcredits7, m.extcredits8, m.email, m.gender, m.showemail, m.invisible, m.xspacestatus, mf.nickname, mf.site,
	mf.icq, mf.qq, mf.yahoo, mf.msn, mf.taobao, mf.alipay, mf.location, mf.medals, mf.avatar, mf.avatarwidth,
	mf.avatarheight, mf.customstatus, mf.spacename $fieldsadd
	FROM {$tablepre}posts p
	LEFT JOIN {$tablepre}members m ON m.uid=p.authorid
	LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
	WHERE p.tid='$tid' AND first=1 LIMIT 1");

$post = $db->fetch_array($query);
$pid = $post['pid'];
$postlist[$post['pid']] = viewthread_procpost($post);

if($attachpids) {
	require_once DISCUZ_ROOT.'./include/attachment.func.php';
	parseattach($attachpids, $attachtags, $postlist, $showimages);
}

viewthread_parsetags();

$post = $postlist[$post['pid']];

include template('viewthread_video');
exit;


?>
<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: viewthread_activity.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($iscircle) {
       $allowjoinactivity = ($circle['allowshare'] && ($circle['ispublic'] == 1 || ($circle['ispublic'] == 2 && $circle['password'] == $_DCOOKIE['gidpw'.$gid]))) || $incircle;
}

$applylist = array();
$activity = $db->fetch_first("SELECT * FROM {$tablepre}activities WHERE tid='$tid'");
$activityclose = $activity['expiration'] ? ($activity['expiration'] > $timestamp - date('Z') ? 0 : 1) : 0;
$activity['starttimefrom'] = gmdate("$dateformat $timeformat", $activity['starttimefrom'] + $timeoffset * 3600);
$activity['starttimeto'] = $activity['starttimeto'] ? gmdate("$dateformat $timeformat", $activity['starttimeto'] + $timeoffset * 3600) : 0;
$activity['expiration'] = $activity['expiration'] ? gmdate("$dateformat $timeformat", $activity['expiration'] + $timeoffset * 3600) : 0;

$isverified = $applied = 0;
if($discuz_uid) {
	$query = $db->query("SELECT verified FROM {$tablepre}activityapplies WHERE tid='$tid' AND uid='$discuz_uid'");
	if($db->num_rows($query)) {
		$isverified = $db->result($query, 0);
		$applied = 1;
	}
}

$sqlverified = $thread['authorid'] == $discuz_uid ? '' : 'AND aa.verified=1';

$query = $db->query("SELECT aa.username, aa.uid, aa.dateline, m.groupid, mf.avatar FROM {$tablepre}activityapplies aa
	LEFT JOIN {$tablepre}members m USING(uid)
	LEFT JOIN {$tablepre}memberfields mf USING(uid)
	WHERE aa.tid='$tid' $sqlverified ORDER BY aa.dateline DESC LIMIT 9");
while($activityapplies = $db->fetch_array($query)) {
	$activityapplies['dateline'] = gmdate("$dateformat $timeformat", $activityapplies['dateline'] + $timeoffset * 3600);
	if($_DCACHE['usergroups'][$activityapplies['groupid']]['groupavatar']) {
		$activityapplies['avatar'] = '<img onload="thumbImg(this)" style="padding: 3px" width="45" height="45" src="'.$_DCACHE['usergroups'][$activityapplies['groupid']]['groupavatar'].'" border="0" alt="" />';
	} elseif($_DCACHE['usergroups'][$activityapplies['groupid']]['allowavatar'] && $activityapplies['avatar']) {
		$activityapplies['avatar'] = '<img onload="thumbImg(this)" style="padding: 3px" width="45" height="45" src="'.$activityapplies['avatar'].'" border="0" alt="" />';
	} else {
		$activityapplies['avatar'] = '<img onload="thumbImg(this)" style="padding: 3px" width="45" height="45" src="images/avatars/noavatar.gif" border="0" alt="" />';
	}
	$applylist[] = $activityapplies;
}
$applynumbers = $db->result_first("SELECT COUNT(*) FROM {$tablepre}activityapplies WHERE tid='$tid' AND verified=1");
$aboutmembers = $activity['number'] >= $applynumbers ? $activity['number'] - $applynumbers : 0;

$post = $db->fetch_first("SELECT p.*, m.uid, m.username, m.groupid, m.adminid, m.regdate, m.lastactivity, m.posts, m.digestposts, m.oltime,
	m.pageviews, m.credits, m.extcredits1, m.extcredits2, m.extcredits3, m.extcredits4, m.extcredits5, m.extcredits6,
	m.extcredits7, m.extcredits8, m.email, m.gender, m.showemail, m.invisible, m.xspacestatus, mf.nickname, mf.site,
	mf.icq, mf.qq, mf.yahoo, mf.msn, mf.taobao, mf.alipay, mf.location, mf.medals, mf.avatar, mf.avatarwidth,
	mf.avatarheight, mf.sightml AS signature, mf.customstatus, mf.spacename $fieldsadd
	FROM {$tablepre}posts p
	LEFT JOIN {$tablepre}members m ON m.uid=p.authorid
	LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
	WHERE p.tid='$tid' AND first=1 LIMIT 1");

$pid = $post['pid'];
$postlist[$post['pid']] = viewthread_procpost($post);

if($attachpids) {
	require_once DISCUZ_ROOT.'./include/attachment.func.php';
	parseattach($attachpids, $attachtags, $postlist, $showimages);
}

viewthread_parsetags();

$post = $postlist[$post['pid']];

include template('viewthread_activity');
exit;

?>
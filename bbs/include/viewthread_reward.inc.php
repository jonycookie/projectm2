<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: viewthread_reward.inc.php 9806 2007-08-15 06:04:37Z cnteacher $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$rewardprice = abs($thread['price']);

$limit = $thread['price'] < 0 ? 2 : 1;
$query = $db->query("SELECT p.*, m.uid, m.username, m.groupid, m.adminid, m.regdate, m.lastactivity, m.posts, m.digestposts, m.oltime,
	m.pageviews, m.credits, m.extcredits1, m.extcredits2, m.extcredits3, m.extcredits4, m.extcredits5, m.extcredits6,
	m.extcredits7, m.extcredits8, m.email, m.gender, m.showemail, m.invisible, m.xspacestatus, mf.nickname, mf.site,
	mf.icq, mf.qq, mf.yahoo, mf.msn, mf.taobao, mf.alipay, mf.location, mf.medals, mf.avatar, mf.avatarwidth,
	mf.avatarheight, mf.sightml AS signature, mf.customstatus, mf.spacename $fieldsadd
	FROM {$tablepre}posts p
	LEFT JOIN {$tablepre}members m ON m.uid=p.authorid
	LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
	WHERE p.tid='$tid' ORDER BY dateline LIMIT $limit");

$bapid = 0;$bestpost = array();
while($post = $db->fetch_array($query)) {
	if($post['first'] == 1) {
		$pid = $post['pid'];
	} else {
		$thread['price'] < 0 && $bapid = $post['pid'];
	}
	$postlist[$post['pid']] = viewthread_procpost($post);
}

if($attachpids) {
	require_once DISCUZ_ROOT.'./include/attachment.func.php';
	parseattach($attachpids, $attachtags, $postlist, $showimages);
}

viewthread_parsetags();

$post = $postlist[$pid];
$bapid && $bestpost = $postlist[$bapid];

include template('viewthread_reward');
exit;

?>
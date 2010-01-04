<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: viewthread_debate.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$debate = $thread;
$debate = $db->fetch_first("SELECT * FROM {$tablepre}debates WHERE tid='$tid'");
$debate['dbendtime'] = $debate['endtime'];
if($debate['dbendtime']) {
	$debate['endtime'] = gmdate("$dateformat $timeformat", $debate['dbendtime'] + $timeoffset * 3600);
}
if($debate['dbendtime'] > $timestamp) {
	$debate['remaintime'] = remaintime($debate['dbendtime'] - $timestamp);
}
$debate['starttime'] = gmdate("$dateformat $timeformat", $debate['starttime'] + $timeoffset * 3600);
$debate['affirmpoint'] = discuzcode($debate['affirmpoint'], 0, 0, 0, 1, 1, 0, 0, 0, 0, 0);
$debate['negapoint'] = discuzcode($debate['negapoint'], 0, 0, 0, 1, 1, 0, 0, 0, 0, 0);
$debate['affirmvoteswidth'] = $debate['affirmvotes']  ? intval(80 * (($debate['affirmvotes'] + 1) / ($debate['affirmvotes'] + $debate['negavotes'] + 1))) : 1;
$debate['negavoteswidth'] = $debate['negavotes']  ? intval(80 * (($debate['negavotes'] + 1) / ($debate['affirmvotes'] + $debate['negavotes'] + 1))) : 1;
if($debate['umpirepoint']) {
	$debate['umpirepoint'] = discuzcode($debate['umpirepoint'], 0, 0, 0, 1, 1, 1, 0, 0, 0, 0);
}
$debate['umpireurl'] = rawurlencode($debate['umpire']);
list($debate['bestdebater'], $debate['bestdebateruid'], $debate['bestdebaterstand'], $debate['bestdebatervoters'], $debate['bestdebaterreplies']) = explode("\t", $debate['bestdebater']);
$debate['bestdebaterurl'] = rawurlencode($debate['bestdebater']);

if(empty($do) || $do == 'viewdebate') {

	$post = $db->fetch_first("SELECT p.*, m.uid, m.username, m.groupid, m.adminid, m.regdate, m.lastactivity, m.posts, m.digestposts, m.oltime,
			m.pageviews, m.credits, m.extcredits1, m.extcredits2, m.extcredits3, m.extcredits4, m.extcredits5, m.extcredits6,
			m.extcredits7, m.extcredits8, m.email, m.gender, m.showemail, m.invisible, m.xspacestatus, mf.nickname, mf.site,
			mf.icq, mf.qq, mf.yahoo, mf.msn, mf.taobao, mf.alipay, mf.location, mf.medals, mf.avatar, mf.avatarwidth,
			mf.avatarheight, mf.customstatus, mf.spacename $fieldsadd
			FROM {$tablepre}posts p
			LEFT JOIN {$tablepre}members m ON m.uid=p.authorid
			LEFT JOIN {$tablepre}memberfields mf ON mf.uid=m.uid
			WHERE tid='$tid' AND first=1 LIMIT 1");
	$post = array_merge($debate, $post);
	$pid = $post['pid'];
	$postlist[$post['pid']] = viewthread_procpost($post);
	if($attachpids) {
		require_once DISCUZ_ROOT.'./include/attachment.func.php';
		parseattach($attachpids, $attachtags, $postlist, $showimages);
	}

	viewthread_parsetags();

	$post = $postlist[$post['pid']];

	if($fastpost && $allowpostreply && $thread['closed'] == 0) {
		$standquery = $db->query("SELECT stand FROM {$tablepre}debateposts WHERE tid='$tid' AND uid='$discuz_uid' AND stand<>'0' ORDER BY dateline LIMIT 1");
		$firststand = $db->result($standquery, 0);
		$firststandselect[$firststand] = ' selected="selected"';
		$firststanddisabled = $firststand != 0 ? ' disabled="disabled"' : '';
	}

	include template('viewthread_debate');
	exit;

} elseif($do == 'viewdebatepost') {

	$ppp = $forum['threadcaches'] && !$discuz_uid ? $_DCACHE['settings']['postperpage'] : $ppp;

	$debatesqladd = $debateurladd = $sqladd = '';
	$rows = $thread['replies'];
	if(isset($stand)) {
		switch($stand = intval($stand)) {
			case 0: $rows = $thread['replies'] - $debate['affirmreplies'] - $debate['negareplies']; $debatesqladd = "AND dp.stand='$stand'"; break;
			case 1: $rows = $debate['affirmreplies']; $debatesqladd = "AND dp.stand='$stand'"; break;
			case 2: $rows = $debate['negareplies']; $debatesqladd = "AND dp.stand='$stand'"; break;
			case 3: $rows = $debate['bestdebaterreplies']; $debatesqladd = "AND dp.uid='$debate[bestdebateruid]'"; break;
		}
		$debateurladd = "&amp;stand=$stand";

	}

	$start_limit = $numpost = ($page - 1) * $ppp;
	if($start_limit > $rows) {
		$start_limit = $numpost = 0;
		$page = 1;
	}

	$multipage = multi($rows, $ppp, $page, "viewthread.php?do=viewdebatepost&tid=$tid$debateurladd&amp;extra=$extra".(isset($highlight) ? "&amp;highlight=".rawurlencode($highlight) : ''));

	$query = $db->query("SELECT p.*,m.username,m.adminid,m.groupid,m.credits,dp.stand,dp.voters FROM {$tablepre}posts p
		LEFT JOIN {$tablepre}members m ON m.uid=p.authorid
		LEFT JOIN {$tablepre}debateposts dp ON p.pid=dp.pid
		WHERE p.tid='$tid' AND p.invisible='0' AND p.first='0' $debatesqladd ORDER BY p.dateline LIMIT $start_limit, $ppp");

	$postlist = $post = array();
	while($post = $db->fetch_array($query)) {
		$post['stand'] = intval($post['stand']);
		$postlist[$post['pid']] = viewthread_procpost($post, 1);
	}
	ksort($postlist);

	if($attachpids) {
		require_once DISCUZ_ROOT.'./include/attachment.func.php';
		parseattach($attachpids, $attachtags, $postlist, $showimages);
	}


	$standchecked = array((!isset($stand) ? 'all' : $stand) => 'class="current"');

	include template('viewthread_debate_posts');
	exit;

}

?>
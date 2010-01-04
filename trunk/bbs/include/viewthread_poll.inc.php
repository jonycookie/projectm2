<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: viewthread_poll.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if($iscircle) {
        $allowvote = ($circle['allowshare'] && ($circle['ispublic'] == 1 || ($circle['ispublic'] == 2 && $circle['password'] == $_DCOOKIE['gidpw'.$gid]))) || $incircle;
}

$polloptions = array();
$votersuid = '';

if($count = $db->fetch_first("SELECT MAX(votes) AS max, SUM(votes) AS total FROM {$tablepre}polloptions WHERE tid = '$tid'")) {

	// $query = $db->query("SELECT MAX(votes) AS max, SUM(votes) AS total FROM {$tablepre}polloptions WHERE tid = '$tid' GROUP BY tid");

	$options = $db->fetch_first("SELECT multiple, visible, maxchoices, expiration FROM {$tablepre}polls WHERE tid='$tid'");
	$multiple = $options['multiple'];
	$visible = $options['visible'];
	$maxchoices = $options['maxchoices'];
	$expiration = $options['expiration'];

	$query = $db->query("SELECT polloptionid, votes, polloption, voterids FROM {$tablepre}polloptions WHERE tid='$tid' ORDER BY displayorder");
	$voterids = '';
	while($options = $db->fetch_array($query)) {
		$viewvoteruid[] = $options['voterids'];
		$voterids .= "\t".$options['voterids'];
		$polloptions[] = array
		(
		'polloptionid'	=> $options['polloptionid'],
		'polloption'	=> preg_replace("/\[url=(https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|synacast){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i",
			"<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $options['polloption']),
		'votes'		=> $options['votes'],
		'width'		=> @round($options['votes'] * 300 / $count['max']) + 2,
		'percent'	=> @sprintf("%01.2f", $options['votes'] * 100 / $count['total'])
		);
	}

	$voterids = explode("\t", $voterids);
	$voters = array_unique($voterids);
	$voterscount = count($voters) - 1;
	array_shift($voters);

	if(!$expiration) {
		$expirations = $timestamp + 86400;
	} else {
		$expirations = $expiration;
		if($expirations > $timestamp) {
			$thread['remaintime'] = remaintime($expirations - $timestamp);
		}
	}

	$allowvote = $allowvote && (empty($thread['closed']) || $alloweditpoll) && !in_array(($discuz_uid ? $discuz_uid : $onlineip), $voters) && $timestamp < $expirations && $expirations > 0;
	$optiontype = $multiple ? 'checkbox' : 'radio';
	$visiblepoll = $visible || $forum['ismoderator'] || ($discuz_uid && $discuz_uid == $thread['authorid']) || ($expirations >= $timestamp && in_array(($discuz_uid ? $discuz_uid : $onlineip), $voters)) ? 0 : 1;
} else {
	$db->query("UPDATE {$tablepre}threads SET special='0' WHERE tid='$tid'", 'UNBUFFERED');
}

$post = $db->fetch_first("SELECT p.*, m.uid, m.username, m.groupid, m.adminid, m.regdate, m.lastactivity, m.posts, m.digestposts, m.oltime,
	m.pageviews, m.credits, m.extcredits1, m.extcredits2, m.extcredits3, m.extcredits4, m.extcredits5, m.extcredits6,
	m.extcredits7, m.extcredits8, m.email, m.gender, m.showemail, m.invisible, m.xspacestatus, mf.nickname, mf.site,
	mf.icq, mf.qq, mf.yahoo, mf.msn, mf.taobao, mf.alipay, mf.location, mf.medals, mf.avatar, mf.avatarwidth,
	mf.avatarheight, mf.customstatus, mf.spacename $fieldsadd
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

include template('viewthread_poll');
exit;

?>
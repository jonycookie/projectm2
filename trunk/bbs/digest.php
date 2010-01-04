<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: digest.php 11169 2007-11-08 06:30:00Z tiger $
*/

require_once './include/common.inc.php';
require_once DISCUZ_ROOT.'./include/misc.func.php';
require_once DISCUZ_ROOT.'./forumdata/cache/cache_forums.php';
require_once DISCUZ_ROOT.'./forumdata/cache/cache_icons.php';

$page = max(1, intval($page));
$page = $threadmaxpages && $page > $threadmaxpages ? 1 : $page;
$startlimit = ($page - 1) * $tpp;

$forumsarray = array();
if(!empty($forums)) {
	foreach((is_array($forums) ? $forums : explode('_', $forums)) as $forum) {
		if($forum = intval(trim($forum))) {
			$forumsarray[] = $forum;
		}
	}
}

$fids = '0';
$forumlist = $forumcheck = array();
foreach($_DCACHE['forums'] as $fid => $forum) {
	if($forum['type'] != 'group' && (!$forum['viewperm'] && $readaccess) || ($forum['viewperm'] && forumperm($forum['viewperm']))) {
		$forumlist[] = array('fid' => $fid, 'name' => $forum['name']);
		if(!$forumsarray || in_array($fid, $forumsarray)) {
			$fids .= ','.$fid;
			$forumcheck[$fid] = 'checked="checked"';
		}
	}
}

if(!empty($author)) {
	$authorid = ($member = $db->fetch_first("SELECT uid FROM {$tablepre}members WHERE username='$author'")) ? $member['uid'] : 0;
}

$authoradd = !empty($authorid) ? "AND authorid='$authorid'" : '';
$keywordadd = !empty($keyword) ? "AND subject LIKE '%$keyword%'" : '';

$threadcount = $db->result_first("SELECT COUNT(*) FROM {$tablepre}threads WHERE digest>'0' AND fid IN ($fids) AND displayorder>='0' $authoradd $keywordadd");

if(!$threadcount) {
	showmessage('digest_nonexistence');
}

if(empty($order) || !in_array($order, array('dateline', 'lastpost', 'replies', 'views'))) {
	$order = 'digest';
}
$ordercheck = array($order => 'selected="selected"');

$forumsarray = $threadlist = array();
$query = $db->query("SELECT * FROM {$tablepre}threads WHERE digest>'0' AND fid IN ($fids) AND displayorder>='0' $authoradd $keywordadd ORDER BY $order DESC LIMIT $startlimit, $tpp");
while($thread = $db->fetch_array($query)) {
	$threadauthor = $thread['author'];
	$threadlist[] = procthread($thread);
}

$multipage = multi($threadcount, $tpp, $page, "digest.php?order=$order&keyword=".rawurlencode($keyword)."&amp;authorid=$authorid&amp;forums=".str_replace(',', '_', $fids), $threadmaxpages);
$keyword = dhtmlspecialchars($keyword);
$author = $authorid ? dhtmlspecialchars($threadauthor) : '';

include template('digest');

?>
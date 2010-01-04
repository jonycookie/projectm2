<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: forum.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$forum = $db->fetch_first("SELECT * FROM {$tablepre}forums f
	LEFT JOIN {$tablepre}forumfields ff USING (fid)
	WHERE f.fid='$fid' AND f.status>0 AND f.type<>'group' AND ff.password=''");

if($forum['redirect']) {
	header("Location: $forum[redirect]");
	exit();
}

$page = max(1, intval($page));

$navtitle = ($forum['type'] == 'sub' ? ' - '.strip_tags($_DCACHE['forums'][$forum['fup']]['name']) : '').
	strip_tags($forum['name']).'('.$lang['page'].' '.$page.') - ';

require_once './include/header.inc.php';

?>
<div id="nav">
<?

if(!$forum || !forumperm($forum['viewperm']) || !forumformulaperm($forum['formulaperm'])) {

?>
<a href="archiver/"><?=$_DCACHE['settings']['bbname']?></a></div>
<div><?=$lang['forum_nonexistence']?></div>
<?

} else {

	$navsub = $forum['type'] == 'sub' ? "<a href=\"archiver/{$qm}fid-$forum[fup].html\">{$_DCACHE[forums][$forum[fup]][name]}</a> &raquo; ": ' ';
	$fullversion = array('title' => $forum['name'], 'link' => "forumdisplay.php?fid=$fid");

	$tpp = $_DCACHE['settings']['topicperpage'] * 2;
	$start = ($page - 1) * $tpp;

?>
<a href="archiver/"><?=$_DCACHE['settings']['bbname']?></a> &raquo; <?=$navsub?><a href="archiver/<?=$qm?>fid-<?=$fid?>.html"><?=$forum['name']?></a></div>
<h1><a href="<?=$fullversion['link']?>" target="_blank"><?=$lang['full_version']?>: <?=$fullversion['title']?></a></h1>
<?

	echo "<ul class=\"archiver_threadlist\" type=\"1\" start=\"".($start + 1)."\">\n";

	$query = $db->query("SELECT * FROM {$tablepre}threads WHERE fid='$fid' AND displayorder>='0' ORDER BY displayorder DESC, lastpost DESC LIMIT $start, $tpp");
	while($thread = $db->fetch_array($query)) {
		echo "<li><a href=\"archiver/{$qm}tid-$thread[tid].html\">$thread[subject]</a> <em>($thread[replies] $lang[replies])</em></li>\n";
	}

	echo "</ul>\n";
	echo "<div class=\"archiver_pages\">".multi($forum['threads'], $page, $tpp, "{$qm}fid-$fid")."</div>";

}

?>

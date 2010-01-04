<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: thread.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$thread = $db->fetch_first("SELECT * FROM {$tablepre}threads t
	LEFT JOIN {$tablepre}forums f ON f.fid=t.fid
	LEFT JOIN {$tablepre}forumfields ff ON ff.fid=f.fid
	WHERE t.tid='$tid' AND t.readperm='0' AND t.price<='0' AND t.displayorder>='0'
	AND f.status>0 AND ff.password=''");

$page = max(1, intval($page));

$navtitle = $thread['subject'].	'('.$lang['page'].' '.$page.') '.
	($thread['type'] == 'sub' ? ' - '.strip_tags($_DCACHE['forums'][$thread['fup']]['name']) : '').' - '.strip_tags($thread['name']).' - ';
echo $_DSESSION['extcredits2'];
if(!$thread || !(!$thread['viewperm'] || ($thread['viewperm'] && forumperm($thread['viewperm']))) || !forumformulaperm($thread['formulaperm'])) {
	$navtitle = '';
	require_once './include/header.inc.php';
?>
<div id="nav"><a href="archiver/"><?=$_DCACHE['settings']['bbname']?></a></div><br />
<div class="simpletable smalltxt"><div class="subtable altbg2"><br /><?=$lang['thread_nonexistence']?><br /><br /></div></div>
<?

} else {

	$navsub = $thread['type'] == 'sub' ? " <a href=\"archiver/{$qm}fid-$thread[fup].html\">{$_DCACHE[forums][$thread[fup]][name]}</a> <b>&raquo;</b> ": ' ';
	$fullversion = array('title' => $thread['subject'], 'link' => "viewthread.php?tid=$tid");

	$ppp = $_DCACHE['settings']['postperpage'] * 2;
	$start = ($page - 1) * $ppp;

	$query = $db->query("SELECT p.author, p.dateline, p.subject, p.message, p.anonymous, p.status, m.groupid
		FROM {$tablepre}posts p
		LEFT JOIN {$tablepre}members m ON p.authorid=m.uid
		WHERE p.tid='$tid' AND p.invisible='0'
		ORDER BY dateline LIMIT $start, $ppp");

	if($firstpost = $db->fetch_array($query)) {
		if(in_array($firstpost['groupid'], array(4, 5, 6))) {
			include_once language('misc');
			$firstpost['message'] = $language['post_banned'];
		} elseif($firstpost['status'] == 1) {
			include_once language('misc');
			$firstpost['message'] = $language['post_single_banned'];
		}
		$meta_contentadd = cutstr(strip_tags(str_replace(array("\r", "\n", "\t"), array('', '', ''), $firstpost['message'])), 200);
	}

	require_once './include/header.inc.php';
?>
<div id="nav">
<a href="archiver/"><?=$_DCACHE['settings']['bbname']?></a> &raquo;<?=$navsub?><a href="archiver/<?=$qm?>fid-<?=$thread['fid']?>.html"><?=$thread['name']?></a> &raquo; <?=$thread[subject]?></div>
<h1><a href="<?=$fullversion['link']?>" target="_blank"><?=$lang['full_version']?>: <?=$fullversion['title']?></a></h1>
<?
	while(($post = $firstpost) || ($post = $db->fetch_array($query))) {
		if(in_array($post['groupid'], array(4, 5, 6))) {
			include_once language('misc');
			$post['message'] = $language['post_banned'];
		} elseif($post['status'] == 1) {
			include_once language('misc');
			$post['message'] = $language['post_single_banned'];
		}
		if(!empty($firstpost)) $firstpost = array();
		$post['dateline'] = gmdate($_DCACHE['settings']['dateformat'].' '.$_DCACHE['settings']['timeformat'], $post['dateline'] + $_DCACHE['settings']['timeoffset'] * 3600);
		$post['message'] = ($post['subject'] ? '<h2>'.$post['subject'].'</h2>' : '').nl2br(preg_replace(array('/&amp;(#\d{3,5};)/', "/\[hide=?\d*\](.+?)\[\/hide\]/is"),
			array('&\\1', '<b>**** Hidden Message *****</b>'),
			str_replace(array('&', '"', '<', '>', "\t", '   ', '  '),
			array('&amp;', '&quot;', '&lt;', '&gt;', '&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'),
			$post['message'])));
		if($thread['jammer']) {
			$post['message'] =  preg_replace("/\<br \/\>/e", "jammer()", $post['message']);
		}
		$post['author'] = !$post['anonymous'] ? $post['author'] : $lang['anonymous'];

?>
<div class="archiver_post">
	<p><cite><?=$post['author']?></cite> <?=$post['dateline']?></p>
	<div class="archiver_postbody"><?=$post['message']?></div>
</div>
<?

	}

	echo "<div class=\"archiver_pages\">".multi($thread['replies'] + 1, $page, $ppp, "{$qm}tid-$tid")."</div>";

}

function jammer() {
	$randomstr = '';
	for($i = 0; $i < mt_rand(5, 15); $i++) {
		$randomstr .= chr(mt_rand(0, 59)).chr(mt_rand(63, 126));
	}
	return mt_rand(0, 1) ? '<font style="font-size:0px;color:'.ALTBG2.'">'.$randomstr.'</font><br />' :
		'<br /><span style="display:none">'.$randomstr.'</span>';
}

function cutstr($string, $length) {
	$strcut = '';
	if(strlen($string) > $length) {
		for($i = 0; $i < $length - 3; $i++) {
			$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
		}
		return $strcut.' ...';
	} else {
		return $string;
	}
}

function language($file, $templateid = 0, $tpldir = '') {
	$tpldir = $tpldir ? $tpldir : TPLDIR;
	$templateid = $templateid ? $templateid : TEMPLATEID;

	$languagepack = DISCUZ_ROOT.'./'.$tpldir.'/'.$file.'.lang.php';
	if(file_exists($languagepack)) {
		return $languagepack;
	} elseif($templateid != 1 && $tpldir != './templates/default') {
		return language($file, 1, './templates/default');
	} else {
		return FALSE;
	}
}
?>
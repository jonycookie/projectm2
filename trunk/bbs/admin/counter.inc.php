<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: counter.inc.php 11349 2007-11-23 06:31:26Z cnteacher $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

if(!submitcheck('forumsubmit', 1) && !submitcheck('digestsubmit', 1) && !submitcheck('membersubmit', 1) && !submitcheck('threadsubmit', 1) && !submitcheck('movedthreadsubmit', 1) && !submitcheck('cleanupsubmit', 1)) {
shownav('menu_tools_updatecounters');

?>
<form method="post" action="admincp.php?action=counter">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['counter_forum']?></td></tr>
<tr class="altbg2">
<td width="15%"><?=$lang['counter_amount']?></td><td><input type="text" name="pertask" value="15"></td>
</tr></table><br /><center>
<input class="button" type="submit" name="forumsubmit" value="<?=$lang['submit']?>"> &nbsp;
<input class="button" type="reset" value="<?=$lang['reset']?>"></center></form><br />

<form method="post" action="admincp.php?action=counter">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['counter_digest']?></td></tr>
<tr class="altbg2">
<td width="15%"><?=$lang['counter_amount']?></td><td><input type="text" name="pertask" value="1000"></td>
</tr></table><br /><center>
<input class="button" type="submit" name="digestsubmit" value="<?=$lang['submit']?>"> &nbsp;
<input class="button" type="reset" value="<?=$lang['reset']?>"></center></form><br />

<form method="post" action="admincp.php?action=counter">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['counter_member']?></td></tr>
<tr class="altbg2">
<td width="15%"><?=$lang['counter_amount']?></td><td><input type="text" name="pertask" value="1000"></td>
</tr></table><br /><center>
<input class="button" type="submit" name="membersubmit" value="<?=$lang['submit']?>"> &nbsp;
<input class="button" type="reset" value="<?=$lang['reset']?>"></center></form><br />

<form method="post" action="admincp.php?action=counter">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['counter_thread']?></td></tr>
<tr class="altbg2">
<td width="15%"><?=$lang['counter_amount']?></td><td><input type="text" name="pertask" value="500"></td></tr>
</table><br /><center>
<input class="button" type="submit" name="threadsubmit" value="<?=$lang['submit']?>"> &nbsp;
<input class="button" type="reset" value="<?=$lang['reset']?>"></center></form><br />

<form method="post" action="admincp.php?action=counter">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['counter_moved_thread']?></td></tr>
<tr class="altbg2">
<td width="15%"><?=$lang['counter_amount']?></td><td><input type="text" name="pertask" value="100"></td></tr>
</table><br /><center>
<input class="button" type="submit" name="movedthreadsubmit" value="<?=$lang['submit']?>"> &nbsp;
<input class="button" type="reset" value="<?=$lang['reset']?>"></center></form><br />

<form method="post" action="admincp.php?action=counter">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['counter_moved_favorites_logs']?></td></tr>
<tr class="altbg2">
<td width="15%"><?=$lang['counter_amount']?></td><td><input type="text" name="pertask" value="100"></td></tr>
</table><br /><center>
<input class="button" type="submit" name="cleanupsubmit" value="<?=$lang['submit']?>"> &nbsp;
<input class="button" type="reset" value="<?=$lang['reset']?>"></center></form>
<?

} elseif(submitcheck('forumsubmit', 1)) {

	if(!$current) {
		$current = 0;
	}
	$pertask = intval($pertask);
	$current = intval($current);
	$next = $current + $pertask;
	$nextlink = "admincp.php?action=counter&current=$next&pertask=$pertask&forumsubmit=yes";
	$processed = 0;

	$queryf = $db->query("SELECT fid FROM {$tablepre}forums WHERE type<>'group' LIMIT $current, $pertask");
	while($forum = $db->fetch_array($queryf)) {
		$processed = 1;

		extract($db->fetch_first("SELECT COUNT(*) AS threads, SUM(replies)+COUNT(*) AS posts FROM {$tablepre}threads WHERE fid='$forum[fid]' AND displayorder>='0'"));

		$thread = $db->fetch_first("SELECT tid, subject, lastpost, lastposter FROM {$tablepre}threads WHERE fid='$forum[fid]' AND displayorder>='0' ORDER BY lastpost DESC LIMIT 1");
		$lastpost = addslashes("$thread[tid]\t$thread[subject]\t$thread[lastpost]\t$thread[lastposter]");

		$db->query("UPDATE {$tablepre}forums SET threads='$threads', posts='$posts', lastpost='$lastpost' WHERE fid='$forum[fid]'");
	}

	if($processed) {
		cpmsg("$lang[counter_forum]: $lang[counter_processing]", $nextlink);
	} else {
		$db->query("UPDATE {$tablepre}forums SET threads='0', posts='0' WHERE type='group'");
		cpmsg('counter_forum_succeed');
	}

} elseif(submitcheck('digestsubmit', 1)) {

	if(!$current) {
		$db->query("UPDATE {$tablepre}members SET digestposts=0", 'UNBUFFERED');
		$current = 0;
	}
	$pertask = intval($pertask);
	$current = intval($current);
	$next = $current + $pertask;
	$nextlink = "admincp.php?action=counter&current=$next&pertask=$pertask&digestsubmit=yes";
	$processed = 0;
	$membersarray = $postsarray = array();

	$query = $db->query("SELECT authorid FROM {$tablepre}threads WHERE digest<>'0' AND displayorder>='0' LIMIT $current, $pertask");
	while($thread = $db->fetch_array($query)) {
		$processed = 1;
		$membersarray[$thread['authorid']]++;
	}

	foreach($membersarray as $uid => $posts) {
		$postsarray[$posts] .= ','.$uid;
	}
	unset($membersarray);

	foreach($postsarray as $posts => $uids) {
		$db->query("UPDATE {$tablepre}members SET digestposts=digestposts+'$posts' WHERE uid IN (0$uids)", 'UNBUFFERED');
	}

	if($processed) {
		cpmsg("$lang[counter_digest]: $lang[counter_processing]", $nextlink);
	} else {
		cpmsg('counter_digest_succeed');
	}

} elseif(submitcheck('membersubmit', 1)) {

	if(!$current) {
		$current = 0;
	}
	$pertask = intval($pertask);
	$current = intval($current);
	$next = $current + $pertask;
	$nextlink = "admincp.php?action=counter&current=$next&pertask=$pertask&membersubmit=yes";
	$processed = 0;

	$queryt = $db->query("SELECT uid FROM {$tablepre}members LIMIT $current, $pertask");
	while($mem = $db->fetch_array($queryt)) {
		$processed = 1;
		$query = $db->query("SELECT COUNT(*) FROM {$tablepre}posts WHERE authorid='$mem[uid]' AND invisible='0'");
		$db->query("UPDATE {$tablepre}members SET posts='".$db->result($query, 0)."' WHERE uid='$mem[uid]'");
	}

	if($processed) {
		cpmsg("$lang[counter_member]: $lang[counter_processing]", $nextlink);
	} else {
		cpmsg('counter_member_succeed');
	}

} elseif(submitcheck('threadsubmit', 1)) {

	if(!$current) {
		$current = 0;
	}
	$pertask = intval($pertask);
	$current = intval($current);
	$next = $current + $pertask;
	$nextlink = "admincp.php?action=counter&current=$next&pertask=$pertask&threadsubmit=yes";
	$processed = 0;

	$queryt = $db->query("SELECT tid, replies FROM {$tablepre}threads WHERE displayorder>='0' LIMIT $current, $pertask");
	while($threads = $db->fetch_array($queryt)) {
		$processed = 1;
		$replynum = $db->result_first("SELECT COUNT(*) FROM {$tablepre}posts WHERE tid='$threads[tid]' AND invisible='0'");
		$replynum--;
		if($threads['replies'] != $replynum) {
			$db->query("UPDATE LOW_PRIORITY {$tablepre}threads SET replies='$replynum' WHERE tid='$threads[tid]'", 'UNBUFFERED');
		}
	}

	if($processed) {
		cpmsg("$lang[counter_thread]: $lang[counter_processing]", $nextlink);
	} else {
		cpmsg('counter_thread_succeed');
	}

} elseif(submitcheck('movedthreadsubmit', 1)) {

	if(!$current) {
		$current = 0;
	}
	$pertask = intval($pertask);
	$current = intval($current);
	$next = $current + $pertask;
	$nextlink = "admincp.php?action=counter&current=$next&pertask=$pertask&movedthreadsubmit=yes";
	$processed = 0;

	$tids = 0;
	$query = $db->query("SELECT t1.tid, t2.tid AS threadexists FROM {$tablepre}threads t1
		LEFT JOIN {$tablepre}threads t2 ON t2.tid=t1.closed AND t2.displayorder>='0'
		WHERE t1.closed>'1' LIMIT $current, $pertask");

	while($thread = $db->fetch_array($query)) {
		$processed = 1;
		if($thread['threadexists']) {
			$tids .= ','.$thread['tid'];
		}
	}

	if($tids) {
		$db->query("DELETE FROM {$tablepre}threads WHERE tid IN ($tids)", 'UNBUFFERED');
	}

	if($processed) {
		cpmsg("$lang[counter_moved_thread]: $lang[counter_processing]", $nextlink);
	} else {
		cpmsg('counter_moved_thread_succeed');
	}

} elseif(submitcheck('cleanupsubmit', 1)) {

	if(!$current) {
		$current = 0;
	}
	$pertask = intval($pertask);
	$current = intval($current);
	$next = $current + $pertask;
	$nextlink = "admincp.php?action=counter&current=$next&pertask=$pertask&cleanupsubmit=yes";
	$processed = 0;

	$queryt = $db->query("SELECT tid,fid FROM {$tablepre}favorites LIMIT $current, $pertask");
	while($fav = $db->fetch_array($queryt)) {
		$processed = 1;
		if($fav['tid']) {
			$favtid[]= $fav['tid'];
		} elseif($fav['fid']) {
			$favfid[]= $fav['fid'];
		}
	}

	if(!empty($favtid)) {
		foreach($favtid as $tid) {
			if(!$db->result_first("SELECT tid FROM {$tablepre}threads WHERE tid='$tid'")) {
				$db->query("DELETE FROM {$tablepre}favorites WHERE tid='$tid'");
				$db->query("DELETE FROM {$tablepre}rewardlog WHERE tid='$tid'");
			}
		}
	}

	if(!empty($favfid)) {
		foreach($favfid as $fid) {
			if(!$db->result_first("SELECT fid FROM {$tablepre}forums WHERE fid='$fid'")) {
				$db->query("DELETE FROM {$tablepre}favorites WHERE fid='$fid'");
			}
		}
	}

	if($processed) {
		cpmsg("$lang[counter_moved_favorites_logs]: $lang[counter_processing]", $nextlink);
	} else {
		cpmsg('counter_moved_favorites_logs_succeed');
	}

}

?>
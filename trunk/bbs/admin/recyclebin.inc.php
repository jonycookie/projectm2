<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: recyclebin.inc.php 9769 2007-08-14 02:08:43Z cnteacher $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

require_once DISCUZ_ROOT.'./include/post.func.php';
require_once DISCUZ_ROOT.'./include/discuzcode.func.php';

cpheader();

if(!submitcheck('rbsubmit')) {

	require_once DISCUZ_ROOT.'./include/forum.func.php';

	$forumselect = '<select name="inforum"><option value="">&nbsp;&nbsp;> '.$lang['select'].'</option>'.
		'<option value="">&nbsp;</option>'.forumselect().'</select>';

	if($inforum) {
		$forumselect = preg_replace("/(\<option value=\"$inforum\")(\>)/", "\\1 selected=\"selected\" \\2", $forumselect);
	}

	$authors = dhtmlspecialchars($authors);
	$keywords = dhtmlspecialchars($keywords);
	$admins = dhtmlspecialchars($admins);
	$pstarttime = dhtmlspecialchars($pstarttime);
	$pendtime = dhtmlspecialchars($pendtime);
	$mstarttime = dhtmlspecialchars($mstarttime);
	$mendtime = dhtmlspecialchars($mendtime);

	shownav('menu_moderate_recyclebin');

?>
<script type="text/javascript" src="include/javascript/calendar.js"></script>
<form method="post" action="admincp.php?action=recyclebin">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td colspan="2"><?=$lang['recyclebin_search']?></td>
</tr>

<tr>
<td class="altbg1" width="45%"><?=$lang['recyclebin_search_forum']?></td>
<td class="altbg2" align="right"><?=$forumselect?></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['recyclebin_search_author']?></td>
<td class="altbg2" align="right"><input type="text" name="authors" size="40" value="<?=$authors?>"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['recyclebin_search_keyword']?></td>
<td class="altbg2" align="right"><input type="text" name="keywords" size="40" value="<?=$keywords?>"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['recyclebin_search_admin']?></td>
<td class="altbg2" align="right"><input type="text" name="admins" size="40" value="<?=$admins?>"></td>
</tr>

<tr>
<td class="altbg1"><?=$lang['recyclebin_search_post_time']?></td>
<td class="altbg2" align="right">
<input type="text" name="pstarttime" size="10" value="<?=$pstarttime?>" onclick="showcalendar(event, this)"> -
<input type="text" name="pendtime" size="10" value="<?=$pendtime?>" onclick="showcalendar(event, this)">
</td>
</tr>

<tr>
<td class="altbg1"><?=$lang['recyclebin_search_mod_time']?></td>
<td class="altbg2" align="right">
<input type="text" name="mstarttime" size="10" value="<?=$mstarttime?>" onclick="showcalendar(event, this)"> -
<input type="text" name="mendtime" size="10" value="<?=$mendtime?>" onclick="showcalendar(event, this)">
</td>
</tr>

</table><br />
<center><input class="button" type="submit" name="searchsubmit" value="<?=$lang['submit']?>"></center>
</form>

<br /><form method="post" action="admincp.php?action=recyclebin&prune=yes">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header">
<td colspan="2"><?=$lang['recyclebin_prune']?></td>
</tr>

<tr>
<td class="altbg1" width="45%"><?=$lang['recyclebin_prune_days']?></td>
<td class="altbg2" align="right"><input type="text" name="days" size="40" value="30"></td>
</tr>

</table><br />
<center><input class="button" type="submit" name="rbsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

} else {

	$moderation = array('delete' => array(), 'undelete' => array(), 'ignore' => array());

	if(empty($prune)) {
		foreach($mod as $tid => $action) {
			$moderation[$action][] = intval($tid);
		}
	} else {
		$query = $db->query("SELECT tm.tid FROM {$tablepre}threadsmod tm, {$tablepre}threads t
			WHERE tm.dateline<$timestamp-'$days'*86400 AND tm.action='DEL' AND t.tid=tm.tid AND t.displayorder='-1'");
		while($thread = $db->fetch_array($query)) {
			$moderation['delete'][] = $thread['tid'];
		}
	}

	$threadsdel = $threadsundel = 0;

	if($moderation['delete']) {
		$deletetids = '\''.implode('\',\'', $moderation['delete']).'\'';

		$query = $db->query("SELECT attachment, thumb, remote FROM {$tablepre}attachments WHERE tid IN ($deletetids)");
		while($attach = $db->fetch_array($query)) {
			dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
		}

		$db->query("DELETE FROM {$tablepre}posts WHERE tid IN ($deletetids)", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}polloptions WHERE tid IN ($deletetids)", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}polls WHERE tid IN ($deletetids)", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}rewardlog WHERE tid IN ($deletetids)", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}trades WHERE tid IN ($deletetids)", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}attachments WHERE tid IN ($deletetids)", 'UNBUFFERED');
		$db->query("DELETE FROM {$tablepre}threads WHERE tid IN ($deletetids)");
		$threadsdel = $db->affected_rows();
	}

	if($moderation['undelete']) {
		$undeletetids = '\''.implode('\',\'', $moderation['undelete']).'\'';

		$tuidarray = $ruidarray = $fidarray = array();
		$query = $db->query("SELECT fid, first, authorid FROM {$tablepre}posts WHERE tid IN ($undeletetids)");
		while($post = $db->fetch_array($query)) {
			if($post['first']) {
				$tuidarray[] = $post['authorid'];
			} else {
				$ruidarray[] = $post['authorid'];
			}
			if(!in_array($post['fid'], $fidarray)) {
				$fidarray[] = $post['fid'];
			}
		}
		if($tuidarray) {
			updatepostcredits('+', $tuidarray, $creditspolicy['post']);
		}
		if($ruidarray) {
			updatepostcredits('+', $ruidarray, $creditspolicy['reply']);
		}

		$db->query("UPDATE {$tablepre}posts SET invisible='0' WHERE tid IN ($undeletetids)", 'UNBUFFERED');
		$db->query("UPDATE {$tablepre}threads SET displayorder='0', moderated='1' WHERE tid IN ($undeletetids)");
		$threadsundel = $db->affected_rows();

		updatemodlog($undeletetids, 'UDL');
		updatemodworks('UDL', $threadsundel);

		foreach($fidarray as $fid) {
			updateforumcount($fid);
		}
	}

	cpmsg('recyclebin_succeed');

}

if(submitcheck('searchsubmit')) {

	$sql = '';

	$sql .= $inforum		? " AND t.fid='$inforum'" : '';
	$sql .= $authors != ''		? " AND t.author IN ('".str_replace(',', '\',\'', str_replace(' ', '', $authors))."')" : '';
	$sql .= $admins != ''		? " AND tm.username IN ('".str_replace(',', '\',\'', str_replace(' ', '', $admins))."')" : '';
	$sql .= $pstarttime != ''	? " AND t.dateline>='".(strtotime($pstarttime) - $timeoffset * 3600)."'" : '';
	$sql .= $pendtime != ''		? " AND t.dateline<'".(strtotime($pendtime) - $timeoffset * 3600)."'" : '';
	$sql .= $mstarttime != ''	? " AND tm.dateline>='".(strtotime($mstarttime) - $timeoffset * 3600)."'" : '';
	$sql .= $mendtime != ''		? " AND tm.dateline<'".(strtotime($mendtime) - $timeoffset * 3600)."'" : '';

	if(trim($keywords)) {
		$sqlkeywords = $or = '';
		foreach(explode(',', str_replace(' ', '', $keywords)) as $keyword) {
			$sqlkeywords .= " $or t.subject LIKE '%$keyword%'";
			$or = 'OR';
		}
		$sql .= " AND ($sqlkeywords)";
	}

	$threads = '';
	$query = $db->query("SELECT f.name AS forumname, f.allowsmilies, f.allowhtml, f.allowbbcode, f.allowimgcode,
		t.tid, t.fid, t.authorid, t.author, t.subject, t.views, t.replies, t.dateline,
		p.message, p.useip, p.attachment, p.htmlon, p.smileyoff, p.bbcodeoff,
		tm.uid AS moduid, tm.username AS modusername, tm.dateline AS moddateline, tm.action AS modaction
		FROM {$tablepre}threads t
		LEFT JOIN {$tablepre}posts p ON p.tid=t.tid AND p.first='1'
		LEFT JOIN {$tablepre}threadsmod tm ON tm.tid=t.tid
		LEFT JOIN {$tablepre}forums f ON f.fid=t.fid
		WHERE t.displayorder='-1' $sql
		GROUP BY t.tid ORDER BY t.dateline DESC");

	$threadcount = $db->num_rows($query);

	while($thread = $db->fetch_array($query)) {
		$thread['message'] = discuzcode($thread['message'], $thread['smileyoff'], $thread['bbcodeoff'], sprintf('%00b', $thread['htmlon']), $thread['allowsmilies'], $thread['allowbbcode'], $thread['allowimgcode'], $thread['allowhtml']);
		$thread['moddateline'] = gmdate("$dateformat $timeformat", $thread['moddateline'] + $timeoffset * 3600);
		$thread['dateline'] = gmdate("$dateformat\<\b\\r\>$timeformat", $thread['dateline'] + $timeoffset * 3600);

		$thisbg = $thisbg == 'altbg2' ? 'altbg1' : 'altbg2';

		$threads .= "<tr class=\"$thisbg\"><td rowspan=\"2\" valign=\"top\" width=\"15%\" height=\"100%\">\n".
			"<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" height=\"100%\">\n".
			"<tr><td valign=\"top\" style=\"border:none\"><a href=\"space.php?action=viewpro&uid=$thread[authorid]\" target=\"_blank\"><b>$thread[author]</b></td></tr><tr><td style=\"border:none\">\n".
			"<input class=\"radio\" type=\"radio\" name=\"mod[$thread[tid]]\" value=\"delete\" checked>$lang[delete]<br />\n".
			"<input class=\"radio\" type=\"radio\" name=\"mod[$thread[tid]]\" value=\"undelete\">$lang[undelete]<br />\n".
			"<input class=\"radio\" type=\"radio\" name=\"mod[$thread[tid]]\" value=\"ignore\">$lang[ignore]<br /><br />\n".
			"$lang[threads_replies]: $thread[replies]<br />$lang[threads_views]: $thread[views]<br /><br />$thread[dateline]</td>\n".
			"</tr></table></td><td style=\"border:none\"><a href=\"forumdisplay.php?fid=$thread[fid]\" target=\"_blank\">$thread[forumname]</a> <b>&raquo;</b>\n".
			"<b>$thread[subject]</b></td></tr><tr class=\"$thisbg\"><td>".
			"<div style=\"border-style: dotted; border-width: 1px; border-color: ".BORDERCOLOR."; padding: 5px; overflow: auto; overflow-y: scroll; width: 95%; height:150px\">".
			"<div align=\"right\" style=\"width: 97%\">\n".
			"$lang[operator]: <a href=\"space.php?action=viewpro&uid=$thread[moduid]\" target=\"_blank\">$thread[modusername]</a> \n".
			"$lang[recyclebin_delete_time]: $thread[moddateline]</div>$thread[message]";

		if($thread['attachment']) {
			require_once DISCUZ_ROOT.'./include/attachment.func.php';
			$queryattach = $db->query("SELECT aid, filename, filetype, filesize FROM {$tablepre}attachments WHERE tid='$thread[tid]'");
			while($attach = $db->fetch_array($queryattach)) {
				$threads .= "<br /><br />$lang[attachment]: ".attachtype(fileext($thread['filename'])."\t".$attach['filetype']).
					" $attach[filename] (".sizecount($attach['filesize']).")";
			}
		}
		$threads .= "</div></td></tr>\n";
	}

?>
<br /><form method="post" action="admincp.php?action=recyclebin">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['recyclebin_result']?> <?=$threadcount?></td></tr>

<tr><td colspan="2" class="category">
<input class="button" type="button" value="<?=$lang['recyclebin_all_delete']?>" onclick="checkalloption(this.form, 'delete')"> &nbsp;
<input class="button" type="button" value="<?=$lang['recyclebin_all_undelete']?>" onclick="checkalloption(this.form, 'undelete')"> &nbsp;
<input class="button" type="button" value="<?=$lang['recyclebin_all_ignore']?>" onclick="checkalloption(this.form, 'ignore')"></td></tr>
<?=$threads?>
</table><br /><center><input class="button" type="submit" name="rbsubmit" value="<?=$lang['submit']?>"></center></form>
<?

}

?>
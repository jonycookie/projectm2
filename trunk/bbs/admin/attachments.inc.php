<?php

/*
	[Discuz!] (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: attachments.inc.php 11168 2007-11-08 05:23:57Z tiger $
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
        exit('Access Denied');
}

cpheader();

if(!submitcheck('deletesubmit') && !submitcheck('searchsubmit')) {
	require_once DISCUZ_ROOT.'./include/forum.func.php';
	shownav('menu_maint_attaches');

?>
<form method="post" action="admincp.php?action=attachments">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td colspan="2"><?=$lang['menu_maint_attaches']?></td></tr>

<tr><td class="altbg1"><?=$lang['attachments_nomatched']?></td>
<td class="altbg2" align="right"><input class="checkbox" type="checkbox" name="nomatched" value="1"></td></tr>

<tr><td class="altbg1"><?=$lang['attachments_forum']?></td>
<td class="altbg2" align="right"><select name="inforum"><option value="all">&nbsp;&nbsp;> <?=$lang['all']?></option>
<option value="">&nbsp;</option><?=forumselect()?></select></td></tr>

<tr><td class="altbg1"><?=$lang['attachments_sizeless']?></td>
<td class="altbg2" align="right"><input type="text" name="sizeless" size="40"></td></tr>

<tr><td class="altbg1"><?=$lang['attachments_sizemore']?></td>
<td class="altbg2" align="right"><input type="text" name="sizemore" size="40"></td></tr>

<tr><td class="altbg1"><?=$lang['attachments_dlcountless']?></td>
<td class="altbg2" align="right"><input type="text" name="dlcountless" size="40"></td></tr>

<tr><td class="altbg1"><?=$lang['attachments_dlcountmore']?></td>
<td class="altbg2" align="right"><input type="text" name="dlcountmore" size="40"></td></tr>

<tr><td class="altbg1"><?=$lang['attachments_daysold']?></td>
<td class="altbg2" align="right"><input type="text" name="daysold" size="40"></td></tr>

<tr><td class="altbg1"><?=$lang['attachments_filename']?></td>
<td class="altbg2" align="right"><input type="text" name="filename" size="40"></td></tr>

<tr><td class="altbg1"><?=$lang['attachments_keyword']?></td>
<td class="altbg2" align="right"><input type="text" name="keywords" size="40"></td></tr>

<tr><td class="altbg1"><?=$lang['attachments_author']?></td>
<td class="altbg2" align="right"><input type="text" name="author" size="40"></td></tr>

</table><br /><center>
<input class="button" type="submit" name="searchsubmit" value="<?=$lang['submit']?>"></center>
</form>
<?

} elseif(submitcheck('searchsubmit')) {

	require_once DISCUZ_ROOT.'./include/attachment.func.php';

	$sql = "a.pid=p.pid";
	$ppp = 100;

	if($inforum != 'all') {
		if($inforum) {
			$sql .= " AND p.fid='$inforum'";
		} else {
			cpmsg('attachments_forum_invalid');
		}
	}
	if($daysold) {
		$sql .= " AND p.dateline<='".($timestamp - (86400 * $daysold))."'";
	}
	if($author) {
		$sql .= " AND p.author='$author'";
	}
	if($filename) {
		$sql .= " AND a.filename LIKE '%$filename%'";
	}
	if($keywords) {
		$sqlkeywords = $or = '';
		foreach(explode(',', str_replace(' ', '', $keywords)) as $keyword) {
			$sqlkeywords .= " $or a.description LIKE '%$keyword%'";
			$or = 'OR';
		}
		$sql .= " AND ($sqlkeywords)";
	}
	if($sizeless) {
		$sql .= " AND a.filesize<'$sizeless'";
	}
	if($sizemore) {
		$sql .= " AND a.filesize>'$sizemore' ";
	}
	if($dlcountless) {
		$sql .= " AND a.downloads<'$dlcountless'";
	}
	if($dlcountmore) {
		$sql .= " AND a.downloads>'$dlcountmore'";
	}

	$attachments = '';
	$page = max(1, intval($page));
	$query = $db->query("SELECT a.*, p.fid, p.author, t.tid, t.tid, t.subject, f.name AS fname
		FROM {$tablepre}attachments a, {$tablepre}posts p, {$tablepre}threads t, {$tablepre}forums f
		WHERE t.tid=a.tid AND f.fid=p.fid AND t.displayorder>='0' AND p.invisible='0' AND $sql LIMIT ".(($page - 1) * $ppp).','.$ppp);
	while($attachment = $db->fetch_array($query)) {
		if(!$attachment['remote']) {
			$matched = file_exists($attachdir.'/'.$attachment['attachment']) ? '' : "$lang[attachments_lost]";
			$attachment['url'] = $attachurl;
		} else {
			@set_time_limit(0);
			if(@fclose(@fopen($ftp['attachurl'].'/'.$attachment['attachment'], 'r'))) {
				$matched = '';
			} else {
				$matched = $lang['attachments_far'];
			}
			$attachment['url'] = $ftp['attachurl'];
		}
		$attachsize = sizecount($attachment['filesize']);
		if(!$nomatched || ($nomatched && $matched)) {
			$attachments .= "<tr><td class=\"altbg1\" align=\"center\" valign=\"middle\"><input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$attachment[aid]\"></td>\n".
				"<td class=\"altbg2\" align=\"center\"><b>$attachment[filename]</b><br />$attachment[description]</td>\n".
				"<td class=\"altbg1\" align=\"center\"><b>".($matched ? $matched : "<a href=\"attachment.php?aid=$attachment[aid]\" target=\"_blank\">[$lang[attachments_download]]</a>")."</b><br /><a href=\"$attachment[url]/$attachment[attachment]\" class=\"smalltxt\" target=\"_blank\">".cutstr($attachment['attachment'], 30)."</a></td>\n".
				"<td class=\"altbg2\" align=\"center\">$attachment[author]</td>\n".
				"<td class=\"altbg1\" valign=\"middle\"><a href=\"viewthread.php?tid=$attachment[tid]\" target=\"_blank\"><b>".cutstr($attachment['subject'], 20)."</b></a><br />$lang[forum]:<a href=\"forumdisplay.php?fid=$attachment[fid]\" target=\"_blank\">$attachment[fname]</a></td>\n".
				"<td class=\"altbg2\" valign=\"middle\" align=\"center\">$attachsize</td>\n".
				"<td class=\"altbg1\" valign=\"middle\" align=\"center\">$attachment[downloads]</td></tr>\n";
		}
	}
	$attachmentcount = $db->result_first("SELECT count(*) FROM {$tablepre}attachments a, {$tablepre}posts p, {$tablepre}threads t, {$tablepre}forums f
		WHERE t.tid=a.tid AND f.fid=p.fid AND t.displayorder>='0' AND p.invisible='0' AND $sql");
	$multi = multi($attachmentcount, $ppp, $page, "admincp.php?action=attachments");
	$multi = preg_replace("/href=\"admincp.php\?action=attachments&amp;page=(\d+)\"/", "href=\"javascript:page(\\1)\"", $multi);
	$multi = str_replace("window.location='admincp.php?action=attachments&amp;page='+this.value", "page(this.value)", $multi);
	shownav('menu_maint_attaches');

?>
<script type="text/javascript">
function page(number) {
	$('attachmentforum').page.value=number;
	$('attachmentforum').searchsubmit.click();
}
</script>
<form id="attachmentforum" method="post" action="admincp.php?action=attachments" style="display:none">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="nomatched" value="<?=$nomatched?>">
<input type="hidden" name="inforum" value="<?=$inforum?>">
<input type="hidden" name="sizeless" value="<?=dhtmlspecialchars($sizeless)?>">
<input type="hidden" name="sizemore" value="<?=dhtmlspecialchars($sizemore)?>">
<input type="hidden" name="dlcountless" value="<?=dhtmlspecialchars($dlcountless)?>">
<input type="hidden" name="dlcountmore" value="<?=dhtmlspecialchars($dlcountmore)?>">
<input type="hidden" name="daysold" value="<?=dhtmlspecialchars($daysold)?>">
<input type="hidden" name="filename" value="<?=dhtmlspecialchars($filename)?>">
<input type="hidden" name="keywords" value="<?=dhtmlspecialchars($keywords)?>">
<input type="hidden" name="author" value="<?=dhtmlspecialchars($author)?>">
<input class="button" type="submit" name="searchsubmit">
</form>

<form method="post" action="admincp.php?action=attachments" target="attachmentframe">
<input type="hidden" name="formhash" value="<?=FORMHASH?>">
<?=$multi?>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tableborder">
<tr class="header"><td width="5%"><input class="checkbox" type="checkbox" name="chkall" onclick="checkall(this.form)"><?=$lang['del']?></td>
<td width="15%" align="center"><?=$lang['attachments_name']?></td>
<td width="27%" align="center"><?=$lang['filename']?></td>
<td width="15%" align="center"><?=$lang['author']?></td>
<td width="17%" align="center"><?=$lang['attachments_thread']?></td>
<td width="13%" align="center"><?=$lang['size']?></td>
<td width="13%" align="center"><?=$lang['download']?></td></tr>
<?=$attachments?>
</table>
<?=$multi?>
<center><input class="button" type="submit" name="deletesubmit" value="<?=$lang['submit']?>"></center></form><iframe name="attachmentframe" style="display:none"></iframe>
<?

} elseif(submitcheck('deletesubmit')) {

	if(is_array($delete)) {

		$ids = '\''.implode('\',\'', $delete).'\'';

		$tids = $pids = 0;
		$query = $db->query("SELECT tid, pid, attachment, thumb, remote FROM {$tablepre}attachments WHERE aid IN ($ids)");
		while($attach = $db->fetch_array($query)) {
			dunlink($attach['attachment'], $attach['thumb'], $attach['remote']);
			$tids .= ','.$attach['tid'];
			$pids .= ','.$attach['pid'];
		}
		$db->query("DELETE FROM {$tablepre}attachments WHERE aid IN ($ids)");
		$db->query("UPDATE {$tablepre}posts SET attachment='0' WHERE pid IN ($pids)");

		$attachtids = 0;
		$query = $db->query("SELECT tid FROM {$tablepre}attachments WHERE tid IN ($tids) GROUP BY tid ORDER BY pid DESC");
		while($attach = $db->fetch_array($query)) {
			$attachtids .= ','.$attach['tid'];
		}
		$db->query("UPDATE {$tablepre}threads SET attachment='' WHERE tid IN ($tids)".($attachtids ? " AND tid NOT IN ($attachtids)" : NULL));

		eval("\$cpmsg = \"".$msglang['attachments_edit_succeed']."\";");

	} else {

		eval("\$cpmsg = \"".$msglang['attachments_edit_invalid']."\";");

	}

?>
<script>alert('<?=$cpmsg?>');parent.$('attachmentforum').searchsubmit.click();</script>
<?
}

?>